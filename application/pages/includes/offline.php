<?php
/**
* PageCarton Page Generator
*
* LICENSE
*
* @category PageCarton
* @package /offline
* @generated Ayoola_Page_Editor_Layout
* @copyright  Copyright (c) PageCarton. (http://www.PageCarton.com)
* @license    http://www.PageCarton.com/license.txt
* @version $Id: offline.php	Thursday 3rd of January 2019 08:44:52 PM	ayoola.falola@yahoo.com $ 
*/
//	Page Include Content

							if( Ayoola_Loader::loadClass( 'Ayoola_Page_Editor_Text' ) )
							{
								
$_a3b4b53c3442969197ced22767c5a2a4 = new Ayoola_Page_Editor_Text( array (
  'advanced_parameters' => '',
  'editable' => '<h3>&nbsp;</h3>

<div class="pc_container" style="padding:1em;">
<h3>You are offline</h3>

<div>&nbsp;</div>

<p>The page you are trying to view currently isn\'t available offline. Please connect to the internet to continue.</p>

<p>&nbsp;</p>

<p>
<a class="pc-btn pc_give_space" onclick="window.history.back();" href="javascript:"> <i class="fa fa-arrow-left pc_give_space"></i> Go back</a> 
<a class="pc-btn pc_give_space" onclick="window.location.reload( true ); " href="javascript:"><i class="fa fa-refresh pc_give_space"></i>  Reload</a> 

<a class="pc-btn pc-bg-color pc_give_space" href="/"> <i class="fa fa-home pc_give_space"></i> Home Page</a> 

</p>
</div>

<p>&nbsp;</p>
',
  'preserved_content' => '			
			<h3><br></h3><h3>You are offline<br></h3><div><br></div>
			<p>Please connect to the internet to continue.</p><p><br></p>
			',
  'url_prefix' => '/pwa',
) );

							}
							else
							{
								
$_a3b4b53c3442969197ced22767c5a2a4 = null;

							}
							