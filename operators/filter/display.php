<?php

/**
* Module Brainstorm V2
* Operator : filter
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

    include_once ("{$CFG->dirroot}/mod/cognitivefactory/operators/{$page}/locallib.php");
    include_once("{$CFG->dirroot}/mod/cognitivefactory/operators/operator.class.php");

    if (!isset($current_operator)) {
        $current_operator = new BrainstormOperator($cognitivefactory->id, $page);
    }
    
    if (has_capability('mod/cognitivefactory:gradable', $context, $USER->id, false)) {
        $userid = $USER->id;    
    } else {
        echo $OUTPUT->box_start('', 'usermenu');
        $userid = optional_param('userid', $USER->id, PARAM_INT);
        include $CFG->dirroot.'/mod/cognitivefactory/user_selector_form.php';
        echo $OUTPUT->box_end();
    }

    echo '<center>';

    echo $OUTPUT->heading(get_string('myfilter', 'cognitiveoperator_'.$page));

/// printing my filtering

    filter_display($cognitivefactory, $userid, $currentgroup);

/// printing status for other users

    if (!$cognitivefactory->privacy && $current_operator->configdata->blindness == 0) {
        filter_display_others($cognitivefactory, $currentgroup, false);
    }
    
    echo '</center>';
