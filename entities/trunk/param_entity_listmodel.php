<?php

/**
 * $_SESSION['m_admin']['entity']['listmodel'] Structure :
 *
 * $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'] = 'demo'
 * 													  ['lastname'] = 'Smith'
 * 													  ['firstname'] = 'John'
 * 													  ['entity_id'] = 'ENTITY1'
 * 													  ['entity_id'] = 'IT Departement'
 *
 * 											  ['copy']['entities'][$i]['entity_id'] = 'ENTITY1'
 * 											  						  ['entity_label'] = 'IT Departement'
 *
 *  										  		  ['users'][$i]['user_id'] = 'demo'
 *  															   ['lastname'] = 'Smith'
 * 													          	   ['firstname'] = 'John'
 * 													  			   ['entity_id'] = 'ENTITY1'
 * 													  			   ['entity_id'] = 'IT Departement'
 *
 **/
//print_r($_SESSION['m_admin']['entity']['listmodel']);exit;
require_once 'core/class/usergroups_controler.php';
require_once 'modules/entities/class/class_manage_listdiff.php';
    
$usergroups_controler = new usergroups_controler();
$listdiff = new diffusion_list();
$roles = $listdiff->list_difflist_roles();

if($_SESSION['service_tag'] == 'entity_add')
{
	if(!isset($_SESSION['m_admin']['entity']['listmodel']))
	{
		$_SESSION['m_admin']['entity']['listmodel'] = array();
        
        # Init listmodel info
        $_SESSION['m_admin']['entity']['listmodel_info']['object_type'] = 'entity_id';
        $_SESSION['m_admin']['entity']['listmodel_info']['object_id'] = $_SESSION['m_admin']['entity']['entityId'];
        $_SESSION['m_admin']['entity']['listmodel_info']['description'] = false;
	}
}
elseif($_SESSION['service_tag'] == 'entity_up')
{
	if(!isset($_SESSION['m_admin']['entity']['listmodel']))
	{  
        $_SESSION['m_admin']['entity']['listmodel'] = 
            $listdiff->get_listmodel(
                'entity_id',
                $_SESSION['m_admin']['entity']['entityId']
            );
	}
}
elseif($_SESSION['service_tag'] == 'entities_list_init')
{
	//
}
elseif($_SESSION['service_tag'] == 'entity_check')
{
	if((count($_SESSION['m_admin']['entity']['listmodel']['copy']['users']) > 0 || count($_SESSION['m_admin']['entity']['listmodel']['copy']['entities']) > 0) && (!isset($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']) || empty($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'])))
	{
		$_SESSION['error'] .= _DEST_MANDATORY;
	}
}
elseif($_SESSION['service_tag'] == 'entity_add_db' || $_SESSION['service_tag'] == 'entity_up_db')
{
	require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_listdiff.php');
	$diff_list = new diffusion_list();
	$diff_list->save_listmodel(
        $_SESSION['m_admin']['entity']['listmodel'],
        $objectType = 'entity_id',
        $objectId = $_SESSION['m_admin']['entity']['entityId'],
        $description = $_SESSION['m_admin']['entity']['listmodel_info']['description']
    );
}

# Default description
if(!isset($_SESSION['m_admin']['entity']['listmodel_info']['description']))
    $_SESSION['m_admin']['entity']['listmodel_info']['description'] = 
        "Diffusion au service " 
            . $_SESSION['m_admin']['entity']['entityId'] 
            . ' - '
            . $_SESSION['m_admin']['entity']['label'];





if($_SESSION['service_tag_form'] == 'formentity') {
	$_SESSION['service_tag_form'] = "";
	?>
	<!--div id="inner_content" class="clearfix"-->
	<div id="listmodel_box" class="block"> <?php 
	if(count($_SESSION['m_admin']['entity']['listmodel']) > 0) { ?>
		<h2 class="tit"><?php echo _LINKED_DIFF_LIST;?> : </h2><?php
        $difflist = $_SESSION['m_admin']['entity']['listmodel'];
        require_once 'modules/entities/difflist_display.php';?>
		<p class="buttons">
			<input type="button" onclick="window.open('<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=';?>manage_listmodel&what=A&objectType=entity_id&objectId=<?php echo $_SESSION['m_admin']['entity']['entityId'];?>', '', 'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=1024,height=650,location=no');" class="button" value="<?php echo _MODIFY_LIST;?>" />
		</p> <?php 
	} else { ?>
		<h2 class="tit"><?php echo _NO_LINKED_DIFF_LIST;?>.</h2>
		<p class="buttons">
			<p>
				<input type="button" onclick="window.open('<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=';?>manage_listmodel&objectType=entity_id&objectId=<?php echo $_SESSION['m_admin']['entity']['entityId'];?>', '', 'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=1024,height=650,location=no');" class="button" value="<?php echo _CREATE_LIST;?>" />
			</p>
		</p> <?php 
	} ?>
	</div> <?php
} 
?>

