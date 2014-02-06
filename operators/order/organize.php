<?php

/**
* Module Brainstorm V2
* Operator : order
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

$responses = order_get_ordering($cognitivefactory->id, null, $currentgroup, false);
$current_operator = new BrainstormOperator($cognitivefactory->id, $page);
if (has_ordering_data($cognitivefactory->id, null, $currentgroup, false)){
    $class = 'saved';
}
else{
    $class = 'unsaved';
}

if (!@$current_operator->configdata->blindness){
    $otherorderings = order_get_otherorderings($cognitivefactory->id, array_keys($responses), $currentgroup);
}

$totalspan = 4;

echo $OUTPUT->heading("<img src=\"".$OUTPUT->pix_url('enabled_small', 'cognitiveoperator_order')."\" align=\"left\" width=\"40\" /> " . get_string('puttingideasinorder','cognitiveoperator_order'));
?>
<center>
<?php
if (isset($current_operator->configdata->requirement))
    echo $OUTPUT->box($current_operator->configdata->requirement);
?>
<style>
.response{ border : 1px solid gray ; background-color : #E1E1E1 }
.saved{ background-color : #BEF4BB }
.unsaved{ background-color : #F7BFB9 }
.op{ width : 10px }
.invisible{ font-size : 0.8em ; visibility : hidden }
.visible{ font-size : 0.85em ; visibility : visible }
</style>
<form name="orderform" action="view.php" method="POST">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php p($page) ?>" />
<input type="hidden" name="what" value="saveorder" />
<script type="text/javascript">
function up(ix){
    $fromelm = document.getElementById('row' + ix);
    $toelm = document.getElementById('row'+ (ix - 1));

    var value = document.forms['orderform'].elements['order_'+ ix].value;
    document.forms['orderform'].elements['order_'+ ix].value = document.forms['orderform'].elements['order_'+ (ix - 1)].value;
    document.forms['orderform'].elements['order_'+ (ix - 1)].value = value;
    value = $fromelm.innerHTML;
    $fromelm.innerHTML = $toelm.innerHTML;
    $toelm.innerHTML = value;
    markunsaved();
}

function down(ix){
    nid = parseInt(ix) + 1;
    $fromelm = document.getElementById('row' + ix);
    $toelm = document.getElementById('row'+ nid);

    var value = document.forms['orderform'].elements['order_'+ ix].value;
    document.forms['orderform'].elements['order_'+ ix].value = document.forms['orderform'].elements['order_'+ nid].value;
    document.forms['orderform'].elements['order_'+ nid].value = value;
    value = $fromelm.innerHTML;
    $fromelm.innerHTML = $toelm.innerHTML;
    $toelm.innerHTML = value;
    markunsaved();
}

function markunsaved(){
    for(i = 0 ; i < <?php echo count($responses) ?> ; i++){
        elm = document.getElementById('num_' + i);
        elm.className = 'unsaved';
        elm = document.getElementById('others_agree_' + i);
        elm.className = 'invisible';
        elm = document.getElementById('others_disagree_' + i);
        elm.className = 'invisible';
    }
}
</script>
<table cellspacing="5" cellpadding="2" width="80%">
<?php
if ($responses){
    $i = 0;
    foreach($responses as $response){
        $up = ($i) ? "<a href=\"javascript:up('{$i}')\"><img src=\"".$OUTPUT->pix_url('t/up').'"></a>' : '' ;
        $down = ($i < count($responses) - 1) ? "<a href=\"javascript:down('{$i}')\"><img src=\"".$OUTPUT->pix_url('t/down').'"></a>' : '' ;
        $field = "<input type=\"hidden\" name=\"order_{$i}\" value=\"{$response->id}\" />";
?>
        <tr>
            <td class="op"><?php echo $up ?></td>
            <td class="op"><?php echo $down ?></td>
            <td id="num_<?php p($i) ?>" class="<?php echo $class ?>">
                <?php echo $field ?><b><?php echo ($i + 1) ?></b>
            </td>
            <td id="row<?php p($i) ?>" align="left" class="response">
                  <?php echo $response->response; ?>
            </td>
<?php
        if (!@$current_operator->configdata->blindness && $class == 'saved'){
            $totalspan += 2;
?>
            <td align="left" width="25%"><span id="others_agree_<?php p($i) ?>" class="visible">
                <?php
                if (!empty($otherorderings->agree)){
                    if (array_key_exists($i, $otherorderings->agree)){
                        if ($otherorderings->agree[$i] == 1)
                            print_string('agreewithyousingle', 'cognitivefactory', $otherorderings->agree[$i]);
                        else
                            print_string('agreewithyou', 'cognitivefactory', $otherorderings->agree[$i]);
                    }
                }
                ?></span>
            </td>
            <td align="left" width="25%"><span id="others_disagree_<?php p($i) ?>" class="visible">
                <?php
                if (!empty($otherorderings->disagree)){
                    if (array_key_exists($i, $otherorderings->disagree)){
                        if ($otherorderings->disagree[$i] == 1)
                            print_string('disagreewithyousingle', 'cognitivefactory', $otherorderings->disagree[$i]);
                        else
                            print_string('disagreewithyou', 'cognitivefactory', $otherorderings->disagree[$i]);
                    }
                }
                ?></span>
            </td>
<?php
        }
        echo '</tr>';
        $i++;
    }
?>
    <tr>
        <td colspan="<?php echo $totalspan; ?>">
            <input type="submit" name="go_btn" value="<?php print_string('saveorder', 'cognitiveoperator_order') ?>" />
            &nbsp;<input type="button" name="clear_btn" value="<?php print_string('clearall', 'cognitivefactory') ?>" onclick="document.forms['orderform'].what.value='clearall';document.forms['orderform'].submit();" />
            &nbsp;<input type="button" name="startproc_btn" value="<?php print_string('startpaircompare', 'cognitiveoperator_order') ?>" onclick="confirmprocedure();" />
            <script language="">
                function confirmprocedure(){
                    if (confirm("<?php print_string('confirmpaircompare', 'cognitiveoperator_order') ?>")){
                        document.forms['orderform'].what.value='startpaircompare';
                        document.forms['orderform'].submit();                
                    }
                }
            </script>
        </td>
    </tr>
<?php
}
else{
    echo '<tr><td>';
    print_simple_box(get_string('noorderset', 'cognitiveoperator_order'));
    echo '</td></tr>';
}
?>
</table>
</form>