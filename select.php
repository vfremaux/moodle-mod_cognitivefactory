<?php

/**
* @package cognitivefactory
* @author Martin Ellermann
* @review Valery Fremaux / 1.8
* @date 22/12/2007
*
*/

if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

print_heading(get_string('chooseoperators', 'cognitivefactory'));
?>
<center>
<table>
    <tr valign="top">
        <td width="20%" align="left">
            <span style="font-size : 90%"><?php print_string('chooseoperatornotice', 'cognitivefactory') ?></span> 
        </td>
        <td>
            <table cellspacing="10">
                <tr>
<?php
$index = 0;
foreach($operators as $operator){
    if ($index && ($index % 4 == 0)){
        echo '</tr><tr>';
    }
    echo '<td align="center">';
    if ($operator->active){
        echo "<a href=\"view.php?id={$cm->id}&amp;what=disable&amp;operatorid={$operator->id}\"><img src=\"{$CFG->wwwroot}/mod/cognitivefactory/operators/{$operator->id}/pix/enabled.gif\" border=\"0\"></a><br/>".get_string($operator->id, 'cognitivefactory');
    }
    else{
        echo "<a href=\"view.php?id={$cm->id}&amp;what=enable&amp;operatorid={$operator->id}\"><img src=\"{$CFG->wwwroot}/mod/cognitivefactory/operators/{$operator->id}/pix/disabled.gif\" border=\"0\"></a><br/>".get_string($operator->id, 'cognitivefactory');
    }
    echo '</td>';
    $index++;
}
?>
                </tr>
            </table>
        </td>
    </tr>
</table>
</center>     