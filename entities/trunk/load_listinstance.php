<?php
/**
* File : change_doctype.php
*
* Script called by an ajax object to process the document type change during
* indexing (index_mlb.php)
*
* @package  maarch
* @version 1
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
* @author  Cyril Vazquez  <dev@maarch.org>
*/
require_once 'modules/entities/class/class_manage_listdiff.php';

$db = new dbquery();
$core = new core_tools();
$core->load_lang();
$diffList = new diffusion_list();

if ((! isset($_REQUEST['objectType']) || empty($_REQUEST['objectType']))
    && $_REQUEST['load_from_model'] == 'true')
{
    if ($_REQUEST['mandatory'] <> 'none') {
        $_SESSION['error'] = _OBJECT_TYPE . ' ' . _IS_EMPTY;
    }
    $_SESSION['indexing']['diff_list'] = array();
    echo "{status : 1, error_txt : '" . addslashes($_SESSION['error']) . "'}";
    exit();
} 

if ((! isset($_REQUEST['objectId']) || empty($_REQUEST['objectId']))
    && $_REQUEST['load_from_model'] == 'true'
) {
    if ($_REQUEST['mandatory'] <> 'none') {
        $_SESSION['error'] = _ENTITY_ID . ' ' . _IS_EMPTY;
    }
    $_SESSION['indexing']['diff_list'] = array();
    echo "{status : 1, error_txt : '" . addslashes($_SESSION['error']) . "'}";
    exit();
}

if (empty($_REQUEST['collId']) && $_REQUEST['load_from_model'] == 'true') {
    $_SESSION['error'] = _COLL_ID . ' ' . _IS_EMPTY;
    echo "{status : 2, error_txt : '" . addslashes($_SESSION['error']) . "'}";
    exit();
}

if (empty($_REQUEST['origin'])) {
    $_SESSION['error'] = _ORIGIN . ' ' . _UNKNOWN;
    echo "{status : 2, error_txt : '" . addslashes($_SESSION['error']) . "'}";
    exit();
}
$onlyCC = false;
if (isset($_REQUEST['only_cc'])) {
    $onlyCC = true;
}

$objectType = $_REQUEST['objectType'];
$objectId = $_REQUEST['objectId'];
$origin = $_REQUEST['origin'];

// Get listmodel_parameters
$_SESSION[$origin]['difflist_type'] = $diffList->get_difflist_type($objectType);

if ($_REQUEST['load_from_model'] == 'true') {
    $_SESSION[$origin]['diff_list'] = $diffList->get_listmodel($objectType, $objectId);
}

$roles = $diffList->list_difflist_roles();
$difflist = $_SESSION[$origin]['diff_list'];

$content = '';
if (! $onlyCC) {
    if (isset($_SESSION['validStep']) && $_SESSION['validStep'] == 'ok') {
        $content .= "";
    } else {
        $content .= '<h2>' . _LINKED_DIFF_LIST . ' : </h2>';
    }
}

if(!empty($_SESSION[$origin]['diff_list'])) {
    /*
    if (isset($_SESSION[$origin]['diff_list']['dest']['user_id'])
        && ! empty($_SESSION[$origin]['diff_list']['dest']['user_id'])
    ) {
        if (! $onlyCC) {
            $content .= '<span class="sstit">' . _RECIPIENT . '</span>';
            $content .= '<table cellpadding="0" cellspacing="0" border="0" class="listing spec detailtabricatordebug">';
            $content .= '<tr class="col">';
            $content .= '<td><img src="' . $_SESSION['config']['businessappurl']
                     . 'static.php?filename=manage_users_entities_b_small.gif'
                     . '&module=entities" alt="' . _USER . '" title="'
                     . _USER . '" /></td>';
            $content .= '<td >' . $_SESSION[$origin]['diff_list']['dest']['firstname']
                     . '</td>';
            $content .= '<td >' . $_SESSION[$origin]['diff_list']['dest']['lastname']
                     .'</td>';
            $content .= '<td>' . $_SESSION[$origin]['diff_list']['dest']['entity_label']
                     .'</td>';
            $content .= '</tr>';
            $content .= '</table><br/>';
        }
    }
    # OTHER ROLES
    #**************************************************************************
    foreach($roles as $role_id => $role_label) {
        if(count($_SESSION[$origin]['diff_list'][$role_id]['users']) > 0 
            || count($_SESSION[$origin]['diff_list'][$role_id]['entities']) > 0
        ) {
            if (count($_SESSION[$origin]['diff_list'][$role_id]['users']) > 0 
                || count($_SESSION[$origin]['diff_list'][$role_id]['entities']) > 0
            ) {
                $content .= '<h4 onclick="new Effect.toggle(\'' . $role_id . '\', \'blind\', {delay:0.2});'
                    . 'whatIsTheDivStatus(\'' . $role_id . '\', \'divStatus_' . $role_id . '\');" '
                    . 'class="categorie" style="width:405px;" onmouseover="this.style.cursor=\'pointer\';">';
                $content .= '<small><span id="divStatus_' . $role_id . '" style="color:#1B99C4;" class="sstit"><<</span>&nbsp;' 
                    . $role_label;
                $content .= '</small></h4>';
                $content .= '<div id="' . $role_id . '"  style="display:block">';
                $content .= '<div>';
            }
            $content .= '<table cellpadding="0" cellspacing="0" border="0" class="listing spec detailtabricatordebug">';
            $color = ' class="col"';
            for ($i=0, $l=count($_SESSION[$origin]['diff_list'][$role_id]['users']); 
                $i<$l; 
                $i++
            ) {
                if ($color == ' class="col"') $color = '';
                else $color = ' class="col"';
                
                $content .= '<tr ' . $color . ' >';
                $content .= '<td><img src="' . $_SESSION['config']['businessappurl']
                         . 'static.php?filename=manage_users_entities_b_small.gif'
                         . '&module=entities" alt="' . _USER . " " .$role_label . '" title="' . _USER . " " .$role_label
                         . '" /></td>';
                $content .= '<td >' . $_SESSION[$origin]['diff_list'][$role_id]['users'][$i]['lastname'] . '</td>';
                $content .= '<td >' . $_SESSION[$origin]['diff_list'][$role_id]['users'][$i]['firstname'] . '</td>';
                $content .= '<td>' . $_SESSION[$origin]['diff_list'][$role_id]['users'][$i]['entity_label'] . '</td>';
                $content .= '</tr>';
            }
            for ($i=0, $l=count($_SESSION[$origin]['diff_list'][$role_id]['entities']); 
                $i<$l; 
                $i++
            ) {
                if ($color == ' class="col"') $color = '';
                else $color = ' class="col"';
                $content .= '<tr ' . $color . ' >';
                $content .= '<td><img src="' . $_SESSION['config']['businessappurl']
                         . 'static.php?filename=manage_entities_b_small.gif&module='
                         . 'entities" alt="' . _ENTITY . " " .$role_label . '" title="' . _ENTITY . " " .$role_label
                         . '" /></td>';
                $content .= '<td >' . $_SESSION[$origin]['diff_list'][$role_id]['entities'][$i]['entity_id'] .'</td>';
                $content .= '<td colspan="2">'. $_SESSION[$origin]['diff_list'][$role_id]['entities'][$i]['entity_label'].'</td>';
                $content .= '</tr>';
            }
            $content .= '</table>';            
            $content .= '</div>';
            $content .= '</div>';
        }
    }*/
    
    # Get content from buffer of difflist_display 
    ob_start();
    require_once 'modules/entities/difflist_display.php';
    $content .= str_replace(array("\r", "\n", "\t"), array("", "", ""), ob_get_contents());
    ob_end_clean();   

    $labelButton = _MODIFY_LIST;
    $arg = '&mode=up';
} else {
    $content .= '<p>' . _NO_DIFF_LIST_ASSOCIATED . '</p>';
    $labelButton = _CREATE_LIST;
    $arg = '&mode=add';
}
if ($onlyCC) {
    $arg .= '&only_cc';
}
$content_standard = '<center><b>' . _DIFF_LIST . '</b> | ';
$content_standard .= '<span class="button" >';
$content_standard .= '<img src="' . $_SESSION['config']['businessappurl']
         . 'static.php?filename=modif_liste.png&module=entities" alt="" />'
         . '<a href="javascript://" onclick="window.open(\''
         . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
         . '&module=entities&page=manage_listinstance&origin=' . $origin . $arg
         . '\', \'\', \'scrollbars=yes,menubar=no,toolbar=no,status=no,'
         . 'resizable=yes,width=1280,height=800,location=no\');"><small>'
         . $labelButton . '</small></a>';
$content_standard .= '</span></center>';

echo "{status : 0, div_content : '" . addslashes($content_standard . $content . '<br>') 
    . "', div_content_action : '" . addslashes($content) . "'}";
exit();
