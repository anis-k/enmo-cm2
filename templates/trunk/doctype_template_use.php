<?php
$db = new dbquery();
$core = new core_tools();
$core->load_lang();

if(empty($_SESSION['indexing_type_id']) || !isset($_SESSION['indexing_type_id']))
{
	// ERREUR
	exit();
}

$db->connect();
$db->query("select is_generated, template_id from ".$_SESSION['tablename']['temp_templates_doctype_ext']." where type_id = ".$_SESSION['indexing_type_id']);
$is_generated = 'N';
$template = '';
if($db->nb_result() > 0)
{
	$res = $db->fetch_object();
	$is_generated = $res->is_generated;
	$template = $res->template_id;
}

array_push($_SESSION['indexing_services'], array('script' => 'modules/templates/js/change_doctype.js', 'function_to_execute' => 'doctype_template', 'arguments' => array(
array('id' => 'is_generated' , 'value' =>$is_generated),
array('id'=> 'template_id', 'value' => $template),
array('id'=> 'doc_frame', 'value' => $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=file_iframe'),
array('id'=> 'model_frame', 'value' =>$_SESSION['config']['businessappurl'].'index.php?display=true&module=templates&page=file_iframe&model_id='.$template))));
?>
