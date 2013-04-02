<?php

/**
* Module Brainstorm V2
* Operator : scale
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/**
*
*
*/
function scale_get_scalings($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $configdata){
    global $CFG;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    $orderClause = (@$configdata->ordereddisplay) ? ' ORDER BY intvalue, floatvalue ' : '' ;

    $sql = "
        SELECT
            r.id,
            r.response,
            od.intvalue,
            od.floatvalue,
            od.blobvalue,
            od.userid,
            od.groupid
        FROM
            {$CFG->prefix}cognitivefactory_responses as r,
            {$CFG->prefix}cognitivefactory_operatordata as od
        WHERE
            r.id = od.itemsource AND
            operatorid = 'scale' AND
            r.cognitivefactoryid = {$cognitivefactoryid} 
            {$accessClause}
        {$orderClause}
    ";
    if (!$records = get_records_sql($sql)){
        return array();
    }
    return $records;
}

/**
*
*
*/
function scale_get_meanscalings($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $configdata){
    global $CFG;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    $orderClause = (@$configdata->ordereddisplay) ? ' ORDER BY intvalue, floatvalue ' : '' ;

    $sql = "
        SELECT
            r.id,
            r.response,
            SUM(od.intvalue) as sumintvalue,
            SUM(od.floatvalue) as sumfloatvalue,
            SUM(od.blobvalue) as sumblobvalue,
            MIN(od.intvalue) as minintvalue,
            MIN(od.floatvalue) as minfloatvalue,
            MIN(od.blobvalue) as minblobvalue,
            MAX(od.intvalue) as maxintvalue,
            MAX(od.floatvalue) as maxfloatvalue,
            MAX(od.blobvalue) as maxblobvalue,
            COUNT(od.intvalue) as countintvalue,
            COUNT(od.floatvalue) as countfloatvalue,
            COUNT(od.blobvalue) as countblobvalue,
            od.userid,
            od.groupid
        FROM
            {$CFG->prefix}cognitivefactory_responses as r,
            {$CFG->prefix}cognitivefactory_operatordata as od
        WHERE
            r.id = od.itemsource AND
            operatorid = 'scale' AND
            r.cognitivefactoryid = {$cognitivefactoryid} 
            {$accessClause}
        {$orderClause}
        GROUP BY
            r.id
    ";
    if (!$records = get_records_sql($sql)){
        return array();
    }
    return $records;
}

/**
*
*
*/
function scale_get_scalebounds($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $configdata){
    global $CFG;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    switch(@$configdata->quantifierype){
        case 'moodlescale' : 
           $field = 'blobvalue';
           break;
        case 'integer' : 
           $field = 'intvalue';
           break;
        default: 
           $field = 'floatvalue';
           break;
    }

    if (isset($field)){
        $sql = "
            SELECT
                MAX($field) as maxvalue,
                MIN($field) as minvalue
            FROM
                {$CFG->prefix}cognitivefactory_operatordata as od
            WHERE
                od.cognitivefactoryid = {$cognitivefactoryid} AND
                operatorid = 'scale'
                {$accessClause}
            GROUP BY
                cognitivefactoryid
        ";
        $bounds = get_record_sql($sql);
        if ($bounds){
            if ($bounds->minvalue > 0) $bounds->minvalue = 0;
            $bounds->range = $bounds->maxvalue - $bounds->minvalue;
            return $bounds;
        }
    }
    else{
        if (isset($configdata->scale)){
            if ($scale = get_record('scale', 'id', $configdata->scale)){
                $bounds->minvalue = 0;
                $bounds->maxvalue = count(explode(',', $scale->scale)) - 1;
                return $bounds;
            }
        }
    }
    $bounds->minvalue = 0;
    $bounds->maxvalue = 0;
    return $bounds;
}

/**
*
*
*/
function scale_display(&$cognitivefactory, $userid, $groupid, &$responses=null, &$scalings=null){
    $current_operator = new BrainstormOperator($cognitivefactory->id, 'scale');
    if (!$responses)
        $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $groupid, false);
    if (!$scalings)
        $scalings = scale_get_scalings($cognitivefactory->id, $userid, $groupid, false, $current_operator->configdata);
    if ($scalings){
        $absscalebounds = scale_get_scalebounds($cognitivefactory->id, 0, $groupid, false, $current_operator->configdata);
    }
?>
<center>
<style>
.match { background-color : #54DE57 }
.bargraph { background-color : #0080FF ; height : 12px }
.bargraphneg { background-color : #FF8000 ; height : 12px }
.barrange { background-color : #0080FF ; height : 4px }
.barmid { background-color : #B000B0 ; height : 12px }
</style>
<table cellspacing="5" width="80%">
<?php
    if ($scalings && $absscalebounds->range != 0){
        $i = 0;
        $absoffset = ($absscalebounds->minvalue < 0) ? abs(($absscalebounds->minvalue / $absscalebounds->range) * $current_operator->configdata->barwidth) : 0 ;
        foreach($scalings as $scaled){
            switch($current_operator->configdata->quantifiertype){
                case 'integer':{
                    $relwidth = $current_operator->configdata->barwidth * ($scaled->intvalue / $absscalebounds->range);
                    $value = $scaled->intvalue;
                    break;
                }
                case 'float':{
                    $relwidth = $current_operator->configdata->barwidth * (abs($scaled->floatvalue) / $absscalebounds->range);
                    $value = $scaled->floatvalue;
                    break;
                }
                case 'moodlescale':{
                    $relwidth = $current_operator->configdata->barwidth * ($scaled->blobvalue / $absscalebounds->range);
                    $value = $scaled->blobvalue;
                    break;
                }
            }
            $bargraphclass = ($value < 0) ? 'bargraphneg' : 'bargraph' ;
            $offset = ($value < 0) ? $absoffset - $relwidth : $absoffset ;
?>
    <tr valign="top">
        <th class="<?php echo $matchclass ?>">
            <?php echo $i + 1 ?>.
        </th>
        <td align="left">
            <?php echo $scaled->response ?>
        </td>
        <td align="left">
            <div style="position : relative ; left: <?php echo $offset ?>px"><img src="<?php $CFG->wwwroot ?>/mod/cognitivefactory/operators/scale/pix/transparent.gif"  style="width: <?php echo $relwidth ?>px" class="<?php echo $bargraphclass ?>" /> <?php echo $value ?></div>
        </td>
    </tr>
<?php
            $i++;
        }
    }
    else{
        echo '<tr><td>';
        print_simple_box(get_string('noscalings', 'cognitivefactory'));    
        echo '</td></tr>';
    }
?>
</table>
<?php
}

/**
*
*
*/
function scale_display_unscaled(&$cognitivefactory, $userid, $groupid, &$responses=null, &$scalings=null){
    
    /// get data if we do not have it yet
    $current_operator = new BrainstormOperator($cognitivefactory->id, 'scale');
    if (!$responses)
        $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $groupid, false);
    if (!$scalings)
        $scalings = scale_get_scalings($cognitivefactory->id, $userid, $groupid, false, $current_operator->configdata);

    /// compute unscaled
    if ($responses){
        $unvaluated = $responses;
        foreach(array_keys($scalings) as $scaledid){
            unset($unvaluated[$scaledid]);
        }
    }
?>
<table cellspacing="5" width="80%">
<?php
    if (!empty($unvaluated)){
        $i = 0;
        foreach($unvaluated as $response){
?>
    <tr valign="top">
        <th class="<?php echo $matchclass ?>" width="10%">
            <?php echo $i + 1 ?>.
        </th>
        <td align="left">
            <?php echo $response->response ?>
        </td>
    </tr>
<?php
            $i++;
        } 
    }
    else{
        echo '<tr><td>';
        print_simple_box(get_string('allevaluated', 'cognitivefactory'));
        echo '</td></tr>';
    }
?>
</table>
<?php
}
?>