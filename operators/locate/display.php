<?php

/**
* Module Brainstorm V2
* @author Martin Ellermann
* @reengineering Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

	include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
	include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

	$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, false);
	
	$operator = new BrainstormOperator($cognitivefactory->id, $page);
	$responses_bounds = locate_get_bounds($cognitivefactory->id, 0, $currentgroup, true);

	if (!isset($operator->configdata->width)){
	    $operator->configdata->width = 400;
	}
	if (!isset($operator->configdata->height)){
	    $operator->configdata->height = 400;
	}

	$responses_locations = locate_get_locations($cognitivefactory->id, null, $currentgroup);

	$w = $operator->configdata->width;
	$h = $operator->configdata->height;
	$H = $operator->configdata->height + 50;

	echo $OUTPUT->heading(get_string('mylocate', 'cognitiveoperator_'.$page));

	echo '<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/cognitivefactory/operators/locate/js/module.js"></script>';
	echo '<center>';
	
	if (isset($operator->configdata->xminrange)){
	    echo "<div style=\"width: {$w}px; height: {$H}px; left: 0px; position: relative ; text-align : left\">";
	    $pleft = g($operator->configdata->xminrange, 0, $operator->configdata);
	    $ptop = g(0, $operator->configdata->xmaxrange, $operator->configdata);
	    echo "<div class=\"cognitiveoperator-locate-axis\" style=\"position:absolute; left: {$pleft->x}px; top: {$pleft->y}px; width: {$w}px; height: 1px;\"></div>";
	    echo "<div class=\"cognitiveoperator-locate-axis\" style=\"position:absolute; left: {$ptop->x}px; top: {$ptop->y}px; width: 1px; height: {$h}px;\"></div>";
	    echo '<div class="cognitiveoperator-locate-axis-label" style="position:absolute; left: '.($ptop->x - 60).'px; top: '.$ptop->y.'px;">'.$operator->configdata->xquantifier.'</div>';
	    echo '<div class="cognitiveoperator-locate-axis-label" style="text-align:right;position:absolute; left: '.($pleft->x + $w - 60).'px; top: '.($pleft->y + 15).'px;width:60px">'.$operator->configdata->yquantifier.'</div>';
	    if ($responses_locations){
	        $i = 0;
	        if (!empty($responses_bounds->min) || !empty($responses_bounds->max)){
	            foreach($responses_locations as $located){
	                if (!empty($responses_bounds->min[$located->itemsource]) && !empty($responses_bounds->max[$located->itemsource])){
	                    $abs->x  = $responses_bounds->min[$located->itemsource]['x'];
	                    $abs->y  = $responses_bounds->max[$located->itemsource]['y'];
	                    $size->x  = $responses_bounds->max[$located->itemsource]['x'] - $responses_bounds->min[$located->itemsource]['x'];
	                    $size->y  = $responses_bounds->max[$located->itemsource]['y'] - $responses_bounds->min[$located->itemsource]['y'];
	                    if ($size->x < 10 || $size->x < 10) continue ;
	                    $p = g($abs->x, $abs->y, $operator->configdata);
	                    $d = g($size->x, $size->y, $operator->configdata);
	                    echo "<div class=\"cognitiveoperator-locate-inbounds\" style=\"position:absolute; left: {$p->x}px; top: {$p->y}px; width: {$d->x}px; height: {$d->y}px;\" title=\"({$abs->x},{$abs->y}) {$responses[$located->itemsource]->response}\"></div>";
	                }
	            }
	        }
	        foreach($responses_locations as $located){
	            $spot = 'cognitiveoperator-locate-spot';
	            $abs = unserialize($located->blobvalue);
	            $p = g($abs->x, $abs->y, $operator->configdata,0,-15);
	            echo "<div class=\"$spot\" style=\"position:absolute; left: {$p->x}px; top: {$p->y}px; width: 15px; height: 15px;\" title=\"({$abs->x},{$abs->y}) {$responses[$located->itemsource]->response}\"></div>";
	            if (@$current_operator->configdata->showlabels){
	                $p->x += 20 + rand(-20,20);
	                $p->y += 20 + rand(-20,20);
	                echo "<div style=\"position:absolute; left: {$p->x}px; top: {$p->y}px;\" >{$responses[$located->itemsource]->response}</div>";
	            }
	            $i++;
	        }    
	    }
		echo '</div>';
	} else {
	    echo $OUTPUT->box(get_string('notconfigured', 'cognitiveoperator_'.$page));
	}
	
	echo '</center>';
