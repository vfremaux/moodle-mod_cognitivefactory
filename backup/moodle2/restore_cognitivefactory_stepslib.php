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
 * @package mod-cognitivefactory
 * @copyright 2010 onwards Valery Fremaux (valery.freamux@club-internet.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_url_activity_task
 */

/**
 * Structure step to restore one cognitivefactory activity
 */
class restore_cognitivefactory_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $cognitivefactory = new restore_path_element('cognitivefactory', '/activity/cognitivefactory');
        $paths[] = $cognitivefactory;
        $operators = new restore_path_element('cognitivefactory_operator', '/activity/cognitivefactory/operators/operator');
        $paths[] = $operators;
        
        if ($userinfo) {
            $categories = new restore_path_element('cognitivefactory_category', '/activity/cognitivefactory/categories/category');
            $paths[] = $categories;
            $opdata = new restore_path_element('cognitivefactory_opdatum', '/activity/cognitivefactory/opdata/opdatum');
            $paths[] = $opdata;
            $responses = new restore_path_element('cognitivefactory_response', '/activity/cognitivefactory/responses/userresponse');
            $paths[] = $responses;
            $userdata = new restore_path_element('cognitivefactory_userdatum', '/activity/cognitivefactory/userata/userdatum');
            $paths[] = $userdata;
            $grades = new restore_path_element('cognitivefactory_grade', '/activity/cognitivefactory/grades/grade');
            $paths[] = $grades;
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_cognitivefactory($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the label record
        $newitemid = $DB->insert_record('cognitivefactory', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        // Add cognitivefactory related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_cognitivefactory', 'intro', null);
        $this->add_related_files('mod_cognitivefactory', 'response', null);
        $this->add_related_files('mod_cognitivefactory', 'userdata', null);
        $this->add_related_files('mod_cognitivefactory', 'feedback', null);
    }

    protected function process_cognitivefactory_category($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;

        $data->cognitivefactoryid = $this->get_new_parentid('cognitivefactory');
        $data->userid = $this->get_mappingid('user', $data->userid);

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('cognitivefactory_categories', $data);
        $this->set_mapping('cognitivefactory_category', $oldid, $newitemid, false); // Has no related files
    }

    protected function process_cognitivefactory_response($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->cognitivefactoryid = $this->get_new_parentid('cognitivefactory');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('cognitivefactory_responses', $data);
        $this->set_mapping('cognitivefactory_response', $oldid, $newitemid, false); // Has no related files
    }

    protected function process_cognitivefactory_operator($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->cognitivefactoryid = $this->get_new_parentid('cognitivefactory');

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('cognitivefactory_operators', $data);
        $this->set_mapping('cognitivefactory_operators', $oldid, $newitemid, false); // Has no related files
    }

    protected function process_cognitivefactory_opdatum($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->cognitivefactoryid = $this->get_new_parentid('cognitivefactory');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('cognitivefactory_opdata', $data);
        $this->set_mapping('cognitivefactory_opdata', $oldid, $newitemid, false); // Has no related files
    }

    protected function process_cognitivefactory_userdatum($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->cognitivefactoryid = $this->get_new_parentid('cognitivefactory');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupidid);

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('cognitivefactory_userdata', $data);
        $this->set_mapping('cognitivefactory_userdata', $oldid, $newitemid, false); // Has no related files
    }

    protected function process_cognitivefactory_grade($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->cognitivefactoryid = $this->get_new_parentid('cognitivefactory');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupidid);

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('cognitivefactory_grades', $data);
        $this->set_mapping('cognitivefactory_grades', $oldid, $newitemid, false); // Has no related files
    }

}
