# GitHub Plugin Installer for Moodle

The local_githubinstaller plugin downloads a public GitHub repository archive and hands it to Moodle's built-in ZIP plugin installer. The administrator sees Moodle's own package validation and confirmation buttons before any plugin files are replaced.

It does not run Git commands, execute downloaded PHP during inspection, manage database upgrades, or bypass Moodle's installation checks.

## Requirements

- Moodle 4.4 or newer, including Moodle 5.3.
- PHP with cURL and ZipArchive enabled.
- Outbound HTTPS access to api.github.com (to determine the default branch) and codeload.github.com (to download source archives).
- Access to Site administration as a site administrator.
- File-system permissions allowing Moodle's native plugin installer to deploy add-ons.
- Automatic plugin deployment must not be disabled by $CFG->disableupdateautodeploy.

This first version supports **public GitHub repositories**. Private repositories and GitHub access tokens are not supported.

## Installation

Place these files in local/githubinstaller/ and visit the Site administration area to finish installation.

The page is registered under Site administration > Plugins > Local plugins > GitHub plugin installer, with exact menu labels depending on language. It is also available at:

    /local/githubinstaller/index.php

## Installing or updating a plugin

1. Enter a repository URL such as https://github.com/EduardoKrausME/moodle-mod_supervideo.
2. Keep the branch field blank to download the default branch, or select a branch, tag, or full commit SHA.
3. Click Download and inspect.
4. Review the detected Moodle component, new version number and the installed version, if any.
5. Continue to Moodle's official ZIP installer. Review the validation report, then choose to confirm or cancel.
6. Moodle deploys the plugin files and redirects to /admin/index.php to apply the database upgrade after confirmation.

The process relies on tool_installaddon_installer::make_installfromzip_storage() and the existing installzipcomponent/installzipstorage flow in admin/tool/installaddon/index.php. It therefore uses core_plugin_manager::install_plugins() and upgrade_install_plugins() without duplicating the core installation logic.

## Supported GitHub URLs and references

Only a canonical HTTPS repository URL is accepted, optionally ending in .git:

    https://github.com/OWNER/REPOSITORY

Use separate reference fields for the branch, tag or 40-character commit SHA. URLs to GitHub tree pages and arbitrary HTTP/HTTPS download endpoints are deliberately rejected.

When the reference is blank, the plugin queries GitHub's public REST API for the default branch, then downloads the corresponding archive from codeload.github.com.

The ZIP must contain one top-level directory with a version.php at its root, declaring $plugin->component and $plugin->version as literal values. Monorepositories containing several plugin folders are not supported by this first release.

## Safety and update behavior

- Requires Moodle administrator privileges and Moodle's sesskey checks.
- Restricts sources to public GitHub repositories.
- Refuses standard Moodle plugins, attempts to self-update, invalid plugin components, missing metadata and version downgrades.
- Accepts archives up to 50 MB compressed and 200 MB unpacked, with at most 10,000 entries.
- Checks the ZIP structure and metadata before starting native validation.
- Warns when the archive and installed plugin have the same version number; same-version redeployment still requires approval.
- Does not automatically install or upgrade dependencies; normal Moodle upgrade checks apply.
- Does not create privileged server-side jobs or grant PHP new filesystem permissions.

**Important:** Moodle's native ZIP installer replaces the existing plugin code folder for an update. Back up the site, plugin customisations, and database before replacing installed plugins. A deployment failure can require restoration. Only install code from trusted maintainers.

Archives are staged in Moodle's native tool_installaddon temporary storage and remain subject to the normal temporary file clean-up.

## Tests

The tests/manager_test.php PHPUnit tests cover repository URL and Git ref validation without network access or installation side effects. Run tests in a configured Moodle PHPUnit environment.

## License

GNU General Public License version 3 or later. Copyright (c) 2026 Eduardo Kraus.
