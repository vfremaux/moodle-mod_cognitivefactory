<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package cognitivefactory
 * @author Martin Ellermann
 * @review Valery Fremaux / 1.8
 * @date 22/12/2007
 *
 * This page shows view for collecting interface. View may change whether 
 * we are 'teacher' or 'student'. 
 * 
 * If we are student we have following states :
 * * We work in parallel mode OR we are in sequential and this phase is allowed to students
 * - recollection is closed. We can see collected ideas depending on group and
 * privacy switch
 * - recollection is open.
 *    - we did not yet provide ideas and work in privacy against other users : we are inclined to 
 *             and rerouted to input form
 *    - we did not yet provide ideas and work in collaboration with other users : we see ideas of the group
 *             and a call to add own.
 *    - we provided ideas : we can see them and add more if max number of imputs has not been reached
 *
 * Teacher view
 * teacher view is sightly different, because he will have a monitoring capbility
 */

if (!defined('MOODLE_INTERNAL')) {
    die('You cannot use this script directly');
}

// Check capabilities.

$ismanager = has_capability('mod/cognitivefactory:manage', $context);
$seeall = has_capability('mod/cognitivefactory:seeallinputs', $context);

// Get viewable responses.
$myresponses = cognitivefactory_get_responses($cognitivefactory->id, $USER->id);

if ($seeall) {
    $otherresponses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, true);
} else {
    if ($groupmode && $currentgroup) {
        $otherresponses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, true);
    } elseif ($groupmode == 0 && !$cognitivefactory->privacy) {
        $otherresponses = cognitivefactory_get_responses($cognitivefactory->id, 0, 0, true);
    } else {
        $otherresponses = array();
    }
}
// Just display responses, sorted in alphabetical order.

echo $OUTPUT->heading(get_string('collectingideas', 'cognitivefactory'));

if (!empty($cognitivefactory->collectrequirement)) {
    echo $OUTPUT->box($cognitivefactory->collectrequirement);
}

echo $OUTPUT->box_start('center');
if (!empty($myresponses) || !empty($otherresponses)) {
    if ($ismanager) {
?>
<form name="deleteform" method="post">
<input type="hidden" name="id" value="<?php p($cm->id)?>" />
<input type="hidden" name="what" value="deleteitems" />
<?php
    }
?>
<p><table align="center" width="80%">
    <tr>
        <td colspan="<?php echo $cognitivefactory->numcolumns * 2 ?>">
            <?php echo $OUTPUT->heading(get_string('myresponses', 'cognitivefactory'), 3) ?>
        </td>
    </tr>
    <tr valign="top">
<?php
    cognitivefactory_print_responses_cols($cognitivefactory, $myresponses, false, $ismanager);
?>
    </tr>
</table></p>
<?php
    if (!$cognitivefactory->privacy || $seeall) {
?>
<p>
<table align="center" width="80%">
    <tr>
        <td colspan="<?php echo $cognitivefactory->numcolumns * 2 ?>">
            <?php echo $OUTPUT->heading(get_string('otherresponses', 'cognitivefactory'), 3) ?>
        </td>
    </tr>
    <tr>
<?php
        $index = 0;
        foreach ($otherresponses as $response) {
            $deletecheckbox = ($ismanager) ? "<input type=\"checkbox\" name=\"items[]\" value=\"{$response->id}\" /> " : '' ;
            if ($index && $index % $cognitivefactory->numcolumns == 0) {
                echo '</tr><tr>';
            }
            echo '<th>' . ($index+1) . '</th>';
            echo '<td>' . $deletecheckbox.$response->response . '</td>';
            $index++;
        }
?>
    </tr>
</table></p>
<?php
        if ($ismanager) {
            echo '</form>';
        }
    }
} else {
    print_string('notsubmitted', 'cognitivefactory');
}

// Now we check if we need fetching more responses.

/*
 * We should get more responses if :
 *    We have not reached max responses required && there is limitation AND
 *    Collecting phase is not over for us (timed or manually switched)
 */
if ((($cognitivefactory->flowmode == 'parallel' || $cognitivefactory->phase == PHASE_COLLECT) and
     (($cognitivefactory->numresponses > count($myresponses)) || $cognitivefactory->numresponses == 0)) or $ismanager) {
    echo '<br/><center><table><tr>';
    // $options = array ('id' => "$cm->id", 'view' => 'collect', 'what' => 'collect');
    // guest froup not member should not interfer here, although he could se our ideas.
    if (!$groupmode or groups_is_member($currentgroup) or $ismanager) {
        echo '<td>';
        $options = array(
            'id' => $cm->id,
            'view' => 'collect',
            'what' => 'collect'
        );
        echo $OUTPUT->single_button(new moodle_url($url, $options), get_string('addmoreresponses','cognitivefactory'));
        echo '</td>';
    }

    if ($ismanager) {
        $options = array(
            'id' => $cm->id,
            'view' => 'collect',
            'what' => 'clearall'
        );
        echo $OUTPUT->single_button(new moodle_url($url, $options), get_string('clearall','cognitivefactory'));
        echo '</td>';
        echo '<td>';
        echo '<input type="button" name="deleteitems_btn" value="'.get_string('deleteselection','cognitivefactory').'" onclick="document.forms[\'deleteform\'].submit();" />';
        echo '</td>';
    }
    if (has_capability('mod/cognitivefactory:import', $context)) {
        echo '<td>';
        $options = array(
            'id' => $cm->id,
        );
        echo $OUTPUT->single_button(new moodle_url('/mod/cognitivefactory/import.php', $options), get_string('importideas', 'cognitivefactory'));
        echo '</td>';
    }
    echo '</tr></table>';
}
echo $OUTPUT->box_end();
