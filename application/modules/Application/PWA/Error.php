<?php

/**
 * PageCarton Content Management System
 *
 * LICENSE
 *
 * @category   PageCarton CMS
 * @package    PageCarton_Table_Sample
 * @copyright  Copyright (c) 2018 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Settings.php Monday 31st of December 2018 12:51PM ayoola.falola@yahoo.com $
 */

/**
 * @see PageCarton_Table
 */


class Application_PWA_Error extends PageCarton_Settings
{
	
    /**
     * creates the form for creating and editing
     * 
     * param string The Value of the Submit Button
     * param string Value of the Legend
     * param array Default Values
     */
	public function createForm( $submitValue = null, $legend = null, Array $values = null )
    {
		if( ! $settings = unserialize( @$values['settings'] ) )
		{
			if( is_array( $values['data'] ) )
			{
				$settings = $values['data'];
			}
			elseif( is_array( $values['settings'] ) )
			{
				$settings = $values['settings'];
			}
			else
			{
				$settings = $values;
			}
		}
	//	$settings = unserialize( @$values['settings'] ) ? : $values['settings'];
        $form = new Ayoola_Form( array( 'name' => $this->getObjectName() ) );
		$form->submitValue = $submitValue ;
		$form->oneFieldSetAtATime = true;
		$fieldset = new Ayoola_Form_Element;



        //  Sample Text Field Retrieving E-mail Address
		Application_Javascript::addFile( '/js/objects/mcColorPicker/mcColorPicker.js' );
		Application_Style::addFile( '/js/objects/mcColorPicker/mcColorPicker.css' );
		$fieldset->addElement( array( 'name' => 'name', 'label' => 'Application Name', 'value' => @$settings['name'], 'type' => 'InputText' ) );
		$fieldset->addElement( array( 'name' => 'short_name', 'label' => 'Application Short Name', 'value' => @$settings['short_name'], 'type' => 'InputText' ) );
		$fieldset->addElement( array( 'name' => 'description', 'label' => 'Application Description', 'value' => @$settings['description'], 'type' => 'InputText' ) );
		$fieldset->addElement( array( 'name' => 'icons', 'label' => 'Application Icons', 'value' => @$settings['icons'], 'multiple' => 'multiple', 'data-multiple' => 'multiple', 'type' => 'Document', 'data-document_type' => 'image/png' ) );
		$fieldset->addElement( array( 'name' => 'start_url', 'value' => @$settings['start_url'], 'type' => 'Select' ), array( '/' => '/' ) + Ayoola_Page::getAll() );
		$display = array( 
				'standalone',
				'fullscreen',
				'minimal-ui',
				'browser',
		);
		$display = array_combine( $display, $display );
		$fieldset->addElement( array( 'name' => 'display', 'value' => @$settings['display'], 'type' => 'Select' ), $display );
		$fieldset->addElement( array( 'name' => 'background_color', 'class' => 'color', 'style' => 'max-width:300px;', 'value' => @$settings['background_color'] ? : Ayoola_Page_Settings::retrieve( 'background_color' ), 'type' => 'InputText' ) );
		$fieldset->addElement( array( 'name' => 'theme_color', 'class' => 'color', 'style' => 'max-width:300px;', 'value' => @$settings['theme_color'] ? : Ayoola_Page_Settings::retrieve( 'background_color' ), 'type' => 'InputText' ) );

		$fieldset->addElement( array( 'name' => 'pages_to_cache', 'label' => 'Pages To Make Available Offline', 'value' => @$settings['pages_to_cache'], 'multiple' => 'multiple', 'type' => 'SelectMultiple' ), Ayoola_Page::getAll() );

		$fieldset->addElement( array( 'name' => 'widget_to_cache', 'label' => 'Widgets To Make Available Offline', 'value' => @$settings['widget_to_cache'], 'multiple' => 'multiple', 'type' => 'SelectMultiple' ), Ayoola_Object_Embed::getWidgets() );

		
		$fieldset->addLegend( 'Progressive Web App Settings' ); 
               
		$form->addFieldset( $fieldset );
		$this->setForm( $form );
    } 
	// END OF CLASS
}
