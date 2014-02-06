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
echo $OUTPUT->heading(get_string('mycategories', 'cognitiveoperator_'.$page));
categorize_display($cognitivefactory, null, $currentgroup);

if (!$cognitivefactory->privacy && (!has_capability('mod/cognitivefactory:gradable', $context) || !@$current_operator->configdata->blindness)){
	echo $OUTPUT->heading(get_string('othercategories', 'cognitiveoperator_'.$page));
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
	        echo '<table width="90%"><tr>';
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
	                            $users[$userid] = $DB->get_record('user', array('id' => $userid), 'id,lastname,firstname,email,picture,imagealt');
	                        }
	                        echo $OUTPUT->user_picture($users[$userid]) . ' ' . fullname($users[$userid]) . '<br/>';
	                    }
	                }
	                echo '</td>';
	            }
	            echo '</tr>';
	        }
	        echo '</table>';
	    } else {
	        echo $OUTPUT->box(get_string('alluncategorized', 'cognitiveoperator_'.$page), 'cognitivefactory-notification');
	    }
	} else {
	    echo $OUTPUT->box(get_string('nootherdata', 'cognitiveoperator_'.$page), 'cognitivefactory-notification');
	}
}
?>
</center>
<br/>
