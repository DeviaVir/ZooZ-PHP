<?php
	Class ZooZ {
		private static $sandbox   = true;
		private static $uniqueID  = '';
		private static $appKey    = '';

		private static function Run() {
			if( self::$sandbox )
				$url = 'https://sandbox.zooz.co/mobile/SecuredWebServlet';
			else
				$url = 'https://app.zooz.com/mobile/SecuredWebServlet';

			if( !function_exists( 'curl_init' ) )
				return false;

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );

			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 
				'ZooZUniqueID: ' . self::$uniqueID, 
				'ZooZAppKey: ' . urlencode( self::$appKey ), 
				'ZooZResponseType: NVP' 
			) );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

			return( $ch );
		}

		public static function newTransaction( $amount, $currency, $email, $firstname, $lastname ) {
			$ch         = self::Run();
			$postFields = "cmd=openTrx&amount=" . $amount / 100 . "&currencyCode=" . $currency;

			$postFields .= "&email=" . urlencode( $email );
			$postFields .= "&firstName=" . urlencode( $firstname );
			$postFields .= "&lastName=" . urlencode( $lastname );

			curl_setopt( $ch, CURLOPT_POST, 6 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields );

			$chResult = curl_exec($ch);
			$vars = explode( '&', $chResult );
			$result = Array();
			foreach( $vars as $var ) {
				$actualVar = explode( '=', $var );
				$result[( $actualVar[0] )] = $actualVar[1];
			}

			if ($result[ 'statusCode' ] == 0) {
				// Get token from ZooZ server
				$trimmedSessionToken = rtrim($result[ 'sessionToken' ], "\n");
					
				// Send token back to page
				return( Array( 'result' => true, 'token' => $result[ 'token' ], 'sessionToken' => $result[ 'sessionToken' ], 'trimmedSessionToken' => $trimmedSessionToken ) );
			} else {
				return( Array( 'result' => false, 'token' => 0, 'sessionToken' => 0, 'trimmedSessionToken' => 0, 'error' => urldecode( $result[ 'errorMessage' ] ) ) );
			}

			curl_close($ch);
		}

		public static function verifyTransaction( $id ) {
			$ch = self::Run();

			$postFields = 'cmd=verifyTrx&trxId=' . urlencode( $id );

			curl_setopt( $ch, CURLOPT_POST, 2 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields );

			$chResult = curl_exec($ch);

			//print '<pre>'; print_r( $chResult ); print '</pre>';
			$vars = explode( '&', $chResult );
			$result = Array();
			foreach( $vars as $var ) {
				$actualVar = explode( '=', $var );
				$result[( $actualVar[0] )] = $actualVar[1];
			}

			if( $result[ 'statusCode' ] == 0 ) {
				$trimmedSessionToken = rtrim($result[ 'sessionToken' ], "\n");
				$trimmedTrxToken = rtrim($result[ 'transactionID' ], "\n");

				// Verify sessionToken with newTransaction();
				return( Array( 'result' => true, 'sessionToken' => $trimmedSessionToken, 'trxToken' => $trimmedTrxToken ) );
			} else {
				return( Array( 'result' => false, 'sessionToken' => 0, 'trxToken' => 0, 'error' => urldecode( $result[ 'errorMessage' ] ) ) );
			}
		}
	}
?>