<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    mod
 * @subpackage tracker
 * @copyright  2010 onwards Valery Fremaux {valery.fremaux@club-internet.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_vodclic_activity_task
 */

/**
 * Define the complete label structure for backup, with file and id annotations
 */
class backup_cognitivefactory_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $cognitivefactory = new backup_nested_element('cognitivefactory', array('id'), array('name','intro','introformat',
        'collectrequirement','collectrequirementformat','flowmode','seqaccesscollect','seqaccessprepare','seqaccessorganize',
        'seqaccessdisplay','seqaccessfeedback','phase','privacy','numresponses','numresponsesinform','numcolumns','oprequirementtype',
        'grade','singlegrade','participationweight','preparingweight','organizeweight','feedbackweight','globalteacherfeedback', 'timemodified'));

        $operators = new backup_nested_element('operators');

        $operator = new backup_nested_element('operator', array('id'), array('operatorid','configdata','active'));

        $opdata = new backup_nested_element('opdata');

        $opdatum = new backup_nested_element('question', array('id'), array('userid','groupid','operatorid','itemsource',
		'itemdest','intvalue','floatvalue','blobvalue','timemodified'));

        $responses = new backup_nested_element('responses');

        $response = new backup_nested_element('response', array('id'), array('userid','groupid','response','timemodified'));
            
        $categories = new backup_nested_element('categories');
        
        $category = new backup_nested_element('category', array('id'), array('userid','groupid','title','timemodified'));

        $userdata = new backup_nested_element('userdata');

        $userdatum = new backup_nested_element('userdatum', array('id'), array('userid', 'report', 'reportformat', 'feedback', 'feedbackformat', 'timeupdated'));

        $grades = new backup_nested_element('grades');

        $grade = new backup_nested_element('grade', array('id'), array('userid', 'grade', 'gradeitem', 'timeupdated'));
            
        // Build the tree
        // (love this)
        $cognitivefactory->add_child($categories);
        $categories->add_child($category);

        $cognitivefactory->add_child($operators);
        $operators->add_child($operator);

        $cognitivefactory->add_child($opdata);
        $opdata->add_child($opdatum);

		$cognitivefactory->add_child($responses);
		$response->add_child($response);

		$cognitivefactory->add_child($userdata);
		$userdata->add_child($userdatum);

		$cognitivefactory->add_child($grades);
		$grades->add_child($grade);

        // Define sources
        $cognitivefactory->set_source_table('cognitivefactory', array('id' => backup::VAR_ACTIVITYID));
        $operator->set_source_table('cognitivefactory_operators', array('cognitivefactoryid' => backup::VAR_PARENTID));

        if ($userinfo) {
	        $category->set_source_table('cognitivefactory_categories', array('cognitivefactoryid' => backup::VAR_PARENTID));
	        $opdatum->set_source_table('cognitivefactory_opdata', array('cognitivefactoryid' => backup::VAR_PARENTID));
	        $response->set_source_table('cognitivefactory_responses', array('cognitivefactoryid' => backup::VAR_PARENTID));
	        $userdatum->set_source_table('cognitivefactory_userdata', array('cognitivefactoryid' => backup::VAR_PARENTID));
	        $grade->set_source_table('cognitivefactory_grades', array('cognitivefactoryid' => backup::VAR_PARENTID));
        }

        // Define id annotations
        // (none)
        $opdatum->annotate_ids('user', 'userid');
        $responses->annotate_ids('user', 'userid');
        $category->annotate_ids('user', 'userid');
        $userdatum->annotate_ids('user', 'userid');
        $grade->annotate_ids('user', 'userid');
        $opdatum->annotate_ids('group', 'groupid');
        $responses->annotate_ids('group', 'groupid');
        $category->annotate_ids('group', 'groupid');

        // Define file annotations
        $cognitivefactory->annotate_files('mod_cognitivefactory', 'intro', null); // This file area hasn't itemid
        $response->annotate_files('mod_cognitivefactory', 'response', 'id'); // This file area has itemid
        $userdata->annotate_files('mod_cognitivefactory', 'report', 'id'); // This file area has itemid
        $userdata->annotate_files('mod_cognitivefactory', 'feedback', 'id'); // This file area has itemid

        // Return the root element (tracker), wrapped into standard activity structure
        return $this->prepare_activity_structure($cognitivefactory);
    }
}
