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
 * Adds new instance of enrol_paypal to specified course
 * or edits current instance.
 *
 * @package    enrol
 * @subpackage paypal
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('edit_form.php');

$courseid   = required_param('courseid', PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT); // instanceid

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/profilefield:config', $context);

$PAGE->set_url('/enrol/profilefield/edit.php', array('courseid' => $course->id, 'id' => $instanceid));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
if (!enrol_is_enabled('profilefield')) {
    redirect($return);
}

$plugin = enrol_get_plugin('profilefield');

if ($instanceid) {
    $instance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'profilefield', 'id' => $instanceid), '*', MUST_EXIST);
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // no instance yet, we have to add new instance
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id' => $course->id)));
    $instance = new stdClass();
    $instance->id       = null;
    $instance->courseid = $course->id;
    $instance->customchar1 = ''; // profile field
    $instance->customchar2 = ''; // profile value
    $instance->customint1 = 1; // notifies teachers of entry by default
    $instance->customtext1 = get_string('defaultnotification', 'enrol_profilefield'); // notification for teachers
}

$mform = new enrol_profilefield_edit_form(NULL, array($instance, $plugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) {
    if ($instance->id) {
        $reset = ($instance->status != $data->status);

        $instance->status         = $data->status;
        $instance->name           = $data->name;
        $instance->roleid         = $data->roleid;
        $instance->customint1     = 0 + @$data->notifymanagers; // checkbox
        $instance->customtext1    = $data->notificationtext;
        $instance->customchar1    = $data->profilefield;
        $instance->customchar2    = $data->profilevalue;
        $instance->enrolperiod    = $data->enrolperiod;
        $instance->enrolstartdate = $data->enrolstartdate;
        $instance->enrolenddate   = $data->enrolenddate;
        $instance->timemodified   = time();
        $DB->update_record('enrol', $instance);

        if ($reset) {
            $context->mark_dirty();
        }

    } else {
        $fields = array('status' => $data->status, 
        				'name' => $data->name, 
        				'profilefield' => $data->profilefield, 
        				'profilevalue' => $data->profilevalue, 
        				'roleid' => $data->roleid,
                        'enrolperiod' => $data->enrolperiod, 
                        'enrolstartdate' => $data->enrolstartdate, 
                        'enrolenddate' => $data->enrolenddate
        );
        $plugin->add_instance($course, $fields);
    }

    redirect($return);
}

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_profilefield'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_profilefield'));
$mform->display();
echo $OUTPUT->footer();
