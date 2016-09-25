<?php 

/**
* Module Brainstorm V2
* Operator : scale
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

$operator = new BrainstormOperator($cognitivefactory->id, $page);
$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, false);
$scalings = scale_get_scalings($cognitivefactory->id, null, $currentgroup, false, $operator->configdata);
echo $OUTPUT->heading("<img src=\"".$OUTPUT->pix_url('enabled_small', 'cognitiveoperator_scale')."\" align=\"left\" width=\"40\" /> " . get_string('givingweightstoideas', 'cognitiveoperator_'.$page));
?>
<center>
<?php
if (isset($operator->configdata->requirement))
    echo $OUTPUT->box($operator->configdata->requirement);
?>
<script>window.dhx_globalImgPath="<?php echo $CFG->wwwroot ?>/mod/cognitivefactory/js/dhtmlxSlider/codebase/imgs/";</script>
<form name="scaleform" action="view.php" method="POST">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php p($page) ?>" />
<input type="hidden" name="what" value="savescalings" />
<table cellspacing="5" width="80%" class="cognitiveoperator">
<?php
if ($responses) {
    $i = 0;
    foreach ($responses as $response) {
?>
    <tr valign="top">
        <th>
            <?php echo ($i + 1) ?>.
        </th>
        <td align="left">
            <?php echo $response->response ?>
        </td>
        <td align="left">
<?php
        switch(@$operator->configdata->quantifiertype) {
            case 'moodlescale':
                break;
            case 'integer':
                $value = (isset($scalings[$response->id])) ? $scalings[$response->id]->intvalue : '' ;
                echo "<input type=\"text\" size=\"10\" name=\"iscale_{$response->id}\" id=\"iscale_{$response->id}\" value=\"{$value}\" />"; 
                scale_build_slider("scale_{$response->id}", 250, $operator->configdata->minrange, $operator->configdata->maxrange, $value, 1);
                break;
            default:
                $value = (isset($scalings[$response->id])) ? sprintf("%.2f", $scalings[$response->id]->floatvalue) : '' ;
                echo "<input type=\"text\" size=\"10\" name=\"iscale_{$response->id}\"  id=\"iscale_{$response->id}\" value=\"{$value}\" />";
        }
?>
        </td>
<?php    
        $i++;    
    }
?>
    <tr>
        <td colspan="3">
            <input type="submit" name="go_btn" value="<?php print_string('savescaling', 'cognitiveoperator_'.$page) ?>" />
            &nbsp;<input type="button" name="clear_btn" value="<?php print_string('clearall', 'cognitiveoperator_'.$page) ?>" onclick="document.forms['scaleform'].what.value='clearall';document.forms['scaleform'].submit();" />
        </td>
    </tr>
<?php
} else {
    echo '<tr><td>';
    echo $OUTPUT->box(get_string('noresponses', 'cognitivefactory'), 'cognitivefactory-notification');
    echo '</td></tr>';
}
?>
</table>
</form>
<script type="text/javascript">
<?php
if ($operator->configdata->absolute) {
?>
var responsekeys = '<?php echo implode(",", array_keys($responses)) ?>';

function checkabsolute(fieldobj) {
    resplist = responsekeys.split(/,/);
    for (respid in resplist) {
        afield = document.forms['scaleform'].elements['scale_' + resplist[respid]];
        if (afield.value == fieldobj.value && afield.name != fieldobj.name) {
            alert("<?php print_string('absoluteconstraint', 'cognitiveoperator_'.$page) ?>");
            fieldobj.value = '';
            fieldobj.focus();
        }
    }    
}
<?php
} else {
?>
function checkabsolute(fieldobj) {
}
<?php
}
?>
</script>
</center>