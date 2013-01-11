<?php

include_once $CFG->libdir.'/formslib.php';

class enrol_profilefield_enrol_form extends moodleform{
    protected $instance;
    protected $toomany = false;

    /**
     * Overriding this function to get unique form id for multiple self enrolments
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
        $formid = $this->_customdata->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
        global $DB;

        $mform = $this->_form;
        $instance = $this->_customdata;
        $this->instance = $instance;
        $plugin = enrol_get_plugin('self');

        $heading = $plugin->get_instance_name($instance);
        $mform->addElement('header', 'profilefieldheader', $heading);

        if ($instance->customint3 > 0) {
            // max enrol limit specified
            $count = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
            if ($count >= $instance->customint3) {
                // bad luck, no more self enrolments here
                $this->toomany = true;
                $mform->addElement('static', 'notice', '', get_string('maxenrolledreached', 'enrol_profilefield'));
                return;
            }
        }

        //change the id of self enrolment key input as there can be multiple self enrolment methods
        $mform->addElement('passwordunmask', 'grouppassword', get_string('grouppassword', 'enrol_profilefield'), array('id' => $instance->id."_enrolpassword"));

        $this->add_action_buttons(false, get_string('enrolme', 'enrol_profilefield'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);
    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $instance = $this->instance;

        if ($this->toomany) {
            $errors['notice'] = get_string('error');
            return $errors;
        }

		if (!empty($data['enrolpassword'])){
	        $groups = $DB->get_records('groups', array('courseid' => $instance->courseid), 'id ASC', 'id, enrolmentkey');
	        $found = false;
	        foreach ($groups as $group) {
	            if (empty($group->enrolmentkey)) {
	                continue;
	            }
	            if ($group->enrolmentkey === $data['enrolpassword']) {
	                $found = true;
	                break;
	            }
	        }
	        if (!$found) {
	            // we can not hint because there are probably multiple passwords
	            $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_profilefield');
	        }
	    }

        return $errors;
    }
}