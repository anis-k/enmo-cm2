<?php
require_once("core/class/class_core_tools.php");
require_once("modules/life_cycle/class/lc_cycles_controler.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$cyclesArray = lc_cycles_controler::getAllIdByPolicy($_POST['policy_id']);
$selectCycle = '';
$selectCycle .= '<p>';
$selectCycle .= '<label for="cycle_id">'. _CYCLE_ID.' : </label>';
$selectCycle .= '<select name="cycle_id" id="cycle_id">';
$selectCycle .= '	<option value="">'._CYCLE_ID.'</option>';
for($cptCycle=0;$cptCycle<count($cyclesArray);$cptCycle++){
	$selectCycle .= '<option value="'.$cyclesArray[$cptCycle].'"';
	if($_SESSION['m_admin']['lc_cycle_steps']['cycle_id'] == $cyclesArray[$cptCycle]) { 
		$selectCycle .= ' selected="selected"';
	}
	$selectCycle .= '>'.$cyclesArray[$cptCycle].'</option>';
}
$selectCycle .= '</select>';
$selectCycle .= '</p>';
echo "{status : 0, selectCycle : '" . addslashes($selectCycle) . "'}";
exit ();
?>
