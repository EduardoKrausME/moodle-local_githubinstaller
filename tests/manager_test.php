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

namespace local_githubinstaller;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for source validation, without performing network requests.
 *
 * @covers \local_githubinstaller\manager
 * @package    local_githubinstaller
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class manager_test extends \advanced_testcase {
    /**
     * Method test_parse_repository.
     *
     * @return void Return value.
     */
    public function test_parse_repository(): void {
        $actual = manager::parse_repository('https://github.com/EduardoKrausME/moodle-mod_supervideo');
        $this->assertSame([
            'owner' => 'EduardoKrausME',
            'repository' => 'moodle-mod_supervideo',
        ], $actual);

        $git = manager::parse_repository('https://github.com/EduardoKrausME/moodle-mod_supervideo.git');
        $this->assertSame($actual, $git);
    }

    /**
     * Method test_rejects_untrusted_repository_urls.
     *
     * @return void Return value.
     */
    public function test_rejects_untrusted_repository_urls(): void {
        $urls = [
            'http://github.com/example/plugin',
            'https://github.com.evil.test/example/plugin',
            'https://github.com/example/plugin/tree/master',
            'https://github.com/example/plugin?ref=main',
            'https://user:secret@github.com/example/plugin',
            'https://github.com:8443/example/plugin',
        ];
        foreach ($urls as $url) {
            try {
                manager::parse_repository($url);
                $this->fail('Accepted invalid URL: ' . $url);
            } catch (\moodle_exception $ex) {
                $this->assertSame('invalidrepository', $ex->errorcode);
            }
        }
    }

    /**
     * Method test_validate_ref.
     *
     * @return void Return value.
     */
    public function test_validate_ref(): void {
        $this->assertSame('feature/moodle53', manager::validate_ref('feature/moodle53', 'branch'));
        $this->assertSame('v1.2.3', manager::validate_ref('v1.2.3', 'tag'));
        $sha = str_repeat('a', 40);
        $this->assertSame($sha, manager::validate_ref($sha, 'commit'));
    }

    /**
     * Method test_rejects_unsafe_refs.
     *
     * @return void Return value.
     */
    public function test_rejects_unsafe_refs(): void {
        foreach (['../main', 'main//test', '/main', 'main/', 'main.lock', 'main?token=abc'] as $ref) {
            try {
                manager::validate_ref($ref, 'branch');
                $this->fail('Accepted invalid ref: ' . $ref);
            } catch (\moodle_exception $ex) {
                $this->assertSame('invalidref', $ex->errorcode);
            }
        }
    }
}
