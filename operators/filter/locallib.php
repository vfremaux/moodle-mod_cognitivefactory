<?php

/**
* Module Brainstorm V2
* Operator : filter
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/**
*
* @uses CFG, USER
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid 
* @param boolean $excludemyself
*/
function filter_get_status($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false) {
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself, 'od.');
    $sql = "
        SELECT
            itemsource,
            intvalue,
            od.userid,
            od.groupid,
            response
         FROM
            {cognitivefactory_opdata} AS od,
            {cognitivefactory_responses} AS r
         WHERE
            od.cognitivefactoryid = {$cognitivefactoryid} AND
            od.itemsource = r.id AND
            operatorid = 'filter'
            {$accessClause}
    ";
    if (!$statusrecords = $DB->get_records_sql($sql)) {
        $statusrecords = array();
    }    
    return $statusrecords;
}

/**
* displays filter information for a user
*
*/
function filter_display(&$cognitivefactory, $userid, $groupid, $return = false) {
    global $OUTPUT;
    
    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $groupid, false);
    $responsesids = array_keys($responses);
    $user_states = filter_get_status($cognitivefactory->id, $userid, $groupid, false);
    $statusesids = array_keys($user_states);
    $other_user_states = filter_get_status($cognitivefactory->id, 0, $groupid, false /* excludes me */);

    // counting allusers and each response choice
    $otherusers = array();

    if (!empty($other_user_states)) {
        foreach ($other_user_states as $astate) {
            @$trends[$astate->itemsource]++;
        }
        $otherusers[$astate->userid] = 1;
    }
    $otheruserscount = count(array_keys($otherusers));
    
    $str = '';   

    $str .= '<table cellspacing="5" class="generaltable" width="70%">';
    $str .= '<tr>';
    $str .= '<th class="header">';
    $str .= '</th>';
    $str .= '<th class="header" align="left">';
    $str .= get_string('filtereddata', 'cognitiveoperator_filter');
    $str .= '</th>';
    $str .= '<th class="header" align="center">';
    $str .= get_string('otherstrend', 'cognitivefactory');
    $str .= '</th>';
    $str .= '</tr>';
    $i = 0;
    foreach ($responses as $response) {
        $match = (in_array($response->id, $statusesids)) ? 'cognitivefactory-match' : '' ;
        $str .= '<tr class="row">';
        $str .= '<th class="'.$match.'" class="cell r'.($i % 2).'">';
        $str .= '<b>'.($i + 1).'.</b>';
        $str .= '</th>';
        $str .= '<td class="cell r'.($i % 2).'">';
        $str .= $response->response;
        $str .= '</td>';
        $str .= '<td align="center" class="cell r'.($i % 2).'">';
        if ($otheruserscount) {
            if (!empty($trends[$response->id])) {
                $str .= sprintf('%02d', 100 * $trends[$response->id] / $otheruserscount).' %';
            }
        }
        $str .= '</td>';
        $str .= '</tr>';
        $i++;
    }
    $str .= '</table>';
    
    if ($return) return $str;
    echo $str;
}

/**
* prints all other user explicit results
*
*/
function filter_display_others(&$cognitivefactory, $currentgroup, $return = true) {
    global $OUTPUT;
    
    $otherstatuses = filter_get_status($cognitivefactory->id, 0, $currentgroup, true);
    $str = '';

/// sorting and dispatching

    if (empty($otherstatuses)) {
        $str .= $OUTPUT->box(get_string('nootherstatuses', 'cognitiveoperator_filter'), 'cognitivefactory-notification');
        if ($return) return $str;
        echo $str;
        return;
    }

    foreach ($otherstatuses as $astatus) {
        $others[$astatus->userid][] = $astatus;
    }

    echo $OUTPUT->heading(get_string('otherfilters', 'cognitiveoperator_'.$page));

    $cols = 0;
    $str .= '<table width="100%">';
    $str .= '<tr>';
    $str .= '<td>';

    foreach (array_keys($others) as $userid) {
        $user = $DB->get_record('user', array('id' => $userid));
        $str .= $OUTPUT->heading(fullname($user));
        $str .= filter_display($cognitivefactory, $userid, $groupid, true);
        if ($cols && $cols % $cognitivefactory->numcolumns == 0) {
            echo "</td></tr><tr><td>";
        } else {
            echo "</td><td>";
        }
        $cols++;
    }
    $str .= '</td>';
    $str .= '</tr>';
    $str .= '</table>';
    
    if ($return) return $str;
    echo $str;
}

