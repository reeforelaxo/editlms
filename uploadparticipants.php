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
 * Displays the form and processes the form submission.
 *
 * @package    local_trainingrequest
 * @copyright  2018 Eoin Campbell
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
// require_once __DIR__ . '/lib/UserModel.php';

$path = optional_param('path', '', PARAM_PATH); // $nameofarray = optional_param_array('nameofarray', null, PARAM_INT);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

// Include our function library.
$pluginname = 'local_trainingrequest';
require_once($CFG->dirroot.'/local/trainingrequest/locallib.php');

// Globals.
global $CFG, $OUTPUT, $USER, $SITE, $PAGE;

// Ensure only administrators have access.
$homeurl = new moodle_url('/');
require_login();
if (!is_siteadmin()) {
    redirect($homeurl, get_string('adminonly', 'badges', 5));
}

// URL Parameters.
// There are none.

// Include form.
require_once(dirname(__FILE__).'/'.$pluginname.'_form.php');

// Heading ==========================================================.

$title = get_string('pluginname', ''.$pluginname);
$heading = get_string('heading', ''.$pluginname);
$url = new moodle_url('/local/trainingrequest/uploadparticipants.php');
// if ($CFG->branch >= 25) { // Moodle 2.5+.
//     $context = context_system::instance();
// } else {
//     $context = get_system_context();
// }
admin_externalpage_setup('uploadtr', '', $pageparams);
// admin_externalpage_setup(''.$pluginname); // Sets the navbar & expands navmenu.

// Set up the form.
$form = new local_trainingrequest_form(null, array('fromdefault' => ''));
if ($form->is_cancelled()) {
    redirect($homeurl);
}

echo $OUTPUT->header();

// Display or process the form.
if ($data = $form->get_data()) {
    // Process the CSV file.
    $importid = csv_import_reader::get_new_iid($pluginname);
    $cir = new csv_import_reader($importid, $pluginname);
    $content = $form->get_file_content('csvfile');
    $readcount = $cir->load_csv_content($content, $data->encoding, $data->delimiter_name);
    unset($content);
    if ($readcount === false) {
        throw new \moodle_exception(get_string('csvfileerror', 'tool_uploadcourse', $url . " " . $cir->get_error()));
    } else if ($readcount == 0) {
        throw new \moodle_exception(get_string('csvemptyfile', 'error', $url . " " . $cir->get_error()));
    }

    // We've got a live file with some entries, so process it.
    $processor = new local_trainingrequest_pro($cir);
    echo $OUTPUT->heading(get_string('results', 'local_trainingrequest'));
    $processor->execute(new local_trainingrequest_track(local_trainingrequest_track::OUTPUT_HTML));
    echo $OUTPUT->continue_button($url);

} else {
    // Display the form.
    echo $OUTPUT->heading($heading);

    $form->display();
    echo $OUTPUT->footer();
    die();
}
    // Footer.
echo $OUTPUT->footer();
  
