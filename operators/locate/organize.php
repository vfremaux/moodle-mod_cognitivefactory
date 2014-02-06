<?php

/**
* Module Brainstorm V2
* Operator : locate
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

echo $OUTPUT->heading("<img src=\"".$OUTPUT->pix_url('enabled_small', 'cognitiveoperator_locate')."\" align=\"left\" width=\"40\" /> " . get_string("organizing{$page}", 'cognitiveoperator_'.$page));

$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup);
$operator = new BrainstormOperator($cognitivefactory->id, $page);

if (!isset($operator->configdata->xminrange)){
    echo '<center>';
    echo $OUTPUT->box(get_string('notconfigured', 'cognitiveoperator_'.$page));
    echo '</center>';
    return;
}

$responses_locations = locate_get_locations($cognitivefactory->id, $USER->id);

$strresponses = get_string('responses', 'cognitivefactory');
$strxquantifier = get_string('xquantifier', 'cognitiveoperator_'.$page);
$stryquantifier = get_string('yquantifier', 'cognitiveoperator_'.$page);
?>
<center>
<?php
$intro = (isset($operator->configdata->requirement)) ? $operator->configdata->requirement . '<br/>' : '' ;
$intro .= get_string('datarangeis', 'cognitiveoperator_'.$page);
$intro .= ' : X['.$operator->configdata->xminrange.','.$operator->configdata->xmaxrange.'] ';
$intro .= ' : Y['.$operator->configdata->yminrange.','.$operator->configdata->ymaxrange.'] ';
echo $OUTPUT->box($intro);
?>
<script>window.dhx_globalImgPath="<?php echo $CFG->wwwroot ?>/mod/cognitivefactory/js/dhtmlxSlider/codebase/imgs/";</script>
<form name="locateform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php p($page) ?>"/>
<input type="hidden" name="what" value="savelocations" />
<table width="90%" cellspacing="5" class="cognitiveoperator">
    <tr>
        <th>
            <?php echo $strresponses ?>
        </th>
        <th>
            <?php echo $strxquantifier ?>
        </th>
        <th>
            <?php echo $stryquantifier ?>
        </th>
    </tr>
<?php
if (count($responses)){
    foreach($responses as $response){
        $locationdata = @unserialize($responses_locations[$response->id]->blobvalue);
        if (!$locationdata){
        	$locationdata = new StdClass();
            switch (@$operator->configdata->quantifiertype){
                case 'integer' :
                    $locationdata->x = '0';
                    $locationdata->y = '0';
                    break;
                case 'float' :
                    $locationdata->x = '0.0';
                    $locationdata->y = '0.0';
                    break;
                default :
                    $locationdata->x = '';
                    $locationdata->y = '';
            }
        }
?>
    <tr valign="top">
        <td align="right">
            <?php echo $response->response ?>
        </td>
        <td align="center">
            <input type="text" size="3" name="ixquantifier_<?php p($response->id) ?>" id="ixquantifier_<?php p($response->id) ?>" value="<?php echo $locationdata->x ?>" />
            <?php locate_build_slider("xquantifier_{$response->id}", 150, $operator->configdata->xminrange, $operator->configdata->xmaxrange, $locationdata->x, 1); ?>
        </td>
        <td align="center">
            <input type="text" size="3" name="iyquantifier_<?php p($response->id) ?>" id="iyquantifier_<?php p($response->id) ?>" value="<?php echo $locationdata->y ?>" />
            <?php locate_build_slider("yquantifier_{$response->id}", 150, $operator->configdata->yminrange, $operator->configdata->ymaxrange, $locationdata->x, 1); ?>
        </td>
<?php
        if (!@$current_operator->configdata->blindness){
            $matchgroup = (!$groupmode) ? 0 : $currentgroup ;
            // $means = locate_get_means($cognitivefactory->id, $USER->id, $matchgroup);
            if (@$current_operator->configdata->neighbourhood > 0){
                $neighbours = locate_get_neighbours($cognitivefactory->id, $locationdata->x, $locationdata->y, $response->id, $operator->configdata, $USER->id, $currentgroup);
?>
        <td align="left">
            <?php
                if ($neighbours){
                    print_string('isinneighbourhood', 'cognitiveoperator_'.$page, $neighbours);
                }
            ?>
        </td>
<?php
            }
        }
?>
    </tr>    
<?php
    }
}
?>
    <tr>
        <td colspan="3">
            <br/><input type="submit" name="go_btn" value="<?php print_string('savelocations', 'cognitiveoperator_'.$page) ?>" />
        </td>
    </tr>
</table>
</form>
</center>