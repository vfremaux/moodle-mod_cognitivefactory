<?php

/**
* Module Brainstorm V2
* Operator : filter
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

    include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
    include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

    echo '<center>';

    echo $OUTPUT->heading($OUTPUT->pix_icon('enabled_small', '', 'cognitiveoperator_'.$page).' '.get_string("organizing{$page}", 'cognitiveoperator_'.$page));

    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, 0);

    if (!isset($current_operator)) { // if was not set by a controller
        $current_operator = new BrainstormOperator($cognitivefactory->id, $page);
    }

    $filterstatus = filter_get_status($cognitivefactory->id);

/// module seems it is not configured

    if (!isset($current_operator->configdata->maxideasleft)) {
        echo $OUTPUT->box(get_string('notconfigured', 'cognitiveoperator_'.$page));
        return;
    }

/// print organizing interface

    $toeliminate = max(0, count($responses) - $current_operator->configdata->maxideasleft);
    echo $OUTPUT->box(get_string('responses', 'cognitiveoperator_'.$page)." <span id=\"leftcount\">$toeliminate</span>".' '.get_string('responsestoeliminate', 'cognitiveoperator_'.$page));
    if (isset($current_operator->configdata->requirement)) {
        if (is_array($current_operator->configdata->requirement)) {
            echo $OUTPUT->box(format_string($current_operator->configdata->requirement['text'], $current_operator->configdata->requirement['format']), 'cognitivefactory-notification');
        } else {
            echo $OUTPUT->box($current_operator->configdata->requirement, 'cognitivefactory-notification');
        }
    }

?>
<form name="filterform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php p($page) ?>" />
<input type="hidden" name="what" value="savefiltering" />
<script type="text/javascript" src="<?php echo $CFG->wwwroot.'/mod/cognitivefactory/operators/filter/js/module.js' ?>"></script>

<script type="text/javascript">
var maxleft = <?php echo 0 + @$current_operator->configdata->maxideasleft ?>;
var total = <?php echo 0 + count($responses) ?>;
var checks = new Array();
var candeletemore = <?php echo 0 + @$current_operator->configdata->candeletemore ?>;
</script>

<table width="80%" cellspacing="5" class="cognitiveoperator">    
<?php
    $i = 0;
    $checks = 0;
    foreach ($responses as $response) {
        $checked = (@$filterstatus[$response->id]->intvalue || empty($filterstatus)) ? "checked=\"checked\"" : '' ;
        if ($checked) $checks++;
        $class = (@$filterstatus[$response->id]->intvalue || empty($filterstatus)) ? 'cognitiveoperator-filter-kept' : 'cognitiveoperator-filter-deleted' ;
?>
    <tr valign="top">
        <td align="right" class="<?php echo $class ?>" id="tdc_<?php echo $i?>">
            <input type="checkbox" id="sel_<?php echo $i ?>" name="keep_<?php p($response->id); ?>" value="1" <?php echo $checked ?> onclick="toggleCheck(this, <?php echo $i ?>)" />
            <input type="hidden" id="shadow_<?php echo $i ?>" name="keep_shadow_<?php p($response->id); ?>" value="" />
        </td>
        <td align="left" class="<?php echo $class ?>" id="tdr_<?php echo $i?>">
            <?php echo $response->response ?>
        </td>
     </tr>
<?php
    $i++;
}

$disabled = ($checks > @$operator->configdata->maxideasleft || ($checks < @$operator->configdata->maxideasleft && empty($operator->configdata->candeletemore))) ? ' disabled="disabled" ' : '' ;
?>
    <tr>
        <td colspan="2">
            <input type="submit" id="go1" name="go_btn" value="<?php print_string('saveordering', 'cognitiveoperator_'.$page) ?>" <?php echo $disabled ?> />
            &nbsp;<input type="button" name="startproc_btn" value="<?php print_string('startpaircompare', 'cognitiveoperator_'.$page) ?>" onclick="confirmprocedure();" />
            <script language="">
                function confirmprocedure() {
                    <?php $confirmmessage = get_string('confirmpaircompare', 'cognitiveoperator_'.$page); ?>
                    if (confirm("<?php echo $confirmmessage ?>")) {
                        document.forms['filterform'].what.value = 'startpaircompare';
                        document.forms['filterform'].submit();                
                    }
                }
            </script>
<?php
if (!empty($current_operator->configdata->allowreducesource)) {
?>
            &nbsp;<input type="button" id="go2" name="reduce_btn" value="<?php print_string('saveorderingandreduce', 'cognitiveoperator_'.$page) ?>" onclick="document.forms['filterform'].what.value='saveandreduce';document.forms['filterform'].submit();" />
<?php
}
?>
        </td>
    </tr>
</table>
</form>
<script type="text/javascript">
initChecksStates();
</script>
</center>