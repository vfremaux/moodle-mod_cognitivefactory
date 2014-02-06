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
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself, 'od.');

    $sql = "
        SELECT
            r.id,
            r.response,
            od.intvalue,
            od.userid,
            od.groupid
        FROM
            {cognitivefactory_responses} as r
        LEFT JOIN
            {cognitivefactory_opdata} as od
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
    if (!$records = $DB->get_records_sql($sql)){
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
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself, 'od.');

    $sql = "
        SELECT
            COUNT(*)
        FROM
            {cognitivefactory_responses} as r,
            {cognitivefactory_opdata} as od
        WHERE
            r.cognitivefactoryid = {$cognitivefactoryid} AND
            r.id = od.itemsource AND
            (od.operatorid = 'order'
            {$accessClause})
    ";
    return $DB->count_records_sql($sql);
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
                } else {
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
function order_display(&$cognitivefactory, $userid, $groupid, $return = false){
    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $groupid, false, 'timemodified,id');
    $myordering = order_get_ordering($cognitivefactory->id, $userid, 0, false);

	$str = '<table>';
	$str .= '<tr>';
	$str .= '<th>';
	$str .= get_string('original', 'cognitiveoperator_order');
	$str .= '</th>';
	$str .= '<th>';
	$str .= get_string('myordering', 'cognitiveoperator_order');
	$str .= '</th>';
	$str .= '</tr>';
	$str .= '<tr>';
	$str .= '<td>';
	if ($responses){
		$i = 0;
    	$str .= '<table class="operator">';
	    $myorderingkeys = array_keys($myordering);
	    foreach($responses as $response){
	        if ($response->id == @$myorderingkeys[$i]){
	        	$matchclass = 'cognitivefactory-match';
	        } else {
	        	// fetch the absolute distance
	        	$d = -1;
	        	if (isset($myorderingkeys)){
		        	for($j = 0; $j < count($myorderingkeys) ; $j++){
		        		if ($response->id == @$myorderingkeys[$j]){
		        			$d = min(abs($i - $j), 10);
		        			break;
		        		}
		        	}
		        }
	        	$matchclass = 'cognitivefactory-nomatch';
	        }
			$str .= '<tr>';
			$str .= '<th class="<?php echo $matchclass ?>">';
			$str .= ($i + 1).'.';
			$str.= '</th>';
			$str .= '<td>';
            $str .= $response->response;
			$str .= '</td>';
			$str .= '</tr>';
        	$i++;
    	}
    	$str .= '</table>';
	} else {
    	$str .= $OUPTUT->box(get_string('noresponses', 'cognitivefactory'));    
	}
	$str .= '</td>';
	$str .= '<td>';
	if ($myordering){
	    $i = 0;
    	$str .= '<table cellspacing="10">';
    	$responsekeys = array_keys($responses);
    	foreach($myordering as $response){
        	if ($response->id == @$responsekeys[$i]){
        		$matchclass = 'match';
        	} else {
	        	// fetch the absolute distance
	        	$d = -1;
	        	if (isset($responsekeys)){
		        	for($j = 0; $j < count($responsekeys) ; $j++){
		        		if ($response->id == @$responsekeys[$j]){
		        			$d = min(abs($i - $j), 10);
		        			break;
		        		}
		        	}
		        }
	        	$matchclass = 'nomatch';
	        }
			$str .= '<tr>';
			$str .= '<th class="'.$matchclass.'">';
			$str .= ($i + 1).'.';
			$str .= '</th>';
			$str .= '<td>';
			$str .= $response->response;
			$str .= '</td>';
			$str .= '</tr>';
        	$i++;
    	}
    	$str .= '</table>';
	} else {
    	$str .= $OUTPUT->box(get_string('noorderset', 'cognitivefactory'));
	}
	$str .= '</td>';
	$str .= '</tr>';
	$str .= '</table>';
	
	if ($return) return $str;
	echo $str;
}
