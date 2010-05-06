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
* @brief Form to choose a collection (used in doctypes administration)
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header('', true, false);
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$sec = new security();
$array_coll = $sec->retrieve_insert_collections();

if(isset($_REQUEST['collection']) && !empty($_REQUEST['collection']) )
{
	for($i=0; $i<count($_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']]);$i++)
	{
		$_SESSION['m_admin']['doctypes'][$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] = '0000000000';
	}
	$_SESSION['m_admin']['doctypes']['COLL_ID'] = $_REQUEST['collection'];
	?>
    	<script language="javascript" type="text/javascript">window.top.frames['choose_index'].location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&page=choose_index';?>';</script>
    <?php
}
?>
<body id="iframe">
<form name="choose_coll" method="get" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=choose_coll" class="forms" >
<input type="hidden" name="display" value="true" />
<input type="hidden" name="page" value="choose_coll" />
  <p>
	<label for="coll_id"><?php  echo _COLLECTION;?> : </label>
	<select name="collection" onChange="this.form.submit();">
		<option value="" ><?php  echo _CHOOSE_COLLECTION;?></option>
		<?php  for($i=0; $i<count($array_coll);$i++)
		{
		?>
			<option value="<?php  echo $array_coll[$i]['id'];?>" <?php  if($_SESSION['m_admin']['doctypes']['COLL_ID'] == $array_coll[$i]['id']){ echo 'selected="selected"';}?> ><?php  echo $array_coll[$i]['label'];?></option>
		<?php
		}
		?>
	</select>
  </p>
</form>
<?php $core_tools->load_js();?>
</body>
</html>
