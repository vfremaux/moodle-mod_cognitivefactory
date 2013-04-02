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
function categorize_get_categories($cognitivefactoryid, $userid=null, $groupid=0){

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);
    
    $select = "
        cognitivefactoryid = $cognitivefactoryid
        {$accessClause}
    ";

    if (!$categories = get_records_select('cognitivefactory_categories as od', $select)) {
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
function categorize_get_responsespercategories($cognitivefactoryid, $userid, $groupid, $excludemyself=false){
    global $CFG, $USER;
    
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    
    $struncategorized = get_string('uncategorized', 'cognitivefactory');
    $responses = array();
    $categories = get_records('cognitivefactory_categories', 'cognitivefactoryid', $cognitivefactoryid);
    if ($categories){
        foreach($categories as $category){
            $sql = "
                SELECT
                    od.id as odid,
                    r.*,
                    od.userid as opuserid
                FROM
                    {$CFG->prefix}cognitivefactory_operatordata as od,
                    {$CFG->prefix}cognitivefactory_responses as r
                WHERE
                    od.itemsource = r.id AND
                    od.cognitivefactoryid = {$cognitivefactoryid} AND
                    od.operatorid = 'categorize' AND
                    od.itemdest = $category->id
                    {$accessClause}
            ";
            $responses[$category->title] = get_records_sql($sql);        
        }
    }
    
    // get responses outside categories
    $sql = "
        SELECT
            r.*
        FROM
            {$CFG->prefix}cognitivefactory_responses as r
        LEFT JOIN
            {$CFG->prefix}cognitivefactory_operatordata as od
        ON
            od.itemsource = r.id AND
            operatorid = 'categorize'
        WHERE
            od.itemdest IS NULL AND
            od.cognitivefactoryid = {$cognitivefactoryid}
    ";
    $responses[$struncategorized] = get_records_sql($sql);        
    return $responses;
}

/**
*
* @uses CFG, USER
* @param int $cognitivefactoryid
* @param int $userid
*/
function categorize_get_categoriesperresponses($cognitivefactoryid, $userid=null, $groupid=0){
    global $CFG, $USER;
        
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid);

    $categorized = array();
    $sql = "
        SELECT
            od.id as odid,
            r.*,
            od.itemdest as category
        FROM
            {$CFG->prefix}cognitivefactory_responses as r
        LEFT JOIN
            {$CFG->prefix}cognitivefactory_operatordata as od
        ON
            od.itemsource = r.id AND
            operatorid = 'categorize'
        WHERE
            od.cognitivefactoryid = {$cognitivefactoryid}
            {$accessClause}
    ";
    $allresponses = get_records_sql($sql); 
    if ($allresponses){
        foreach($allresponses as $response){
            $categorized[$response->id]->response = &$response;
            if (!empty($response->category)){
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
function categorize_get_matchings($cognitivefactoryid, $userid=null, $groupid=0){
    global $CFG;
    
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, true);
        
    /// get interesting responses
    $select = "
       cognitivefactoryid = '{$cognitivefactoryid}' AND
       operatorid = 'categorize'
       $accessClause
    ";
    $allcategorizations = get_records_select('cognitivefactory_operatordata AS od', $select);

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, false);
        
    /// get interesting responses
    $select = "
       cognitivefactoryid = '{$cognitivefactoryid}' AND
       operatorid = 'categorize'
       $accessClause
    ";
    $mycategorizations = get_records_select('cognitivefactory_operatordata AS od', $select);
    
    /// compile our values first
    $reference = array();
    if ($mycategorizations){
        foreach($mycategorizations as $cat){
            $reference[$cat->itemsource][] = $cat->itemdest;
        }
    }

    if ($allcategorizations && !empty($reference)){            
        /// compile values for other users
        foreach($allcategorizations as $cat){
            if (!in_array($cat->itemsource, array_keys($reference))) continue ; // discard those responses we did not give any assignation
            if (@in_array($cat->itemdest, $reference[$cat->itemsource])){
                @$match[$cat->itemsource]++;
            }
            else{
                @$unmatch[$cat->itemsource]++;
            }
        }
        $matchings->match = &$match;
        $matchings->unmatch = &$unmatch;
    }
    else{
        $matchings->match = array();
        $matchings->unmatch = array();
    }

    return $matchings;
}

/**
* displays categorization for a user
*
*/
function categorize_display(&$cognitivefactory, $userid, $groupid){
    $responses = categorize_get_responsespercategories($cognitivefactory->id, $userid, $groupid);
    $cols = 0;
?>
<center>
<table width="80%">
    <tr valign="top">
<?php
    foreach(array_keys($responses) as $acategoryname){
        if ($cols && $cols % $cognitivefactory->numcolumns == 0){
            echo '</tr><tr valign="top">';
        }
?>
        <td>

            <table width="90%">
                <tr>
                    <th colspan="2">
                        <?php echo format_string($acategoryname) ?>
                    </th>
                </tr>
<?php
    $index = 1;
    if ($responses[$acategoryname]){
        foreach($responses[$acategoryname] as $aresponse){
?>
                <tr>
                    <th>
                        <?php echo $index ?>
                    </th>
                    <td>
                        <?php echo format_string($aresponse->response) ?>
                    </td>
                </tr>
<?php
            $index++;
            }
        }
        else{
?>
                <tr>
                    <td colspan="2">
                        <?php print_string('nothinghere', 'cognitivefactory') ?>
                    </td>
                </tr>
<?php
        }
?>
            </table>
        </td>
<?php
        $cols++;
    }
?>
    </tr>
</table>
</center>
<?php
}
?>