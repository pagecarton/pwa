<?php

/**
 * PageCarton Content Management System
 *
 * LICENSE
 *
 * @category   PageCarton CMS
 * @package    Application_PWA_Manifest
 * @copyright  Copyright (c) 2018 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Manifest.php Monday 31st of December 2018 12:51PM ayoola.falola@yahoo.com $
 */

/**
 * @see PageCarton_Widget
 */

class Application_PWA_Manifest extends PageCarton_Widget
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
	protected static $_objectTitle = 'PWA Manifest'; 

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
			$settings = Application_PWA_Settings::retrieve();
			
			$icons = $settings['icons'] ? : array();
			$icons[] = '/img/logo.png';
			$settings['start_url'] = '' . Ayoola_Application::getUrlPrefix() . '/';
			$settings['background_color'] = $settings['background_color'] ? : Ayoola_Page_Settings::retrieve( 'background_color' );
			$settings['background_color'] = $settings['background_color'] ? : '#000000';
			$settings['theme_color'] = $settings['theme_color'] ? : Ayoola_Page_Settings::retrieve( 'background_color' );
			$settings['theme_color'] = $settings['theme_color'] ? : '#000000';
			$settings['name'] = $settings['name'] ? : Ayoola_Application::getDomainName();
			$settings['short_name'] = $settings['short_name'] ? : Ayoola_Application::getDomainName();
			$settings['display'] = 'minimal-ui';
			$iconsInfo = array();
			$requiredImgSizes = array(
				'72x72' ,
				'96x96' ,
				'120x120',
				'128x128' ,
				'144x144' ,
				'152x152' ,
				'180x180',
				'192x192' ,
				'384x384',
				'512x512',
			);
			$requiredImgSizes = array_combine( $requiredImgSizes, $requiredImgSizes );
			foreach( $icons as $each )
			{
				$imageInfo = Application_Slideshow_Abstract::getImageInfo( $each );
		//	var_export( $imageInfo );
				$ext = array_pop( explode( '.', strtolower( $each ) ) );
				switch( $ext )
				{
					case 'jpg':
					case 'jpeg':
						$type = 'image/jpeg';
					break;
					case 'png':
						$type = 'image/png';
					break;
					case 'gif':
						$type = 'image/gif';
					break;
				}
				$size = $imageInfo['width'] . 'x' . $imageInfo['width'];
				$iconsInfo[] = array(
					'src' => '' .  Ayoola_Application::getUrlPrefix() . '' . $each,
					'sizes' => $size,
					'tyoe' => $type,
				);
				if( in_array( $size, $requiredImgSizes ) )
				{
					unset( $requiredImgSizes[$size] );
				}
			}
			foreach( $requiredImgSizes as $each )
			{
				list( $width, $height ) = explode( 'x', $each );
				$iconsInfo[] = array(
					'src' => '' .  Ayoola_Application::getUrlPrefix() . '/widgets/Application_IconViewer?url=/img/logo.png&width=' . $width . '&height=' . $height,
					'sizes' => $each,
					'tyoe' => 'image/png',
				);
			}
			$settings['icons'] = $iconsInfo;
		//	var_export( $settings );
			$json = json_encode( $settings );
			header( 'Content-Type: application/json' );
			echo $json;
			exit();
             // end of widget process
          
		}  
		catch( Exception $e )
        { 
            exit();
            $this->setViewContent( self::__( '<p class="badnews">Theres an error in the code</p>' ) ); 
            return false; 
        }
	}
	// END OF CLASS
}
