<?php
/**
* File : choose_coll.php
*
* Form to choose a collection (used in doctypes administration)
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
include('core/init.php');

require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require("core/class/class_core_tools.php");
$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
require_once("core/class/class_security.php");
$sec = new security();
$array_coll = $sec->retrieve_insert_collections();

if(isset($_REQUEST['collection']) && !empty($_REQUEST['collection']) )
{
	$_SESSION['m_admin']['doctypes']['COLL_ID'] = $_REQUEST['collection'];
}
?>
<body id="iframe">
<form name="choose_coll" method="get" action="choose_coll.php" class="forms" >
	<p>
		<label for="coll_id"><?php  echo _COLLECTION;?> : </label>
		<select name="collection" onChange="this.form.submit();">
			<option value="" ><?php  echo _CHOOSE_COLLECTION;?></option>
			<?php
			for($i=0; $i<count($array_coll);$i++)
			{
				?>
					<option value="<?php  echo $array_coll[$i]['id'];?>" <?php  if($_SESSION['m_admin']['doctypes']['COLL_ID'] == $array_coll[$i]['id']){ echo 'selected="selected"';}?> ><?php  echo $array_coll[$i]['label'];?></option>
				<?php
			}
			?>
		</select>
	</p>
</form>
</body>
</html>
