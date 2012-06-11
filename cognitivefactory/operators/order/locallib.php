<?php

/**
* Module Brainstorm V2
* Operator : order
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/**
* get ordering on distinct contexts. Knows how to get an incomplete ordering.
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
*/
function order_get_ordering($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false){
    global $CFG;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);

    $sql = "
        SELECT
            r.id,
            r.response,
            od.intvalue,
            od.userid,
            od.groupid
        FROM
            {$CFG->prefix}cognitivefactory_responses as r
        LEFT JOIN
            {$CFG->prefix}cognitivefactory_operatordata as od
        ON
            r.id = od.itemsource AND
            (od.operatorid = 'order'
            {$accessClause})
        WHERE
            r.cognitivefactoryid = {$cognitivefactoryid}
         ORDER BY
            od.intvalue, 
            od.userid
    ";
    if (!$records = get_records_sql($sql)){
        return array();
    }
    return $records;
}

/**
* checks if there are ordering data for the given user context
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
*/
function has_ordering_data($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false){
    global $CFG;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);

    $sql = "
        SELECT
            COUNT(*)
        FROM
            {$CFG->prefix}cognitivefactory_responses as r,
            {$CFG->prefix}cognitivefactory_operatordata as od
        WHERE
            r.cognitivefactoryid = {$cognitivefactoryid} AND
            r.id = od.itemsource AND
            (od.operatorid = 'order'
            {$accessClause})
    ";
    return count_records_sql($sql);
}

/**
*
*
*/
function order_get_otherorderings($cognitivefactoryid, $orderedresponsekeys, $groupid=0){
    $orderings = order_get_ordering($cognitivefactoryid, 0, $groupid, true);
    $agree = array();
    $disagree = array();
    if ($orderings){
        foreach($orderings as $ordering){
            if (array_key_exists($ordering->intvalue, $orderedresponsekeys)) {
                if ($orderedresponsekeys[$ordering->intvalue] == $ordering->id){
                    $agree[$ordering->intvalue] = @$agree[$ordering->intvalue] + 1;
                }
                else{
                    $disagree[$ordering->intvalue] = @$disagree[$ordering->intvalue] + 1;
                }
            }
        }
    }
    $result->agree = &$agree;
    $result->disagree = &$disagree;
    return $result;
}

/**
*
*
*/
function order_display(&$cognitivefactory, $userid, $groupid){
    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $groupid, false, 'timemodified,id');
    $myordering = order_get_ordering($cognitivefactory->id, $userid, 0, false);
?>
<center>
<style>
.match { background-color : #54DE57 }
</style>
<table>
    <tr>
        <th>
            <?php print_string('original', 'cognitivefactory'); ?>
        </th>
        <th>
            <?php print_string('myordering', 'cognitivefactory'); ?>
        </th>
    </tr>
    <tr>
        <td>
<?php
if ($responses){
    $i = 0;
    echo '<table cellspacing="10">';
    $myorderingkeys = array_keys($myordering);
    foreach($responses as $response){
        $matchclass = ($response->id == @$myorderingkeys[$i]) ? 'match' : '';
?>
                <tr>
                    <th class="<?php echo $matchclass ?>">
                        <?php echo $i + 1 ?>.
                    </th>
                    <td>
                        <?php echo $response->response ?>
                    </td>
                </tr>
<?php
        $i++;
    }
    echo '</table>';
}
else{
    print_simple_box(get_string('noresponses', 'cognitivefactory'));    
}
?>
        </td>
        <td>
<?php
if ($myordering){
    $i = 0;
    echo '<table cellspacing="10">';
    $responsekeys = array_keys($responses);
    foreach($myordering as $response){
        $matchclass = ($response->id == @$responsekeys[$i]) ? 'match' : '';
?>
                <tr>
                    <th class="<?php echo $matchclass ?>">
                        <?php echo $i + 1 ?>.
                    </th>
                    <td>
                        <?php echo $response->response ?>
                    </td>
                </tr>
<?php
        $i++;
    }
    echo '</table>';
}
else{
    print_simple_box(get_string('noorderset', 'cognitivefactory'));
}
?>
        </td>
    </tr>
</table>
<?php
}
?>