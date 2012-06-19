<?php

/**
* Module Brainstorm V2
* Operator : categorize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

include_once $CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php";
?>
<style>
.categorizecell { border : 1px solid gray ; padding : 2px }
</style>
<center>
<?php
print_heading(get_string('mycategories', 'cognitivefactory'));
categorize_display($cognitivefactory, null, $currentgroup);

if (!$cognitivefactory->privacy && (!has_capability('mod/cognitivefactory:gradable' || !@$current_operator->configdata->blindness))){
	print_heading(get_string('othercategories', 'cognitivefactory'));
	$responses = categorize_get_responsespercategories($cognitivefactory->id, 0, $currentgroup);
	if ($responses){
	    foreach ($responses as $categorytitle => $responsesincategory){
	        if (empty($responsesincategory)) continue;
	        foreach($responsesincategory as $response){
	            if (isset($response->opuserid))
	                $responsemap[$response->response][$categorytitle][] = $response->opuserid;
	        }
	        $categories[] = $categorytitle;
	    }
	    if (!empty($responsemap)){
	        sort($categories);
	        echo '<table width="90%"><tr><td></td>';
	    
	        /// print categories as title row
	        foreach ($categories as $category){
	            echo '<th>'.$category.'</th>';
	        }
	        echo '</tr>';
	        
	        /// print data rows
	        foreach(array_keys($responsemap) as $response){
	            echo '<tr><th>'.$response.'</th>';
	            $users = array();
	            foreach ($categories as $category){
	                echo '<td class="categorizecell">';
	                if (!empty($responsemap[$response][$category])){
	                    foreach($responsemap[$response][$category] as $userid){
	                        if (!array_key_exists($userid, $users)){
	                            $users[$userid] = get_record('user', 'id', $userid, '', '', '', '', 'id,lastname,firstname,email,picture');
	                        }
	                        echo print_user_picture($userid, $course->id, $users[$userid]->picture, 0, true, true) . ' ' . fullname($users[$userid]) . '<br/>';
	                    }
	                }
	                echo '</td>';
	            }
	            echo '</tr>';
	        }
	        echo '</table>';
	    } else {
	        print_simple_box(get_string('alluncategorized', 'cognitivefactory'));
	    }
	} else {
	    print_simple_box(get_string('nootherdata', 'cognitivefactory'));
	}
}
?>
</center>
<br/>
