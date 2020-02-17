<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   manage_tag_list_controller
* @author  dev <dev@maarch.org>
* @ingroup tags
*/

core_tools::load_lang();
$core_tools = new core_tools();
$core_tools->test_admin('admin_tag', 'tags');

// Default mode is add
$mode = 'list';
if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
}

try{
    include_once 'core/class/ActionControler.php';
    include_once 'core/class/ObjectControlerAbstract.php';
    include_once 'core/class/ObjectControlerIF.php';
    include_once 'modules/tags/class/TagControler.php' ;
    include_once 'modules/tags/class/Tag.php' ;
    include_once 'modules/tags/route.php' ;
    
    if ($mode == 'list') {
        include_once 'core/class/class_request.php' ;
        include_once 'apps' . DIRECTORY_SEPARATOR
                     . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR
                     . 'class' . DIRECTORY_SEPARATOR . 'class_list_show.php' ;
    }
} catch (Exception $e) {
    functions::xecho($e->getMessage());
}

//Get list of aff availables actions
//$al = new ActionControler();
//$tagslist = $al->getAllTags();

$func = new functions();

//Get list of all templates
if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
    $_REQUEST['id'] = htmlspecialchars_decode($_REQUEST['id']);
    $tag_id = $func->protect_string_db($_REQUEST['id']);
}


if (isset($_REQUEST['tag_submit'])) {
    // Action to do with db
    validate_tag_submit();

} else {
    // Display to do

    $state = true;
        
    switch ($mode) {
    case 'up' :
        display_up($tag_id);
        
        $_SESSION['service_tag'] = 'tag_init';
        core_tools::execute_modules_services(
            $_SESSION['modules_services'], 'event_init', 'include'
        );
        location_bar_management($mode);
        break;
    case 'add' :
        display_add();
        $_SESSION['service_tag'] = 'tag_init';
        core_tools::execute_modules_services(
            $_SESSION['modules_services'], 'tag_init', 'include'
        );
        location_bar_management($mode);
        break;
    case 'del' :
        display_del($tag_id);
        break;
    case 'list' :
        $tagslist = display_list();
        location_bar_management($mode);
        // print_r($tagslist); exit();
        break;
    }

    include 'manage_tag_list.php';
}

/**
 * Management of the location bar
 */
function location_bar_management($mode)
{
    $pageLabels = array('add'  => _ADDITION,
                    'up'   => _MODIFICATION,
                    'list' => _MANAGE_TAGS
                );
    $pageIds = array('add' => 'tags_add',
                    'up' => 'tags_up',
                    'list' => 'tags_list'
            );
    $init = false;
    if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
        $init = true;
    }

    $level = '';
    if (isset($_REQUEST['level'])
        && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3
        || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
    ) {
        $level = $_REQUEST['level'];
    }

    $pagePath = $_SESSION['config']['businessappurl'] . 'index.php?page='
                . 'manage_tag_list_controller&module=tags&mode=' . $mode ;
    $pageLabel = $pageLabels[$mode];
    $pageId = $pageIds[$mode];
    $ct = new core_tools();
    $ct->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
}

/**
 * Initialize session parameters for update display
 * @param String $statusId
 */
function display_up($tag_id)
{
    $func    = new functions();
    $tagCtrl = new tag_controler;
    $state   = true;
    $tag     = $tagCtrl->get_by_id($tag_id);
    if (empty($tag)) {
        $state = false;
    } else {
        //put_in_session('tag', $tag->getArray());
        $_SESSION['m_admin']['tag']['tag_id']    = $tag->id;
        $_SESSION['m_admin']['tag']['tag_label'] = $tag->label;
        $_SESSION['m_admin']['tag']['entities']  = $tag->entities;
        $_SESSION['m_admin']['tag']['tag_count'] = (string) $tagCtrl->countdocs(
            $tag->tag_id
        );
    }
    //récupération de l'ensemble des tags dans un tableau
    $all_tags = array();
    $all_tags = $tagCtrl->get_all_tags();
    $_SESSION['tmp_all_tags'] = $all_tags;
}

/**
 * Initialize session parameters for add display
 */
function display_add()
{

    include_once "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    ."class_security.php";

    if (!isset($_SESSION['m_admin']['init'])) {
        init_session();
    }

    //Recuperation du nombre de documents taggués
    $sec = new Security();
    $arrayColl = $sec->retrieve_insert_collections();
    $_SESSION['m_admin']['tags']['coll_id'] = $arrayColl;
    //var_dump($arrayColl); exit();
    return $state;

}

/**
 * Initialize session parameters for list display
 */
function display_list() 
{
    $_SESSION['m_admin'] = array();
    $list = new list_show();

    init_session();

    $select['tags'] = [];
    array_push(
        $select['tags'], 'id', 'label'
    );

    $where = '';
    $where_what = array();

    $what = '';

    if (isset($_REQUEST['what'])) {
        $what = $_REQUEST['what'];
    }

    if(!empty($where)){
        $where .= " and ";
    }

    if ($_SESSION['config']['databasetype'] == 'POSTGRESQL') {
        $where .= " (label ilike ? ) ";
        $where_what[] = $what.'%';

    } else {
        $where .= " (label like ? ) ";
        $where_what[] = $what.'%';
    }

    // Checking order and order_field values
    $order = 'asc';
    if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
        $order = trim($_REQUEST['order']);
    }

    $field = 'label';
    if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) {
        $field = trim($_REQUEST['order_field']);
    }

    $orderstr = $list->define_order($order, $field);

    if (isset($_REQUEST['start']) && !empty($_REQUEST['start'])) {
        $parameters .= '&start='.$_REQUEST['start'];
    } else {
        $_REQUEST['start'] = 0;
    }

    $request  = new request();
    $tab = $request->PDOselect(
        $select, $where, $where_what, $orderstr, $_SESSION['config']['databasetype'], 
        "default", false, "", "", "", true, false, false, $_REQUEST['start']
    );

    for ($i=0;$i<count($tab);$i++) {
        foreach ($tab[$i] as &$item) {
            switch ($item['column']) {
            case 'id':
                format_item(
                    $item, _ID, '10', 'left', 'left', 'bottom', true
                );
                break;
            
            case 'label':
                format_item(
                    $item, _NAME_TAGS, '70', 'left', 'left', 'bottom', true
                );
                break;
            }
        }
    }
    $_SESSION['m_admin']['init'] = true;
    $result = array(
        'tab'                 => $tab,
        'what'                => $what,
        'page_name'           => 'manage_tag_list_controller&mode=list',
        'page_name_add'       => 'manage_tag_list_controller&mode=add',
        'page_name_up'        => 'manage_tag_list_controller&mode=up',
        'page_name_del'       => 'manage_tag_list_controller&mode=del',
        'page_name_val'       => '',
        'page_name_ban'       => '',
        'label_add'           => _ADD_TAG,
        'title'               => _TAGS_LIST . ' : ' . $_SESSION['save_list']['full_count'],
        'autoCompletionArray' => array(
                                     'list_script_url'  =>
                                        $_SESSION['config']['businessappurl']
                                        . 'index.php?display=true&module=tags'
                                        . '&page=manage_tag_list_by_name',
                                     'number_to_begin'  => 1
                                 ),

    );
    return $result;
}

/**
 * Delete given tag if exists and initialize session parameters
 * @param string $statusId
 */
function display_del($tag_id)
{

    if (!$_SESSION['m_admin']['tags']['coll_id']) {
        $_SESSION['m_admin']['tags']['coll_id'] = 'letterbox_coll';
    }

    $coll_id = $_SESSION['m_admin']['tags']['coll_id'];

    $tagCtrl = new tag_controler();
    if (isset($tag_id)) {
        // Deletion
        $control = $tagCtrl->delete($tag_id, $coll_id);
        if (!$control) {
            $_SESSION['error'] = str_replace("#", "<br />", $control['error']);
        } else {
            $_SESSION['info'] = _TAG_DELETED.' : '. str_replace("''", "'", $_SESSION['m_admin']['tags']['tag_label']).' (ID : '.$tag_id.')';
        }
        ?><script type="text/javascript">window.top.location='<?php
            echo $_SESSION['config']['businessappurl']
                . 'index.php?page=manage_tag_list_controller&mode=list&module='
                . 'tags&order=' . $_REQUEST['order'] . '&order_field='
                . $_REQUEST['order_field'] . '&start=' . $_REQUEST['start']
                . '&what=' . $_REQUEST['what'];
        ?>';</script>
        <?php
        exit();
    } else {
        // Error management
        $_SESSION['error'] = _TAG.' '._UNKNOWN;
    }
}

/**
 * Format given item with given values, according with HTML formating.
 * NOTE: given item needs to be an array with at least 2 keys:
 * 'column' and 'value'.
 * NOTE: given item is modified consequently.
 * @param $item
 * @param $label
 * @param $size
 * @param $labelAlign
 * @param $align
 * @param $valign
 * @param $show
 */
function format_item(
    &$item, $label, $size, $labelAlign, $align, $valign, $show, $order = true
) {
    $func = new functions();
    $item['value']         = $func->show_string($item['value']);
    $item[$item['column']] = $item['value'];
    $item['label']         = $label;
    $item['size']          = $size;
    $item['label_align']   = $labelAlign;
    $item['align']         = $align;
    $item['valign']        = $valign;
    $item['show']          = $show;
    if ($order) {
        $item['order'] = $item['value'];
    } else {
        $item['order'] = '';
    }
}

/**
 * Validate a submit (add or up),
 * up to saving object
 */
function validate_tag_submit()
{

    $pageName = 'manage_tag_list_controller';
    $func     = new functions();
    $mode     = 'up';
    $mode     = $_REQUEST['mode'];
    $tagObj   = new tag_controler();
    
    if ($_REQUEST['collection']) {

        $coll_id = $_REQUEST['collection'];
    
    } else {
        $coll_id = "letterbox_coll";
    }
    if ($_REQUEST['tag_label']) {

        $new_tag_label = trim($func->protect_string_db($_REQUEST['tag_label']));
    }
    //$_SESSION['m_admin']['tag']['tag_label'] = $_REQUEST['tag_label_id'];

    $params = array();
    array_push($params, $new_tag_label);
    array_push($params, $coll_id);

    var_dump($new_tag_label);
    if ($new_tag_label == '' || !$new_tag_label || empty($new_tag_label)) {
        $_SESSION['error'] = _TAG_LABEL_IS_EMPTY;
        header(
            'location: ' . $_SESSION['config']['businessappurl']
            . 'index.php?page=' . $pageName . '&mode='.$mode.'&id='
            . $tag->tag_idl . '&module=tags'
        );
        exit();
    }

    $func->wash(
        $new_tag_label, 'no', _NAME_TAGS, 'yes', 0, 50
    );
    
    if ($_SESSION['error'] <> '') {
        header(
            'location: ' . $_SESSION['config']['businessappurl']
            . 'index.php?page=' . $pageName . '&mode='.$mode.'&id='
            . $tag->tag_id . '&module=tags'
        );
        exit();
    }
    $_SESSION['m_admin']['tag']['tag_label'] = $new_tag_label;
    $_SESSION['m_admin']['tag']['coll_id'] = $coll_id;
    $_SESSION['m_admin']['tag']['entities'] = $_REQUEST['entitieslist'];
    
    $tag = $tagObj->store($_SESSION['m_admin']['tag']['tag_id'], $mode, $params);
    
    switch ($mode) {
    case 'up':
        if ($_SESSION['error'] == "")
            $_SESSION['info'] = _TAG_UPDATED . ' : ' . str_replace("''", "'", $new_tag_label) . ' (ID : ' . $_SESSION['m_admin']['tag']['tag_id'] . ')';

        if (!empty($_SESSION['m_admin']['tag']['dddtag_label'])) {
            header(
                'location: ' . $_SESSION['config']['businessappurl']
                . 'index.php?page=' . $pageName . '&mode=up&id='
                . $tag->tag_id . '&module=tags'
            );
        } else {
            header(
                'location: ' . $_SESSION['config']['businessappurl']
                . 'index.php?page=' . $pageName . '&mode=list&module='
                . 'tags&order=' . $status['order'] . '&order_field='
                . $status['order_field'] . '&start=' . $status['start']
                . '&what=' . $status['what']
            );
        }
        exit();
    case 'add':
        if (empty($_SESSION['error'])) {
            $_SESSION['info'] = _TAG_ADDED . ' : ' . str_replace("''", "'", $new_tag_label);
        }
        header(
            'location: ' . $_SESSION['config']['businessappurl']
            . 'index.php?page=' . $pageName . '&mode=list&module='
            . 'tags&order=' . $status['order'] . '&order_field='
            . $status['order_field'] . '&start=' . $status['start']
            . '&what=' . $status['what']
        );
        exit();
    }
}

function init_session()
{
    $_SESSION['m_admin']['tag'] = array(
        'tag_id' => '',
        'tag_label' => '',
        'coll_id' => '',
        'entities' => array()
    );
}

/**
 * Put given object in session, according with given type
 * NOTE: given object needs to be at least hashable
 * @param string $type
 * @param hashable $hashable
 */
function put_in_session($type, $hashable, $showString = true)
{
    $func = new functions();
    foreach ($hashable as $key=>$value) {
        if ($showString) {
            $_SESSION['m_admin'][$type][$key]=$func->show_string($value);
        } else {
            $_SESSION['m_admin'][$type][$key]=$value;
        }
    }
}
