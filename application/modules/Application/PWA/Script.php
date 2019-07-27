<?php

/**
 * PageCarton Content Management System
 *
 * LICENSE
 *
 * @category   PageCarton CMS
 * @package    Application_PWA_Script
 * @copyright  Copyright (c) 2018 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Script.php Monday 31st of December 2018 02:42PM ayoola.falola@yahoo.com $
 */

/**
 * @see PageCarton_Widget
 */

class Application_PWA_Script extends PageCarton_Widget
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
	protected static $_objectTitle = 'PWA JS Script'; 

    /**
     * 
     * 
     */
	public static function hook( $object, $method, & $data = null )
    {    
		switch( $method )
		{
			case '__construct':			
				$scope = Ayoola_Application::getUrlPrefix() . '';
                $file = file_get_contents( Ayoola_Doc_Browser::getDocumentsDirectory() . DS . '/js/pwa-init.js' );
                $js =
                '
                <script>
                    ' . $file .  '
                    let pwaScope = "' . $scope .  '";
                    window.addEventListener( "load", function()
                    {
                        runPWA( 
                            { 
                                "serviceWorkerUrl": "' . Ayoola_Application::getUrlPrefix()  .  '/widgets/' . __CLASS__ . '",
                                "scope": "' . $scope .  '",
                                "description": "' . Application_PWA_Settings::retrieve( 'description' ) .  '",
                                "shortname": "' . Application_PWA_Settings::retrieve( 'short_name' ) .  '"
                            } 
                        );
                    }
                );
				</script>
                ';
                echo $js;
            break;
		}
	//	var_export( $object );
	//	var_export( $method );     
	//	var_export( $data );
	}

    /**
     * Performs the whole widget running process
     * 
     */
	public function init()
    {    
		try
		{ 
			$scope = Ayoola_Application::getUrlPrefix() . ( Application_PWA_Settings::retrieve( 'start_url' )  ? : '/' );
            //  Code that runs the widget goes here...
			$jsToInclude = array_values( Application_Javascript::getFilesUrl() );
			$cssToInclude = array_values( Application_Style::getFilesUrl() );
			
			//	pages
			$pages = Application_PWA_Settings::retrieve( 'pages_to_cache' ) ? : Ayoola_Page::getAll();
		//	unset( $pages['/404'] );
			foreach( $pages as $key => $each )
			{
				if( ! $pageInfo = Ayoola_Page::getInfo( $each ) OR ( ! empty( $pageInfo['auth_level'] ) && ! self::hasPriviledge( $pageInfo['auth_level'] ) ) )
				{
					unset( $pages[$key] );
				//	var_export( $each );
					continue;
				}
				$pages[$key] = Ayoola_Application::getUrlPrefix() . $each;
			}
            $pages = array_values( $pages );
            
			//	pages
			$static = Application_PWA_Settings::retrieve( 'static_pages_to_cache' ) ? : array();
		//	unset( $pages['/404'] );
			foreach( $static as $key => $each )
			{
				if( ! $pageInfo = Ayoola_Page::getInfo( $each ) OR ( ! empty( $pageInfo['auth_level'] ) && ! self::hasPriviledge( $pageInfo['auth_level'] ) ) )
				{
					unset( $static[$key] );
				//	var_export( $each );
					continue;
				}
				$static[$key] = Ayoola_Application::getUrlPrefix() . $each;
			}
			$static = array_values( $static );
			
			$defaultPages = array_values( array_unique( array( Ayoola_Application::getUrlPrefix() . '/', $scope, Ayoola_Application::getUrlPrefix() . '/offline', Ayoola_Application::getUrlPrefix() . '/500' ) ) );  
			$static = array_values( $static );  
		//	var_export( $pages );
			$cache = array_values( array_unique( array_merge( $jsToInclude, $cssToInclude, $pages ) ) );
			
			$widgets = Application_PWA_Settings::retrieve( 'widget_to_cache' );
			$widgets = $widgets ? $widgets : array();
			$widgets += array( 'Application_PWA_Manifest' );
			$widgetsText = trim( implode( '|\\/', $widgets ), '\\/|' );
			$widgetsText = $widgetsText ? '|\\/' . $widgetsText : null;
		//	foreach( $widgets as $each )
			{
				
			}
			
			
            //  Output demo content to screen
            $js = '
					/* global self, caches, fetch, URL, Response */
					\'use strict\';

					var config = {
					  version: \'achilles-y\',
					  staticCacheItems: ' . json_encode( $defaultPages ) .  ',
					  pagesToCache: ' . json_encode( $cache ) .  ',
					  staticPages: ' . json_encode( $static ) .  ',
					  cachePathPattern: /((\/Application_Javascript)|(\/layout\/)|(\/css)|(\/fonts\/)|(\/img\/)|(\.eot$)|(\.ttf$)|(\.woff$)|(\.woff2$)|(\.svg$)|(\.html$)' . $widgetsText .  ')/,
					  offlineImage: \'<svg role="img" aria-labelledby="offline-title"\'
						+ \' viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">\'
						+ \'<title id="offline-title">Offline</title>\'
						+ \'<g fill="none" fill-rule="evenodd"><path fill="#D8D8D8" d="M0 0h400v300H0z"/>\'
						+ \'<text fill="#9B9B9B" font-family="Times New Roman,Times,serif" font-size="72" font-weight="bold">\'
						+ \'<tspan x="93" y="172">offline</tspan></text></g></svg>\',
					  serverErrorPage: \'' . Ayoola_Application::getUrlPrefix() . '/500\',
					  offlinePage: \'' . Ayoola_Application::getUrlPrefix() . '/offline\'
					};
            ';
            $js .= file_get_contents( Ayoola_Doc_Browser::getDocumentsDirectory() . DS . '/js/service-worker.js' );
			header('Content-Type: text/javascript; charset=utf-8');
			header( 'Service-Worker-Allowed: ' . ( Ayoola_Application::getUrlPrefix() ? : '/' ) .  '' );
			echo $js;
			exit();

             // end of widget process
          
		}  
		catch( Exception $e )
        { 
            //  Alert! Clear the all other content and display whats below.
        //    $this->setViewContent( self::__( '<p class="badnews">' . $e->getMessage() . '</p>' ) ); 
            $this->setViewContent( self::__( '<p class="badnews">Theres an error in the code</p>' ) ); 
            return false; 
        }
	}
	// END OF CLASS
}
