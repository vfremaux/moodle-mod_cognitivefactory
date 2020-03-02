<?php

/**
* Module Brainstorm V2
* Operator : locate
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/**
*
*
*/
function locate_get_locations($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false) {
    global $CFG, $USER, $DB;
    
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    $select = "
        cognitivefactoryid = ? AND
        operatorid = 'locate'
        $accessClause
    ";
    if (!$locations = $DB->get_records_select('cognitivefactory_opdata', $select, array($cognitivefactoryid), '', 'itemsource,blobvalue')) {
        $locations = array();
    }
    return $locations;
}

/**
*
*
*/
function locate_get_means($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false) {
    global $CFG, $DB;
    
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);

    $select = "
        cognitivefactoryid = ? AND
        operatorid = 'locate'
        {$accessClause}
    ";
    if (!$locations = $DB->get_records_select('cognitivefactory_opdata', $select, array($cognitivefactoryid), '', 'itemsource,blobvalue')) {
        $locations = array();
    }

    $means = array();
    $sigmas = array();
    $locationdatas = array();
    /// calculate mean
    foreach ($locations as $responseid => $locationblob) {
        $locationdata = unserialize($locationblob->blobvalue);
        $locationdatas[$responseid][] = $locationdata;
        $means[$responseid]['x'] = @$means[$responseid]['x'] + $locationdata->x;
        $means[$responseid]['y'] = @$means[$responseid]['y'] + $locationdata->y;
    }

    /// calculate sigmas square sums
    foreach (array_keys($locationdatas) as $responseid) {
        foreach ($locationdatas[$responseid] as $asample) {
            $deltax = $asample->x - $means[$responseid]['x'];
            $deltay = $asample->y - $means[$responseid]['y'];
            $sigmasum[$responseid]['x'] = @$sigmasum[$responseid]['x'] + $deltax * $deltax;
            $sigmasum[$responseid]['y'] = @$sigmasum[$responseid]['y'] + $deltay * $deltay;
            $sigmasum[$responseid]['n'] = @$sigmasum[$responseid]['n'] + 1; // sample count
        }
    }

    /// calculate sigmas
    foreach (array_keys($locationdatas) as $responseid) {
        $sigmas[$responseid]['x'] = sqrt($sigmasum[$responseid]['x'] / $sigmasum[$responseid]['n']);
        $sigmas[$responseid]['y'] = sqrt($sigmasum[$responseid]['y'] / $sigmasum[$responseid]['n']);
    }
    $result->mean = &$means;
    $result->sigma = &$sigmas;
}

/**
*
*
*/
function locate_get_neighbours($cognitivefactoryid, $x, $y, $responseid, $config, $userid=null, $groupid=0) {
    global $CFG, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, true);

    $select = "
        cognitivefactoryid = ? AND
        operatorid = 'locate' AND
        itemsource = ?
        {$accessClause}
    ";
    if (!$locations = $DB->get_records_select('cognitivefactory_opdata', $select, array($cognitivefactoryid, $responseid), '', 'itemsource, blobvalue')) {
        $locations = array();
    }
    /// count neighbours
    $neighbours = 0;
    foreach ($locations as $responseid => $locationblob) {
        $locationdata = unserialize($locationblob->blobvalue);
        if (($locationdata->x - $x) * ($locationdata->x - $x) + ($locationdata->y - $y) * ($locationdata->y - $y) < $config->neighbourhood * $config->neighbourhood) {
            $neighbours++;
        }
    }
    return $neighbours;
}

/**
* calculates bounds of record set given for any responses
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid
*/
function locate_get_bounds($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false) {
    global $CFG, $DB;
    
    $operator = new BrainstormOperator($cognitivefactoryid, 'locate');
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);

    $select = "
        cognitivefactoryid = ? AND
        operatorid = 'locate'
        {$accessClause}
    ";
    if (!$locations = $DB->get_records_select('cognitivefactory_opdata', $select, array($cognitivefactoryid), '', 'itemsource,blobvalue')) {
        $locations = array();
    }

    $maxs = array();
    $mins = array();
    /// calculate bounds
    foreach ($locations as $responseid => $locationblob) {
        $locationdata = unserialize($locationblob->blobvalue);
        // $locationdatas[$responseid][] = $locationdata;
        if (!isset($maxs[$responseid]['x'])) $maxs[$responseid]['x'] = $operator->configdata->xminrange;
        if (!isset($mins[$responseid]['x'])) $mins[$responseid]['x'] = $operator->configdata->xmaxrange;
        if (!isset($maxs[$responseid]['y'])) $maxs[$responseid]['y'] = $operator->configdata->yminrange;
        if (!isset($mins[$responseid]['y'])) $mins[$responseid]['y'] = $operator->configdata->ymaxrange;
        $maxs[$responseid]['x'] = ($maxs[$responseid]['x'] - $locationdata->x < 0) ? $locationdata->x : $maxs[$responseid]['x'] ;
        $mins[$responseid]['x'] = ($mins[$responseid]['x'] - $locationdata->x > 0) ? $locationdata->x : $mins[$responseid]['x'] ;
        $maxs[$responseid]['y'] = ($maxs[$responseid]['y'] - $locationdata->y < 0) ? $locationdata->y : $maxs[$responseid]['y'] ;
        $mins[$responseid]['y'] = ($mins[$responseid]['y'] - $locationdata->y < 0) ? $locationdata->y : $mins[$responseid]['y'] ;
    }
    foreach (array_keys($maxs) as $responseid) {
        if ($maxs[$responseid]['x'] == $operator->configdata->xminrange) {
            $maxs[$responseid] = null;
            continue;
        }
        if ($maxs[$responseid] == $operator->configdata->yminrange) {
            $maxs[$responseid] = null;
        }
    }
    foreach (array_keys($mins) as $responseid) {
        if ($mins[$responseid]['x'] == $operator->configdata->xmaxrange) {
            $mins[$responseid] = null;
            continue;
        }
        if ($mins[$responseid]['y'] == $operator->configdata->ymaxrange) {
            $mins[$responseid] = null;
        }
    }
    // print_object($maxs);
    // print_object($mins);
    $result->max = &$maxs;
    $result->min = &$mins;
    return $result;
}

/**
*
*
*/
function locate_display(&$cognitivefactory, $userid, $groupid, $return = false) {
    global $OUTPUT;

    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $groupid, false);
    $responses_locations = locate_get_locations($cognitivefactory->id, $userid, $groupid);
    $current_operator = new BrainstormOperator($cognitivefactory->id, 'locate');
    $current_operator->configdata->width = $w = 200;
    $current_operator->configdata->height = $h = 200;

    $str = '<center>';
    if (!isset($current_operator->configdata->xminrange)) {
        $str .= $OUTPUT->box(get_string('notconfigured', 'cognitiveoperator_locate'));
    } else {
        $str .= '<div style="width:'.$w.'px;height:'.$h.'px;left:0px;position:relative;text-align:left">';
        $pleft = g($current_operator->configdata->xminrange, 0, $current_operator->configdata);
        $ptop = g(0, $current_operator->configdata->xmaxrange, $current_operator->configdata);
        $str .= '<div class="cognitiveoperator-locate-axis" style="position:absolute;left:'.$pleft->x.'px;top:'.$pleft->y.'px;width:'.$w.'px;height:1px;"></div>';
        $str .= '<div class="cognitiveoperator-locate-axis" style="position:absolute;left:'.$ptop->x.'px; top:'.$ptop->y.'px; width:1px;height:'.$h.'px;"></div>';
        if ($responses_locations) {
            $i = 0;
            foreach ($responses_locations as $located) {
                $spot = 'spot';
                $abs = unserialize($located->blobvalue);
                $p = g($abs->x, $abs->y, $current_operator->configdata,0,-15);
                $str .= '<div class="'.$spot.'" style="position:absolute; left:'.$p->x.'px; top: '.$p->y.'px; width: 15px; height: 15px;" title="('.$abs->x.','.$abs->y.' '.$responses[$located->itemsource]->response.'"></div>';
                if (@$current_operator->configdata->showlabels) {
                    $p->x += 20 + rand(-20,20);
                    $p->y += 20 + rand(-20,20);
                    $str .= '<div style="position:absolute; left: '.$p->x.'px; top: '.$p->y.'px;" >'.$responses[$located->itemsource]->response.'</div>';
                }
                $i++;
            }    
        }
    }
    $str .= '</div>';
    $str .= '</center>';
    
    if ($return) return $str;
    echo $str;
}

function g($absx, $absy, $configdata, $xshift = 0, $yshift = 0) {
    $xabsoffset = 0;
    $yabsoffset = 0;
    $xfactor = ($configdata->width / ($configdata->xmaxrange - $configdata->xminrange));
    $yfactor = ($configdata->height / ($configdata->ymaxrange - $configdata->yminrange));
    $p = new StdClass();
    $p->x = $xfactor * ($absx - $configdata->xminrange) + $xabsoffset + $xshift;
    $p->y = $configdata->height - $yfactor * ($absy - $configdata->yminrange) + $yabsoffset + $yshift;
    return $p;
}

function locate_requires() {
    global $PAGE;
    
    $PAGE->requires->js('/mod/cognitivefactory/js/dhtmlxSlider/codebase/dhtmlxcommon.js', true);
    $PAGE->requires->js('/mod/cognitivefactory/js/dhtmlxSlider/codebase/dhtmlxslider.js', true);
    $PAGE->requires->css('/mod/cognitivefactory/js/dhtmlxSlider/codebase/dhtmlxslider.css', true);
}

function locate_build_slider($name, $size = 120, $min = 0, $max = 100, $value = 0, $step = 1, $return = false) {
    static $magic = 0;
    
    $str = '';
    $str .= '<div id="'.$name.'" style="display:inline"></div>';
    $str .= '<script type="text/javascript">
        var sld_'.$magic.' = new dhtmlxSlider('.$name.', '.$size.', \'ball\', false, '.$min.', '.$max.', '.(0 + $value).', '.$step.');
        sld_'.$magic.'.init();
        sld_'.$magic.'.linkTo(\'i\'+\''.$name.'\');
        sld_'.$magic.'.attachEvent("onChange",function(newValue,sliderObj) {
              color = (255 * newValue) / '.($max - $min).';
              color2 = (color <= 128) ? 128 + color : 255;
              color1 = (color > 128) ? 255 + 128 - color : 255;
              $(\'#i'.$name.'\').css(\'background-color\', \'rgb(\'+color1+\',\'+color2+\',128)\');
          })  
      </script>';

    $magic++;

    if ($return) return $str;
    echo $str;
}
