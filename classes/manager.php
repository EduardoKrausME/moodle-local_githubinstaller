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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

namespace local_githubinstaller;

/**
 * Prepares GitHub source archives for Moodle's native add-on installer.
 *
 * No downloaded PHP is executed here. Deployment and database upgrades are
 * delegated entirely to tool_installaddon and the Moodle core upgrade flow.
 *
 * @package    local_githubinstaller
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class manager {
    /** Maximum compressed archive size, in bytes. */
    private const MAXZIPBYTES = 52428800;

    /** Maximum unpacked archive size, in bytes. */
    private const MAXUNPACKEDBYTES = 209715200;

    /** Maximum number of ZIP entries. */
    private const MAXENTRIES = 10000;

    /**
     * Accept only a canonical, public HTTPS github.com repository URL.
     *
     * @param string $url Repository URL.
     * @return array{owner:string, repository:string}
     */
    public static function parse_repository(string $url): array {
        $parts = parse_url(trim($url));
        if ($parts === false || ($parts['scheme'] ?? '') !== 'https' ||
                strtolower($parts['host'] ?? '') !== 'github.com' ||
                isset($parts['user']) || isset($parts['pass']) ||
                isset($parts['port']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new \moodle_exception('invalidrepository', 'local_githubinstaller');
        }

        $path = trim($parts['path'] ?? '', '/');
        if (!preg_match('~^([A-Za-z0-9](?:[A-Za-z0-9-]{0,38}))/([A-Za-z0-9._-]+?)(?:\.git)?$~D', $path, $matches)) {
            throw new \moodle_exception('invalidrepository', 'local_githubinstaller');
        }
        $repository = preg_replace('/\.git$/i', '', $matches[2]);
        if ($repository === '' || $repository === '.' || $repository === '..') {
            throw new \moodle_exception('invalidrepository', 'local_githubinstaller');
        }
        return ['owner' => $matches[1], 'repository' => $repository];
    }

    /**
     * Validate a Git ref without allowing a URL, traversal or ref ambiguity.
     *
     * @param string $ref Branch, tag or commit.
     * @param string $type branch, tag or commit.
     * @return string Validated reference.
     */
    public static function validate_ref(string $ref, string $type): string {
        $ref = trim($ref);
        if (!in_array($type, ['branch', 'tag', 'commit'], true)) {
            throw new \moodle_exception('invalidref', 'local_githubinstaller');
        }
        if ($type === 'commit') {
            if (!preg_match('/^[a-fA-F0-9]{40}$/D', $ref)) {
                throw new \moodle_exception('invalidref', 'local_githubinstaller');
            }
            return $ref;
        }
        if ($ref === '' || strlen($ref) > 180 ||
                !preg_match('~^[A-Za-z0-9_./-]+$~D', $ref) ||
                str_contains($ref, '..') || str_contains($ref, '//') ||
                str_starts_with($ref, '/') || str_ends_with($ref, '/') ||
                str_ends_with($ref, '.lock') || str_ends_with($ref, '.')) {
            throw new \moodle_exception('invalidref', 'local_githubinstaller');
        }
        return $ref;
    }

    /**
     * Look up the default branch through GitHub's public API.
     *
     * @param string $owner GitHub owner.
     * @param string $repository GitHub repository.
     * @return string Validated default branch.
     */
    private static function default_branch(string $owner, string $repository): string {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $curl->setHeader(['Accept: application/vnd.github+json', 'User-Agent: Moodle-GitHubInstaller']);
        $url = 'https://api.github.com/repos/' . rawurlencode($owner) . '/' . rawurlencode($repository);
        $response = $curl->get($url, null, [
            'CURLOPT_TIMEOUT' => 25,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_FOLLOWLOCATION' => false,
        ]);
        $info = $curl->get_info();
        if ($curl->get_errno() || (int)($info['http_code'] ?? 0) !== 200) {
            throw new \moodle_exception('githubmetadataerror', 'local_githubinstaller');
        }
        $details = json_decode($response);
        if (!is_object($details) || empty($details->default_branch) ||
                !empty($details->private)) {
            throw new \moodle_exception('githubmetadataerror', 'local_githubinstaller');
        }
        return self::validate_ref($details->default_branch, 'branch');
    }

    /**
     * Download a public GitHub archive to the storage expected by tool_installaddon.
     *
     * @param string $repositoryurl Canonical GitHub repository URL.
     * @param string $ref Selected branch, tag or commit, empty for the default branch.
     * @param string $type Reference type.
     * @return array{storage:string, component:string, version:string, ref:string}
     */
    public static function prepare(string $repositoryurl, string $ref, string $type): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        if (!empty($CFG->disableupdateautodeploy)) {
            throw new \moodle_exception('autodeploydisabled', 'local_githubinstaller');
        }
        if (!class_exists(\ZipArchive::class)) {
            throw new \moodle_exception('ziprequired', 'local_githubinstaller');
        }

        $repository = self::parse_repository($repositoryurl);
        if (trim($ref) === '') {
            if ($type !== 'branch') {
                throw new \moodle_exception('invalidref', 'local_githubinstaller');
            }
            $ref = self::default_branch($repository['owner'], $repository['repository']);
        } else {
            $ref = self::validate_ref($ref, $type);
        }

        // This URL is constructed solely from checked GitHub path components.
        $owner = rawurlencode($repository['owner']);
        $name = rawurlencode($repository['repository']);
        if ($type === 'commit') {
            $suffix = $ref;
        } else {
            $suffix = 'refs/' . ($type === 'tag' ? 'tags/' : 'heads/') .
                implode('/', array_map('rawurlencode', explode('/', $ref)));
        }
        $url = 'https://codeload.github.com/' . $owner . '/' . $name . '/zip/' . $suffix;

        $installer = \tool_installaddon_installer::instance();
        $storage = $installer->make_installfromzip_storage();
        $zipfilepath = $storage . '/plugin.zip';
        try {
            $response = download_file_content(
                $url,
                ['User-Agent' => 'Moodle-GitHubInstaller'],
                null,
                true,
                120,
                15,
                false,
                $zipfilepath
            );
            if (empty($response) || (int)$response->status !== 200 ||
                    !is_file($zipfilepath) || filesize($zipfilepath) === 0) {
                throw new \moodle_exception('downloadfailed', 'local_githubinstaller');
            }
            $package = self::inspect_archive($zipfilepath);
            $package['storage'] = basename($storage);
            $package['ref'] = $ref;
            return $package;
        } catch (\Throwable $ex) {
            if (is_file($zipfilepath)) {
                @unlink($zipfilepath);
            }
            @rmdir($storage);
            throw $ex;
        }
    }

    /**
     * Inspect an existing staged ZIP and re-check its metadata at review time.
     *
     * @param string $storage Native installer storage directory name.
     * @return array Package details.
     */
    public static function inspect_storage(string $storage): array {
        if ($storage === '' || $storage !== clean_param($storage, PARAM_FILE) ||
                str_contains($storage, '/') || str_contains($storage, '\\') ||
                $storage === '.' || $storage === '..') {
            throw new \moodle_exception('invalidstorage', 'local_githubinstaller');
        }
        $base = make_temp_directory('tool_installaddon');
        $path = $base . '/' . $storage . '/plugin.zip';
        if (!is_file($path)) {
            throw new \moodle_exception('invalidstorage', 'local_githubinstaller');
        }
        $package = self::inspect_archive($path);
        $package['storage'] = $storage;
        return $package;
    }

    /**
     * Inspect metadata without including or executing the downloaded version.php.
     *
     * @param string $file Absolute path to a ZIP file.
     * @return array Package details.
     */
    private static function inspect_archive(string $file): array {
        if (!is_file($file) || filesize($file) > self::MAXZIPBYTES) {
            throw new \moodle_exception('archivesize', 'local_githubinstaller');
        }

        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \moodle_exception('invalidarchive', 'local_githubinstaller');
        }

        try {
            if ($zip->numFiles < 1 || $zip->numFiles > self::MAXENTRIES) {
                throw new \moodle_exception('invalidarchive', 'local_githubinstaller');
            }
            $root = null;
            $versionfile = null;
            $unpackedbytes = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->statIndex($i);
                if (!$entry) {
                    throw new \moodle_exception('invalidarchive', 'local_githubinstaller');
                }
                $path = $entry['name'];
                if (str_contains($path, '\\') || str_contains($path, "\0") ||
                        str_starts_with($path, '/') || preg_match('~(^|/)\.\.?(/|$)~', $path)) {
                    throw new \moodle_exception('invalidarchive', 'local_githubinstaller');
                }
                $sections = explode('/', $path);
                if ($root === null) {
                    $root = $sections[0];
                }
                if ($sections[0] !== $root || $root === '') {
                    throw new \moodle_exception('invalidarchive', 'local_githubinstaller');
                }
                $unpackedbytes += (int)$entry['size'];
                if ($unpackedbytes > self::MAXUNPACKEDBYTES) {
                    throw new \moodle_exception('archivesize', 'local_githubinstaller');
                }
                if ($path === $root . '/version.php') {
                    $versionfile = $path;
                }
            }

            if (!$versionfile) {
                throw new \moodle_exception('missingversion', 'local_githubinstaller');
            }
            $versionphp = $zip->getFromName($versionfile);
            if (!is_string($versionphp) || strlen($versionphp) > 1048576 ||
                    !preg_match('/^\s*\$plugin->component\s*=\s*([\'"])([a-z][a-z0-9_]+)\1\s*;/m',
                        $versionphp, $match) ||
                    !preg_match('/^\s*\$plugin->version\s*=\s*([0-9]+(?:\.[0-9]+)?)\s*;/m',
                        $versionphp, $versions)) {
                throw new \moodle_exception('missingversion', 'local_githubinstaller');
            }
        } finally {
            $zip->close();
        }

        $component = $match[2];
        [$type, $name] = \core_component::normalize_component($component);
        $types = \core_component::get_plugin_types();
        if ($type === 'core' || !isset($types[$type]) ||
                $component !== $type . '_' . $name ||
                !\core_component::is_valid_plugin_name($type, $name)) {
            throw new \moodle_exception('invalidcomponent', 'local_githubinstaller');
        }
        if ($component === 'local_githubinstaller') {
            throw new \moodle_exception('selfupdate', 'local_githubinstaller');
        }

        $pluginman = \core_plugin_manager::instance();
        $installed = $pluginman->get_plugin_info($component);
        if ($installed && $installed->is_standard()) {
            throw new \moodle_exception('standardplugin', 'local_githubinstaller');
        }

        $currentversion = '';
        if ($installed) {
            $currentversion = (string)($installed->versiondb ?? $installed->versiondisk ?? '');
            if ($currentversion !== '' && (float)$versions[1] < (float)$currentversion) {
                throw new \moodle_exception('downgradeblocked', 'local_githubinstaller');
            }
        }

        return [
            'component' => $component,
            'version' => $versions[1],
            'currentversion' => $currentversion,
            'installed' => $installed !== null,
            'type' => $type,
        ];
    }
}
