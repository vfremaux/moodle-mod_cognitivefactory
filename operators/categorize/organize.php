<?php

/**
* Module Brainstorm V2
* Operator : categorize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

if (has_capability('mod/cognitivefactory:gradable', $context)){
	// if i organize for my own if this ativity is open to students
	$behalfed = $USER->id;
	$categoryowner = $behalfed;
} else {
	$behalfed = optional_param('behalfed', $USER->id, PARAM_INT);
	$categoryowner = 0;
	// groups_print_course_menu($course, $CFG->wwwroot."/mod/cognitivefactory/view.php?id=$id&view=$view");
	$currentgroupid = groups_get_course_group ($course, true);
	$users = groups_get_members($currentgroupid);
	$behalfedmenu = array();
	foreach($users as $u){
		if (has_capability('mod/cognitivefactory:gradable', $context, $u->id)){
			$behalfedmenu[$u->id] = fullname($u);
		}
	}
}

print_heading("<img src=\"{$CFG->wwwroot}/mod/cognitivefactory/operators/{$page}/pix/enabled_small.gif\" align=\"left\" width=\"40\" /> " . get_string("organizing$page", 'cognitivefactory'));
$categories = categorize_get_categories($cognitivefactory->id, $categoryowner, $currentgroup);
$categorization = categorize_get_categoriesperresponses($cognitivefactory->id, $behalfed, $currentgroup);
// print_object($categorization);
$responses = cognitivefactory_get_responses($cognitivefactory->id, $behalfed, $currentgroup);
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
    print_simple_box($current_operator->configdata->requirement);
?>
<a name="theform"></a>
<form name="categorizationform" method="post" action="view.php#theform">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<?php
	if (!empty($behalfedmenu)){
		print_string('behalfof', 'cognitivefactory');
		choose_from_menu($behalfedmenu, 'behalfed', $behalfed);
		echo "<input type=\"submit\" name=\"go_btn\" value=\"".get_string('changeuser', 'cognitivefactory').'" />';
	}
?>
<input type="hidden" name="operator" value="<?php p($page) ?>"/>
<input type="hidden" name="what" value="" />
<br/>
<br/>
<table width="90%" cellspacing="5">
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
                choose_from_menu($category_menu, 'cat_'.$response->id, $categoryvalue, '', 'checkmaxrange(this)');            
            }
            else{
                choose_multiple_from_menu($category_menu, 'cat_'.$response->id.'[]', @$categorization[$response->id]->categories, 'choose', '',
                           '0', false, false, 0, '', round(count($categories) / 2));                        
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
            <br/><input onclick="document.categorizationform.what.value = 'savecategorization';" type="submit" name="go_btn" value="<?php print_string('savecategorization', 'cognitivefactory') ?>" />
        </td>
    </tr>
<?php
} else {
	if (!has_capability('mod/cognitivefactory:gradable', $context, $USER->id, false)){
		notify(get_string('noanswers', 'cognitivefactory'));
	}
}
?>
</table>
</form>
<script type="text/javascript">
// check for more than allowed items per category
<?php
if (!empty($current_operator->configdata->maxitemspercategory) && !@$currentoperator->configdata->allowmultiple){
?>
var responsekeys = '<?php echo implode(",", array_keys($responses)) ?>';

function countvalues(value){
    resplist = responsekeys.split(/,/);
    cnt = 0;
    for (respid in resplist){
        listobj = document.forms['categorizationform'].elements['cat_' + resplist[respid]];
        if (listobj.options[listobj.selectedIndex].value == value){
            cnt++;
        }
    }
    return cnt;
}

function checkmaxrange(listobj){
    if (countvalues(listobj.options[listobj.selectedIndex].value) > <?php echo $current_operator->configdata->maxitemspercategory ?>) {
       alert("<?php print_string('exceedspercategorylimit', 'cognitivefactory', 0 + @$current_operator->configdata->maxitemspercategory ) ?>");
       listobj.selectedIndex = 0; 
       listobj.focus();
    }
}
<?php
} else {
?>
function checkmaxrange(listobj){
}
<?php
}
?>
</script>
</center>