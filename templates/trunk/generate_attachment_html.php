<?php
/*
*   Copyright 2008-2012 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* File : generate_attachment.php
*
* Form to generate an attachment to a mail with an existing template
*
* @since 10/2007
* @license GPL
* @author  Claire Figueras <dev@maarch.org>
* @author  Laurent Giovannoni <dev@maarch.org>
*/
//require_once('modules/templates/class/class_modules_tools.php');
require_once('modules/templates/class/templates_controler.php');
require_once 'modules/templates/templates_tables_definition.php';
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$db = new dbquery();
$db->connect();
$templates_controler = new templates_controler();
$answer= array();
$answer['TITLE'] = "";
$answer['CONTENT'] = "";
$answer['TEMPLATE_ID'] = "";
$id = "";
$res_id = '';
$coll_id = '';

$func = new functions();

if (isset($_REQUEST['coll_id']) && !empty($_REQUEST['coll_id'])) {
    $coll_id = $_REQUEST['coll_id'];
}
if (isset($_REQUEST['res_id']) && !empty($_REQUEST['res_id'])) {
    $res_id = $_REQUEST['res_id'];
}
if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
} else {
    $mode = "";
    $_SESSION['error'] .= _NO_MODE_DEFINED.".<br/>";
}
if (isset($_GET['template']) && !empty($_GET['template'])) {
    $answer['TEMPLATE_ID']= trim($_GET['template']);
}
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = trim($_GET['id']);
    $db->query("select title from " 
        . $_SESSION['tablename']['attach_res_attachments'] 
        . " where res_id = ".$id);
    $res = $db->fetch_object();
    $answer['TITLE'] = $func->show_string($res->title);
}
if (!empty($answer['TEMPLATE_ID']) &&  $mode == 'add') {
    $template = $templates_controler->get($answer['TEMPLATE_ID']);
    
    if ($template->template_id == '') {
        $_SESSION['error'] .= _TEMPLATE.' '._UNKNOWN."<br/>";
    } else {
        $line = $db->fetch_object();
        $answer['TEMPLATE_ID'] = $template->template_id;
        $answer['MODEL_LABEL'] = $func->show_string($template->template_label);
        $answer['CONTENT'] = $func->show_string($template->template_content);
        $answer['CONTENT'] = $templates_controler->fieldsReplace(
            $answer['CONTENT'], $res_id, $coll_id
        );
        $answer['TITLE'] = $_SESSION['courrier']['res_id'] . '_' 
            . $answer['MODEL_LABEL'] . '_' . date('dmY');
    }
} elseif (!empty($id) && $mode == 'up') {
    $db->query("select title, res_id, path, docserver_id, filename from " 
        . $_SESSION['tablename']['attach_res_attachments'] . " where res_id = " . $id);

    if ($db->nb_result() < 1) {
        $_SESSION['error'] .= _FILE.' '._UNKNOWN.".<br/>";
    } else {
        $line = $db->fetch_object();
        $docserver = $line->docserver_id;
        $path = $line->path;
        $filename = $line->filename;
        $answer['TITLE'] = $func->show_string($line->title);
        $db->query("select path_template from " . $_SESSION['tablename']['docservers'] 
            . " where docserver_id = '" . $docserver . "'");
        $line_doc = $db->fetch_object();
        $docserver = $line_doc->path_template;
        $file = $docserver.$path.strtolower($filename);
        $file = str_replace('#', DIRECTORY_SEPARATOR, $file);
        $myfile = fopen($file, "r");
        $data = fread($myfile, filesize($file));
        fclose($myfile);
        $answer['CONTENT'] = $func->show_string($data);
    }
} else {
    $_SESSION['error'] .= _TEMPLATE_OR_ANSWER_ERROR . '.<br/>';
}
$core_tools->load_html();
$time = $core_tools->get_session_time_expire();
//here we building the header
if ($_REQUEST['mode'] == 'add') {
    $title =  _GENERATE_ANSWER." : ".$answer['TITLE'];
} elseif ($_REQUEST['mode'] == 'up') {
    $title = _ANSWER.' : '.$answer['TITLE'];
} else {
    $title =  _GENERATE_ANSWER;
}
$core_tools->load_header($title, true, false);
?>
<body onload="moveTo(0,0);resizeTo(screen.width, screen.height);setTimeout(window.close, <?php 
    echo $time;?>*60*1000);">
<?php
$_SESSION['mode_editor'] = false;
include('modules/templates/load_editor.php');?>
<div class="error"><?php  echo $_SESSION['error']; $_SESSION['error']= "";?></div>
<div align="center">
    <form name="frmtemplate" id="frmtemplate" method="post" action="<?php 
        echo $_SESSION['config']['businessappurl'];
        ?>index.php?display=true&module=templates&page=manage_generated_attachment&mode=<?php 
        echo $mode;?>">
    <input type="hidden" name="display" value="true" />
    <input type="hidden" name="module" value="templates" />
    <input type="hidden" name="page" value="manage_generated_attachment" />
    <?php
    if ($mode == "up") {
        ?>
        <input type="hidden" name="id" id="id" value="<?php echo $id;?>" />
        <?php
    }
    ?>
    <input type="hidden" name="res_id" id="res_id" value="<?php 
        echo $res_id;?>" />
    <input type="hidden" name="coll_id" id="coll_id" value="<?php 
        echo $coll_id;?>" />
    <input type="hidden" name="template_id" id="template_id" value="<?php 
        echo $answer['TEMPLATE_ID'];?>" />
    <input type="hidden" name="template_label" id="template_label" value="<?php 
        echo $answer['MODEL_LABEL'];?>" />
     <textarea name="template_content" style="width:100%" rows="50">
        <?php echo $answer['CONTENT'];?>
    </textarea>
    <br/>
    <p>
        <label><?php  echo _ANSWER_TITLE;?> :</label>
        <input type="text" name="answer_title" id="answer_title" value="<?php 
            echo $answer['TITLE']?>" style="width: 250px;" />
    </p>
    <p>
    <input type="submit" name="submit" id="submit" value="<?php 
        echo _VALIDATE;?>" class="button"/>
    <input type="button" name="cancel" id="cancel" value="<?php 
        echo _CANCEL;?>" class="button" onclick="window.close();" />
    </p>
    </form>
</div>
<?php $core_tools->load_js();?>
</body>
</html>
