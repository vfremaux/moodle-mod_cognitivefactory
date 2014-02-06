<?php 

/**
* Module Brainstorm V2
* Operator : categorize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/********************************** Call the new category form ********************************/
if ($action == 'add'){
	$form = new StdClass();
   	$form->id = $cm->id;
   	$form->categoryid = 0;
   	include "$CFG->dirroot/mod/cognitivefactory/operators/categorize/edit.html";
   	return -1;
}
/********************************** Call the update form ********************************/
if ($action == 'update'){
	$form = new StdClass();
    $form->id = $cm->id;
    $form->categoryid = required_param('categoryid', PARAM_INT);
    include "$CFG->dirroot/mod/cognitivefactory/operators/categorize/edit.html";
    return -1;
}
/********************************** Stores data for a category ********************************/
if ($action == 'doadd'){
	$form = new StdClass();
    $form->title = required_param('title', PARAM_CLEANHTML);

    $category = new StdClass();
    $category->cognitivefactoryid = $cognitivefactory->id;
    $category->userid = $USER->id;
    $category->groupid = $currentgroup;
    $category->title = $form->title;
    $category->timemodified = time();

    if (!$DB->insert_record('cognitivefactory_categories', $category)){
        print_error('erroinsert', 'cognitivefactory', '', get_string('category', 'cognitiveoperator_categorize'));
    }
}
/********************************** Stores new data for a category ********************************/
if ($action == 'doupdate'){
	$form = new StdClass();
    $form->categoryid = required_param('categoryid', PARAM_INT);
    $form->title = required_param('title', PARAM_CLEANHTML);
    $category->id = $form->categoryid;
    $category->userid = $USER->id;
    $category->groupid = $currentgroup;
    $category->title = $form->title;
    $category->timemodified = time();
    if (!$DB->update_record('cognitivefactory_categories', $category)){
        print_error('erroupdate', 'cognitivefactory', '', get_string('category', 'cognitiveoperator_categorize'));
    }
}
/********************************** Delete a category ********************************/
if ($action == 'delete'){
	$form = new StdClass();
    $form->categoryid = required_param('categoryid', PARAM_INT);
    if (!$DB->delete_records('cognitivefactory_categories', array('id' => $form->categoryid))){
        print_error('errodelete', 'cognitivefactory', '', get_string('category', 'cognitiveoperator_categorize'));
    }
}
