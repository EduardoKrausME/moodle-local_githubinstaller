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
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

$string['archivesize'] = 'The archive exceeds the safety limits: 50 MB compressed or 200 MB uncompressed.';
$string['autodeploydisabled'] = 'Automatic deployment of plugins is disabled by the Moodle disableupdateautodeploy setting.';
$string['branch'] = 'Branch';
$string['commit'] = 'Commit';
$string['component'] = 'Component';
$string['continuevalidation'] = 'Continue to Moodle validation and approval';
$string['currentversion'] = 'Installed version';
$string['downgradeblocked'] = 'The GitHub version is older than the installed version. Downgrades are not supported.';
$string['downloadfailed'] = 'The GitHub ZIP download failed. Check the reference and the server network connection.';
$string['githubmetadataerror'] = 'Could not obtain the default branch from GitHub. Check that the repository is public and GitHub’s API is available.';
$string['install'] = 'New installation';
$string['introduction'] = 'Enter a public GitHub repository URL to download a plugin. Deployment or upgrade occurs only after validation and approval in Moodle’s native plugin installer.';
$string['invalidarchive'] = 'The downloaded file is not a GitHub repository archive with the expected ZIP layout.';
$string['invalidcomponent'] = 'The component declared by version.php is not a valid Moodle plugin component.';
$string['invalidref'] = 'The branch, tag or commit SHA is invalid.';
$string['invalidrepository'] = 'Enter a valid HTTPS repository URL in the form https://github.com/owner/repository. File URLs, branch pages and alternate hosts are not accepted.';
$string['invalidstorage'] = 'The staged native installer file is unavailable or invalid. Download the plugin again.';
$string['missingversion'] = 'No valid root-level version.php with directly declared component and version was found.';
$string['newversion'] = 'ZIP version';
$string['notinstalled'] = 'Not installed';
$string['operation'] = 'Operation';
$string['pluginname'] = 'GitHub plugin installer';
$string['prepare'] = 'Download and inspect';
$string['privacy:metadata'] = 'This plugin does not maintain its own tables containing personal data.';
$string['ref'] = 'Branch, tag or commit SHA';
$string['ref_help'] = 'Leave this empty to use the repository default branch. Select a tag or commit type to use a specific reference. A commit requires the full 40-character SHA.';
$string['refplaceholder'] = 'Blank = default branch';
$string['reftype'] = 'Source reference type';
$string['repository'] = 'GitHub repository URL';
$string['reviewheading'] = 'Review GitHub package';
$string['reviewintro'] = 'The archive was downloaded and its identity inspected. Check the details before continuing to Moodle’s full validation.';
$string['reviewwarning'] = 'The next page is the native Moodle validation screen. No plugin files have been installed yet. Only deploy trusted code, and back up installed plugins before upgrading them.';
$string['sameversionwarning'] = 'The version number has not changed. Code may have changed, but Moodle does not run a database upgrade simply because files of the same version were replaced.';
$string['selfupdate'] = 'For safety, this plugin cannot update its own code during a request. Use Moodle’s native ZIP installer or server deployment.';
$string['standardplugin'] = 'Replacing plugins shipped with Moodle is not supported.';
$string['tag'] = 'Tag';
$string['update'] = 'Update';
$string['ziprequired'] = 'The PHP ZipArchive extension is needed to inspect downloaded archives.';
