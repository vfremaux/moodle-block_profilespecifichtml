<?php
// This file is not part of Moodle - http://moodle.org/
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
 * Database enrolment plugin.
 *
 * This plugin synchronises enrolment and roles with external database table.
 *
 * @package    enrol
 * @subpackage profilefield
 * @version Moodle 2
 * @copyright  2012 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Database enrolment plugin implementation.
 * @author  Valery Fremaux - based on code by Martin Dougiamas, Martin Langhoff and others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_profilefield_plugin extends enrol_plugin {

    public function allow_enrol(stdClass $instance) {
        // users with enrol cap may enrol other users manually
        return true;
    }

    public function allow_unenrol(stdClass $instance) {
        // users with unenrol cap may unenrol other users manually
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // users with manage cap may tweak period and status
        return true;
    }

    public function show_enrolme_link(stdClass $instance) {
        return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    public function get_info_icons(array $instances) {
        return array(new pix_icon('icon', get_string('pluginname', 'enrol_profilefield'), 'enrol_profilefield'));
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/profilefield:config', $context)) {
            return NULL;
        }

        // multiple instances supported - different cost for different roles
        return new moodle_url('/enrol/profilefield/edit.php', array('courseid'=>$courseid));
    }


    /**
     * Returns enrolment instance manage link.
     *
     * By defaults looks for manage.php file and tests for manage capability.
     *
     * @param navigation_node $instancesnode
     * @param stdClass $instance
     * @return moodle_url;
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'profilefield') {
             throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/profilefield:config', $context)) {
            $managelink = new moodle_url('/enrol/profilefield/edit.php', array('courseid' => $instance->courseid));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'profilefield') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = get_context_instance(CONTEXT_COURSE, $instance->courseid);

        $icons = array();

        if (has_capability('enrol/profilefield:config', $context)) {
            $editlink = new moodle_url("/enrol/profilefield/edit.php", array('courseid' => $instance->courseid, 'id' => $instance->id));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('i/edit', get_string('edit'), 'core', array('class' => 'icon')));
        }

        return $icons;
    }

     /**
     * Gets an array of the user enrolment actions. These are provided
     * in the enrolled user list, in the enrolment method column.
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol($instance) && has_capability("enrol/profilefield:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class' => 'unenrollink', 'rel' => $ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/profilefield:manage", $context)) {
            $url = new moodle_url('/enrol/profilefield/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class' => 'editenrollink', 'rel' => $ue->id));
        }
        return $actions;
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $CFG, $OUTPUT, $SESSION, $USER, $DB;

        if (isguestuser()) {
            // can not enrol guest!!
            return null;
        }

        if ($DB->record_exists('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
            //TODO: maybe we should tell them they are already enrolled, but can not access the course
            return null;
        }

        if ($instance->enrolstartdate != 0 and $instance->enrolstartdate > time()) {
            //TODO: inform that we can not enrol yet
            return null;
        }

        if ($instance->enrolenddate != 0 and $instance->enrolenddate < time()) {
            //TODO: inform that enrolment is not possible any more
            return null;
        }
        
        if ($this->check_user_profile_conditions($instance)){

	        require_once("$CFG->dirroot/enrol/profilefield/enrol_form.php");
	        require_once("$CFG->dirroot/group/lib.php");
	
	        $form = new enrol_profilefield_enrol_form(NULL, $instance);
	        $instanceid = optional_param('instance', 0, PARAM_INT);
	
	        if ($instance->id == $instanceid) {
	            if ($data = $form->get_data()) {
	                $enrol = enrol_get_plugin('profilefield');
	                $timestart = time();
	                if ($instance->enrolperiod) {
	                    $timeend = $timestart + $instance->enrolperiod;
	                } else {
	                    $timeend = 0;
	                }
	
	                $this->enrol_user($instance, $USER->id, $instance->roleid, $timestart, $timeend);
	                add_to_log($instance->courseid, 'course', 'enrol', '../enrol/users.php?id='.$instance->courseid, $instance->courseid); //there should be userid somewhere!
					
	                if (!empty($data->enrolpassword)) {
	                    // it must be a group enrolment, let's assign group too
	                    if ($groups = $DB->get_records('groups', array('courseid' => $instance->courseid), 'id', 'id, enrolmentkey')){
		                    foreach ($groups as $group) {
		                        if (empty($group->enrolmentkey)) {
		                            continue;
		                        }
		                        if ($group->enrolmentkey === $data->enrolpassword) {
		                            groups_add_member($group->id, $USER->id);
		                            break;
		                        }
		                    }
		                }
	                }
	
	                // send notification to teachers 
	                if ($instance->customint1) {
	                    $this->notify_owners($instance, $USER);
	                }
	            }
	        }
	    } else {
	    	$output = $OUTPUT->heading($instance->name, 2);
	    	$output .= $OUTPUT->notification(get_string('badprofile', 'enrol_profilefield'));
	    	$output .= $OUTPUT->continue_button($CFG->wwwroot);
        	return $OUTPUT->box($output);
	    }

        ob_start();
	   	echo $OUTPUT->notification(get_string('enrolmentconfirmation', 'enrol_profilefield'));
        $form->display();
        $output = ob_get_clean();

        return $OUTPUT->box($output);
    }
    
    /**
    * checks all user profile conditions to get in
    *
    */
    function check_user_profile_conditions(stdClass $instance){
    	global $USER, $DB;
    	
    	$profilefield = $instance->customchar1;
    	$profilevalue = $instance->customchar2;
    	
    	if (preg_match('/^profile_field_(.*)$/', $profilefield, $matches)){
    		// case of user custom fields
    		
	    	if (!$pfield = $DB->get_record('user_info_field', array('shortname' => $matches[1]))){
	    		return false;
	    	}
	    	
	    	$uservalue = $DB->get_field('user_info_data', 'data', array('userid' => $USER->id, 'fieldid' => $pfield->id));
	    	if ($uservalue == $profilevalue) return true;
    	} else {
    		// we guess it is a standard user attribute
    		if (isset($USER->$profilefield)){
	    		if ($profilevalue == $USER->$profilefield) return true;
	    	}
    	}
    	    	
    	return false;
    }
    
    function notify_owners(&$instance, &$appliant){
    	global $DB, $CFG;

        $course = $DB->get_record('course', array('id' => $instance->courseid), '*', MUST_EXIST);

        $a = new stdClass();
        $a->profileurl = $CFG->wwwroot."/user/view.php?id={$appliant->id}&course={$course->id}";

        if (trim($instance->customtext1) !== '') {
            $message = $instance->customtext1;
            $message = str_replace('<%%USERNAME%%>', fullname($appliant), $message);
            $message = str_replace('<%%COURSE%%>', format_string($course->fullname), $message);
            $message = str_replace('<%%SHORTNAME%%>', $course->shortname, $message);
            $message = str_replace('<%%URL%%>', $a->profileurl, $message);

        	$subject = get_string('newcourseenrol', 'enrol_profilefield', format_string($course->fullname));
        }

        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        
        if ($managers = get_users_by_capability($context, 'enrol/profilefield:manage', 'u.id, firstname, lastname, email, emailstop')){

			foreach($managers as $m){
            	$message = str_replace('<%%TEACHER%%>', fullname($m), $message);
		        // directly emailing message rather than using messaging
		        /*
		        echo "Mailing notification to ".fullname($m);
		        echo "-------<br/>";
		        echo "<b>$subject</b><br/>";
		        echo $message;
		        echo "-------<br/>";
		        die; // for test
				*/		        
		        email_to_user($m, $appliant, $subject, $message);
		    }
	    } else {
	    	/*
	        echo "NO MANAGERS -------<br/>";
	        die; // for test
	        */
	    }
    }
}

/**
 * Indicates API features that the enrol plugin supports.
 *
 * @param string $feature
 * @return mixed True if yes (some features may use other values)
 */
function enrol_profilefield_supports($feature) {
    switch($feature) {
        case ENROL_RESTORE_TYPE: return ENROL_RESTORE_EXACT;

        default: return null;
    }
}
