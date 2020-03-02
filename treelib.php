<?php  // $Id: treelib.php,v 1.2 2012-07-07 17:49:23 vf Exp $

// Project : Technical Project Manager (IEEE like)
// Author : Valery Fremaux (France) (admin@www.ethnoinformatique.fr)
// Contributors : LUU Tao Meng, So Gerard (parts of treelib.php), Guillaume Magnien, Olivier Petit

/// Library of tree dedicated operations. This library is adapted from the treelib.php in techproject, 
/// but wrapped back for cognitivefactory. The standard API for tree operation has been respected, although
/// the function parameters may have some changes.

/**
Major change resides in where the tree is stored. In the cognitivefactory module, the tree is stored
as records in operatordata. The itemsource field identifies the node. The itemdest stands for father's
id. The intvalue will serve as ordering
*/

/*** Index of functions **********************
/// the function parameters may have some changes.
function cognitivefactory_tree_delete($cognitivefactoryid, $userid, $groupid, $id) {
function tree_delete_rec($id) {
function cognitivefactory_tree_updateordering($cognitivefactoryid, $groupid, $userid, $id, $istree) {
function cognitivefactory_tree_up($cognitivefactoryid, $userid, $groupid, $id, $istree = 1) {
function cognitivefactory_tree_down($cognitivefactoryid, $userid, $groupid, $id, $istree=1) {
function cognitivefactory_tree_left($cognitivefactoryid, $userid, $groupid, $id) {
function cognitivefactory_tree_right($cognitivefactoryid, $userid, $groupid, $id) {
function cognitivefactory_get_subtree_list($table, $id) {
function cognitivefactory_count_subs($id) {
function cognitivefactory_tree_get_upper_branch($id, $includeStart = false, $returnordering = false) {
function cognitivefactory_tree_get_max_ordering($cognitivefactoryid, $userid=null, $groupid=0, $istree = false, $fatherid = 0) {
**********************************************/

/**
* deletes into tree a full branch. note that it will work either
* @param id the root node id
* @param table the table where the tree is in 
* @param istree if istree is not set, considers table as a simple ordered list
* @return an array of deleted ids
*/
function cognitivefactory_tree_delete($cognitivefactoryid, $userid, $groupid, $id) {
        cognitivefactory_tree_updateordering($cognitivefactoryid, $userid, $groupid, $id, 1);
        return tree_delete_rec($id);
}

/**
* deletes recursively a node and its subnodes. this is the recursion deletion
* @return an array of deleted ids
*/
function tree_delete_rec($id) {
    global $CFG, $DB;

    $deleted = array();
    if (empty($id)) return $deleted;    

    // echo "deleting $id<br/>";
    // getting all subnodes to delete if is tree.
    if ($istree) {
        $sql = "
            SELECT 
                id
            FROM 
                {{$table}cognitivefactory_opdata}
            WHERE
                operatorid = 'hierarchize' AND
                itemdest = {$id}
        ";
        // deleting subnodes if any
        if ($subs = $DB->get_record_sql($sql)) {
            foreach ($subs as $aSub) {
                $deleted = array_merge($deleted, tree_delete_rec($aSub->id));
            }
        }
    }
    // deleting current node
    $DB->delete_records('cognitivefactory_opdata', array('id' => $id)); 
    $deleted[] = $id;
    return $deleted;
}

/**
* updates ordering of a tree branch from a specific node, reordering 
* all subsequent siblings. 
* @param id the node from where to reorder
* @param table the table-tree
*/
function cognitivefactory_tree_updateordering($cognitivefactoryid, $groupid, $userid, $id, $istree) {

    // getting ordering value of the current node
    global $CFG, $DB;

    $res =  $DB->get_record('cognitivefactory_opdata', array('id' => $id));
    if (!$res) return;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid);
    $treeClause = ($istree) ? "     AND itemdest = {$res->itemdest} " : '';

    // getting subsequent nodes that have same father
    $sql = "
        SELECT 
            id   
        FROM 
            {cognitivefactory_opdata} AS od
        WHERE 
            cognitivefactoryid = {$cognitivefactoryid} AND
            operatorid = 'hierarchize' AND
            intvalue > {$res->intvalue}
            {$treeClause}
            {$accessClause}
        ORDER BY 
            intvalue
    ";

    // reordering subsequent nodes using an object
    if ( $nextsubs = $DB->get_record_sql($sql)) {
        $ordering = $res->intvalue + 1;
        foreach ($nextsubs as $asub) {
            $opdata = new StdClass();
            $opdata->id = $asub->id;
            $opdata->intvalue = $ordering;
            $DB->update_record('cognitivefactory_opdata', $opdata);
            $ordering++;
        }
    }
}

/**
* raises a node in the tree, reordering all what needed
* @param id the id of the raised node
* @param table the table-tree where to operate
* @param istree true if is a table-tree rather than a table-list
* @return void
*/
function cognitivefactory_tree_up($cognitivefactoryid, $userid, $groupid, $id, $istree = 1) {
    global $CFG, $DB;

    $res =  $DB->get_record('cognitivefactory_opdata', array('id' => $id));
    if (!$res) return;
    $operator = ($istree) ? 'hierarchize' : 'order' ;
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);
    $treeClause = ($istree) ? "     AND itemdest = {$res->itemdest} " : '';

    if ($res->intvalue > 1) {
        $newordering = $res->intvalue - 1;

        $sql = "
            SELECT 
                id
            FROM 
                {cognitivefactory_opdata} AS od
            WHERE                 
                cognitivefactoryid = {$cognitivefactoryid} AND
                operatorid = '{$operator}' AND
                intvalue = {$newordering}
                {$treeClause}
                {$accessClause}
        ";
        // echo $sql;
        $result =  $DB->get_record_sql($sql);
        $resid = $result->id;

        // swapping
        $opdata = new StdClass();
        $opdata->id = $resid;
        $opdata->intvalue = $res->intvalue;
        $DB->update_record('cognitivefactory_opdata', $opdata);

        $opdata = new StdClass();
        $opdata->id = $id;
        $opdata->intvalue = $newordering;
        $DB->update_record('cognitivefactory_opdata', $opdata);
    }
}

/**
* lowers a node on its branch. this is done by swapping ordering.
* @param project the current project
* @param group the current group
* @param id the node id
* @param table the table-tree where to perform swap
* @param istree if not set, performs swapping on a single list
*/
function cognitivefactory_tree_down($cognitivefactoryid, $userid, $groupid, $id, $istree=1) {
    global $CFG, $DB;

    $res =  $DB->get_record('cognitivefactory_opdata', array('id' => $id));
    $operator = ($istree) ? 'hierarchize' : 'order' ;
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);
    $treeClause = ($istree) ? " AND itemdest = {$res->itemdest} " : '';

    $sql = "
        SELECT 
            MAX(intvalue) AS ordering
        FROM 
            {cognitivefactory_opdata} AS od
        WHERE
            cognitivefactoryid = {$cognitivefactoryid} AND
            operatorid = '{$operator}'
            {$treeClause}
            {$accessClause}
    ";
    $resmaxordering = $DB->get_record_sql($sql);
    $maxordering = $resmaxordering->ordering;
    if ($res->intvalue < $maxordering) {
        $newordering = $res->intvalue + 1;

        $sql = "
            SELECT 
                id
            FROM    
                {cognitivefactory_opdata} AS od
            WHERE 
                cognitivefactoryid = {$cognitivefactoryid} AND
                operatorid = '{$operator}' AND
                intvalue = {$newordering}
                {$treeClause}
                {$accessClause}
        ";
        $result =  $DB->get_record_sql($sql);
        $resid = $result->id;

        // swapping
        $opdata = new StdClass();
        $opdata->id = $resid;
        $opdata->intvalue = $res->intvalue;
        $DB->update_record('cognitivefactory_opdata', $opdata);

        $opdata = new StdClass();
        $opdata->id = $id;
        $opdata->intvalue = $newordering;
        $DB->update_record('cognitivefactory_opdata', $opdata);
    }
}

/**
* raises a node to the upper level. Subsequent nodes become sons of the raised node
* @param int cognitivefactoryid the current module
* @param int $groupid the current group
* @param int $id the node to be raised
*/
function cognitivefactory_tree_left($cognitivefactoryid, $userid, $groupid, $id) {
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);

    $sql = "
        SELECT 
            itemdest, 
            intvalue
        FROM 
            {cognitivefactory_opdata} AS od
        WHERE 
            id = $id
    ";
    $res =  $DB->get_record_sql($sql);
    $ordering = $res->intvalue;
    $fatherid = $res->itemdest;

    $sql = "
        SELECT 
            id,
            itemdest
        FROM 
            {cognitivefactory_opdata} as od
        WHERE 
            id = $fatherid
    ";
    $resfatherid =  $DB->get_record_sql($sql);
    if (!$resfatherid) return; // this protects against bouncing left request
    $fatheridbis = $resfatherid->itemdest; //id grandpa...

    $sql = "
        SELECT 
            id,
            intvalue
        FROM 
            {cognitivefactory_opdata} AS od
        WHERE 
            cognitivefactoryid = {$cognitivefactoryid} AND
            operatorid = 'hierarchize' AND
            intvalue > $ordering AND 
            itemdest = $fatherid
            {$accessClause}
        ORDER BY 
            intvalue
    ";
    $newbrotherordering = $ordering;

    if ($ress = $DB->get_records_sql($sql)) {
        foreach ($ress as $res) {
            $opdata = new StdClass();
            $opdata->id = $res->id;
            $opdata->intvalue = $newbrotherordering;
            $DB->update_record('cognitivefactory_opdata', $opdata);
            $newbrotherordering = $newbrotherordering + 1;
        }
    }

    // getting father's ordering
    $sql = "
        SELECT
            id, 
            intvalue
        FROM 
            {cognitivefactory_opdata} AS od
        WHERE 
            cognitivefactoryid = {$cognitivefactoryid} AND
            operatorid = 'hierarchize' AND
            id = $fatherid
            {$accessClause}
    ";
    $resorderingfather =  $DB->get_record_sql($sql);
    $orderingfather = $resorderingfather->intvalue;

    // reordering uncles
    $select = "
            cognitivefactoryid = ? AND
            operatorid = 'hierarchize' AND
            intvalue > ? AND 
            itemdest = ?
            {$accessClause}
    ";
    if ($resbrotherfathers = $DB->get_records_select('cognitivefactory_opdata', $select, array($cognitivefactoryid, $orderingfather, $fatheridbis), 'id, intvalue', 'intvalue')) {
        foreach ($resbrotherfathers as $resbrotherfather) {
            $idbrotherfather = $resbrotherfather->id;
            $nextordering = $resbrotherfather->intvalue + 1;

            $opdata = new StdClass();
            $opdata->id = $idbrotherfather;
            $opdata->intvalue = $nextordering;
            $DB->update_record('cognitivefactory_opdata', $opdata);
        }
    }

    // reordering
    $newordering = $orderingfather + 1;

    $opdata = new StdClass();
    $opdata->id = $id;
    $opdata->intvalue = $newordering;
    $opdata->itemdest = $fatheridbis;
    $DB->update_record('cognitivefactory_opdata', $opdata);
}

/**
* lowers a node within its own branch setting it as 
* sub node of the previous sibling. The first son cannot be lowered.
* @param project the current project
* @param group the current group
* @param id the node to be lowered
* @param table the table-tree name
*/
function cognitivefactory_tree_right($cognitivefactoryid, $userid, $groupid, $id) {
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);

    /// get ordering and parent for the moving node
    $sql = "
        SELECT 
            itemdest, 
            intvalue
        FROM 
            {cognitivefactory_opdata} AS od
        WHERE 
            id = $id
    ";
    $res =  $DB->get_record_sql($sql);
    $ordering = $res->intvalue;
    $fatherid = $res->itemdest;

    /// get previous record if not first. It will become our parent.
    if ($ordering > 1) {
        $orderingbis = $ordering - 1;

        $sql = "
            SELECT 
                id,
                id
            FROM 
                {cognitivefactory_opdata} AS od
            WHERE 
                cognitivefactoryid = {$cognitivefactoryid} AND
                operatorid = 'hierarchize' AND
                intvalue = $orderingbis AND 
                itemdest = $fatherid
                {$accessClause}
        ";
        $resid = $DB->get_record_sql($sql);
        $newfatherid = $resid->id;

        /// get our upward brothers. They should be ordered back from ordering
        $sql = "
            SELECT 
                id, 
                intvalue
            FROM 
                {cognitivefactory_opdata} AS od
            WHERE 
                cognitivefactoryid = {$cognitivefactoryid} AND
                operatorid = 'hierarchize' AND
                intvalue > $ordering AND 
                itemdest = $fatherid 
                {$accessClause}
            ORDER BY 
                intvalue
        ";
        $newbrotherordering = $ordering;

        /// order back all upward brothers
        if ($resbrothers = $DB->get_records_sql($sql)) {
            foreach ($resbrothers as $resbrother) {
                $opdata = new StdClass();
                $opdata->id = $resbrother->id;
                $opdata->intvalue = $newbrotherordering;
                $DB->update_record('cognitivefactory_opdata', $opdata);
                $newbrotherordering = $newbrotherordering + 1;
            }
        }

        $maxordering = cognitivefactory_tree_get_max_ordering($cognitivefactoryid, null, $groupid, true, $newfatherid);
        $newordering = $maxordering + 1;

        // assigning father's id
        $object = new StdClass();
        $opdata->id = $id;
        $opdata->itemdest = $newfatherid;
        $opdata->intvalue = $newordering;
        $DB->update_record('cognitivefactory_opdata', $opdata);
    }
}

/**
* get the full list of dependencies in a tree
* @param table the table-tree
* @param id the node from where to start of
* @return a comma separated list of nodes
*/
function cognitivefactory_get_subtree_list($table, $id) {
    global $DB;
    
    $res = $DB->get_records_menu($table, array('fatherid' => $id));
    $ids = array();
    if (is_array($res)) {
        foreach (array_keys($res) as $aSub) {
            $ids[] = $aSub;
            $subs = cognitivefactory_get_subtree_list($table, $aSub);
            if (!empty($subs)) $ids[] = $subs;
        }
    }
    return(implode(',', $ids));
}

/**
* count direct subs in a tree
* @param table the table-tree
* @param the node
* @return the number of direct subs
*/
function cognitivefactory_count_subs($id) {
    global $CFG, $DB;
    
    // counting direct subs
    $sql = "
        SELECT 
            COUNT(id)
        FROM 
            {cognitivefactory_opdata}
        WHERE 
            itemdest = {$id} AND
            operatorid = 'hierarchize'
    ";
    $res = $DB->count_records_sql($sql);
    return $res;
}

/**
* get upper branch to a node from root to node
* @param the table-tree where to oper
* @param id the node id to reach
* @param includeStart true if leaf node is in the list
* @return array of node ids
*/
function cognitivefactory_tree_get_upper_branch($id, $includeStart = false, $returnordering = false) {
    global $CFG, $DB;

    $nodelist = array();
    $res = $DB->get_record('cognitivefactory_opdata', array('id' => $id));
    if ($includeStart) $nodelist[] = ($returnordering) ? $res->intvalue : $id ;    
    while($res->itemdest != 0) {
        $res = $DB->get_record($table, array('id' => $res->itemdest, 'operatorid' => 'hierarchize'));
        $nodelist[] = ($returnordering) ? $res->intvalue : $res->itemdest;
    }
    $nodelist = array_reverse($nodelist);
    return $nodelist;
}

/**
* get the max ordering available in sequence at a specified node
* @param int $cognitivefactoryid the current cognitivefactory context
* @param int $groupid the current group
* @param boolean $istree true id the entity is table-tree rather than table-list
* @param fatherid the parent node
* @return integer the max ordering found
*/
function cognitivefactory_tree_get_max_ordering($cognitivefactoryid, $userid=null, $groupid=0, $istree = false, $fatherid = 0) {
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);

    $operator = ($istree) ? 'hierarchize' : 'order' ;
    $treeClause = ($istree) ? "AND itemdest = {$fatherid}" : '';
    $sql = "
        SELECT 
            MAX(intvalue) as position
        FROM 
            {cognitivefactory_opdata} AS od
        WHERE 
            cognitivefactoryid = {$cognitivefactoryid} AND
            operatorid = '{$operator}'
            {$accessClause}
            {$treeClause}
    ";

    if (!$result = $DB->get_record_sql($sql)) {
        $result->position = 1;
    }
    return $result->position;
}
