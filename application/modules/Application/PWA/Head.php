<?php

/**
 * PageCarton Content Management System
 *
 * LICENSE
 *
 * @category   PageCarton CMS
 * @package    Application_PWA_Head
 * @copyright  Copyright (c) 2018 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Head.php Monday 31st of December 2018 12:52PM ayoola.falola@yahoo.com $
 */

/**
 * @see PageCarton_Widget
 */

class Application_PWA_Head extends PageCarton_Widget
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
	protected static $_objectTitle = 'HTML Head Section for PageCarton'; 

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
			
			
				echo '
<link rel="manifest" href="' . Ayoola_Application::getUrlPrefix() . '/widgets/Application_PWA_Manifest">
<meta name="mobile-web-app-capable" content="yes">
<meta name="msapplication-starturl" content="' . $scope . '">
<meta name="theme-color" content="' . Application_PWA_Settings::retrieve( 'theme_color' ) . '">
<!-- Windows Phone -->
<meta name="msapplication-navbutton-color" content="' . Application_PWA_Settings::retrieve( 'theme_color' ) . '">
<!-- iOS Safari -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

'; 
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
            //  Code that runs the widget goes here...

            //  Output demo content to screen
             $this->setViewContent( self::__( '<h1>Hello PageCarton Widget</h1>' ) ); 
             $this->setViewContent( self::__( '<p>Customize this widget (' . __CLASS__ . ') by editing this file below:</p>' ) ); 
             $this->setViewContent( self::__( '<p style="font-size:smaller;">' . __FILE__ . '</p>' ) ); 

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
