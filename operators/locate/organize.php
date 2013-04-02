<?php

/**
* Module Brainstorm V2
* Operator : locate
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

print_heading("<img src=\"{$CFG->wwwroot}/mod/cognitivefactory/operators/{$page}/pix/enabled_small.gif\" align=\"left\" width=\"40\" /> " . get_string("organizing$page", 'cognitivefactory'));

$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup);
if (!isset($current_operator)){
    $current_operator = new BrainstormOperator($cognitivefactory->id, $page);
}

if (!isset($current_operator->configdata->xminrange)){
    echo '<center>';
    print_simple_box(get_string('notconfigured', 'cognitivefactory'));
    echo '</center>';
    return;
}

$responses_locations = locate_get_locations($cognitivefactory->id, $USER->id);

$strresponses = get_string('responses', 'cognitivefactory');
$strxquantifier = get_string('xquantifier', 'cognitivefactory');
$stryquantifier = get_string('yquantifier', 'cognitivefactory');
?>
<center>
<?php
$intro = (isset($current_operator->configdata->requirement)) ? $current_operator->configdata->requirement . '<br/>' : '' ;
$intro .= get_string('datarangeis','cognitivefactory');
$intro .= ' : X['.$current_operator->configdata->xminrange.','.$current_operator->configdata->xmaxrange.'] ';
$intro .= ' : Y['.$current_operator->configdata->yminrange.','.$current_operator->configdata->ymaxrange.'] ';
print_simple_box($intro);
?>
<form name="locateform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php p($page) ?>"/>
<input type="hidden" name="what" value="savelocations" />
<table width="90%" cellspacing="5">
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
            switch (@$current_operator->configdata->quantifiertype){
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
            <input type="text" name="xquantifier_<?php p($response->id) ?>" value="<?php echo $locationdata->x ?>" />
        </td>
        <td align="center">
            <input type="text" name="yquantifier_<?php p($response->id) ?>" value="<?php echo $locationdata->y ?>" />
        </td>
<?php
        if (!@$current_operator->configdata->blindness){
            $matchgroup = (!$groupmode) ? 0 : $currentgroup ;
            // $means = locate_get_means($cognitivefactory->id, $USER->id, $matchgroup);
            if (@$current_operator->configdata->neighbourhood > 0){
                $neighbours = locate_get_neighbours($cognitivefactory->id, $locationdata->x, $locationdata->y, $response->id, $current_operator->configdata, $USER->id, $currentgroup);
?>
        <td align="left">
            <?php
                if ($neighbours){
                    print_string('isinneighbourhood', 'cognitivefactory', $neighbours);
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
            <br/><input type="submit" name="go_btn" value="<?php print_string('savelocations', 'cognitivefactory') ?>" />
        </td>
    </tr>
</table>
</form>
</center>