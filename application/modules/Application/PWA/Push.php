<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    Application_PWA_Push
 * @copyright  Copyright (c) 2019 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Push.php Friday 26th of July 2019 09:33PM ayoola@ayoo.la $
 */

/**
 * @see PageCarton_Widget
 */

class Application_PWA_Push extends PageCarton_Widget
{
    
    /**
     * Access level for player. Defaults to everyone
     *
     * @var boolean
     */
    protected static $_accessLevel = array( 0 );
    
    /**
     *
     *
     * @var string
     */
    protected static $_objectTitle = 'Send a push notification';

    /**
     * Performs the whole widget running process
     *
     */
    public function init()
    {
        try {
            //  Code that runs the widget goes here...


        //    exit();
            //  Output demo content to screen
            // here I'll get the subscription endpoint in the POST parameters
            // but in reality, you'll get this information in your database
            // because you already stored it (cf. push_subscription.php)
            $subscriptionInfo = Application_PWA_Push_Subscription::getInstance()->selectOne();
        //    self::v( $subscriptionInfo );
            $header = array( "typ" => "JWT", "alg" => "ES256" );
            $header = json_encode( $header );
            $base64URL = function( $data )
            {
                $data = base64_encode( $data );
                $data = str_replace( ['+', '/', '='], ['-', '_', ''], $data );
                $data = rtrim( $data, '=' );
                return $data;
            };
            $header = $base64URL( $header );

            $urlInfo = parse_url( $subscriptionInfo['endpoint'] );
            
            $payload = array();
            $payload['aud'] = $urlInfo['scheme'] . '//' . $urlInfo['host'] . '';
            $payload['exp'] = 1564322089;
            $payload['sub'] = "mailto:info@comeriver.com";
            $payload = json_encode( $payload );
            
            $payload = $base64URL( $payload );

            $stringtosign = $header . '.' . $payload;

            $signature = "";

            $priv = 'vplfkITvu0cwHqzK9Kj-DYStbCH_9AhGx9LqMyaeI6w';
            $pub = 'BMBlr6YznhYMX3NgcWIDRxZXs0sh7tCv7_YCsWcww0ZCv9WGg-tRCXfMEHTiBPCksSqeve1twlbmVAZFv7GSuj0';
            $cert = file_get_contents( Ayoola_Application::getDomainSettings( APPLICATION_PATH ) . '/prime256v1-key.pem');

            $prkey = openssl_pkey_get_private( $cert );

            // Public key as PEM string
            $pem_public_key = openssl_pkey_get_details($prkey)['key'];
            $keys = array_map( 'trim', explode( "\n", $pem_public_key ) );
            array_pop( $keys );
            array_pop( $keys );
            array_shift( $keys );
        //    $pub = implode( '', $keys );
        //    $pub = str_replace( ['+', '/', '='], ['-', '_', ''], $pub );
        //    $pub = rtrim( $pub, '=' );
        //    self::v( $pem_public_key );
        //    self::v( $pub );
        //    openssl_sign( $stringtosign, $signature, $prkey, OPENSSL_ALGO_SHA256 );
        //    self::v( $signature );

            $signature = hash_hmac( 'sha256', $signature, $priv, true );
            $signature = $base64URL( $signature );
        //    self::v( $signature );
            $jwt = $header . "." . $payload . "." . $signature;

            $options = array();
            $options['return_as_array'] = true;
            $options['return_error_response'] = true;
            $options['post'] = true;
            $options['http_header'] = array();
            $options['http_header'][] = 'Authorization:  vapid t=' . $jwt . ', k=' . $pub ;
            $options['http_header'][] = 'TTL: 0';
            $options['http_header'][] = 'Content-length: 0';
        //    $options['http_header'][] = 'Crypto-Key: ' . $pub;

            $response = self::fetchLink( $subscriptionInfo['endpoint'], $options );
        //    self::v( $subscriptionInfo['endpoint'] );
            self::v( $options );
            self::v( $response );

            #   curl "https://updates.push.services.mozilla.com/wpush/v1/gAAAAABXmk....dyR" --request POST --header "TTL: 60" --header "Content-Length: 0" --header "Authorization: WebPush eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.eyJhdWQiOiJodHRwczovL2ZjbS5nb29nbGVhcGlzLmNvbSIsImV4cCI6MTQ2NjY2ODU5NCwic3ViIjoibWFpbHRvOnNpbXBsZS1wdXNoLWRlbW9AZ2F1bnRmYWNlLmNvLnVrIn0.Ec0VR8dtf5qb8Fb5Wk91br-evfho9sZT6jBRuQwxVMFyK5S8bhOjk8kuxvilLqTBmDXJM5l3uVrVOQirSsjq0A" --header "Crypto-Key: p256ecdsa=BDd3_hVL9fZi9Ybo2UUzA284WG5FZR30_95YeZJsiApwXKpNcF1rRPF3foIiBHXRdJI2Qhumhf6_LFTeZaNndIo"



            $this->setViewContent(self::__('<h1>Hello PageCarton Widget</h1>'));
            $this->setViewContent(self::__('<p>Customize this widget (' . __CLASS__ . ') by editing this file below:</p>'));
            $this->setViewContent(self::__('<p style="font-size:smaller;">' . __FILE__ . '</p>'));

            // end of widget process
        } catch (Exception $e) {
            //  Alert! Clear the all other content and display whats below.
            //    $this->setViewContent( self::__( '<p class="badnews">' . $e->getMessage() . '</p>' ) );
            $this->setViewContent(self::__('<p class="badnews">Theres an error in the code</p>'));
            return false;
        }
    }
    // END OF CLASS
}
