<?PHP  // $Id: categorise.php,v 1.1 2004/08/24 16:40:57 diml Exp $

/**
* Module Brainstorm V2
* Operator : hierarchize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

if (!cognitivefactory_legal_include()){
    error("The way you loaded this page is not the way this script is done for.");
}

$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);
$usehtmleditor = can_use_html_editor();

if (!isset($currentoperator->configdata->maxlevels)){
    $currentoperator->configdata->maxlevels = 0;
}
if (!isset($currentoperator->configdata->maxarity)){
    $currentoperator->configdata->maxarity = 0;
}
if (!isset($currentoperator->configdata->requirement)){
    $currentoperator->configdata->requirement = '';
}

print_heading(get_string("{$page}settings", 'cognitivefactory'));

?>
<center>
<img src="<?php echo "{$CFG->wwwroot}/mod/cognitivefactory/operators/{$page}/pix/enabled.gif" ?>" align="left" />

<form name="addform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php echo $page ?>" />
<input type="hidden" name="what" value="saveconfig" />
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
        <td align="right"><b><?php print_string('maxlevels', 'cognitivefactory') ?>:</b></td>
        <td align="left">
            <?php
            $maxitems_options[0] = get_string('unlimited', 'cognitivefactory');
            for($i = 1 ; $i <= 10 ; $i++){
                $maxitems_options[$i] = $i;
            }
            choose_from_menu($maxitems_options, 'config_maxlevels', $currentoperator->configdata->maxlevels);
            helpbutton('operatorrouter.html&amp;operator=hierarchize&amp;helpitem=maxlevels', get_string('maxlevels', 'cognitivefactory'), 'cognitivefactory');
            ?>
        </td>
    </tr>
    <tr>
        <td align="right"><b><?php print_string('maxarity', 'cognitivefactory') ?>:</b></td>
        <td align="left">
            <?php
            $maxitems_options[0] = get_string('unlimited', 'cognitivefactory');
            for($i = 1 ; $i <= 10 ; $i++){
                $maxitems_options[$i] = $i;
            }
            choose_from_menu($maxitems_options, 'config_maxarity', $currentoperator->configdata->maxarity);
            helpbutton('operatorrouter.html&amp;operator=hierarchize&amp;helpitem=maxarity', get_string('maxarity', 'cognitivefactory'), 'cognitivefactory');
            ?>
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