<?php

/**
* Module Brainstorm V2
* Operator : categorize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

if (has_capability('mod/cognitivefactory:gradable', $context, $USER->id, false)){
	// if i organize for my own if this activity is open to students
	$behalfed = $USER->id;
	$categoryowner = $behalfed;
} else {
	$behalfed = optional_param('behalfed', $USER->id, PARAM_INT);
	$categoryowner = 0;
	// groups_print_course_menu($course, $CFG->wwwroot."/mod/cognitivefactory/view.php?id=$id&view=$view");
	$currentgroupid = groups_get_activity_group ($cm);
	$users = groups_get_members($currentgroupid);
	$behalfedmenu = array();
	foreach($users as $u){
		if (has_capability('mod/cognitivefactory:gradable', $context, $u->id)){
			$behalfedmenu[$u->id] = fullname($u);
		}
	}
}

echo $OUTPUT->heading("<img src=\"".$OUTPUT->pix_url('enabled_small', 'cognitiveoperator_categorize')."\" align=\"left\" width=\"40\" /> " . get_string("organizing{$page}", 'cognitiveoperator_'.$page));
$categories = categorize_get_categories($cognitivefactory->id, $categoryowner, $currentgroup);
$categorization = categorize_get_categoriesperresponses($cognitivefactory->id, $behalfed, $currentgroup);

$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup);
$current_operator = new BrainstormOperator($cognitivefactory->id, $page);
$category_menu = array();
if (!empty($categories)){
	foreach($categories as $category){
	  $category_menu[$category->id] = $category->title;
	}
}

$matchgroup = (!$groupmode) ? 0 : $currentgroup ;
$matchings = categorize_get_matchings($cognitivefactory->id, $behalfed, $matchgroup);
$maxspan = 2;
?>
<center>
<?php
if (isset($current_operator->configdata->requirement))
    echo $OUTPUT->box($current_operator->configdata->requirement);
?>
<a name="theform"></a>
<form name="categorizationform" method="post" action="view.php#theform">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<?php
	if (!empty($behalfedmenu)){
		print_string('behalfof', 'cognitiveoperator_'.$page);
		echo html_writer::select($behalfedmenu, 'behalfed', $behalfed);
		echo "<input type=\"submit\" name=\"go_btn\" value=\"".get_string('changeuser', 'cognitiveoperator_'.$page).'" />';
	}
?>
<input type="hidden" name="operator" value="<?php p($page) ?>"/>
<input type="hidden" name="what" value="" />
<br/>
<br/>
<table width="90%" cellspacing="5" class="cognitiveoperator">
<?php

if (!empty($responses)){
    $counts = array();
    foreach($responses as $response){
?>
    <tr valign="top">
        <td align="right">
            <?php echo $response->response ?>
        </td>
        <td align="left">
            <?php
            if (!@$current_operator->configdata->allowmultiple){
                $categoryvalue = (isset($categorization[$response->id]->categories)) ? $categorization[$response->id]->categories[0] : 0 ;
                $counts[$categoryvalue] = 0 + @$counts[$categoryvalue] + 1;
                echo html_writer::select($category_menu, 'cat_'.$response->id, $categoryvalue, '', array('onchange' => 'checkmaxrange(this)'));            
            }
            else{
            	// todo : check multiple selection
                echo html_writer::select($category_menu, 'cat_'.$response->id.'[]', @$categorization[$response->id]->categories, array('choose' => 'choosedots'));                        
            }
            ?>
        </td>
<?php
        if (!$cognitivefactory->privacy && !@$current_operator->configdata->blindness){
            $maxspan = 4;
?>
        <td align="left">
            <?php
            if (!empty($matchings->match)){
                if (array_key_exists($response->id, $matchings->match)){
                    if ($matchings->match[$response->id] == 1)
                        print_string('agreewithyousingle', 'cognitivefactory', $matchings->match[$response->id]);
                    else
                        print_string('agreewithyou', 'cognitivefactory', $matchings->match[$response->id]);
                }
            }
            ?>
        </td>
        <td align="left">
            <?php
            if (!empty($matchings->unmatch)){
                if (array_key_exists($response->id, $matchings->unmatch)){
                    if ($matchings->unmatch[$response->id] == 1)
                        print_string('disagreewithyousingle', 'cognitivefactory', $matchings->unmatch[$response->id]);
                    else
                        print_string('disagreewithyou', 'cognitivefactory', $matchings->unmatch[$response->id]);
                }
            }
            ?>
        </td>
<?php
        }
?>
    </tr>    
<?php
    }
?>
    <tr>
        <td colspan="<?php echo $maxspan ?>">
            <br/><input onclick="document.categorizationform.what.value = 'savecategorization';" type="submit" name="go_btn" value="<?php print_string('savecategorization', 'cognitiveoperator_'.$page) ?>" />
        </td>
    </tr>
<?php
} else {
	if (!has_capability('mod/cognitivefactory:gradable', $context, $USER->id, false)){
		echo $OUTPUT->notification(get_string('noanswers', 'cognitiveoperator_categorize'));
	}
}
?>
</table>
</form>
<script type="text/javascript" src="<?php echo $CFG->wwwroot.'/mod/cognitivefactory/operator/categorize/js/module.js' ?>"></script>
<script type="text/javascript">
// check for more than allowed items per category
var responsekeys = '<?php echo implode(",", array_keys($responses)) ?>';
var maxitemspercategory = <?php echo 0 + $current_operator->configdata->maxitemspercategory ?> ;
var message = '<?php echo get_string('exceedspercategorylimit', 'cognitiveoperator_'.$page, 0 + @$current_operator->configdata->maxitemspercategory ) ?>';
var allowmultiple = <?php echo 0 + @$currentoperator->configdata->allowmultiple ?>;
</script>

</center>