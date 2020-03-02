<?php

/**
* Module Brainstorm V2
* Operator : order
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
// include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");


echo $OUTPUT->heading(get_string('mytree', 'cognitiveoperator_'.$page));

$tree = hierarchize_get_childs($cognitivefactory->id, null, 0, false);
?>
<center>
<style>
.response{ font-size : 1.2em ; border : 1px solid gray }
.subtree{ font-size : 1.0em ; border : 1px solid gray }
</style>
<form name="treeform" action="view.php" method="POST">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php p($page) ?>" />
<input type="hidden" name="what" value="" />
<table width="80%">
<?php
if ($tree) {
    if ($action == 'printindeepness') {
        echo hierarchize_print_levelindeepness($cognitivefactory->id, $cm, null, 0, 0);
?>
</table>
<table width="80%">
    <tr>
        <td align="center">
            <br/><input type="button" name="printdeep_btn" value="<?php print_string('printbyline', 'cognitiveoperator_'.$page) ?>" onclick="document.forms['treeform'].what.value='printbyline';document.forms['treeform'].submit();" />
        </td>
    </tr>
<?php
    }
    else{
        $i = 0;
        $indent = 25;
        foreach ($tree as $child) {
            $prefix = $i + 1;
?>
    <tr>
        <td style="text-align: left; margin-left : <?php $indent ?>px" class="response">
            <b><?php echo $prefix ?>.</b> <?php echo $child->response ?>
        </td>
    </tr>
<?php
            echo hierarchize_print_level($cognitivefactory->id, $cm, null, 0, false, $child->odid, $i+1, $indent, null, false);
            $i++;
        }
?>
    <tr>
        <td>
            <br/><input type="button" name="printdeep_btn" value="<?php print_string('printindeepness', 'cognitiveoperator_'.$page) ?>" onclick="document.forms['treeform'].what.value='printindeepness';document.forms['treeform'].submit();" />
        </td>
    </tr>
<?php
    }
}
else{
    echo '<tr><td>';
    echo $OUTPUT->box(get_string('notreeset', 'cognitiveoperator_'.$page), 'cognitivefactory-notification');
    echo '</td></tr>';
}
?>
</table>
</form>
