<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* File : create_folder_form.php
*
* Form to create a folder
*
* @package  Folder
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
include('core/init.php');


$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->test_service('create_folder', 'folder');

 /****************Management of the location bar  ************/
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=create_folder_form&module=folder';
$page_label = _CREATE_FOLDER;
$page_id = "fold_create_folder_form";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$core_tools->load_html();

$db = new dbquery();
$db->connect();
$db->query("select foldertype_id, foldertype_label from ".$_SESSION['tablename']['fold_foldertypes']." order by foldertype_label");

$foldertypes = array();
while($res = $db->fetch_object())
{
	array_push($foldertypes, array('id' => $res->foldertype_id, 'label' => $db->show_string($res->foldertype_label)));
}
?>

<h1><img src="<?php  echo $_SESSION['urltomodules']."folder/img/s_sheet_b.gif";?>" alt="" /> <?php  echo _CREATE_FOLDER;?></h1>
<div id="inner_content">
	<form name="create_folder" id="create_folder" action="<?php echo $_SESSION['urltomodules'];?>folder/manage_create_folder.php" method="post" class="forms">
		<p>
			<label for="foldertype"><?php echo _FOLDERTYPE;?> :</label>
			<select name="foldertype" id="foldertype" onchange="get_folder_index('<?php echo $_SESSION['urltomodules'];?>folder/create_folder_get_folder_index.php', this.options[this.options.selectedIndex].value, 'folder_indexes');">
				<option value=""><?php  echo _CHOOSE_FOLDERTYPE;?></option>
				<?php  for($i=0; $i< count($foldertypes);$i++)
				{
				?><option value="<?php  echo $foldertypes[$i]['id'];?>" <?php  if($_SESSION['m_admin']['folder']['foldertype_id'] == $foldertypes[$i]['id']){ echo 'selected="selected"'; }?>><?php  echo $foldertypes[$i]['label'];?></option>
				<?php
				}?>
			</select> <span class="red_asterisk">*</span>
		</p>
		<p>
			<label for="folder_id"><?php echo _ID;?></label>
			<input name="folder_id" id="folder_id" value="<?php echo $_SESSION['m_admin']['folder']['folder_id'];?>" /> <span class="red_asterisk">*</span>
		</p>
		<p>
			<label for="folder_name"><?php echo _FOLDERNAME;?></label>
			<input name="folder_name" id="folder_name" value="<?php echo $_SESSION['m_admin']['folder']['folder_name'];?>" /> <span class="red_asterisk">*</span>
		</p>
		<div id="folder_indexes"></div>
		<p class="buttons">
			<input type="submit" name="validate" id="validate" value="<?php echo _VALIDATE;?>" class="button"/>
			<input type="button" name="cancel" id="cancel" value="<?php echo _CANCEL;?>" class="button" onclick="window.top.location='<?php echo $_SESSION['config']['businessappurl'];?>index.php';" />
		</p>
	</form>
	<?php if(isset($_SESSION['m_admin']['folder']['foldertype_id']) && !empty($_SESSION['m_admin']['folder']['foldertype_id']))
	{?>
	<script>
	var ft_list = $('foldertype');
	if(ft_list)
	{
		get_folder_index('<?php echo $_SESSION['urltomodules'];?>folder/create_folder_get_folder_index.php', ft_list.options[ft_list.options.selectedIndex].value, 'folder_indexes');
	}
	</script>
	<?php } ?>
</div>
