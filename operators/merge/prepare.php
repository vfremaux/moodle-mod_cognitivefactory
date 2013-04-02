<?PHP  // $Id: categorise.php,v 1.1 2004/08/24 16:40:57 diml Exp $

/**
* Module Brainstorm V2
* Operator : merge
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

if (!cognitivefactory_legal_include()){
    error("The way you loaded this page is not the way this script is done for.");
}

$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, 0);
$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);
$usehtmleditor = can_use_html_editor();

if (!isset($currentoperator->configdata->maxideasleft)){
    $currentoperator->configdata->maxideasleft = count($responses);
}
if (!isset($currentoperator->configdata->requirement)){
    $currentoperator->configdata->requirement = '';
}
if (!isset($currentoperator->configdata->allowreducesource)){
    $currentoperator->configdata->allowreducesource = 0;
}

$noselected = (!$currentoperator->configdata->allowreducesource) ? 'checked="checked"' : '' ;
$yesselected = ($currentoperator->configdata->allowreducesource) ? 'checked="checked"' : '' ;

print_heading(get_string('filtersettings', 'cognitivefactory'));
?>
<center>
<img src="<?php echo "$CFG->wwwroot/mod/cognitivefactory/operators/{$page}/pix/enabled.gif" ?>" align="left" />

<script type="text/javascript">
function warnclear(){
    alert('<?php print_string('warnclear', 'cognitivefactory') ?>');
    document.forms['prepareform'].shouldcleardata.value = 1;
}
</script>
<form name="prepareform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php echo $page ?>" />
<input type="hidden" name="what" value="saveconfig" />
<input type="hidden" name="shouldcleardata" value="" />
<table width="80%" cellspacing="10">
    <tr valign="top">
        <td align="right"><b><?php print_string('requirement', 'cognitivefactory') ?>:</b></td>
        <td align="left">
<?php
if ($cognitivefactory->oprequirementtype == 0){ 
?>       
            <input type="text" size="80" name="config_requirement" value="<?php echo stripslashes($currentoperator->configdata->requirement); ?>" />
<?php
}
elseif ($cognitivefactory->oprequirementtype == 2){ 
    print_textarea($usehtmleditor, 20, 50, 680, 400, 'config_requirement', stripslashes($currentoperator->configdata->requirement));
    if (!$usehtmleditor){        
        $cognitivefactory->oprequirementtype = 1;
    }
    else{
        $htmleditorneeded = true;
    }
}
elseif ($cognitivefactory->oprequirementtype == 1){ 
?>       
            <textarea style="width:100%;height:150px" name="config_requirement"><?php echo stripslashes($currentoperator->configdata->requirement); ?></textarea>
<?php
}
?>
            <?php helpbutton('requirement', get_string('requirement', 'cognitivefactory'), 'cognitivefactory'); ?>
        </td>
    </tr>
    <tr>
        <td align="right" width="50%"><b><?php print_string('maxideasleft', 'cognitivefactory') ?>:</b></td>
        <td align="left" width="50%">
            <?php
            $maxitems_options = array();
            for($i = 1 ; $i <= count($responses) ; $i++){
                $maxitems_options[$i] = $i;
            }
            choose_from_menu($maxitems_options, 'config_maxideasleft', $currentoperator->configdata->maxideasleft, 'choose', 'warnclear();');
            helpbutton('operatorrouter.html&amp;operator=merge&amp;helpitem=maxideasleft', get_string('maxideasleft', 'cognitivefactory'), 'cognitivefactory');
            ?>
        </td>
    </tr>
    <tr>
        <td align="right" width="50%"><b><?php print_string('allowreducesource', 'cognitivefactory') ?>:</b></td>
        <td align="left" width="50%">
            <input type="radio" name="config_allowreducesource" value="0" <?php echo $noselected ?> /> <?php print_string('no') ?>&nbsp;-&nbsp;
            <input type="radio" name="config_allowreducesource" value="1" <?php echo $yesselected ?> /> <?php print_string('yes') ?>
            <?php helpbutton('operatorrouter.html&amp;operator=merge&amp;helpitem=allowreducesource', get_string('allowreducesource', 'cognitivefactory'), 'cognitivefactory'); ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <br/><input type="submit" name="go_btn" value="<?php print_string('saveconfig', 'cognitivefactory') ?>" />
        </td>
    </tr>
</table>
</form>
</center>