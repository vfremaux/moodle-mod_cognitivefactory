<?php

/**
* Module Brainstorm V2
* Operator : categories
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/**
*
* @param int $cognitivefactoryid
*/
function categorize_get_categories($cognitivefactoryid, $userid=null, $groupid=0) {
    global $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);
    $select = "
        cognitivefactoryid = $cognitivefactoryid
        {$accessClause}
    ";

    if (!$categories = $DB->get_records_select('cognitivefactory_categories', $select)) {
        $categories = array() ;
    }
    return $categories;
}

/**
*
* @uses CFG, USER
* @param int $cognitivefactoryid
* @param int $userid
*/
function categorize_get_responsespercategories($cognitivefactoryid, $userid, $groupid, $excludemyself=false) {
    global $CFG, $USER, $DB;
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself, 'od.');
    $struncategorized = get_string('uncategorized', 'cognitiveoperator_categorize');
    $responses = array();
    $categories = $DB->get_records('cognitivefactory_categories', array('cognitivefactoryid' => $cognitivefactoryid));
    if ($categories) {
        foreach ($categories as $category) {
            $sql = "
                SELECT
                    od.id as odid,
                    r.*,
                    od.userid as opuserid
                FROM
                    {cognitivefactory_opdata} as od,
                    {cognitivefactory_responses} as r
                WHERE
                    od.itemsource = r.id AND
                    od.cognitivefactoryid = {$cognitivefactoryid} AND
                    od.operatorid = 'categorize' AND
                    od.itemdest = $category->id
                    {$accessClause}
            ";
            $responses[$category->title] = $DB->get_records_sql($sql);        
        }
    }
    // get responses outside categories
    $sql = "
        SELECT
            r.*
        FROM
            {cognitivefactory_responses} as r
        LEFT JOIN
            {cognitivefactory_opdata} as od
        ON
            od.itemsource = r.id AND
            operatorid = 'categorize'
        WHERE
            od.itemdest IS NULL AND
            od.cognitivefactoryid = {$cognitivefactoryid}
    ";
    $responses[$struncategorized] = $DB->get_records_sql($sql);        
    return $responses;
}

/**
*
* @uses CFG, USER
* @param int $cognitivefactoryid
* @param int $userid
*/
function categorize_get_categoriesperresponses($cognitivefactoryid, $userid=null, $groupid=0) {
    global $CFG, $USER, $DB;
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false, 'od.');

    $categorized = array();
    $sql = "
        SELECT
            od.id as odid,
            r.*,
            od.itemdest as category
        FROM
            {cognitivefactory_responses} as r
        LEFT JOIN
            {cognitivefactory_opdata} as od
        ON
            od.itemsource = r.id AND
            operatorid = 'categorize'
        WHERE
            od.cognitivefactoryid = {$cognitivefactoryid}
            {$accessClause}
    ";
    $allresponses = $DB->get_records_sql($sql); 
    if ($allresponses) {
        foreach ($allresponses as $response) {
            $categorized[$response->id]->response = &$response;
            if (!empty($response->category)) {
                $categorized[$response->id]->categories[] = $response->category;
            }
            else{
                $categorized[$response->id]->categories = array();
            }
        }
    }
    return $categorized;
}

/**
* returns an array of matching indicators by response.
* @uses CFG, USER
* @param int $braintormid
* @param int $userid
*/
function categorize_get_matchings($cognitivefactoryid, $userid=null, $groupid=0) {
    global $CFG, $DB;
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, true);
    /// get interesting responses
    $select = "
       cognitivefactoryid = '{$cognitivefactoryid}' AND
       operatorid = 'categorize'
       $accessClause
    ";
    $allcategorizations = $DB->get_records_select('cognitivefactory_opdata', $select);

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);
    /// get interesting responses
    $select = "
       cognitivefactoryid = '{$cognitivefactoryid}' AND
       operatorid = 'categorize'
       $accessClause
    ";
    $mycategorizations = $DB->get_records_select('cognitivefactory_opdata', $select);
    /// compile our values first
    $reference = array();
    if ($mycategorizations) {
        foreach ($mycategorizations as $cat) {
            $reference[$cat->itemsource][] = $cat->itemdest;
        }
    }

    if ($allcategorizations && !empty($reference)) {            
        /// compile values for other users
        foreach ($allcategorizations as $cat) {
            if (!in_array($cat->itemsource, array_keys($reference))) continue ; // discard those responses we did not give any assignation
            if (@in_array($cat->itemdest, $reference[$cat->itemsource])) {
                @$match[$cat->itemsource]++;
            } else {
                @$unmatch[$cat->itemsource]++;
            }
        }
        $matchings = new StdClass();
        $matchings->match = &$match;
        $matchings->unmatch = &$unmatch;
    } else {
        $matchings = new StdClass();
        $matchings->match = array();
        $matchings->unmatch = array();
    }

    return $matchings;
}

/**
* displays categorization for a user
*
*/
function categorize_display(&$cognitivefactory, $userid, $groupid, $return = false) {
    $responses = categorize_get_responsespercategories($cognitivefactory->id, $userid, $groupid);
    $cols = 0;

    $str = '';
    $str .= '<center>';
    $str .= '<table width="80%">';
    $str .= '<tr valign="top">';

    foreach (array_keys($responses) as $acategoryname) {
        if ($cols && $cols % $cognitivefactory->numcolumns == 0) {
            $str .= '</tr><tr valign="top">';
        }

        $str .= '<td>';
        $str .= '<table width="90%">';
        $str .= '<tr>';
        $str .= '<th colspan="2">';
        $str .= format_string($acategoryname);
        $str .= '</th>';
        $str .= '</tr>';
        $index = 1;
        if ($responses[$acategoryname]) {
            foreach ($responses[$acategoryname] as $aresponse) {
                $str .= '<tr>';
                $str .= '<th>';
                $str .= $index;
                $str .= '</th>';
                $str .= '<td>';
                $str .= format_string($aresponse->response);
                $str .= '</td>';
                $str .= '</tr>';
                $index++;
            }
        } else {
            $str .= '<tr>';
            $str .= '<td colspan="2">';
            $str .= get_string('nothinghere', 'cognitiveoperator_categorize');
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '</table>';
        $str .= '</td>';
        $cols++;
    }
    $str .= '</tr>';
    $str .= '</table>';
    $str .= '</center>';
    
    if ($return) return $str;
    echo $str;
}
