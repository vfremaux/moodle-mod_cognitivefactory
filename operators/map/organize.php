<?php

/**
* Module Brainstorm V2
* Operator : map
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

echo $OUTPUT->heading("<img src=\"".$OUTPUT->pix_url('enabled_small', 'cognitiveoperator_map')."\" align=\"left\" width=\"40\" /> " . get_string("organizing{$page}", 'cognitiveoperator_'.$page));

$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, 0);
if (count($responses) > $MAP_MAX_DATA){
    echo $OUTPUT->notification(get_string('toomuchdata', 'cognitiveoperator_'.$page, $MAP_MAX_DATA));
    return;
}

if (!isset($current_operator)){
    $current_operator = new BrainstormOperator($cognitivefactory->id, $page);
}
$map = map_get_cells($cognitivefactory->id, $USER->id, $currentgroup, $current_operator->configdata);
?>
<center>
<?php
if (isset($current_operator->configdata->requirement))
    echo $OUTPUT->box($current_operator->configdata->requirement);
?>
<form name="mapform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php p($page) ?>"/>
<input type="hidden" name="what" value="savemappings" />
<center>
<table cellspacing="5" class="cognitiveoperator-map-table">
<?php
if ($responses){
    $width = 100 / count($responses) + 2;
    $titlewidth = $width * 2;

    /// draw top title line
    echo "<tr>\n";
    echo "<th width=\"{$titlewidth}%\" class=\"cognitiveoperator-map-tablecell\">&nbsp;</th>\n";
    foreach($responses as $responsecol){
        echo "<td width=\"{$width}%\" class=\"cognitiveoperator-map-tablecell\">{$responsecol->response}</td>\n";
    }
    echo "</tr>\n";

    foreach($responses as $responserow){
        echo "<tr>\n";
        echo "<th width=\"{$titlewidth}%\" class=\"cognitiveoperator-map-tablecell\">{$responserow->response}</th>\n";
        foreach($responses as $responsecol){
            if (!@$current_operator->configdata->quantified){
                $checked = (@$map[$responserow->id][$responsecol->id]) ? 'checked="checked"' : '' ;
                $mapcheck = "<input type=\"checkbox\" name=\"map_{$responserow->id}_{$responsecol->id}\" value=\"1\" $checked /> ";
                echo "<td width=\"{$width}%\" class=\"cognitiveoperator-map-tablecell\">$mapcheck</td>\n";
            } else {
                switch($current_operator->configdata->quantifiertype){
                    case 'multiple':
                        $itemdata = map_print_multiple_value(@$map[$responserow->id][$responsecol->id]);
                        if (!empty($itemdata)){
                            $itemdata .= '<br/>';
                            $maplink = "<a href=\"view.php?id={$cm->id}&amp;operator={$page}&amp;what=updatemultiple&amp;source={$responserow->id}&amp;dest={$responsecol->id}\"><img src=\"".$OTPUT->pix_url('t/edit').'" /></a>';
                            $maplink .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;operator={$page}&amp;what=deletemultiple&amp;source={$responserow->id}&amp;dest={$responsecol->id}\"><img src=\"".$OUTPUT->pix_url('t/delete').'" /></a>';
                        } else {
                            $maplink = "<a href=\"view.php?id={$cm->id}&amp;operator={$page}&amp;what=inputmultiple&amp;source={$responserow->id}&amp;dest={$responsecol->id}\">".get_string('inputdata', 'cognitivefactory').'</a>';
                        }
                        echo "<td width=\"{$width}%\" class=\"cognitiveoperator-map-tablecell\">{$itemdata}{$maplink}</td>\n";
                        break;                    
                    default:
                        $itemvalue = (isset($map[$responserow->id][$responsecol->id])) ? $map[$responserow->id][$responsecol->id] : '' ;
                        $mapinput = "<input type=\"text\" size=\"5\" name=\"{$page}_{$responserow->id}_{$responsecol->id}\" value=\"{$itemvalue}\" /> ";
                        echo "<td width=\"{$width}%\" class=\"maptablecell\">$mapinput</td>\n";
                        break;
                }
            }
        }
        echo "</tr>\n";
    }
} else {
    echo '<tr><td coslpan="3">';
    print_string('noresponses', 'cognitiveoperator_'.$page);
    echo '</td></tr>';
}
?>
    <tr>
        <td colspan="<?php echo count($responses) + 1; ?>">
            <br/><input type="submit" name="go_btn" value="<?php print_string('saveconnections', 'cognitiveoperator_'.$page) ?>" />
            &nbsp;<input type="button" name="clear_btn" value="<?php print_string('clearconnections', 'cognitiveoperator_'.$page) ?>" onclick="document.forms['mapform'].what.value='clearmappings';document.forms['mapform'].submit();" />
        </td>
    </tr>
</table>
</form>
</center>