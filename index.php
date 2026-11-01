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
 * index.php
 *
 * @package   local_githubinstaller
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

admin_externalpage_setup('local_githubinstaller');
require_capability('moodle/site:config', context_system::instance());

$action = optional_param('action', '', PARAM_ALPHA);
$storage = optional_param('storage', '', PARAM_FILE);
$PAGE->set_url(new moodle_url('/local/githubinstaller/index.php',
    $action === 'review' ? ['action' => $action, 'storage' => $storage] : []));

if (!empty($CFG->disableupdateautodeploy)) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('autodeploydisabled', 'local_githubinstaller'), 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
}

if ($action === 'review') {
    require_sesskey();
    $package = \local_githubinstaller\manager::inspect_storage($storage);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('reviewheading', 'local_githubinstaller'));
    echo html_writer::tag('p', get_string('reviewintro', 'local_githubinstaller'));
    $table = new html_table();
    $table->data = [
        [get_string('component', 'local_githubinstaller'), s($package['component'])],
        [get_string('newversion', 'local_githubinstaller'), s($package['version'])],
        [get_string('currentversion', 'local_githubinstaller'),
            $package['currentversion'] !== '' ? s($package['currentversion']) : get_string('notinstalled', 'local_githubinstaller')],
        [get_string('operation', 'local_githubinstaller'),
            get_string($package['installed'] ? 'update' : 'install', 'local_githubinstaller')],
    ];
    echo html_writer::table($table);

    if ($package['currentversion'] !== '' &&
            (float)$package['currentversion'] === (float)$package['version']) {
        echo $OUTPUT->notification(get_string('sameversionwarning', 'local_githubinstaller'), 'notifywarning');
    }

    echo $OUTPUT->notification(get_string('reviewwarning', 'local_githubinstaller'), 'notifymessage');

    // This is the same entry point used by Moodle's official ZIP installer.
    // The native tool validates the archive and displays its own approval buttons.
    $nativeurl = new moodle_url('/admin/tool/installaddon/index.php', [
        'installzipcomponent' => $package['component'],
        'installzipstorage' => $package['storage'],
        'sesskey' => sesskey(),
    ]);
    echo $OUTPUT->single_button($nativeurl, get_string('continuevalidation', 'local_githubinstaller'), 'get');
    echo html_writer::link(new moodle_url('/local/githubinstaller/index.php'),
        get_string('cancel', 'moodle'), ['class' => 'btn btn-secondary']);
    echo $OUTPUT->footer();
    exit;
}

$repositoryform = new \local_githubinstaller\form\repository_form();
$error = '';
if ($data = $repositoryform->get_data()) {
    try {
        $package = \local_githubinstaller\manager::prepare($data->repository, $data->ref, $data->reftype);
        redirect(new moodle_url('/local/githubinstaller/index.php', [
            'action' => 'review',
            'storage' => $package['storage'],
            'sesskey' => sesskey(),
        ]));
    } catch (\moodle_exception $ex) {
        $error = $ex->getMessage();
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_githubinstaller'));
echo html_writer::tag('p', get_string('introduction', 'local_githubinstaller'));
if ($error !== '') {
    echo $OUTPUT->notification(s($error), 'notifyproblem');
}
$repositoryform->display();
echo $OUTPUT->footer();
