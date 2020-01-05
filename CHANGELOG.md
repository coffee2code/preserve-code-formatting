# Changelog

## 3.9.1 _(2020-01-04)_
* Fix: Don't attempt to handle posts containing a code block
* Change: Note compatibility through WP 5.3+
* Change: Update copyright date (2020)

## 3.9 _(2019-04-26)_

### Highlights:

This release is a minor update that verifies compatibility through WordPress 5.1+ and makes minor behind-the-scenes improvements.

### Details:

* Change: Initialize plugin on `plugins_loaded` action instead of on load
* Change: Update plugin framework to 049
    * 049:
    * Correct last arg in call to `add_settings_field()` to be an array
    * Wrap help text for settings in `label` instead of `p`
    * Only use `label` for help text for checkboxes, otherwise use `p`
    * Ensure a `textarea` displays as a block to prevent orphaning of subsequent help text
    * Note compatibility through WP 5.1+
    * Update copyright date (2019)
    * 048:
    * When resetting options, delete the option rather than setting it with default values
    * Prevent double "Settings reset" admin notice upon settings reset
    * 047:
    * Don't save default setting values to database on install
    * Change "Cheatin', huh?" error messages to "Something went wrong.", consistent with WP core
    * Note compatibility through WP 4.9+
    * Drop compatibility with version of WP older than 4.7
* Unit tests:
    * New: Add unit test for settings defaults
    * Change: Update unit test install script and bootstrap to use latest WP unit test repo
    * Change: Use actual setting name in a unit test
* Change: Cast settings values as either array or bool before use, as/if appropriate
* New: Add CHANGELOG.md file and move all but most recent changelog entries into it
* New: Add README.md link to plugin's page in Plugin Directory
* Change: Note compatibility through WP 5.1+
* Change: Wrap function docblocks at roughly 80 characters
* Change: Update copyright date (2019)
* Change: Update License URI to be HTTPS
* Change: Split paragraph in README.md's "Support" section into two

## 3.8 _(2018-01-04)_

### Highlights:

This release consists of minor behind-the-scenes changes.

### Details:

* Change: Update plugin framework to 046
    * 046:
    * Fix `reset_options()` to reference instance variable `$options`
    * Note compatibility through WP 4.7+
    * Update copyright date (2017)
    * 045:
    * Ensure `reset_options()` resets values saved in the database
    * 044:
    * Add `reset_caches()` to clear caches and memoized data. Use it in `reset_options()` and `verify_config()`
    * Add `verify_options()` with logic extracted from `verify_config()` for initializing default option attributes
    * Add  `add_option()` to add a new option to the plugin's configuration
    * Add filter 'sanitized_option_names' to allow modifying the list of whitelisted option names
    * Change: Refactor `get_option_names()`
    * 043:
    * Disregard invalid lines supplied as part of hash option value
    * 042:
    * Update `disable_update_check()` to check for HTTP and HTTPS for plugin update check API URL
    * Translate "Donate" in footer message
* New: Add README.md
* Change: Store setting name in constant
* Change: Unit tests:
    * Add and update unit tests
    * Prevent direct invocation
    * Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable
    * Enable more error output for unit tests
* Change: Add GitHub link to readme
* Change: Note compatibility through WP 4.9+
* Change: Drop compatibility with versions of WP older than 4.7
* Change: Update copyright date (2018)

## 3.7 _(2016-03-29)_

### Highlights:

This release largely consists of minor behind-the-scenes changes.

### Details:

* Change: Update plugin framework to 041
    * Change class name to `c2c_PreserveCodeFormatting_Plugin_041` to be plugin-specific
    * Set textdomain using a string instead of a variable
    * Don't load textdomain from file
    * Change admin page header from 'h2' to 'h1' tag
    * Add `c2c_plugin_version()`
    * Formatting improvements to inline docs
* Change: Add support for language packs:
    * Set textdomain using a string instead of a variable
    * Don't load textdomain from file
    * Remove .pot file and /lang subdirectory
    * Remove 'Domain Path' from plugin header.
* New: Add LICENSE file.
* New: Add empty index.php to prevent files from being listed if web server has enabled directory listings.
* Change: Declare class as final.
* Change: Explicitly declare methods in unit tests as public or protected.
* Change: Minor tweak to description.
* Change: Minor code reformatting (spacing).
* Change: Minor improvements to inline docs and test docs.
* Change: Note compatibility through WP 4.5+.
* Change: Remove support for WordPress older than 4.1.
* Change: Update copyright date (2016).

## 3.6 _(2015-02-24)_
* Cast some variable as array to avoid potential PHP warnings
* Add more unit tests
* Update plugin framework to 039
* Explicitly declare `activation()` and `uninstall()` static
* Reformat plugin header
* Minor code reformatting (spacing, bracing)
* Change documentation links to wp.org to be https
* Minor documentation spacing changes throughout
* Note compatibility through WP 4.1+
* Update copyright date (2015)
* Add plugin icon
* Regenerate .pot

## 3.5 _(2014-01-11)_
* Add setting to control if code should be preserved in posts (default is true)
* Don't wrap 'pre' tags in 'pre' despite settings values
* Update plugin framework to 037
* Better singleton implementation:
    * Add `get_instance()` static method for returning/creating singleton instance
    * Make static variable 'instance' private
    * Make constructor protected
    * Make class final
    * Additional related changes in plugin framework (protected constructor, erroring `__clone()` and `__wakeup()`)
* Add unit tests
* Add checks to prevent execution of code if file is directly accessed
* Re-license as GPLv2 or later (from X11)
* Add 'License' and 'License URI' header tags to readme.txt and plugin file
* Use explicit path for require_once()
* Discontinue use of PHP4-style constructor
* Discontinue use of explicit pass-by-reference for objects
* Remove ending PHP close tag
* Minor documentation improvements
* Minor code reformatting (spacing, bracing)
* Note compatibility through WP 3.8+
* Drop compatibility with version of WP older than 3.6
* Add comments explaining use of base64_encode and base64_decode
* Update copyright date (2014)
* Regenerate .pot
* Change plugin description (to make it shorter)
* Change donate link
* Omit final closing PHP tag
* Add assets directory to plugin repository checkout
* Update screenshot
* Move screenshot into repo's assets directory
* Add banner

## 3.2
* Fix bug with settings form not appearing in MS
* Update plugin framework to 032
* Remove support for `c2c_preserve_code_formatting` global
* Note compatibility through WP 3.3+
* Drop support for versions of WP older than 3.1
* Change parent constructor invocation
* Create 'lang' subdirectory and move .pot file into it
* Regenerate .pot
* Add 'Domain Path' directive to top of main plugin file
* Add link to plugin directory page to readme.txt
* Add text and FAQ question regarding how shortcodes are prevented from being evaluated
* Tweak installation instructions in readme.txt
* Update screenshot for WP 3.3
* Update copyright date (2012)

## 3.1
* Fix to properly register activation and uninstall hooks
* Update plugin framework to version v023
* Save a static version of itself in class variable $instance
* Deprecate use of global variable `$c2c_preserve_code_formatting` to store instance
* Add `__construct()`, `activation()`, and `uninstall()`
* Explicitly declare functions public and variable private
* Remove declarations of instance variable which have since become part of the plugin framework
* Note compatibility through WP 3.2+
* Drop compatibility with version of WP older than 3.0
* Minor code formatting changes (spacing)
* Update copyright date (2011)
* Add plugin homepage and author links in description in readme.txt

## 3.0
* Re-implementation by extending `C2C_Plugin_016`, which among other things adds support for:
    * Reset of options to default values
    * Better sanitization of input values
    * Offload of core/basic functionality to generic plugin framework
    * Additional hooks for various stages/places of plugin operation
    * Easier localization support
* Full localization support
* Change storing plugin instance in global variable to `$c2c_preserve_code_formatting` (instead of `$preserve_code_formatting`), to allow for external manipulation
* Rename class from `PreserveCodeFormatting` to `c2c_PreserveCodeFormatting`
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Note compatibility with WP 2.9+, 3.0+
* Drop compatibility with versions of WP older than 2.8
* Add PHPDoc documentation
* Minor tweaks to code formatting (spacing)
* Add package info to top of plugin file
* Add Upgrade Notice section to readme.txt
* Update copyright date
* Remove trailing whitespace
* Add .pot file

## 2.5.4
* Fix some borked code preservation by restoring some processing removed in previous release

## 2.5.3
* Fix recently introduced bug affecting occasional code preservation by using a more robust alternative approach
* Fix "Settings" link for plugin in plugin listing, which lead to blank settings page
* Change help text for preservable tags settings input to be more explicit

## 2.5.2
* Fix to retain any attributes defined for tags being preserved
* Fix recently introduced bug affecting occasional code preservation

## 2.5.1
* Fix newly introduced bug that added unnecessary slashes to preserved code
* Fix long-running bug where intended slashes in code got stripped on display (last remaining known bug)

## 2.5
* Fix long-running bug that caused some preserved code to appear garbled
* Update a lot of internal plugin management code
* Add 'Settings' link to plugin's plugin listing entry
* Use `plugins_url()` instead of hardcoded path
* Bring admin markup in line with modern conventions
* Minor reformatting (spacing)
* Note compatibility through WP2.8+
* Dropp support for pre-WP2.6
* Update copyright date
* Update screenshot

## 2.0
* Complete rewrite
* Now properly handles code embedded in comments
* Create its own class to encapsulate plugin functionality
* Add admin options page under Options -> Code Formatting (or in WP 2.5: Settings -> Code Formatting). Options are now saved to database, negating need to customize code within the plugin source file.
* Remove function anti_wptexturize() as the new handling approach negates its need
* Change description; updated installation instructions
* Add compatibility note
* Update copyright date
* Move into its own subdirectory; added readme.txt and screenshot
* Verify compatibility with WP 2.3.3 and 2.5

## 0.9
* Initial release
