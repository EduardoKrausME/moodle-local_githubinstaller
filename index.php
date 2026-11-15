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

admin_externalpage_setup('local_githubinstaller');
require_capability('moodle/site:config', context_system::instance());

$action = optional_param('action', '', PARAM_ALPHA);
$storage = optional_param('storage', '', PARAM_FILE);
$PAGE->set_url(new moodle_url('/local/githubinstaller/index.php',
    $action === 'review' ? ['action' => $action, 'storage' => $storage] : []));

if (!empty($CFG->disableupdateautodeploy)) {
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_githubinstaller/overview', [
        'disabled' => true,
    ]);
    echo $OUTPUT->footer();
    exit;
}

if ($action === 'review') {
    require_sesskey();
    $package = \local_githubinstaller\manager::inspect_storage($storage);

    // Moodle's native ZIP installer performs the subsequent validation and approval.
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_githubinstaller/review', [
        'component' => $package['component'],
        'version' => $package['version'],
        'currentversion' => $package['currentversion'],
        'hascurrentversion' => $package['currentversion'] !== '',
        'installed' => $package['installed'],
        'sameversion' => $package['currentversion'] !== '' &&
            (float)$package['currentversion'] === (float)$package['version'],
        'nativeaction' => (new moodle_url('/admin/tool/installaddon/index.php'))->out(false),
        'storage' => $package['storage'],
        'sesskey' => sesskey(),
        'cancelurl' => (new moodle_url('/local/githubinstaller/index.php'))->out(false),
    ]);
    echo $OUTPUT->footer();
    exit;
}

$repository = '';
$ref = '';
$reftype = 'branch';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_sesskey();
    $repository = optional_param('repository', '', PARAM_RAW_TRIMMED);
    $ref = optional_param('ref', '', PARAM_RAW_TRIMMED);
    $reftype = optional_param('reftype', 'branch', PARAM_ALPHA);

    try {
        $package = \local_githubinstaller\manager::prepare($repository, $ref, $reftype);
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
echo $OUTPUT->render_from_template('local_githubinstaller/overview', [
    'actionurl' => (new moodle_url('/local/githubinstaller/index.php'))->out(false),
    'sesskey' => sesskey(),
    'repository' => $repository,
    'ref' => $ref,
    'branchselected' => $reftype === 'branch',
    'tagselected' => $reftype === 'tag',
    'commitselected' => $reftype === 'commit',
    'error' => $error,
]);
echo $OUTPUT->footer();
