<?PHP  // $Id: view.php,v 1.3 2004/08/25 12:35:07 diml Exp $

    /**
    * @package mod_cognitivefactory
    * @category module
    * @author Martin Ellermann, Valery Fremaux > 1.8
    * @date 22/12/2007
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    *
    * This page prints a particular instance of a cognitivefactory and handles
    * top level interactions
    */
    
    /**
    * Include and requires
    */
    require_once("../../config.php");
    require_once($CFG->dirroot."/mod/cognitivefactory/lib.php");
    require_once($CFG->dirroot."/mod/cognitivefactory/locallib.php");
    
    $id = required_param('id', PARAM_INT);           // Course Module ID
        
    if (! $cm = get_coursemodule_from_id('cognitivefactory', $id)) {
        error("Course Module ID was incorrect");
    }
    
    if (! $course = get_record('course', 'id', $cm->course)) {
        error("Course is misconfigured");
    }
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    $usehtmleditor = false;
    $editorfields = '';
    
    require_login($course->id);
    
    if (!$cognitivefactory = get_record('cognitivefactory', 'id', $cm->instance)) {
        error("Course module is incorrect");
    }
    
    $strcognitivefactory = get_string('modulename', 'cognitivefactory');
    $strcognitivefactorys = get_string('modulenameplural', 'cognitivefactory');
    
    add_to_log($course->id, 'cognitivefactory', 'view', "view.php?id={$cm->id}", $cognitivefactory->id, $cm->id);

/// get the master MVC control parameters

    // PART OF MVC Implementation
    $action = optional_param('what', '', PARAM_CLEAN); 
    $view = optional_param('view', '', PARAM_CLEAN); 
    $page = optional_param('page', '', PARAM_CLEAN); 

    // memorizes current view - typical session switch
    if (!empty($view)){
    	$_SESSION['currentview'] = $view;
    } 
    elseif (empty($_SESSION['currentview'])) {
    	$_SESSION['currentview'] = 'collect';
    }
    $view = $_SESSION['currentview'];
    
    // memorizes current page - typical session switch
    if (!empty($page)){
    	$_SESSION['currentpage'] = $page;
    } 
    elseif (empty($_SESSION['currentpage'])) {
    	$_SESSION['currentpage'] = '';
    }
    $page = $_SESSION['currentpage'];
    // !PART OF MVC Implementation
   
/// Print the page header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }
    
    $navlinks = array(
    );
    
    print_header($course->shortname.': '.format_string($cognitivefactory->name), format_string($course->fullname),
                 build_navigation($navlinks, $cm), '', '', true,
                  update_module_button($cm->id, $course->id, $strcognitivefactory), navmenu($course, $cm));
    
/// integrate module specific stylesheets

    echo '<link rel="stylesheet" href="'.$CFG->themewww.'/'.current_theme().'/cognitivefactory.css" type="text/css" />';
        
/// Check to see if groups are being used in this cognitivefactory

    if ($groupmode = groupmode($course, $cm)) {   // Groups are being used
        $currentgroup = setup_and_print_groups($course, $groupmode, "view.php?id={$cm->id}");
    } 
    else {
        $currentgroup = 0;
    }
    
    print_simple_box(text_to_html($cognitivefactory->description) , 'center');
    
    ?>
    <table width="100%">
        <tr>
            <td>
    <?php
    $collectstr = get_string('collect', 'cognitivefactory');
    $preparestr = get_string('prepare', 'cognitivefactory');
    $organizestr = get_string('organize', 'cognitivefactory');
    $displaystr = get_string('display', 'cognitivefactory');
    $feedbackstr = get_string('feedback', 'cognitivefactory');
    $gradestr = get_string('grading', 'cognitivefactory');
    
    if (has_capability('mod/cognitivefactory:manage', $context)){
        if ($action != ''){
            include "{$CFG->dirroot}/mod/cognitivefactory/phase.controller.php";
        }
    }
    
/// make flow control activators

    if ($cognitivefactory->flowmode == 'sequential'){ // make flow control table
        $collectphaseclass = ($cognitivefactory->phase == PHASE_COLLECT) ? 'pressed' : 'raised' ;
        $preparephaseclass = ($cognitivefactory->phase == PHASE_PREPARE) ? 'pressed' : 'raised' ;
        $organizephaseclass = ($cognitivefactory->phase == PHASE_ORGANIZE) ? 'pressed' : 'raised' ;
        $displayphaseclass = ($cognitivefactory->phase == PHASE_DISPLAY) ? 'pressed' : 'raised' ;
        $feedbackphaseclass = ($cognitivefactory->phase == PHASE_FEEDBACK) ? 'pressed' : 'raised' ;
        $collectaccessclass = (!$cognitivefactory->seqaccesscollect) ? 'manager' : 'participant' ;
        $prepareaccessclass = (!$cognitivefactory->seqaccessprepare) ? 'manager' : 'participant' ;
        $organizeaccessclass = (!$cognitivefactory->seqaccessorganize) ? 'manager' : 'participant' ;
        $displayaccessclass = (!$cognitivefactory->seqaccessdisplay) ? 'manager' : 'participant' ;
        $feedbackaccessclass = (!$cognitivefactory->seqaccessfeedback) ? 'manager' : 'participant' ;
        if (!has_capability('mod/cognitivefactory:manage', $context)){
            $collectbutton = "<div class=\"{$collectphaseclass} {$collectaccessclass}\">{$collectstr}</div>";
            $preparebutton = "<div class=\"{$preparephaseclass} {$prepareaccessclass}\">{$preparestr}</div>";
            $organizebutton = "<div class=\"{$organizephaseclass} {$organizeaccessclass}\">{$organizestr}</div>";
            $displaybutton = "<div class=\"{$displayphaseclass} {$displayaccessclass}\">{$displaystr}</div>";
            $feedbackbutton = "<div class=\"{$feedbackphaseclass} {$feedbackaccessclass}\">{$feedbackstr}</div>";
            $view = 'unallowedphase';
            switch($cognitivefactory->phase){
                case PHASE_COLLECT:
                    if ($cognitivefactory->seqaccesscollect){
                        $view = 'collect';
                    }
                    break;
                case PHASE_PREPARE:
                    if ($groupmode && !groups_is_member($currentgroup)){
                        $view = 'notmember';
                    } 
                    else {
                        if ($cognitivefactory->seqaccessprepare){
                            $view = 'prepare';
                        }
                    }
                    break;
                case PHASE_ORGANIZE:
                    if ($groupmode && !groups_is_member($currentgroup)){
                        $view = 'notmember';
                    } 
                    else {
                        if ($cognitivefactory->seqaccessorganize){
                            $view = 'organize';
                        }
                    }
                    break;
                case PHASE_DISPLAY:
                    if ($cognitivefactory->seqaccessdisplay){
                        $view = 'display';
                    }
                    break;
                case PHASE_FEEDBACK:
                    if ($cognitivefactory->seqaccessfeedback){
                        $view = 'feedback';
                    }
                    break;
                 default:
                    error("Unknown phase. Please report to developers.");
            }
        }
        else{
            $collectbutton = "<a href=\"view.php?id={$cm->id}&amp;what=switchphase&amp;phase=0\"><div class=\"{$collectphaseclass} {$collectaccessclass}\">{$collectstr}</div></a>";
            $preparebutton = "<a href=\"view.php?id={$cm->id}&amp;what=switchphase&amp;phase=1\"><div class=\"{$preparephaseclass} {$prepareaccessclass}\">{$preparestr}</div></a>";
            $organizebutton = "<a href=\"view.php?id={$cm->id}&amp;what=switchphase&amp;phase=2\"><div class=\"{$organizephaseclass} {$organizeaccessclass}\">{$organizestr}</div></a>";
            $displaybutton = "<a href=\"view.php?id={$cm->id}&amp;what=switchphase&amp;phase=3\"><div class=\"{$displayphaseclass} {$displayaccessclass}\">{$displaystr}</div></a>";
            $feedbackbutton = "<a href=\"view.php?id={$cm->id}&amp;what=switchphase&amp;phase=4\"><div class=\"{$feedbackphaseclass} {$feedbackaccessclass}\">{$feedbackstr}</div></a>";
        }
        $gradebutton = (has_capability('mod/cognitivefactory:grade', $context)) ? "<a href=\"view.php?id={$cm->id}&amp;view=grade\"><div class=\"raised manager\">{$gradestr}</div></a>" : '' ;
    ?>
    <center>
    <table>
        <tr>
            <td>
                <?php echo $collectbutton ?>
            </td>
            <td>
                <?php echo $preparebutton ?>
            </td>
            <td>
                <?php echo $organizebutton ?>
            </td>
            <td>
                <?php echo $displaybutton ?>
            </td>
            <td>
                <?php echo $feedbackbutton ?>
            </td>
            <td>
                <?php echo $gradebutton ?>
            </td>
        </tr>
    </table>
    </center>
    <?php
    }
    
/// main menu

    if ($cognitivefactory->flowmode == 'parallel' || has_capability('mod/cognitivefactory:manage', $context)){ // make a first row of tabs
        if (has_capability('mod/cognitivefactory:collect', $context))
            $rows[0][] = new tabobject('collect', "view.php?id={$cm->id}&amp;view=collect", $collectstr);
        if (has_capability('mod/cognitivefactory:prepare', $context))
            $rows[0][] = new tabobject('prepare', "view.php?id={$cm->id}&amp;view=prepare", $preparestr);
        if (has_capability('mod/cognitivefactory:organize', $context))
            $rows[0][] = new tabobject('organize', "view.php?id={$cm->id}&amp;view=organize", $organizestr);
        if (has_capability('mod/cognitivefactory:display', $context))
            $rows[0][] = new tabobject('display', "view.php?id={$cm->id}&amp;view=display", $displaystr);
        if (has_capability('mod/cognitivefactory:report', $context))
            $rows[0][] = new tabobject('feedback', "view.php?id={$cm->id}&amp;view=feedback", $feedbackstr);
        if (has_capability('mod/cognitivefactory:grade', $context))
        	$rows[0][] = new tabobject('grade', "view.php?id={$cm->id}&amp;view=grade", $gradestr);   
    }
    
/// submenus

    switch ($view){
        case 'collect':
            $page = '';
        break;
        case 'prepare' :
            // This is a special location for controller as tabs are dynamically build as resulting of selection/unselection of operators
            $result = 0;
            if ($action != ''){
                $result = include 'prepare.controller.php';
            }
            $operators = cognitivefactory_get_operators($cognitivefactory->id);
            $operatorlist = cognitivefactory_get_operatorlist($operators, '|');
            if (empty($operatorlist)){
                $page = 'select';
            }
            else if (!preg_match("/select|$operatorlist/", $page)) {
                $page = 'select';
            }
            
            /// prepare submenu
            if (has_capability('mod/cognitivefactory:select', $context))
    		    $rows[1][] = new tabobject('select', "view.php?id={$cm->id}&amp;view=prepare&amp;page=select", get_string('select', 'cognitivefactory'));
    		foreach($operators as $operator){
    		    if (!$operator->active) continue;
    		    $rows[1][] = new tabobject($operator->id, "view.php?id={$cm->id}&amp;view=prepare&amp;page={$operator->id}", get_string($operator->id, 'cognitivefactory'));
    		}
            break;
        case 'organize':
            $operators = cognitivefactory_get_operators($cognitivefactory->id);
            $operatorlist = cognitivefactory_get_operatorlist($operators, '|');
            if (empty($operatorlist)){
                $page = 'summary';
            }
            else if (!preg_match("/select|$operatorlist/", $page)) {
                $page = 'summary';
            }
    		$rows[1][] = new tabobject('summary', "view.php?id={$cm->id}&amp;view=organize&amp;page=summary", get_string('summary', 'cognitivefactory'));
    		foreach($operators as $operator){
    		    if (!$operator->active) continue;
    		    $rows[1][] = new tabobject($operator->id, "view.php?id={$cm->id}&amp;view=organize&amp;page={$operator->id}", get_string($operator->id, 'cognitivefactory'));
    		}
            break;
        case 'display':
            $operators = cognitivefactory_get_operators($cognitivefactory->id);
            $operatorlist = cognitivefactory_get_operatorlist($operators, '|');
            if (empty($operatorlist)){
                $page = 'summary';
            }
            else if (!preg_match("/summary|$operatorlist/", $page)) {
                $page = 'summary';
            }
    		$rows[1][] = new tabobject('summary', "view.php?id={$cm->id}&amp;view=display&amp;page=summary", get_string('summary', 'cognitivefactory'));
    		foreach($operators as $operator){
    		    if (!$operator->active) continue;
    		    $rows[1][] = new tabobject($operator->id, "view.php?id={$cm->id}&amp;view=display&amp;page={$operator->id}", get_string($operator->id, 'cognitivefactory'));
    		}
            break;
        case 'feedback':
            if (!preg_match("/report|feedback/", $page)) {
                $page = 'report';
            }
    	    $rows[1][] = new tabobject('report', "view.php?id={$cm->id}&amp;view=feedback&amp;page=report", get_string('report', 'cognitivefactory'));
    	    if ($feedback = cognitivefactory_get_feedback($cognitivefactory->id))
    	        $rows[1][] = new tabobject('feedback', "view.php?id={$cm->id}&amp;view=feedback&amp;page=feedback", get_string('seefeedback', 'cognitivefactory'));
            break;
        case 'grade':
            $page = '';
            break;
        default:
    }    
    
    $selected = null;
    $activated = null;
    if (!empty($page)){
        $selected = $page;
        $activated = array($view);
    }
    else{
        $selected = $view;
    }
    
/// if sequential, bring back second row to first row before printing tags

    if (isset($rows)){
        if ($cognitivefactory->flowmode == 'sequential' && !has_capability('mod/cognitivefactory:manage', $context)){
            $rows[0] = $rows[1];
            unset($rows[1]);
        }
        print_tabs($rows, $selected, '', $activated);
    }
    ?>
            </td>
        </tr>
        <tr>
            <td>
    <?php
    
/// routing to active views
    
    echo " <br/><br/>";
    // echo "routing $view : $page : $action "; // for debug only
    
    if ($view == 'collect'){
        $result = 0;
        if ($action != ''){
            $result = include 'collect.controller.php';
        }
        if ($result != -1){
    	    include 'collect.php';
    	}
    }
    if ($view == 'prepare'){
        // here we call the local operator controller at a standard location if needed
        $operator = optional_param('operator', '', PARAM_ALPHA);
        $result = 0;
        if ($operator && $action){
            $result = include $CFG->dirroot."/mod/cognitivefactory/operators/{$operator}/prepare.controller.php";
        }
        if ($result != -1){
            switch($page){
                case 'select':
            	    include 'select.php';
            	    break;
                default: 
                    if (file_exists($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/prepare.php")){
            	        include $CFG->dirroot."/mod/cognitivefactory/operators/{$page}/prepare.php";
                    }
            	    break;
            }
        }
    }
    if ($view == 'organize'){
        // here we call the local operator controller at a standard location if needed
        $operator = optional_param('operator', '', PARAM_ALPHA);
        $result = 0;
        if ($operator && $action){
            $result = include $CFG->dirroot."/mod/cognitivefactory/operators/{$operator}/organize.controller.php";
        }
        if ($result != -1){
            switch($page){
                case 'summary':
    	            include 'summary.php';
        	        break;
                default: 
                    if (file_exists($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/organize.php")){
            	        include $CFG->dirroot."/mod/cognitivefactory/operators/{$page}/organize.php";
                    }
            	    break;
            }
    	}
    }
    if ($view == 'display'){
        $operator = optional_param('operator', '', PARAM_ALPHA);
        $result = 0;
        if ($operator && $action){
            $result = include $CFG->dirroot."/mod/cognitivefactory/operators/{$operator}/display.controller.php";
        }
        if ($result != -1){
            switch($page){
                case 'summary':
    	            include 'displaysummary.php';
        	        break;
                default: 
                    if (file_exists($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/display.php")){
            	        include $CFG->dirroot."/mod/cognitivefactory/operators/{$page}/display.php";
                    }
            	    break;
            }
    	}
    }
    if ($view == 'feedback'){
        $result = 0;
        if ($action != ''){
            $result = include $CFG->dirroot."/mod/cognitivefactory/feedback.controller.php";
        }
        if ($result != -1){
            switch($page){
                case 'report':
    	            include 'report.php';
    	            break;
                case 'feedback':
    	            include 'feedback.php';
    	            break;
    	        default:;
    	    }
    	}
    }
    if ($view == 'grade'){
        $result = 0;
        if ($action != ''){
            $result = include 'grade.controller.php';
        }
        if ($result != -1){
    	    include 'grade.php';
    	}
    }
    if ($view == 'unallowedphase'){
        $lang = current_language();
        include "{$CFG->dirroot}/mod/cognitivefactory/lang/{$lang}/unallowedphase.html";
    }
    if ($view == 'notmember'){
        $lang = current_language();
        include "{$CFG->dirroot}/mod/cognitivefactory/lang/{$lang}/notmember.html";
    }
    ?>
            </td>
        </tr>
    </table>
    <?php

/// Finish the page

    if (!empty($htmleditorneeded) and $usehtmleditor) {
        use_html_editor($editorfields);
    }
    print_footer($course);
?>