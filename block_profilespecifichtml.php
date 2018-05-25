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
 * @package   block_profilespecifichtml
 * @category  blocks
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright 2012 Valery Fremaux
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/filelib.php');

class block_profilespecifichtml extends block_base {

    public function init() {
        $this->title = get_string('blockname', 'block_profilespecifichtml');
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function specialization() {
        $newblockstr = get_string('newhtmlblock', 'block_profilespecifichtml');
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string($newblockstr);
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid)) {
            return false;
        }

        // Find out if this block is on the profile page.
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                /*
                 * this is exception - page is completely private, nobody else may see content there
                 * that is why we allow JS here
                 */
                return true;
            } else {
                // No JS on public personal pages, it would be a big security issue.
                return false;
            }
        }

        return true;
    }

    public function get_content() {
        global $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // Fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;

        if (!isset($this->config)) {
            $this->config = new StdClass;
        }

        $this->config->text_all = file_rewrite_pluginfile_urls(@$this->config->text_all, 'pluginfile.php', 
                                                            $this->context->id, 'block_profilespecifichtml', 'content', null);
        $this->content->text = !empty($this->config->text_all) ? format_text($this->config->text_all, FORMAT_HTML, $filteropt) : '';

        if (empty($this->config->field1) && empty($this->config->field2)) {
            $this->content->footer = '';
            return($this->content);
        }

        if (!empty($this->config->field1)) {
            if (is_numeric($this->config->field1) && $this->config->field1 > 0) {
                // Custom field.
                $params = array('fieldid' => $this->config->field1, 'userid' => $USER->id);
                $uservalue = $DB->get_field('user_info_data', 'data', $params);
            } else {
                // Userfield by name in user's core profile.
                $stduserfield = $this->config->field1;
                $uservalue = $USER->$stduserfield;
            }
        }

        switch ($this->config->op1) {

            case '~=': {
                $res1 = preg_match("/{$this->config->value1}/", "{$uservalue}");
                break;
            }

            case '=': {
                $res1 = $this->config->value1 == $uservalue;
                break;
            }

            case '!=': {
                $res1 = $this->config->value1 != $uservalue;
                break;
            }

            case '>': {
                $res1 = $this->config->value1 > uservalue;
                break;
            }

            case '<': {
                $res1 = $this->config->value1 < uservalue;
                break;
            }

            case '>=': {
                $res1 = $this->config->value1 >= uservalue;
                break;
            }

            case '<=': {
                $res1 = $this->config->value1 <= uservalue;
                break;
            }
        }

        if ($this->config->op) {

            if (!empty($this->config->field2)) {
                if (is_numeric($this->config->field2) && $this->config->field2 > 0) {
                    $params = array('fieldid' => $this->config->field2, 'userid' => $USER->id);
                    $uservalue = $DB->get_field('user_info_data', 'data', $params);
                } else {
                    $stduserfield = $this->config->field2;
                    $uservalue = $USER->$stduserfield;
                }
            }

            switch ($this->config->op2) {
                case '~=': {
                    $res2 = preg_match("/{$this->config->value2}/", "{$uservalue}");
                    break;
                }

                case '=': {
                    $res2 = $this->config->value2 == $uservalue;
                    break;
                }

                case '>': {
                    $res2 = $this->config->value2 > uservalue;
                    break;
                }

                case '<': {
                    $res2 = $this->config->value2 < uservalue;
                    break;
                }

                case '>=': {
                    $res2 = $this->config->value2 >= uservalue;
                    break;
                }

                case '<=': {
                    $res2 = $this->config->value2 <= uservalue;
                    break;
                }
            }

            switch ($this->config->op) {
                case '&&': {
                    $finalexpr = $res = $res1 && $res2;
                    break;
                }

                case '||': {
                    $finalexpr = $res = $res1 || $res2;
                    break;
                }

                case '^': {
                    $finalexpr = $res = $res1 ^ $res2;
                    break;
                }
            }

        } else {
            $res = @$res1;
        }

        if (@$res) {
            $this->config->text_match = file_rewrite_pluginfile_urls($this->config->text_match, 'pluginfile.php', 
                                                                $this->context->id, 'block_profilespecifichtml', 'match', null);
            $this->content->text .= format_text(@$this->config->text_match, FORMAT_HTML, $filteropt);
        } else {
            $this->config->text_nomatch = file_rewrite_pluginfile_urls($this->config->text_nomatch, 'pluginfile.php', 
                                                                $this->context->id, 'block_profilespecifichtml', 'nomatch', null);
            $this->content->text .= format_text(@$this->config->text_nomatch, FORMAT_HTML, $filteropt);
        }
        $this->content->footer = '';

        unset($filteropt); // Memory footprint.

        return $this->content;
    }

    /**
     * Serialize and store config data
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links.
        $config->text_all = file_save_draft_area_files($data->text_all['itemid'], $this->context->id,
                                                       'block_profilespecifichtml', 'content', 0,
                                                       array('subdirs'=>true), $data->text_all['text']);
        $config->format_all = (!isset($data->text_all['format'])) ? FORMAT_MOODLE : $data->text_all['format'];

        $config->text_match = file_save_draft_area_files($data->text_match['itemid'], $this->context->id,
                                                         'block_profilespecifichtml', 'match', 0, array('subdirs'=>true),
                                                         $data->text_match['text']);
        $config->format_match = (!isset($data->text_matched['format'])) ? FORMAT_MOODLE : $data->text_matched['format'];

        $config->text_nomatch = file_save_draft_area_files($data->text_nomatch['itemid'], $this->context->id,
                                                           'block_profilespecifichtml', 'nomatch', 0,
                                                           array('subdirs'=>true), $data->text_nomatch['text']);
        $config->format_nomatch = (!isset($data->text_nomatched['format'])) ? FORMAT_MOODLE : $data->text_nomatched['format'];

        parent::instance_config_save($config, $nolongerused);
    }

    /*
     * Hide the title bar when none set.
     */
    public function hide_header() {
        return empty($this->config->title);
    }
}

