<?php

/**
* Module Brainstorm V2
* Operator : scale
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

$current_operator = new BrainstormOperator($cognitivefactory->id, $page);
$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, false);
$scalings = scale_get_scalings($cognitivefactory->id, null, 0, false, $current_operator->configdata);

print_heading(get_string('myscaling', 'cognitivefactory'));
scale_display($cognitivefactory, null, $currentgroup, $responses, $scalings);

print_heading(get_string('unscaled', 'cognitivefactory'));
scale_display_unscaled($cognitivefactory, null, $currentgroup, $responses, $scalings);

print_heading(get_string('otherscales', 'cognitivefactory'));
print_simple_box_start('center');

$otherscalings = scale_get_meanscalings($cognitivefactory->id, 0, $currentgroup, true, $current_operator->configdata);
if ($otherscalings){
    $absscalebounds = scale_get_scalebounds($cognitivefactory->id, 0, $currentgroup, false, $current_operator->configdata);
}
?>
<table cellspacing="5" width="80%">
<?php
if ($otherscalings && $absscalebounds->range != 0){
    $i = 0;
    $absoffset = ($absscalebounds->minvalue < 0) ? abs(($absscalebounds->minvalue / $absscalebounds->range) * $current_operator->configdata->barwidth) : 0 ;
    foreach($otherscalings as $other){
        switch($current_operator->configdata->quantifiertype){
            case 'integer':{
                $relmid = $current_operator->configdata->barwidth * ( ($other->sumintvalue  / $other->countintvalue) / $absscalebounds->range);
                $relstart = $current_operator->configdata->barwidth * ( $other->minintvalue / $absscalebounds->range);
                $relend = $current_operator->configdata->barwidth * ( $other->maxintvalue / $absscalebounds->range);
                $value = $other->sumintvalue / $other->countintvalue;
                break;
            }
            case 'float':{
                $relmid = $current_operator->configdata->barwidth * ( ($other->sumfloatvalue / $other->countfloatvalue) / $absscalebounds->range);
                $relstart = $current_operator->configdata->barwidth * ( $other->minfloatvalue / $absscalebounds->range);
                $relend = $current_operator->configdata->barwidth * ( $other->maxfloatvalue / $absscalebounds->range);
                $value = $other->sumfloatvalue / $other->countfloatvalue;
                break;
            }
            case 'moodlescale':{
                $relwidth = $current_operator->configdata->barwidth * ($other->blobvalue / $absscalebounds->range);
                $value = $other->blobvalue;
                break;
            }
        }
        $offset = $absoffset + $relstart ;
?>
    <tr valign="top">
        <th width="10%">
            <?php echo $i + 1 ?>.
        </th>
        <td align="left" width="30%">
            <?php echo $other->response ?>
            <?php // echo ' ' . $offset . ' ' . $absoffset . ' (' . $relstart . ',' . $relmid.','.$relend.')' ; ?>
        </td>
        <td align="left">
            <div style="position : relative ; left: <?php echo $offset ?>px">
            <img src="<?php $CFG->wwwroot ?>/mod/cognitivefactory/operators/scale/pix/transparent.gif"  style="width: <?php echo $relmid - $relstart - 2; ?>px" class="barrange" align="middle" /><img src="<?php $CFG->wwwroot ?>/mod/cognitivefactory/operators/scale/pix/transparent.gif"  style="width: 5px" class="barmid" align="middle" /><img src="<?php $CFG->wwwroot ?>/mod/cognitivefactory/operators/scale/pix/transparent.gif"  style="width: <?php echo $relend - $relmid - 2; ?>px" class="barrange" align="middle" /> <?php echo $value ?></div>
        </td>
    </tr>
<?php
        $i++;
    }
}
else{
    echo '<tr><td>';
    print_simple_box(get_string('nootherscalings', 'cognitivefactory'));
    echo '</td></tr>';
}
?>
</table>
<?php
print_simple_box_end();
?>
</center>