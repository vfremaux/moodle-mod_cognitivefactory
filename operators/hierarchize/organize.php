<?php

/**
* Module Brainstorm V2
* Operator : order
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

if (!isset($current_operator)) {
    $current_operator = new BrainstormOperator($cognitivefactory->id, $page);
}

$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, false, $sort = 'timemodified,id');
$responses = hierarchize_refresh_tree($cognitivefactory->id, $currentgroup);
$tree = hierarchize_get_childs($cognitivefactory->id, null, $currentgroup, false, 0);

echo $OUTPUT->heading("<img src=\"".$OUTPUT->pix_url('enabled_small', 'cognitiveoperator_'.$page)."\" align=\"left\" width=\"40\" /> " . get_string('tree', 'cognitiveoperator_'.$page));
?>
<center>
<?php
if (isset($current_operator->configdata->requirement))
    echo $OUTPUT->box($current_operator->configdata->requirement);
?>
<style>
.response{ border : 1px solid gray ; background-color : #E1E1E1 }
</style>
<form name="treeform" action="view.php" method="POST">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php p($page) ?>" />
<input type="hidden" name="what" value="maketree" />
<table width="80%" cellspacing="5" class="cognitiveoperator">
<?php
if ($tree) {
    $i = 0;
    $indent = 25;
    $level = 1;
    $subscount = 0;
    foreach ($tree as $child) {
        $prefix = $i + 1;
        $up = ($i) ? "<a href=\"view.php?id={$cm->id}&amp;operator={$page}&amp;what=up&amp;item={$child->odid}\"><img src=\"".$OUTPUT->pix_url('t/up').'"></a>' : '&nbsp;' ;
        $down = ($i < count($tree) - 1) ? "<a href=\"view.php?id={$cm->id}&amp;operator={$page}&amp;what=down&amp;item={$child->odid}\"><img src=\"".$OUTPUT->pix_url('t/down').'"></a>' : '&nbsp;' ;
        $left = ($indent > 25) ? "<a href=\"view.php?id={$cm->id}&amp;operator={$page}&amp;what=left&amp;item={$child->odid}\"><img src=\"".$OUTPUT->pix_url('t/left').'"></a>' : '&nbsp;' ;
        if ((@$current_operator->configdata->maxarity && $subscount >= $current_operator->configdata->maxarity) || (@$current_operator->configdata->maxlevels && $level > $current_operator->configdata->maxlevels)) {
            $right = '';
        } else {
            $right = ($i) ? "<a href=\"view.php?id={$cm->id}&amp;operator={$page}&amp;what=right&amp;item={$child->odid}\"><img src=\"".$OUTPUT->pix_url('t/right').'"></a>' : '&nbsp;' ;
        }
?>
                <tr>
                    <td>
                        <table cellspacing="3">
                            <tr>
                                <td width="10">
                                    <?php echo $left ?>
                                 </td>
                                <td width="10">
                                    <?php echo $up ?>
                                 </td>
                                <td width="10">
                                    <?php echo $down ?>
                                 </td>
                                <td width="10">
                                    <?php echo $right ?>
                                 </td>
                             </tr>
                         </table>
                    </td>
                    <td style="text-align : left; padding-left : <?php echo ($indent - 23) ?>px" class="response">
                        <b><?php echo $prefix ?>.</b> <?php echo $child->response ?>
                    </td>
                </tr>
<?php
        echo hierarchize_print_level($cognitivefactory->id, $cm, null, $currentgroup, false, $child->odid, $i+1, $indent, $current_operator->configdata);
        $i++;
        $subscount = cognitivefactory_count_subs($child->odid); // get subs status of previous entry
    }
?>
    <tr>
        <td colspan="2">
            <br/><input type="button" name="clear_btn" value="<?php print_string('clearalldata', 'cognitiveoperator_'.$page) ?>" onclick="document.forms['treeform'].what.value='clearall';document.forms['treeform'].submit();" />
        </td>
    </tr>
<?php
}
else{
    echo '<tr><td>';
    echo $OUTPUT->box_start('center');
    print_string('notreeset', 'cognitiveoperator_'.$page);
?>
    <p><center><input type="submit" name="go_btn" value="<?php print_string('maketree', 'cognitiveoperator_'.$page) ?>" /></center></p>
<?php
    echo $OUTPUT->box_end();
    echo '</td></tr>';
}
?>
</table>
</form>