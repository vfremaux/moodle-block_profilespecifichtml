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
 * Form for editing HTML block instances.
 *
 * @package   block_profilespecifichtml
 * @copyright 2012 Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version   Moodle 2.x
 */

function block_profilespecifichtml_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload) {
    global $SCRIPT;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    require_course_login($course);

    if (!in_array($filearea, array('content', 'match', 'nomatch'))) {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'block_profilespecifichtml', $filearea, 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    if ($parentcontext = get_context_instance_by_id($birecord_or_cm->parentcontextid)) {
        if ($parentcontext->contextlevel == CONTEXT_USER) {
            /*
             * force download on all personal pages including /my/
             * because we do not have reliable way to find out from where this is used
             */
            $forcedownload = true;
        }
    } else {
        // Weird, there should be parent context, better force dowload then.
        $forcedownload = true;
    }

    session_get_instance()->write_close();
    send_stored_file($file, 60 * 60, 0, $forcedownload);
}

/**
 * Perform global search replace such as when migrating site to new URL.
 * @param  $search
 * @param  $replace
 * @return void
 */
function block_profilespecifichtml_global_db_replace($search, $replace) {
    global $DB;

    $instances = $DB->get_recordset('block_instances', array('blockname' => 'profilespecifichtml'));
    foreach ($instances as $instance) {
        // TODO: intentionally hardcoded until MDL-26800 is fixed.
        $config = unserialize(base64_decode($instance->configdata));
        $commit = false;
        if (isset($config->text_all) and is_string($config->text_all)) {
            $commit = true;
            $config->text_all = str_replace($search, $replace, $config->text_all);
        }

        if (isset($config->text_match) and is_string($config->text_match)) {
            $commit = true;
            $config->text_match = str_replace($search, $replace, $config->text_match);
        }

        if (isset($config->text_nomatch) and is_string($config->text_nomatch)) {
            $commit = true;
            $config->text_nomatch = str_replace($search, $replace, $config->text_nomatch);
        }

        if ($commit) {
            $DB->set_field('block_instances', 'configdata', base64_encode(serialize($config)), array('id' => $instance->id));
        }
    }
    $instances->close();
}