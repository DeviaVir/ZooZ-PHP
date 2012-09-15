<?PHP

    // Include ZooZ library
    Require( 'lib/zooz.lib.php' );

    // You will have to do your own routing, default is empty subpage (new transaction)
    switch( $page ) {
        case 'success': // 
            $vars = explode( '&', $args[1] );
            $result = Array();
            foreach( $vars as $var ) {
                $actualVar = explode( '=', $var );
                $result[( $actualVar[0] )] = $actualVar[1];
            }
            $check = ZooZ::verifyTransaction( $result[ 'transactionID' ] );
            if( $check[ 'result' ] ) {
                print 'Paid!';
            } else {
                print 'Failed!';
            }
        break;
        case 'failed':
            // Your error handler page
        break;
        default: // Create a new transaction
            $res = ZooZ::newTransaction( '100' /* cents */, 'EUR' /* currency */, 'client@provider.ext', 'Firstname', 'Lastname' );
            if( $res[ 'result' ] ) {
                print '<script src="https://app.zooz.com/mobile/js/zooz-ext-web.js"></script>';
                print '<script type="text/javascript">
                    zoozStartCheckout({
                        token : "' . $res[ 'token' ] . '",
                        uniqueId : "com.wamtam.sweebr",
                        isSandbox : true,
                        returnUrl : window.location + "/success/",
                        cancelUrl : window.location + "/failed/"
                    });
                </script>';
            } else {
                // Error handler, transaction couldn't be opened
            }
        break;
    }

?>
