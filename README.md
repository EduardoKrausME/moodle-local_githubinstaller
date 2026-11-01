# GitHub Plugin Installer

GitHub Plugin Installer lets Moodle site administrators select a public GitHub repository, inspect the plugin it contains and continue through Moodle's own plugin validation and confirmation process.

It is useful when a plugin is maintained on GitHub but is not distributed through the official plugins directory, or when an administrator needs to review a specific published version before updating an existing plugin.

## What administrators can do

- **Use a public GitHub repository:** provide the repository's standard URL instead of first downloading its archive manually.
- **Choose a version:** use the repository's default branch or specify a branch, tag or full commit identifier.
- **Review the package:** see the detected Moodle plugin component, its version and whether it would be added or updated.
- **Keep Moodle's validation:** continue through the normal Moodle plugin checks and confirmation screens.
- **Control updates:** receive a warning when the downloaded code has the same version as the installed plugin, and prevent accidental version downgrades.

The plugin simplifies how an administrator obtains and reviews the package. It does not bypass Moodle's approval process.

## A typical administrator workflow

An administrator wants to evaluate a new release of a plugin maintained in a public GitHub repository. After selecting the repository and version, GitHub Plugin Installer identifies the package and shows its component and version information.

The administrator reviews those details and decides whether to continue. Moodle then performs its normal validation and presents the final confirmation before replacing or adding plugin files.

The same workflow can be used for a new plugin or an update.

## Supported repositories

This release supports public GitHub repositories containing a single Moodle plugin. The repository must expose a valid Moodle plugin package.

Private repositories, GitHub access tokens and repositories that contain multiple separate Moodle plugins are not supported. The plugin also rejects unsupported repository URL formats and invalid packages.

Moodle must permit plugin deployment through its built-in administration tools, and the server must be able to access public GitHub downloads.

## Security and updates

Only site administrators can use this feature. Package checks help reject invalid Moodle components, unsupported upgrades, attempts to update the installer itself and version downgrades. Downloading a repository does not execute its PHP code during inspection.

**Review the source before approving a change.** A public GitHub repository is not necessarily a trusted or maintained plugin, and successful package validation does not guarantee that its code is secure or appropriate for production.

Updating a plugin can replace its existing code files. Keep an appropriate backup and a recovery plan before changing plugins on a production Moodle site. Dependencies are not automatically obtained from GitHub; Moodle's usual compatibility and dependency checks still apply.

**Compatibility:** Moodle 4.4 or newer.

**License:** GNU GPL v3 or later.
