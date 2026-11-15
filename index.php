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
    echo $OUTPUT->render_from_template('local_githubinstaller/overview', [
        'error' => get_string('autodeploydisabled', 'local_githubinstaller'),
    ]);
    echo $OUTPUT->footer();
    exit;
}

if ($action === 'review') {
    require_sesskey();
    $package = \local_githubinstaller\manager::inspect_storage($storage);

    $rows = [
        [
            'label' => get_string('component', 'local_githubinstaller'),
            'value' => $package['component'],
        ],
        [
            'label' => get_string('newversion', 'local_githubinstaller'),
            'value' => $package['version'],
        ],
        [
            'label' => get_string('currentversion', 'local_githubinstaller'),
            'value' => $package['currentversion'] !== ''
                ? $package['currentversion']
                : get_string('notinstalled', 'local_githubinstaller'),
        ],
        [
            'label' => get_string('operation', 'local_githubinstaller'),
            'value' => get_string($package['installed'] ? 'update' : 'install', 'local_githubinstaller'),
        ],
    ];

    // The native installer validates the archive and presents its own approval buttons.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('reviewheading', 'local_githubinstaller'));
    echo $OUTPUT->render_from_template('local_githubinstaller/review', [
        'intro' => get_string('reviewintro', 'local_githubinstaller'),
        'rows' => $rows,
        'sameversion' => $package['currentversion'] !== '' &&
            (float)$package['currentversion'] === (float)$package['version'],
        'sameversionwarning' => get_string('sameversionwarning', 'local_githubinstaller'),
        'warning' => get_string('reviewwarning', 'local_githubinstaller'),
        'nativeaction' => (new moodle_url('/admin/tool/installaddon/index.php'))->out(false),
        'component' => $package['component'],
        'storage' => $package['storage'],
        'sesskey' => sesskey(),
        'continuevalidation' => get_string('continuevalidation', 'local_githubinstaller'),
        'cancelurl' => (new moodle_url('/local/githubinstaller/index.php'))->out(false),
        'cancel' => get_string('cancel', 'moodle'),
    ]);
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
echo $OUTPUT->render_from_template('local_githubinstaller/overview', [
    'introduction' => get_string('introduction', 'local_githubinstaller'),
    'error' => $error,
]);
$repositoryform->display();
echo $OUTPUT->footer();
