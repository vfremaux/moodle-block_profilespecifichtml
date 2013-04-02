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
 * Adds new instance of enrol_profilefield to specified course
 * or edits current instance.
 *
 * @package    enrol
 * @subpackage profilefield
 * @copyright  2013 Valery Fremaux  (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_profilefield_edit_form extends moodleform {

    function definition() {
    	global $DB;
    	
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_profilefield'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_profilefield'), $options);
        $mform->setDefault('status', $plugin->get_config('status'));
        
        
        $userfields = array(
        	'country' => get_string('country'),
        	'lang' => get_string('language'),
        	'institution' => get_string('institution'),
        	'department' => get_string('department'),
        	'city' => get_string('city'),
        );
        
        $userextrafields = $DB->get_records('user_info_field', array());
        if ($userextrafields){
        	foreach($userextrafields as $uf){
        		$userfields['profile_field_'.$uf->shortname] = $uf->name;
        	}
        }

        $mform->addElement('select', 'profilefield', get_string('profilefield', 'enrol_profilefield'), $userfields);

        $mform->addElement('text', 'profilevalue', get_string('profilevalue', 'enrol_profilefield'), array('size' => 10));

        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_profilefield'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));

        $mform->addElement('checkbox', 'notifymanagers', get_string('notifymanagers', 'enrol_profilefield'));

        $mform->addElement('textarea', 'notificationtext', get_string('notificationtext', 'enrol_profilefield'), array('cols' => 60, 'rows' => 10));
        $mform->addHelpButton('notificationtext', 'notificationtext', 'enrol_profilefield');

        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_profilefield'), array('optional' => true, 'defaultunit' => 86400));
        $mform->setDefault('enrolperiod', $plugin->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_profilefield');

        $mform->addElement('date_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_profilefield'), array('optional' => true));
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_profilefield');

        $mform->addElement('date_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_profilefield'), array('optional' => true));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_profilefield');

        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'courseid');

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

		$instance->profilefield = $instance->customchar1;
		$instance->profilevalue = $instance->customchar2;
		$instance->notifymanagers = $instance->customint1;
		if (empty($instance->customtext1)){
			$instance->customtext1 = get_string('defaultnotification', 'enrol_profilefield');
		}
		$instance->notificationtext = $instance->customtext1;
        $this->set_data($instance);
    }

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        list($instance, $plugin, $context) = $this->_customdata;
        
        if ($data['status'] == ENROL_INSTANCE_ENABLED) {
            if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
                $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_profilefield');
            }
        }

        return $errors;
    }
}