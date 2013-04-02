<?PHP  // $Id: categorise.php,v 1.1 2004/08/24 16:40:57 diml Exp $

/**
* Module Brainstorm V2
* Operator : schedule
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);

if (!isset($currentoperator->configdata->quantifyedges)){
    $currentoperator->configdata->quantifyedges = 0;
}
if (!isset($currentoperator->configdata->quantifiertype)){
    $currentoperator->configdata->quantifiertype = 'float';
}

$noselected = (!@$currentoperator->configdata->quantifyedges) ? 'CHECKED' : '' ;
$yesselected = (@$currentoperator->configdata->quantifyedges) ? 'CHECKED' : '' ;
$multipleselected = ($currentoperator->configdata->quantifiertype == 'multiple') ? 'CHECKED' : '' ;
$integerselected = ($currentoperator->configdata->quantifiertype == 'integer') ? 'CHECKED' : '' ;
$floatselected = ($currentoperator->configdata->quantifiertype == 'float') ? 'CHECKED' : '' ;

print_heading(get_string("{$page}settings", 'cognitivefactory'));
?>
<center>
<img src="<?php echo "$CFG->wwwroot/mod/cognitivefactory/operators/{$page}/pix/enabled.gif" ?>" align="left" />

<form name="addform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php echo $page ?>" />
<input type="hidden" name="what" value="saveconfig" />
<table width="80%" cellspacing="10">
    <tr>
        <td align="right"><b><?php print_string('quantifyedges', 'cognitivefactory') ?>:</b></td>
        <td align="left">
            <input type="radio" name="config_quantifyedges" value="0" <?php echo $noselected ?> /> <?php print_string('no') ?>&nbsp;-&nbsp;
            <input type="radio" name="config_quantifyedges" value="1" <?php echo $yesselected ?> /> <?php print_string('yes') ?>
        </td>
    </tr>
    <tr>
        <td align="right"><b><?php print_string('quantifiertype', 'cognitivefactory') ?>:</b></td>
        <td align="left">
            <input type="radio" name="config_quantifiertype" value="integer" <?php echo $integerselected ?> /> <?php print_string('integer', 'cognitivefactory') ?>&nbsp;-&nbsp;
            <input type="radio" name="config_quantifiertype" value="float" <?php echo $floatselected ?> /> <?php print_string('float', 'cognitivefactory') ?>
            <input type="radio" name="config_quantifiertype" value="multiple" <?php echo $multipleselected ?> /> <?php print_string('multiple', 'cognitivefactory') ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <br/><input type="submit" name="go_btn" value="<?php print_string('saveconfig', 'cognitivefactory') ?>" />
        </td>
    </tr>
</table>
</form>
<?php
echo '</center>';
?>