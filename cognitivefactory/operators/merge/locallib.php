<?php

/**
* Module Brainstorm V2
* Operator : merge
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
* @param object $configdata
*/
function merge_get_unassigned($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $configdata){
    global $CFG;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    
    $sql = "
        SELECT
            r.*, 
            od.itemsource
        FROM
            {$CFG->prefix}cognitivefactory_responses as r
        LEFT JOIN
            {$CFG->prefix}cognitivefactory_operatordata as od
        ON
            r.id = od.itemsource AND
            operatorid = 'merge'
        WHERE
            r.cognitivefactoryid = {$cognitivefactoryid} 
            {$accessClause}
            AND od.itemsource IS NULL
    ";
    if (!$records = get_records_sql($sql)){
        return array();
    }
    return $records;
}

/**
*
* @uses CFG, USER
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
* @param object $configdata
*/
function merge_get_assignations($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $configdata){
    global $CFG;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    
    $sql = "
        SELECT
            r.*,
            od.intvalue as slotid,
            od.itemdest as choosed,
            od.blobvalue as merged
        FROM
            {$CFG->prefix}cognitivefactory_responses as r,
            {$CFG->prefix}cognitivefactory_operatordata as od
        WHERE
            r.id = od.itemsource AND
            r.cognitivefactoryid = {$cognitivefactoryid} AND
            od.operatorid = 'merge'
            {$accessClause}
    ";
    if (!$records = get_records_sql($sql)){
        return array();
    }
    
    $assignations = array();
    foreach($records as $record){
        $assignations[$record->slotid][] = $record;
    }
    return $assignations;
}

/**
*
* @uses CFG
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
* @param object $configdata
*/
function merge_get_merges($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $configdata){
    global $CFG;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    
    $sql = "
        SELECT
            r.*,
            od.intvalue as slotid,
            od.blobvalue as merged
        FROM
            {$CFG->prefix}cognitivefactory_responses as r,
            {$CFG->prefix}cognitivefactory_operatordata as od
        WHERE
            r.id = od.itemsource AND
            r.cognitivefactoryid = {$cognitivefactoryid} AND
            od.operatorid = 'merge' AND
            od.itemdest IS NOT NULL
            {$accessClause}
        ORDER BY
            od.intvalue,
            userid
    ";
    if (!$merges = get_records_sql($sql)){
        return array();
    }
    return $merges;
}

/**
*
* @uses CFG, USER
* @param int $cognitivefactoryid
* @param int $slotid
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
*/
function merge_get_customentries($cognitivefactoryid, $slotid, $userid=null, $groupid=0, $excludemyself=false){
    global $CFG;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);

    $select = "
        cognitivefactoryid = {$cognitivefactoryid} AND
        operatorid = 'merge' AND
        intvalue = {$slotid} AND
        itemsource = 0
        {$accessClause}
    ";
    $records = get_records_select('cognitivefactory_operatordata AS od', $select);
    return $records;
}

/**
*
*
*/
function merge_get_dataset_from_query($prefix){
    $keys = preg_grep("/^$prefix/", array_keys($_POST));
    $dataset = array();
    if ($keys){
        foreach($keys as $key){
            preg_match("/^$prefix(.*)/", $key, $matches);
            $dataset[$matches[1]] = required_param($key, PARAM_RAW);
        }
    }
    return $dataset;
}

?>