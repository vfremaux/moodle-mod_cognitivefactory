<?php

/**
* Module Brainstorm V2
* Operator : filter
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ("{$CFG->dirroot}/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("{$CFG->dirroot}/mod/cognitivefactory/operators/operator.class.php");
?>
<center>
<?php
$current_operator = new BrainstormOperator($cognitivefactory->id, $page);
print_heading(get_string('myfilter', 'cognitivefactory'));
filter_display($cognitivefactory, null, $currentgroup);

/// printing status for other users
$otherstatuses = filter_get_status($cognitivefactory->id, 0, $currentgroup, true);

/// sorting and dispatching
foreach($otherstatuses as $astatus){
    $others[$astatus->userid][] = $astatus;
}

print_heading(get_string('otherfilters', 'cognitivefactory'));
print_simple_box_start('center');
$cols = 0;
if (!empty($others)){
?>
<table>
    <tr>
        <td>
<?php
foreach(array_keys($others) as $userid){
    $user = get_record('user', 'id', $userid);
    print_heading(fullname($user), 'h2');
    echo '<table cellspacing="5">';
    $i = 0;
    foreach($others[$userid] as $status){
        $match = (in_array($status->itemsource, $statusesids)) ? 'match' : 'nomatch' ;
?>
        <tr>
            <th class="<?php echo $match?>">
                <?php echo ($i + 1) ?>.
            </th>
            <td align="left">
                <?php echo $status->response ?>
            </td>
        </tr>
<?php
        $i++;
    }
    echo '</table>';
    if ($cols && $cols % $cognitivefactory->numcolumns == 0){
        echo "</td></tr><tr><td>";
    }
    else{
        echo "</td><td>";
    }
    $cols++;
}
?>
        </td>
    </tr>
</table>
<?php
}
else{
    print_string('nootherstatuses', 'cognitivefactory');
}
print_simple_box_end();
?>