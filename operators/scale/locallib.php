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
function scale_get_scalings($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $configdata) {
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself, 'od.');
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
            {cognitivefactory_responses} as r,
            {cognitivefactory_opdata} as od
        WHERE
            r.id = od.itemsource AND
            operatorid = 'scale' AND
            r.cognitivefactoryid = {$cognitivefactoryid} 
            {$accessClause}
        {$orderClause}
    ";
    if (!$records = $DB->get_records_sql($sql)) {
        return array();
    }
    return $records;
}

/**
*
*
*/
function scale_get_meanscalings($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $configdata) {
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself, 'od.');
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
            {cognitivefactory_responses} as r,
            {cognitivefactory_opdata} as od
        WHERE
            r.id = od.itemsource AND
            operatorid = 'scale' AND
            r.cognitivefactoryid = {$cognitivefactoryid} 
            {$accessClause}
        {$orderClause}
        GROUP BY
            r.id
    ";
    if (!$records = $DB->get_records_sql($sql)) {
        return array();
    }
    return $records;
}

/**
*
*
*/
function scale_get_scalebounds($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $configdata) {
    global $CFG, $DB;
    
    
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself, 'od.');
    if (@$configdata->quantifiertype == 'moodlescale') {
       $field = 'blobvalue';
    } elseif (@$configdata->quantifiertype == 'integer') { 
        $field = 'intvalue';
    } else {
       $field = 'floatvalue';
    }
    
    if (isset($field)) {
        $sql = "
            SELECT
                MAX($field) as maxv,
                MIN($field) as minv
            FROM
                {cognitivefactory_opdata} as od
            WHERE
                od.cognitivefactoryid = {$cognitivefactoryid} AND
                operatorid = 'scale'
                {$accessClause}
            GROUP BY
                cognitivefactoryid
        ";
        $bounds = $DB->get_record_sql($sql);
        if ($bounds) {
            if ($bounds->minv > 0) $bounds->minv = 0;
            $bounds->range = $bounds->maxv - $bounds->minv;
            return $bounds;
        }
    } else {
        if (isset($configdata->scale)) {
            if ($scale = $DB->get_record('scale', array('id' => $configdata->scale))) {
                $bounds->minv = 0;
                $bounds->maxv = count(explode(',', $scale->scale)) - 1;
                return $bounds;
            }
        }
    }
    $bounds->minv = 0;
    $bounds->maxv = 0;
    return $bounds;
}

/**
*
*
*/
function scale_display(&$cognitivefactory, $userid, $groupid, $return = false) {
    global $OUTPUT;
    
    $current_operator = new BrainstormOperator($cognitivefactory->id, 'scale');
    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $groupid, false);
    $scalings = scale_get_scalings($cognitivefactory->id, $userid, $groupid, false, $current_operator->configdata);
    if ($scalings) {
        $absscalebounds = scale_get_scalebounds($cognitivefactory->id, 0, $groupid, false, $current_operator->configdata);
    }
    
    if (!isset($current_operator->configdata->barwidth)) $current_operator->configdata->barwidth = 400;
    
    $str = '';

    $str .= '<table cellspacing="5" width="80%">';
    if ($scalings && ($absscalebounds->range != 0)) {
        $i = 0;
        $absoffset = ($absscalebounds->minv < 0) ? abs(($absscalebounds->minv / $absscalebounds->range) * $current_operator->configdata->barwidth) : 0 ;
        foreach ($scalings as $scaled) {
            switch($current_operator->configdata->quantifiertype) {
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
            $bargraphclass = ($value < 0) ? 'cognitivefactory-bargraphneg' : 'cognitivefactory-bargraph' ;
            $offset = ($value < 0) ? $absoffset - $relwidth : $absoffset ;

            $str .= '<tr valign="top">';
            $str .= '<th class="'.$bargraphclass.'">';
            $str .= ($i + 1).'.';
            $str .= '</th>';
            $str .= '<td align="left">';
            $str .= $scaled->response;
            $str .= '</td>';
            $str .= '<td align="left">';
            $str .= '<div style="position:relative;left:'.$offset.'px"><img src="'.$OUTPUT->image_url('transparent', 'cognitiveoperator_scale').'" style="width:'.$relwidth.'px" class="'.$bargraphclass.'" />'.$value.'</div>';
            $str .= '</td>';
            $str .= '</tr>';
            $i++;
        }
    } else {
        $str .= '<tr><td>';
        $str .= $OUTPUT->box(get_string('noscalings', 'cognitiveoperator_scale'), 'cognitivefactory-notification');    
        $str .= '</td></tr>';
    }
    $str .= '</table>';
    
    if ($return) return $str;
    echo $str;
}

/**
*
*
*/
function scale_display_unscaled(&$cognitivefactory, $userid, $groupid, &$responses = null, &$scalings = null, $return = false) {
    global $OUTPUT;
    
    $str = '';
    
    /// get data if we do not have it yet
    $current_operator = new BrainstormOperator($cognitivefactory->id, 'scale');
    if (!$responses)
        $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $groupid, false);
    if (!$scalings)
        $scalings = scale_get_scalings($cognitivefactory->id, $userid, $groupid, false, $current_operator->configdata);

    /// compute unscaled
    if ($responses) {
        $unvaluated = $responses;
        foreach (array_keys($scalings) as $scaledid) {
            unset($unvaluated[$scaledid]);
        }
    }
    $str .= '<table cellspacing="5" width="80%">';
    if (!empty($unvaluated)) {
        $i = 0;
        foreach ($unvaluated as $response) {
            $str .= '<tr valign="top">';
            $str .= '<th class="'.$matchclass.'" width="10%">';
            $str .= $i + 1 .'.';
            $str.= '</th>';
            $str .= '<td align="left">';
            $str .= $response->response;
            $str .= '</td>';
            $str .= '</tr>';
            $i++;
        } 
    } else {
        $str .= '<tr><td>';
        $str .= $OUTPUT->box(get_string('allevaluated', 'cognitiveoperator_scale'));
        $str .= '</td></tr>';
    }
    $str .= '</table>';

    if ($return) return $str;
    echo $str;
}

function scale_requires() {
    global $PAGE;
    
    $PAGE->requires->js('/mod/cognitivefactory/js/dhtmlxSlider/codebase/dhtmlxcommon.js', true);
    $PAGE->requires->js('/mod/cognitivefactory/js/dhtmlxSlider/codebase/dhtmlxslider.js', true);
    $PAGE->requires->css('/mod/cognitivefactory/js/dhtmlxSlider/codebase/dhtmlxslider.css', true);
}

function scale_build_slider($name, $size = 120, $min = 0, $max = 100, $value = 0, $step = 1, $return = false) {
    static $magic = 0;
    
    $str = '';
    $str .= '<div id="'.$name.'" style="display:inline"></div>';
    $str .= '<script type="text/javascript">
        var sld_'.$magic.' = new dhtmlxSlider('.$name.', '.$size.', \'ball\', false, '.$min.', '.$max.', '.(0 + $value).', '.$step.');
        sld_'.$magic.'.init();
        sld_'.$magic.'.linkTo(\'i\'+\''.$name.'\');
        sld_'.$magic.'.attachEvent("onChange",function(newValue,sliderObj) {
              color = Math.ceil((255 * newValue) / '.($max - $min).');
              color2 = (color <= 128) ? 128 + color : 255;
              color1 = (color > 128) ? 255 + 128 - color : 255;
              $(\'#i'.$name.'\').css(\'background-color\', \'rgb(\'+color1+\',\'+color2+\',128)\');
          })  
      </script>';

    $magic++;

    if ($return) return $str;
    echo $str;
}
