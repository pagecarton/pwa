<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    Application_PWA_Push_Subscription
 * @copyright  Copyright (c) 2019 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Subscription.php Friday 26th of July 2019 09:33PM ayoola@ayoo.la $
 */

/**
 * @see PageCarton_Table
 */


class Application_PWA_Push_Subscription extends PageCarton_Table
{

    /**
     * The table version (SVN COMPATIBLE)
     *
     * @param string
     */
    protected $_tableVersion = '0.0';  

    /**
     * Table data types and declaration
     * array( 'fieldname' => 'DATATYPE' )
     *
     * @param array
     */
	protected $_dataTypes = array (
  'endpoint' => 'INPUTTEXT,UNIQUE',
  'publicKey' => 'INPUTTEXT',
  'authToken' => 'INPUTTEXT',
  'contentEncoding' => 'INPUTTEXT',
  'status' => 'INPUTTEXT',
  'data' => 'INPUTTEXT',
);


	// END OF CLASS
}
