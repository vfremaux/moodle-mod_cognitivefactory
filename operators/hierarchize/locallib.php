<?php

include_once "{$CFG->dirroot}/mod/cognitivefactory/treelib.php";

/**
* Module Brainstorm V2
* Operator : hierarchize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/**
*
* @uses CFG
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
* @param int $fatherid
* @returns array
*/
function hierarchize_get_childs($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $fatherid=0) {
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself, 'od.');
    $fatherClause = ($fatherid != 0) ?  " AND od.itemdest = $fatherid " : ' AND (od.itemdest IS NULL OR od.itemdest = 0) ';
    $sql = "
        SELECT
            r.id,
            r.response,
            od.itemdest,
            od.intvalue,
            od.userid,
            od.groupid,
            od.id as odid
        FROM
            {cognitivefactory_responses} as r,
            {cognitivefactory_opdata} as od
        WHERE
            r.id = od.itemsource AND
            operatorid = 'hierarchize' AND
            r.cognitivefactoryid = ?
            {$accessClause}
            {$fatherClause}
         ORDER BY
            od.intvalue, 
            od.userid
    ";
    // echo $sql;
    if (!$records = $DB->get_records_sql($sql, array($cognitivefactoryid))) {
        return array();
    }
    return $records;
}


/**
*
* @param int $cognitivefactoryid
* @param array $referencelevelkeys
* @param int $groupid
* @param int $fatherid
*/
function hierarchize_get_otherchilds($cognitivefactoryid, $referencelevelkeys, $groupid=0, $fatherid=0) {
    $allchilds = order_get_childs($cognitivefactoryid, 0, $groupid, true, $fatherid);
    $agree = array();
    $disagree = array();
    if ($allchilds) {
        foreach ($allchilds as $child) {
            if ($referencelevelkeys[$child->intvalue] == $child->id) {
                $agree[$child->intvalue] = @$agree[$child->intvalue] + 1;
            } else {
                $disagree[$child->intvalue] = @$disagree[$child->intvalue] + 1;
            }
        }
    }
    $result->agree = &$agree;
    $result->disagree = &$disagree;
    return $result;
}

/**
* refreshes the tree data if new responses where in in the meanwhile
*
*/
function hierarchize_refresh_tree($cognitivefactoryid, $groupid=0) {
    global $CFG, $USER, $DB;

    // get those responses who are new
    $sql = "
        SELECT
            r.id,r.id
        FROM
            {cognitivefactory_responses} as r
        WHERE
            r.cognitivefactoryid = {$cognitivefactoryid} AND
            r.groupid = {$groupid} AND
            r.id NOT IN
        (SELECT
            od.itemsource
        FROM
            {cognitivefactory_opdata} as od
        WHERE
            od.cognitivefactoryid = $cognitivefactoryid AND
            operatorid = 'hierarchize' AND
            od.groupid = ? AND
            od.userid = ?)
    "; 
    // echo $sql;
    $diff = $DB->get_records_sql($sql, array($groupid, $USER->id));
    $maxordering = cognitivefactory_tree_get_max_ordering($cognitivefactoryid, null, $groupid, 1, 0);
    if ($diff) {
        $treerecord = new StdClass;
        $treerecord->cognitivefactoryid = $cognitivefactoryid;
        $treerecord->userid = $USER->id;
        $treerecord->groupid = $groupid;
        $treerecord->operatorid = 'hierarchize';
        $treerecord->itemdest = 0;
        $treerecord->intvalue = $maxordering + 1;
        $treerecord->timemodified = time();
        foreach ($diff as $adif) {
            $treerecord->itemsource = $adif->id;
            if (!$DB->insert_record('cognitivefactory_opdata', $treerecord)) {
                print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            }
            $treerecord->intvalue++;
        }
    }
}

/**
*
* @param int $cognitivefactoryid
* @param object $cm
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
* @param int $fatherid
* @param string $prefix
* @param int $indent
* @param boolean $editing
*/
function hierarchize_print_level($cognitivefactoryid, $cm, $userid, $groupid, $excludemyself, $fatherid, $prefix, $indent, $configdata, $editing=true) {
    global $CFG, $OUTPUT;

    $str = '';
    
    $subs = hierarchize_get_childs($cognitivefactoryid, $userid, $groupid, $excludemyself, $fatherid);
    if (!empty($subs)) {
        $i = 0;
        $indent += 25;
        $level = $indent / 25;
        $subscount = 0;
        foreach ($subs as $sub) {
            $levelprefix = $prefix . '.' . ($i + 1);
            $str .= '<tr>';
            if ($editing) {
                $up = ($i) ? "<a href=\"view.php?id={$cm->id}&amp;operator=hierarchize&amp;what=up&amp;item={$sub->odid}\"><img src=\"".$OUTPUT->pix_url('t/up').'"></a>' : '' ;
                $down = ($i < count($subs) - 1) ? "<a href=\"view.php?id={$cm->id}&amp;operator=hierarchize&amp;what=down&amp;item={$sub->odid}\"><img src=\"".$OUTPUT->pix_url('t/down').'"></a>' : '' ;
                $left = ($indent > 25) ? "<a href=\"view.php?id={$cm->id}&amp;operator=hierarchize&amp;what=left&amp;item={$sub->odid}\"><img src=\"".$OUTPUT->pix_url('t/left').'"></a>' : '' ;
                if ((isset($configdata->maxarity) && $configdata->maxarity && $subscount >= $configdata->maxarity) || (isset($configdata->maxlevels) && $configdata->maxlevels && $level >= $configdata->maxlevels)) {
                    $right = '';
                } else {
                    $right = ($i) ? "<a href=\"view.php?id={$cm->id}&amp;operator=hierarchize&amp;what=right&amp;item={$sub->odid}\"><img src=\"".$OUTPUT->pix_url('t/right').'"></a>' : '' ;
                }
                $str .= '<td>';
                $str .= '<table cellspacing="3">';
                $str .= '<tr>';
                $str .= '<td width="10">';
                $str .= $left;
                $str .= '</td>';
                $str .= '<td width="10">';
                $str .= $up;
                $str .= '</td>';
                $str .= '<td width="10">';
                $str .= $down;
                $str .= '</td>';
                $str .= '<td width="10">';
                $str .= $right;
                $str .= '</td>';
                $str .= '</tr>';
                $str .= '</table>';
                $str .= '</td>';
            }
            $str .= '<td style="text-align : left; padding-left : '.($indent - 23).'px" class="response">';
            $str .= '<b>'.$levelprefix.'.</b> '.$sub->response;
            $str .= '</td>';
            $str .= '</tr>';
            $str .= hierarchize_print_level($cognitivefactoryid, $cm, $userid, $groupid, $excludemyself, $sub->odid, $levelprefix, $indent, $configdata, $editing);
            $i++;
            $subscount = cognitivefactory_count_subs($sub->odid); // get subs status of previous entry
        }
    }
    
    return $str;
}

/**
*
* @param int $cognitivefactoryid
* @param object $cm
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
* @param string $previouslevel
*/
function hierarchize_print_levelindeepness($cognitivefactoryid, $cm, $userid=null, $groupid=0, $excludemyself=false, $fatherid=0) {
    global $CFG;
    
    $subs = hierarchize_get_childs($cognitivefactoryid, $userid, $groupid, $excludemyself, $fatherid);
    // $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);

    if (!empty($subs)) {
        // get column spanning counts
        $idlist = join("','", array_keys($subs));
        $str .= '<tr valign="top">';        
        foreach ($subs as $sub) {
            $str .= '<td class="subtree">';
            $str .= $sub->response;
            $str .= '<br/><table width="100%">';                       
            $str .= hierarchize_print_levelindeepness($cognitivefactoryid, $cm, $userid, $groupid, $excludemyself, $sub->odid);
            $str .= '</table>';
            $str .= '</td>';
        }
        $str .= '</tr>';        
    }
    
    return $str;
}

/**
*
*
*/
function hierarchize_display(&$cognitivefactory, $userid, $groupid, $return = false) {
    $tree = hierarchize_get_childs($cognitivefactory->id, $userid, $groupid);
    
    $str = '';

    if ($tree) {
        $str .= '<table width="80%">';
        $str .= hierarchize_print_levelindeepness($cognitivefactory->id, $cognitivefactory->cm, $userid, $groupid, 0);
        $str .= '</table>';
    } else {
        $str = get_string('notreeset', 'cognitiveoperator_hierarchize');
    }
    
    if ($return) return $str;
    echo $str;
}
