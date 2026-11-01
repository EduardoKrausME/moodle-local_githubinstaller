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

namespace local_githubinstaller\form;

/**
 * Form for preparing a GitHub repository archive.
 *
 * @package    local_githubinstaller
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class repository_form extends \moodleform {
    /** Form definition. */
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('text', 'repository', get_string('repository', 'local_githubinstaller'),
            ['size' => 75, 'placeholder' => 'https://github.com/EduardoKrausME/moodle-mod_supervideo']);
        $mform->setType('repository', PARAM_RAW_TRIMMED);
        $mform->addRule('repository', null, 'required', null, 'client');

        $mform->addElement('select', 'reftype', get_string('reftype', 'local_githubinstaller'), [
            'branch' => get_string('branch', 'local_githubinstaller'),
            'tag' => get_string('tag', 'local_githubinstaller'),
            'commit' => get_string('commit', 'local_githubinstaller'),
        ]);
        $mform->setType('reftype', PARAM_ALPHA);
        $mform->setDefault('reftype', 'branch');

        $mform->addElement('text', 'ref', get_string('ref', 'local_githubinstaller'), [
            'size' => 45,
            'placeholder' => get_string('refplaceholder', 'local_githubinstaller'),
        ]);
        $mform->setType('ref', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('ref', 'ref', 'local_githubinstaller');

        $this->add_action_buttons(false, get_string('prepare', 'local_githubinstaller'));
    }

    /**
     * Run strictly local validation before any network request.
     *
     * @param array $data Submitted values.
     * @param array $files Uploaded files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        try {
            \local_githubinstaller\manager::parse_repository($data['repository'] ?? '');
        } catch (\moodle_exception $ex) {
            $errors['repository'] = get_string('invalidrepository', 'local_githubinstaller');
        }
        try {
            if (($data['ref'] ?? '') !== '' || ($data['reftype'] ?? '') !== 'branch') {
                \local_githubinstaller\manager::validate_ref($data['ref'] ?? '', $data['reftype'] ?? '');
            }
        } catch (\moodle_exception $ex) {
            $errors['ref'] = get_string('invalidref', 'local_githubinstaller');
        }
        return $errors;
    }
}
