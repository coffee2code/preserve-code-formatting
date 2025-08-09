<?php
/**
 * Plugin Name: Preserve Code Formatting
 * Version:     4.0.1
 * Plugin URI:  https://coffee2code.com/wp-plugins/preserve-code-formatting/
 * Author:      Scott Reilly
 * Author URI:  https://coffee2code.com/
 * Text Domain: preserve-code-formatting
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: Preserve formatting of code for display by preventing its modification by WordPress and other plugins while also retaining whitespace.
 *
 * NOTE: Use of the visual text editor will pose problems as it can mangle your intent in terms of <code> tags. I do not
 * offer any support for those who have the visual editor active.
 *
 * Compatible with WordPress 5.5+ through 6.8+, and PHP through at least 8.3+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/preserve-code-formatting/
 *
 * @package Preserve_Code_Formatting
 * @author  Scott Reilly
 * @version 4.0.1
 */

/*
	Copyright (c) 2004-2025 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'c2c_PreserveCodeFormatting' ) ) :

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'c2c-plugin.php' );

final class c2c_PreserveCodeFormatting extends c2c_Plugin_070 {
	/**
	 * Name of plugin's setting.
	 *
	 * @var string
	 */
	const SETTING_NAME = 'c2c_preserve_code_formatting';

	/**
	 * Whether to disable the use of WP_HTML_Tag_Processor.
	 *
	 * This is used for testing purposes.
	 *
	 * @since 5.0
	 * @var bool
	 */
	public $disable_wp_html_tag_processor = false;

	/**
	 * The one true instance.
	 *
	 * @var c2c_PreserveCodeFormatting
	 * @access private
	 */
	private static $instance;

	/**
	 * The chunk split token.
	 *
	 * @var string
	 * @access private
	 */
	private $chunk_split_token = '{[&*&]}';

	/**
	 * Maximum size for individual code blocks to prevent DoS attacks.
	 *
	 * @var int
	 * @access private
	 */
	private $max_code_block_size = 100000; // 100KB limit

	/**
	 * Get singleton instance.
	 *
	 * @since 3.5
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		parent::__construct( '4.0.1', 'preserve-code-formatting', 'c2c', __FILE__, array() );
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );

		return self::$instance = $this;
	}

	/**
	 * Handles activation tasks, such as registering the uninstall hook.
	 *
	 * @since 3.1
	 */
	public static function activation() {
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * Handles uninstallation tasks, such as deleting plugin options.
	 *
	 * @since 3.1
	 */
	public static function uninstall() {
		delete_option( self::SETTING_NAME );
	}

	/**
	 * Initializes the plugin's configuration and localizable text variables.
	 */
	public function load_config() {
		$this->name      = __( 'Preserve Code Formatting', 'preserve-code-formatting' );
		$this->menu_name = __( 'Code Formatting', 'preserve-code-formatting' );

		$this->config = array(
			'preserve_tags' => array(
				'input'    => 'text',
				'default'  => array( 'code', 'pre' ),
				'datatype' => 'array',
				'label'    => __( 'Tags that will have their contents preserved', 'preserve-code-formatting' ),
				'help'     => __( 'Space and/or comma-separated list of HTML tag names.', 'preserve-code-formatting' ),
			),
			'preserve_in_posts' => array(
				'input'    => 'checkbox',
				'default'  => true,
				'label'    => __( 'Preserve code in posts?', 'preserve-code-formatting' ),
				'help'     => __( 'Preserve code included in posts/pages?', 'preserve-code-formatting' ),
			),
			'preserve_in_comments' => array(
				'input'    => 'checkbox',
				'default'  => true,
				'label'    => __( 'Preserve code in comments?', 'preserve-code-formatting' ),
				'help'     => __( 'Preserve code posted by visitors in comments?', 'preserve-code-formatting' ),
			),
			'wrap_multiline_code_in_pre' => array(
				'input'    => 'checkbox',
				'default'  => true,
				'label'    => __( 'Wrap multiline code in <code>&lt;pre></code> tag?', 'preserve-code-formatting' ),
				'help'     => __( '&lt;pre> helps to preserve whitespace', 'preserve-code-formatting' ),
			),
			'use_nbsp_for_spaces' => array(
				'input'    => 'checkbox',
				'default'  => true,
				'label'    => __( 'Use <code>&amp;nbsp;</code> for spaces?', 'preserve-code-formatting' ),
				'help'     => __( 'Not necessary if you are wrapping code in <code>&lt;pre></code> or you use CSS to define whitespace:pre; for code tags.', 'preserve-code-formatting' ),
			),
			'nl2br' => array(
				'input'    => 'checkbox',
				'default'  => false,
				'label'    => __( 'Convert newlines to <code>&lt;br/></code>?', 'preserve-code-formatting' ),
				'help'     => __( 'Depending on your CSS styling, you may need this. Otherwise, code may appear double-spaced.', 'preserve-code-formatting' ),
			),
		);
	}

	/**
	 * Returns translated strings used by c2c_Plugin parent class.
	 *
	 * @since 4.0
	 *
	 * @param string $string Optional. The string whose translation should be
	 *                       returned, or an empty string to return all strings.
	 *                       Default ''.
	 * @return string|string[] The translated string, or if a string was provided
	 *                         but a translation was not found then the original
	 *                         string, or an array of all strings if $string is ''.
	 */
	public function get_c2c_string( $string = '' ) {
		$strings = array(
			'%s cannot be cloned.'
				/* translators: %s: Name of plugin class. */
				=> __( '%s cannot be cloned.', 'preserve-code-formatting' ),
			'%s cannot be unserialized.'
				/* translators: %s: Name of plugin class. */
				=> __( '%s cannot be unserialized.', 'preserve-code-formatting' ),
			'A value is required for: "%s"'
				/* translators: %s: Label for setting. */
				=> __( 'A value is required for: "%s"', 'preserve-code-formatting' ),
			'Click for more help on this plugin'
				=> __( 'Click for more help on this plugin', 'preserve-code-formatting' ),
			' (especially check out the "Other Notes" tab, if present)'
				=> __( ' (especially check out the "Other Notes" tab, if present)', 'preserve-code-formatting' ),
			'Coffee fuels my coding.'
				=> __( 'Coffee fuels my coding.', 'preserve-code-formatting' ),
			'Did you find this plugin useful?'
				=> __( 'Did you find this plugin useful?', 'preserve-code-formatting' ),
			'Donate'
				=> __( 'Donate', 'preserve-code-formatting' ),
			'Expected integer value for: %s'
				/* translators: %s: Label for setting. */
				=> __( 'Expected integer value for: %s', 'preserve-code-formatting' ),
			'Invalid file specified for C2C_Plugin: %s'
				/* translators: %s: Path to the plugin file. */
				=> __( 'Invalid file specified for C2C_Plugin: %s', 'preserve-code-formatting' ),
			'More information about %1$s %2$s'
				/* translators: 1: plugin name 2: plugin version */
				=> __( 'More information about %1$s %2$s', 'preserve-code-formatting' ),
			'More Help'
				=> __( 'More Help', 'preserve-code-formatting' ),
			'More Plugin Help'
				=> __( 'More Plugin Help', 'preserve-code-formatting' ),
			'Please consider a donation'
				=> __( 'Please consider a donation', 'preserve-code-formatting' ),
			'Reset Settings'
				=> __( 'Reset Settings', 'preserve-code-formatting' ),
			'Save Changes'
				=> __( 'Save Changes', 'preserve-code-formatting' ),
			'See the "Help" link to the top-right of the page for more help.'
				=> __( 'See the "Help" link to the top-right of the page for more help.', 'preserve-code-formatting' ),
			'Settings'
				=> __( 'Settings', 'preserve-code-formatting' ),
			'Settings reset.'
				=> __( 'Settings reset.', 'preserve-code-formatting' ),
			'Show'
				=> _x( 'Show', 'password toggle', 'preserve-code-formatting' ),
			'Show password'
				=> __( 'Show password', 'preserve-code-formatting' ),
			'Something went wrong.'
				=> __( 'Something went wrong.', 'preserve-code-formatting' ),
			'The method %1$s should not be called until after the %2$s action.'
				/* translators: 1: The name of a code function, 2: The name of a WordPress action. */
				=> __( 'The method %1$s should not be called until after the %2$s action.', 'preserve-code-formatting' ),
			'The plugin author homepage.'
				=> __( 'The plugin author homepage.', 'preserve-code-formatting' ),
			"The plugin configuration option '%s' must be supplied."
				/* translators: %s: The setting configuration key name. */
				=>__( "The plugin configuration option '%s' must be supplied.", 'preserve-code-formatting' ),
			'This plugin brought to you by %s.'
				/* translators: %s: Link to plugin author's homepage. */
				=> __( 'This plugin brought to you by %s.', 'preserve-code-formatting' ),
		);

		if ( ! $string ) {
			return array_values( $strings );
		}

		return ! empty( $strings[ $string ] ) ? $strings[ $string ] : $string;
	}

	/**
	 * Override the plugin framework's register_filters() to register actions and
	 * filters.
	 */
	public function register_filters() {
		$options = $this->get_options();

		if ( $options['preserve_in_posts'] ) {
			add_filter( 'the_content',             array( $this, 'preserve_preprocess' ), 2 );
			add_filter( 'the_content',             array( $this, 'preserve_postprocess_and_preserve'), 100 );
			add_filter( 'content_save_pre',        array( $this, 'preserve_preprocess' ), 2 );
			add_filter( 'content_save_pre',        array( $this, 'preserve_postprocess' ), 100 );

			add_filter( 'the_excerpt',             array( $this, 'preserve_preprocess' ), 2 );
			add_filter( 'the_excerpt',             array( $this, 'preserve_postprocess_and_preserve' ), 100 );
			add_filter( 'excerpt_save_pre',        array( $this, 'preserve_preprocess' ), 2 );
			add_filter( 'excerpt_save_pre',        array( $this, 'preserve_postprocess' ), 100 );
		}

		if ( $options['preserve_in_comments'] ) {
			add_filter( 'comment_text',            array( $this, 'preserve_preprocess' ), 2 );
			add_filter( 'comment_text',            array( $this, 'preserve_postprocess_and_preserve' ), 100 );
			add_filter( 'pre_comment_content',     array( $this, 'preserve_preprocess' ), 2 );
			add_filter( 'pre_comment_content',     array( $this, 'preserve_postprocess' ), 100 );
		}
	}

	/**
	 * Outputs the text above the setting form.
	 *
	 * @param string $localized_heading_text Optional. Localized page heading
	 *                                       text. Default ''.
	 */
	public function options_page_description( $localized_heading_text = '' ) {
		$options = $this->get_options();
		parent::options_page_description( __( 'Preserve Code Formatting Settings', 'preserve-code-formatting' ) );
		echo '<p>';
		echo sprintf(
			wp_kses(
				/* translators: 1: Markup for code tag, 2: Markup for pre tag */
				__( 'Preserve formatting for text within %1$s and %2$s tags (other tags can be defined as well). Helps to preserve code indentation, multiple spaces, prevents WP\'s fancification of text (ie. ensures quotes don\'t become curly, etc).', 'preserve-code-formatting' ),
				array()
			),
			'<code>&lt;code&gt;</code>',
			'<code>&lt;pre&gt;</code>'
		);
		echo "</p>\n";
		echo '<p>';
		echo sprintf(
			wp_kses(
				/* translators: %s: Markup for code tag */
				__( 'NOTE: Use of the visual text editor will pose problems as it can mangle your intent in terms of %s tags. I do not offer any support for those who have the visual editor active.', 'preserve-code-formatting' ),
				array()
			),
			'<code>&lt;code&gt;</code>'
		);
		echo "</p>\n";
	}

	/**
	 * Preps code.
	 *
	 * @param  string $text Text to prep.
	 * @return string The prepped text.
	 */
	public function prep_code( $text ) {
		$options = $this->get_options();

		$text = preg_replace( "/(\r\n|\n|\r)/", "\n", $text );
		$text = preg_replace( "/\n\n+/", "\n\n", $text );
		$text = str_replace( array( "&#36&;", "&#39&;" ), array( "$", "'" ), $text );
		$text = htmlspecialchars( $text, ENT_QUOTES );
		$text = str_replace( "\t", '  ', $text );

		if ( $options['use_nbsp_for_spaces'] ) {
			$text = str_replace( '  ', '&nbsp;&nbsp;', $text );
		}

		if ( $options['nl2br'] ) {
			$text = nl2br( $text );
		}

		return $text;
	}

	/**
	 * Preserves the code formatting for text.
	 *
	 * @param  string $text Text with code formatting to preserve.
	 * @return string The text with code formatting preserved.
	 */
	public function preserve_code_formatting( $text ) {
		$text = str_replace( array( '$', "'" ), array( '&#36&;', '&#39&;' ), $text );
		$text = $this->prep_code( $text );
		$text = str_replace( array( '&#36&;', '&#39&;', '&lt; ?php' ), array( '$', "'", '&lt;?php' ), $text );

		return $text;
	}

	/**
	 * Clean any placeholder strings that might have been injected into content.
	 *
	 * These placeholder strings are used internally by the plugin and could
	 * interfere with processing if present in user content.
	 *
	 * @since 5.0
	 *
	 * @param  string $content The content to clean.
	 * @return string The content with placeholder strings removed.
	 */
	public function clean_placeholder_strings( $content ) {
		$placeholder_patterns = array(
			'___HTML_LT_PLACEHOLDER___',
			'___HTML_GT_PLACEHOLDER___',
			'{!{',
			'}!}'
		);

		foreach ( $placeholder_patterns as $pattern ) {
			$content = str_replace( $pattern, '', $content );
		}

		return $content;
	}

	/**
	 * Returns a regex pattern for a given tag in the preprocess phase.
	 *
	 * This method provides a regex pattern specifically designed for matching
	 * HTML tags that should have their content preserved. The pattern captures:
	 * - Group 1: The entire matched tag including content (for replacement)
	 * - Group 2: The opening tag attributes (e.g., 'code class="test"')
	 * - Group 3: The content between opening and closing tags
	 *
	 * @since 5.0
	 *
	 * @param string $tag The tag to get the regex pattern for.
	 * @return string The regex pattern for matching HTML tags in preprocessing.
	 */
	public function get_preprocess_regex_pattern( $tag ) {
		$escaped_tag = preg_quote( $tag, '/' );

		return "/(<({$escaped_tag}[^>]*)>((?:[^<]|<(?!\\/{$escaped_tag}>))+)<\\/{$escaped_tag}>)/Us";
	}

	/**
	 * Validates content for security and size constraints.
	 *
	 * @since 5.0
	 *
	 * @param string $content The content to validate.
	 * @param int    $max_size Optional. Maximum allowed size. Default null (uses class default).
	 * @return bool True if content is valid, false otherwise.
	 */
	public function is_content_safe( $content, $max_size = null ) {
		if ( ! is_string( $content ) ) {
			return false;
		}

		$max_size = $max_size ?: $this->max_code_block_size;

		if ( strlen( $content ) > $max_size ) {
			return false;
		}

		return true;
	}

	/**
	 * Preprocessor for code formatting preservation process.
	 *
	 * This method performs the first phase of code preservation by:
	 * 1. Validating content for security and size constraints
	 * 2. Cleaning malicious pseudo-tags
	 * 3. Using an improved regex pattern to process preserve tags while handling nested tags correctly
	 * 4. Converting code blocks to pseudo-tags with encoded content
	 *
	 * @param  string $content Text with code formatting to preserve.
	 * @return string The text with code formatting preprocessed.
	 */
	public function preserve_preprocess( $content ) {
		// Clean any placeholder strings that could interfere with processing.
		$content = $this->clean_placeholder_strings( $content );

		if ( has_blocks( $content ) ) {
			return $content;
		}

		$options       = $this->get_options();
		$preserve_tags = (array) $options['preserve_tags'];

		// Bail with unchanged content if no preserve tags.
		if ( ! $preserve_tags ) {
			return $content;
		}

		// Process all preserve tags in a single pass to avoid double-processing
		$result = $this->process_all_tags_single_pass( $content, $preserve_tags );

		return $result;
	}

	/**
	 * Processes all preserve tags in a single pass to avoid double-processing.
	 *
	 * @since 5.0
	 *
	 * @param string $content The content to process.
	 * @param array $preserve_tags Array of tag names to preserve.
	 * @return string The processed content.
	 */
	private function process_all_tags_single_pass( $content, $preserve_tags ) {
		// Process all tags in a single pass to prevent double-processing.
		// This is essential because processing tags sequentially can cause
		// inner tags to be processed multiple times.
		$result = $content;

		// Create a unified pattern that matches any preserve tag.
		// Use boundary-aware patterns that prioritize adjacent tag correctness.
		$tag_patterns = array();
		foreach ( $preserve_tags as $tag ) {
			$escaped_tag = preg_quote( $tag, '/' );
			// Match tags with content, but be conservative about boundaries.
			// This pattern stops at the first valid closing tag to prevent crossing into adjacent tags.
			$tag_patterns[] = "(<{$escaped_tag}[^>]*>)(?!\\s*<\\/{$escaped_tag}>)([^<]*|<(?!\\/{$escaped_tag}>)[^<]*)*<\\/{$escaped_tag}>";
		}

		$unified_pattern = "/(" . implode( '|', $tag_patterns ) . ")/Us";

		// Process all tags in one pass.
		$result = preg_replace_callback( $unified_pattern, function( $matches ) use ( $preserve_tags ) {
			// Find which tag type this match corresponds to.
			$tag_name = null;
			$tag_attributes = '';
			$tag_content = '';

			// Determine which tag type matched by checking the pattern.
			foreach ( $preserve_tags as $tag ) {
				$escaped_tag = preg_quote( $tag, '/' );
				$tag_pattern = "(<{$escaped_tag}[^>]*>)(?!\\s*<\\/{$escaped_tag}>)([^<]*|<(?!\\/{$escaped_tag}>)[^<]*)*<\\/{$escaped_tag}>";

				if ( preg_match( "/^{$tag_pattern}$/Us", $matches[0] ) ) {
					$tag_name = $tag;
					// Extract the content using the specific tag pattern.
					// Use the same boundary-aware pattern for content extraction.
					if ( preg_match( "/<{$escaped_tag}([^>]*)>(([^<]*|<(?!\\/{$escaped_tag}>)[^<]*)*)<\\/{$escaped_tag}>/Us", $matches[0], $tag_matches ) ) {
						$tag_attributes = $tag_matches[1];
						$tag_content = $tag_matches[2];
					}
					break;
				}
			}

			if ( ! $tag_name || ! isset( $tag_content ) ) {
				// Something went wrong, return original content.
				return $matches[0];
			}

			// Skip empty tags.
			if ( empty( trim( $tag_content ) ) ) {
				return $matches[0];
			}

			// Validate the content before processing.
			if ( ! $this->is_content_safe( $tag_content ) ) {
				// If content is invalid or potentially malicious, return original content.
				return $matches[0];
			}

			// Convert inner HTML tags to placeholders to prevent double-processing.
			$encoded_content = $this->encode_inner_html_tags( $tag_content );

			// Create the pseudo-tag.
			$pseudo_tag = "{!{{$tag_name}{$tag_attributes}}!}";
			$pseudo_tag .= base64_encode( addslashes( chunk_split( json_encode( $encoded_content ), 76, $this->chunk_split_token ) ) );
			$pseudo_tag .= "{!{/{$tag_name}}!}";

			return $pseudo_tag;
		}, $result );

		// Use the original content if an issue was encountered.
		if ( null === $result ) {
			$result = $content;
		}

		return $result;
	}

	/**
	 * Encode inner HTML tags to prevent them from being processed.
	 *
	 * @since 5.0
	 *
	 * @param string $content The content to encode.
	 * @return string Content with HTML tags converted to placeholders.
	 */
	private function encode_inner_html_tags( $content ) {
		// Use unique placeholders that won't be processed by any subsequent encoding
		// This prevents double-encoding issues
		$result = str_replace( '<', '___HTML_LT_PLACEHOLDER___', $content );
		$result = str_replace( '>', '___HTML_GT_PLACEHOLDER___', $result );

		return $result;
	}

	/**
	 * Decode placeholder-encoded HTML tags back to HTML entities.
	 *
	 * @since 5.0
	 *
	 * @param string $content The content to decode.
	 * @return string Content with placeholders converted to HTML entities.
	 */
	private function decode_inner_html_tags( $content ) {
		// Convert placeholders back to HTML entities
		$result = str_replace( '___HTML_LT_PLACEHOLDER___', '&lt;', $content );
		$result = str_replace( '___HTML_GT_PLACEHOLDER___', '&gt;', $result );

		return $result;
	}

	/**
	 * Returns a regex pattern for a given tag in the postprocess phase.
	 *
	 * This method provides a regex pattern specifically designed for matching
	 * the pseudo-tags created during preprocessing. The pattern captures:
	 * - Group 1: The opening tag attributes (e.g., 'code class="test"')
	 * - Group 2: The encoded content between pseudo-tags
	 *
	 * @since 5.0
	 *
	 * @param string $tag The tag to get the regex pattern for.
	 * @return string The regex pattern for matching pseudo-tags in postprocessing.
	 */
	public function get_postprocess_regex_pattern( $tag ) {
		$escaped_tag = preg_quote( $tag, '/' );

		return "/\\{\\!\\{({$escaped_tag}[^\\]]*)\\}\\!\\}(.*)\\{\\!\\{\\/{$escaped_tag}\\}\\!\\}/Us";
	}

	/**
	 * Post-processor for code formatting preservation process.
	 *
	 * This method performs the second phase of code preservation by:
	 * 1. Decoding previously encoded pseudo-tags back to HTML
	 * 2. Optionally applying code formatting preservation (spaces, newlines, etc.)
	 * 3. Adding CSS classes for styling
	 * 4. Wrapping multiline code in <pre> tags when configured
	 *
	 * @param  string $content  Text that was preprocessed for code formatting.
	 * @param  bool   $preserve Optional. Whether to apply code formatting preservation.
	 *                          When true, applies whitespace preservation, converts tabs to spaces,
	 *                          and handles newlines according to plugin settings.
	 *                          When false, only decodes the pseudo-tags without formatting changes.
	 *                          Default false.
	 * @return string The text with code formatting post-processed.
	 */
	public function preserve_postprocess( $content, $preserve = false ) {
		$options                    = $this->get_options();
		$preserve_tags              = (array) $options['preserve_tags'];
		$wrap_multiline_code_in_pre = (bool)  $options['wrap_multiline_code_in_pre'];

		// Bail with unchanged content if no preserve tags.
		if ( ! $preserve_tags ) {
			return $content;
		}

		// First pass: Find which preserve tags actually exist in the content.
		$found_tags = array();
		foreach ( $preserve_tags as $tag ) {
			if ( preg_match( $this->get_postprocess_regex_pattern( $tag ), $content ) ) {
				$found_tags[] = $tag;
			}
		}

		// Bail with unchanged content if no preserve tags found.
		if ( ! $found_tags ) {
			return $content;
		}

		// Second pass: Process only the tags that actually exist.
		$result = '';
		foreach ( $found_tags as $tag ) {
			if ( $result ) {
				$content = $result;
				$result = '';
			}

			$result = preg_replace_callback( $this->get_postprocess_regex_pattern( $tag ), function( $matches ) use ( $tag, $preserve, $wrap_multiline_code_in_pre ) {
				// Note: base64_decode is only being used to decode user-supplied content of code tags which
				// had been encoded earlier in the filtering process to prevent modification by WP.
				$decoded_data = str_replace( $this->chunk_split_token, '', stripslashes( base64_decode( $matches[2] ) ) );

				// Validate the decoded data before processing.
				if ( ! $this->is_content_safe( $decoded_data ) ) {
					// If data is invalid or potentially malicious, skip processing.
					return $matches[0];
				}

				$data = json_decode( $decoded_data, true );
				if ( $data === null && json_last_error() !== JSON_ERROR_NONE ) {
					// If JSON decoding fails, use the raw decoded data as fallback.
					$data = $decoded_data;
				}

				if ( $preserve ) {
					$data = $this->preserve_code_formatting( $data );
				}

				// Decode placeholder-encoded HTML tags back to HTML entities
				// This must be done AFTER preserve_code_formatting to prevent double-encoding
				$data = $this->decode_inner_html_tags( $data );

				$pcf_class = 'preserve-code-formatting';

				// Use HTML tag processor to add class to existing class attribute only if tag name is valid.
				if ( preg_match( '/^[a-zA-Z][a-zA-Z0-9-]*$/', $tag ) ) {
					$open_tag = $this->add_class_to_tag( "<{$matches[1]}>", $pcf_class );
				} else {
					// Just slap on the class if the tag name is invalid.
					$open_tag = "<{$matches[1]} class=\"{$pcf_class}\">";
				}

				$code = "{$open_tag}{$data}</{$tag}>";

				if ( $preserve && $wrap_multiline_code_in_pre && ( 'pre' != $tag ) && preg_match( "/\n/", $data ) ) {
					$code = '<pre>' . $code . '</pre>';
				}

				return $code;
			}, $content );
		}

		return $result;
	}

	/**
	 * Post-processor for code formatting preservation process that defaults to
	 * true for preserving.
	 *
	 * @param  string $content Text with code formatting to post-process and preserve.
	 * @return string The text with code formatting post-processed and preserved.
	 */
	public function preserve_postprocess_and_preserve( $content ) {
		return $this->preserve_postprocess( $content, true );
	}

	/**
	 * Adds a class to an HTML tag.
	 *
	 * Prefers WP_HTML_Tag_Processor if available (WP 6.2+), but falls back to
	 * a simple regex-based approach for older WordPress versions.
	 *
	 * @since 5.0
	 *
	 * @param string $tag_html The HTML tag to add the class to.
	 * @param string $class    The class to add.
	 * @return string The updated HTML tag.
	 */
	public function add_class_to_tag( $tag_html, $class ) {
		// Use the robust WP_HTML_Tag_Processor class if it exists (WP 6.2+).
		if ( class_exists( 'WP_HTML_Tag_Processor' ) && ! $this->disable_wp_html_tag_processor ) {
			$processor = new WP_HTML_Tag_Processor( $tag_html );

			while ( $processor->next_tag() ) {
				$current_class = $processor->get_attribute( 'class' );

				// If no class attribute, just add the class.
				if ( ! $current_class ) {
					$processor->set_attribute( 'class', $class );
					continue;
				}

				// Otherwise amend class unless it already exists.
				$classes = array_map( 'trim', explode( ' ', trim( $current_class ) ) );
				if ( ! in_array( $class, $classes, true ) ) {
					$classes[] = $class;
					$processor->set_attribute( 'class', implode( ' ', $classes ) );
				}
			}

			return $processor->get_updated_html();
		}

		// Simple regex-based approach for older WordPress versions.
		// This handles basic cases but is not as robust as WP_HTML_Tag_Processor.

		// If no class attribute exists, add one.
		if ( ! preg_match( '/\bclass\s*=\s*["\'][^"\']*["\']/', $tag_html ) ) {
			// Add class attribute before the closing >
			return preg_replace( '/>$/', ' class="' . esc_attr( $class ) . '">', $tag_html );
		}

		// If class attribute exists, check if our class is already present.
		if ( preg_match( '/\bclass\s*=\s*["\']([^"\']*)["\']/', $tag_html, $matches ) ) {
			$existing_classes = explode( ' ', trim( $matches[1] ) );
			if ( ! in_array( $class, $existing_classes, true ) ) {
				// Add our class to existing classes.
				$existing_classes[] = $class;
				$new_class_attr = 'class="' . esc_attr( implode( ' ', $existing_classes ) ) . '"';
				return preg_replace( '/\bclass\s*=\s*["\'][^"\']*["\']/', $new_class_attr, $tag_html );
			}
		}

		return $tag_html;
	}

} // end c2c_PreserveCodeFormatting

add_action( 'plugins_loaded', array( 'c2c_PreserveCodeFormatting', 'get_instance' ) );

endif; // end if !class_exists()
