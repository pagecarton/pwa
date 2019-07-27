<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    Application_PWA_Push_Subscription_Creator
 * @copyright  Copyright (c) 2017 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Creator.php Wednesday 20th of December 2017 03:23PM ayoola@ayoo.la $
 */

/**
 * @see PageCarton_Widget
 */

class Application_PWA_Push_Subscription_Creator extends Application_PWA_Push_Subscription_Abstract
{
	
    /**
     * 
     * 
     * @var string 
     */
	protected static $_objectTitle = 'Add new subscription'; 

    /**
     * Performs the whole widget running process
     * 
     */
	public function init()
    {    
		try
		{ 
            //  Code that runs the widget goes here...
			$this->createForm( 'Submit...', 'Add new' );
			$this->setViewContent( $this->getForm()->view() );

		//	self::v( $_POST );
		//	self::v( file_get_contents('php://input') );
            if( ! $values = $this->getForm()->getValues() )
            { 
                $values = json_decode( file_get_contents('php://input'), true );

                if(! isset( $values['endpoint'] ) ) 
                {
                    return false;
                }
            }
        //	self::v( $values );
            $data = $values;
            $endpoint = $values['endpoint'];
            unset( $data['endpoint'] );
            if(! isset( $values['endpoint'] ) ) 
            {
                return false;
            }
        
			//	Notify Admin
			$mailInfo = array();
			$mailInfo['subject'] = __CLASS__;
			$mailInfo['body'] = 'Form submitted on your PageCarton Installation with the following information: "' . self::arrayToString( $values ) . '". 
			
			';
			try
			{
		//		var_export( $mailInfo );
				@Ayoola_Application_Notification::mail( $mailInfo );
			}
			catch( Ayoola_Exception $e ){ null; }
		//	if( ! $this->insertDb() ){ return false; }
            switch( $values['method'] ) 
            {
                case 'PUT':
                    // update the key and token of subscription corresponding to the endpoint
                    if( $this->getDbTable()->update( $data, array( 'endpoint' => $values['endpoint'] ) ) )
                    { 
                        $this->setViewContent(  '' . self::__( '<div class="goodnews">Subscription updated successfully. </div>' ) . '', true  ); 
                    }
                    else
                    {
                        $this->insertDb( $values );
                    }      

                break;
                case 'DELETE':
                    // delete the subscription corresponding to the endpoint
                    if( $this->getDbTable()->delete( array( 'endpoint' => $values['endpoint'] ) ) )
                    { 
                        $this->setViewContent(  '' . self::__( '<div class="goodnews">Subscription delete successfully. </div>' ) . '', true  ); 
                    }        

                    break;
                default:
                    // create a new subscription entry in your database (endpoint is unique)
                    if( $this->getDbTable()->select( null, array( 'endpoint' => $values['endpoint'] ) ) )
                    {
                        if( $this->getDbTable()->update( $data, array( 'endpoint' => $values['endpoint'] ) ) )
                        { 
                            $this->setViewContent(  '' . self::__( '<div class="goodnews">Subscription updated successfully. </div>' ) . '', true  ); 
                        }
                    }
                    else
                    { 
                        $this->insertDb( $values );
                        $this->setViewContent(  '' . self::__( '<div class="goodnews">Subscription added successfully. </div>' ) . '', true  ); 
                    }        
                break;
            }
		//	$this->setViewContent( $this->getForm()->view() );
            


            // end of widget process
          
		}  
		catch( Exception $e )
        { 
            //  Alert! Clear the all other content and display whats below.
            $this->setViewContent( self::__( '<p class="badnews">' . $e->getMessage() . '</p>' ) ); 
            $this->setViewContent( self::__( '<p class="badnews">Theres an error in the code</p>' ) ); 
            return false; 
        }
	}
	// END OF CLASS
}
