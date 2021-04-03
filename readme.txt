=== Preserve Code Formatting ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: code, formatting, post body, content, display, writing, escape, coffee2code
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.9
Tested up to: 5.7
Stable tag: 3.9.2

Preserve formatting of code for display by preventing its modification by WordPress and other plugins while also retaining whitespace.

== Description ==

This plugin preserves formatting of code for display by preventing its modification by WordPress and other plugins while also retaining whitespace.

NOTE: Use of the visual text editor will pose problems as it can mangle your intent in terms of `code` tags. I strongly suggest you not use the visual editor in conjunction with this plugin as I have taken no effort to make the two compatible.

Notes:

Basically, you can just paste code into `code`, `pre`, and/or other tags you additionally specify and this plugin will:

* Prevent WordPress from HTML-encoding text (i.e. single- and double-quotes will not become curly; "--" and "---" will not become en dash and em dash, respectively; "..." will not become a horizontal ellipsis, etc)
* Prevent most other plugins from modifying preserved code
* Prevent shortcodes from being processed
* Optionally preserve whitespace (in a variety of methods)
* Optionally preserve code added in comments

Keep these things in mind:

* ALL embedded HTML tags and HTML entities will be rendered as text to browsers, appearing exactly as you wrote them (including any `br` tags).
* By default this plugin filters 'the_content' (post content), 'the_excerpt' (post excerpt), and 'get_comment_text (comment content)'.

Example:

A post containing this within `code` tags:

`
$wpdb->query("
        INSERT INTO $tablepostmeta
        (post_id,meta_key,meta_value)
        VALUES ('$post_id','link','$extended')
");
`

Would, with this plugin enabled, look in a browser pretty much how it does above, instead of like:

`
$wpdb->query(&#8212;
INSERT INTO $tablepostmeta
(post_id,meta_key,meta_value)
VALUES ('$post_id','link','$extended')
&#8213;);
`

Links: [Plugin Homepage](https://coffee2code.com/wp-plugins/preserve-code-formatting/) | [Plugin Directory Page](https://wordpress.org/plugins/preserve-code-formatting/) | [GitHub](https://github.com/coffee2code/preserve-code-formatting/) | [Author Homepage](https://coffee2code.com)


== Installation ==

1. Whether installing or updating, whether this plugin or any other, it is always advisable to back-up your data before starting
1. Install via the built-in WordPress plugin installer. Or download and unzip `preserve-code-formatting.zip` inside the plugins directory for your site (typically `wp-content/plugins/`)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Go to the `Settings` -> `Code Formatting` admin settings page (which you can also get to via the Settings link next to the plugin on the Manage Plugins page) and customize the settings.
1. Write a post with code contained within opening and closing `code` tags. If you are using the block editor (aka Gutenberg), then this plugin is only useful for maintaining code formatting for posts written before WP 5.0 (or whenever you started creating posts with the block editor). You should be using the built-in code block when including code into the block editor. Otherwise, if you are actively using the classic editor, be sure to use the HTML (aka "Text") editor and not the "Visual" editor or you'll encounter formatting issues.


== Frequently Asked Questions ==

= Why does my code still display all funky (for instance, I'm seeing `&amp;` in places where I expect to see `&`)? =

Are you using the visual editor? The visual editor has a tendency to screw up some of your intent, especially when you are attempting to include raw code. This plugin does not make any claims about working when you create posts with the visual editor enabled.

How to tell if you're using the visual editor: you're using what is now referred to as the Classic Editor (the editing experience in WordPress that pre-dates the block editor since WordPress 5.0). Above the post content field and to the right, there is a tab labeled "Visual" and another labeled "Text". If you're writing code, you want to use "Text" for such posts and not switch back to "Visual".

= Can I put shortcode examples within code tags and not have them be evaluated by WordPress? =

Yes, shortcodes within code tags (or any tag processed by this plugin) will be output as pure text and not be processed as shortcodes by WordPress.

= Is this plugin compatible with the code block in the block editor? =

Yes, in the sense that it doesn't do anything at all. The code block in the block editor should preserve code formatting without this plugin's intervention.

(If you have older content that predates the block editor and has not been converted to blocks, you'll still want to keep this plugin active to preserve code formatting in those older posts. But having this plugin active won't interfere with the behavior of code blocks.)

= Does this plugin include unit tests? =

Yes.


== Screenshots ==

1. A screenshot of the plugin's admin options page.


== Changelog ==

= 3.9.2 (2020-07-01) =
Highlights:

* This minor release updates its plugin framework, adds a TODO.md file, updates a few URLs to be HTTPS, expands unit testing, updates compatibility to be WP 4.9 through 5.4+, and minor documentation tweaks.

Details:

* Change: Update plugin framework to 050
    * Allow a hash entry to literally have '0' as a value without being entirely omitted when saved
    * Output donation markup using `printf()` rather than using string concatenation
    * Update copyright date (2020)
    * Note compatibility through WP 5.4+
    * Drop compatibility with version of WP older than 4.9
* New: Add TODO.md and move existing TODO list from top of main plugin file into it (and add more items to the list)
* Change: Note compatibility through WP 5.4+
* Change: Drop compatibility for version of WP older than 4.9
* Change: Tweak FAQ verbiage and add an entry addressing code block compatibility
* Change: Update installation instruction to clarify its use within the two types of editors and the two classic editor modes
* Change: Update links to coffee2code.com to be HTTPS
* Unit tests:
    * New: Add test for `options_page_description()`
    * New: Add tests for default hooks
    * New: Add test for setting name
    * New: Add test to verify shortcodes within preserved tags don't get replaced
    * Change: Store plugin instance in class variable to simplify referencing it
    * Change: Use HTTPS for link to WP SVN repository in bin script for configuring unit tests (and delete commented-out code)

= 3.9.1 (2020-01-04) =
* Fix: Don't attempt to handle posts containing a code block
* Change: Note compatibility through WP 5.3+
* Change: Update copyright date (2020)

= 3.9 (2019-04-26) =
Highlights:

* This release is a minor update that verifies compatibility through WordPress 5.1+ and makes minor behind-the-scenes improvements.

Details:

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

_Full changelog is available in [CHANGELOG.md](https://github.com/coffee2code/preserve-code-formatting/blob/master/CHANGELOG.md)._


== Upgrade Notice ==

= 3.9.2 =
Minor update: updated plugin framework, added a TODO.md file, updated a few URLs to be HTTPS, expanded unit testing, updated compatibility to be WP 4.9 through 5.4+, and minor documentation tweaks.

= 3.9.1 =
Bugfix update: fixed bug to prevent handling of posts containing a code block, noted compatibility through WP 5.3+, and updated copyright date (2020)

= 3.9 =
Minor update: tweaked plugin initialization, updates plugin framework to version 049, noted compatibility through WP 5.1+, created CHANGELOG.md to store historical changelog outside of readme.txt, and updated copyright date (2019)

= 3.8 =
Recommended minor update: updates plugin framework to version 046; compatibility is now with WP 4.7-4.9+; updated copyright date (2018).

= 3.7 =
Minor update: improve support for localization; verified compatibility through WP 4.5; removed compatibility with WP earlier than 4.1; updated copyright date (2016)

= 3.6 =
Minor update: added more unit tests; updated plugin framework to 039; noted compatibility through WP 4.1+; updated copyright date (2015); added plugin icon

= 3.5 =
Recommended update: fix bug where 'pre' tag could get wrapped in '<pre>' tag; added setting to disable preserving code in posts; added unit tests; updated plugin framework; compatibility now WP 3.6-3.8+

= 3.3 =
Minor update. Highlights: added setting to control if code should be preserved in posts; prevent 'pre' tag from getting wrapped in 'pre'; updated plugin framework.

= 3.2 =
Recommended update. Highlights: fixed bug with settings not appearing in MS; updated plugin framework; noted compatibility with WP 3.3+; dropped compatibility with versions of WP older than 3.1.

= 3.1 =
Recommended update. Highlights: fixed numerous bugs; added a debug mode; updated compatibility through WP 3.2; dropped compatibility with version of WP older than 3.0; updated plugin framework; and more.

= 3.0.1 =
Trivial update: updated plugin framework to v021; noted compatibility with WP 3.1+ and updated copyright date.

= 3.0 =
Recommended update. Highlights: re-implementation using custom plugin framework; full localization support; misc non-functionality documentation and formatting tweaks; renamed class; verified WP 3.0 compatibility; dropped support for versions of WP older than 2.8.
