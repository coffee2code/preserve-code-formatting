<?php

defined( 'ABSPATH' ) or die();

class Preserve_Code_Formatting_Test extends WP_UnitTestCase {

	protected $obj;

	public function setUp(): void {
		parent::setUp();

		$this->obj = c2c_PreserveCodeFormatting::get_instance();
		$this->obj->install();
		$this->obj->reset_options();

		add_filter( 'pcf_text', array( $this->obj, 'preserve_preprocess' ), 2 );
		add_filter( 'pcf_text', array( $this->obj, 'preserve_postprocess_and_preserve' ), 100 );
	}


	//
	//
	// DATA PROVIDERS
	//
	//


	public static function get_default_hooks() {
		return array(
			array( 'filter', 'the_content',      'preserve_preprocess', 2 ),
			array( 'filter', 'the_content',      'preserve_postprocess_and_preserve', 100 ),
			array( 'filter', 'content_save_pre', 'preserve_preprocess', 2 ),
			array( 'filter', 'content_save_pre', 'preserve_postprocess', 100 ),
			array( 'filter', 'the_excerpt',      'preserve_preprocess', 2 ),
			array( 'filter', 'the_excerpt',      'preserve_postprocess_and_preserve', 100 ),
			array( 'filter', 'excerpt_save_pre', 'preserve_preprocess', 2 ),
			array( 'filter', 'excerpt_save_pre', 'preserve_postprocess', 100 ),
		);
	}

	public static function get_default_comment_hooks() {
		return array(
			array( 'filter', 'comment_text',        'preserve_preprocess', 2 ),
			array( 'filter', 'comment_text',        'preserve_postprocess_and_preserve', 100 ),
			array( 'filter', 'pre_comment_content', 'preserve_preprocess', 2 ),
			array( 'filter', 'pre_comment_content', 'preserve_postprocess', 100 ),
		);
	}

	public static function get_settings_and_defaults() {
		return array(
			array( 'preserve_tags', array( 'code', 'pre' ) ),
			array( 'preserve_in_posts', true ),
			array( 'preserve_in_comments', true ),
			array( 'wrap_multiline_code_in_pre', true ),
			array( 'use_nbsp_for_spaces', true ),
			array( 'nl2br', false ),
		);
	}

	 public static function get_preserved_tags( $more_tags = array() ) {
		return array(
			array( 'code' ),
			array( 'pre' ),
		);
	}

	public static function get_default_filters() {
		return array(
			array( 'the_content' ),
			array( 'the_excerpt' ),
		);
	}


	//
	//
	// HELPER FUNCTIONS
	//
	//


	private function set_option( $settings = array() ) {
		$defaults = array(
			'preserve_tags'              => array( 'code', 'pre' ),
			'preserve_in_posts'          => true,
			'preserve_in_comments'       => true,
			'wrap_multiline_code_in_pre' => true,
			'use_nbsp_for_spaces'        => true,
			'nl2br'                      => false,
		);
		$settings = wp_parse_args( $settings, $defaults );
		$this->obj->update_option( $settings, true );
	}

	private function preserve( $text, $filter = 'pcf_text' ) {
		return apply_filters( $filter, $text );
	}


	//
	//
	// TESTS
	//
	//


	public function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_PreserveCodeFormatting' ) );
	}

	public function test_plugin_framework_class_name() {
		$this->assertTrue( class_exists( 'c2c_Plugin_070' ) );
	}

	public function test_plugin_framework_version() {
		$this->assertEquals( '070', $this->obj->c2c_plugin_version() );
	}

	public function test_get_version() {
		$this->assertEquals( '4.0.1', $this->obj->version() );
	}

	public function test_setting_name() {
		$this->assertEquals( 'c2c_preserve_code_formatting', $this->obj::SETTING_NAME );
	}

	public function test_instance_object_is_returned() {
		$this->assertTrue( is_a( $this->obj, 'c2c_PreserveCodeFormatting' ) );
	}

	public function test_hooks_plugins_loaded() {
		$this->assertEquals( 10, has_action( 'plugins_loaded', array( 'c2c_PreserveCodeFormatting', 'get_instance' ) ) );
	}

	/**
	 * @dataProvider get_default_hooks
	 */
	public function test_default_hooks( $hook_type, $hook, $function, $priority = 10, $class_method = true ) {
		$callback = $class_method ? array( $this->obj, $function ) : $function;

		$prio = $hook_type === 'action' ?
			has_action( $hook, $callback ) :
			has_filter( $hook, $callback );

		$this->assertNotFalse( $prio );
		if ( $priority ) {
			$this->assertEquals( $priority, $prio );
		}
	}

	/**
	 * @dataProvider get_default_comment_hooks
	 */
	public function test_default_comment_hooks( $hook_type, $hook, $function, $priority = 10, $class_method = true ) {
		$callback = $class_method ? array( $this->obj, $function ) : $function;

		$prio = $hook_type === 'action' ?
			has_action( $hook, $callback ) :
			has_filter( $hook, $callback );

		$this->assertNotFalse( $prio );
		if ( $priority ) {
			$this->assertEquals( $priority, $prio );
		}
	}

	/**
	 * @dataProvider get_default_comment_hooks
	 */
	public function test_comment_hooks_not_hooked_when_not_enabled( $hook_type, $hook, $function, $priority = 10, $class_method = true ) {
		$callback = $class_method ? array( $this->obj, $function ) : $function;

		// Unregister hook that was registered by default.
		$hook_type === 'action' ? remove_action( $hook, $callback, $priority ) : remove_filter( $hook, $callback, $priority );

		$this->set_option( array( 'preserve_in_comments' => false ) );
		// Re-register filters.
		$this->obj->register_filters();


		$prio = $hook_type === 'action' ?
			has_action( $hook, $callback ) :
			has_filter( $hook, $callback );

		$this->assertFalse( $prio );
	}

	/**
	 * @dataProvider get_settings_and_defaults
	 */
	public function test_default_settings( $setting, $value ) {
		$options = $this->obj->get_options();

		if ( is_bool( $value ) ) {
			if ( $value ) {
				$this->assertTrue( $options[ $setting ] );
			} else {
				$this->assertFalse( $options[ $setting ] );
			}
		} else {
			$this->assertEquals( $value, $options[ $setting ] );
		}
	}

	/*
	 * options_page_description()
	 */

	public function test_options_page_description() {
		$expected = '<h1>Preserve Code Formatting Settings</h1>' . "\n";
		$expected .= '<p class="see-help">See the &quot;Help&quot; link to the top-right of the page for more help.</p>' . "\n";
		$expected .= '<p>Preserve formatting for text within <code>&lt;code&gt;</code> and <code>&lt;pre&gt;</code> tags (other tags can be defined as well). Helps to preserve code indentation, multiple spaces, prevents WP\'s fancification of text (ie. ensures quotes don\'t become curly, etc).</p>' . "\n";
		$expected .= '<p>NOTE: Use of the visual text editor will pose problems as it can mangle your intent in terms of <code>&lt;code&gt;</code> tags. I do not offer any support for those who have the visual editor active.</p>' . "\n";

		$this->expectOutputRegex( '~' . preg_quote( $expected ) . '~', $this->obj->options_page_description() );
	}

	/**
	 * @dataProvider get_preserved_tags
	 */
	public function test_html_tags_are_preserved_in_preserved_tag( $tag ) {
		$code = '<strong>bold</strong> other markup <i>here</i>';
		$text = "Example <code>$code</code>";

		$this->assertEquals(
			'Example <code>' . htmlspecialchars( $code, ENT_QUOTES ) . '</code>',
			$this->preserve( $text )
		);
	}

	/**
	 * @dataProvider get_preserved_tags
	 */
	public function test_special_characters_are_preserved_in_preserved_tag( $tag ) {
		$code = "first\r\nsecond\rthird\n\n\n\n\$fourth\nfifth<?php test(); ?>";
		$text = "Example <code>$code</code>";
		$expected_code = "first\nsecond\nthird\n\n\$fourth\nfifth&lt;?php test(); ?&gt;";

		$this->assertEquals(
			'Example <pre><code>' . $expected_code . '</code></pre>',
			$this->preserve( $text )
		);
	}

	/**
	 * @dataProvider get_preserved_tags
	 */
	public function test_shortcodes_are_preserved_in_preserved_tag( $tag ) {
		add_shortcode( 'color', function( $atts, $content, $shortcode_tag ) {
			return ! empty( $atts['favorite'] ) ? 'blue' : 'gray';
		} );

		$text1 = 'Example <code>This is my [color type="favorite"].</code> and ';
		$text2 = '[color].';

		$this->assertEquals( '<p>' . str_replace( '"', '&quot;', $text1 ) . "gray.</p>\n", apply_filters( 'the_content', $text1 . $text2 ) );
	}

	/**
	 * @dataProvider get_preserved_tags
	 */
	public function test_tabs_are_replaced_in_preserved_tag( $tag ) {
		$code = "\tfirst\n\t\tsecond";
		$text = "Example <code>$code</code>";

		$this->assertEquals(
			'Example <pre><code>' . str_replace( "\t", "&nbsp;&nbsp;", $code ) . '</code></pre>',
			$this->preserve( $text )
		);
	}

	/**
	 * @dataProvider get_preserved_tags
	 */
	public function test_spaces_are_preserved_in_preserved_tag( $tag ) {
		$text = "Example <$tag>preserve  multiple  spaces</$tag>";

		$this->assertEquals(
			"Example <$tag>preserve&nbsp;&nbsp;multiple&nbsp;&nbsp;spaces</$tag>",
			$this->preserve( $text )
		);
	}

	public function test_spaces_are_not_preserved_in_unhandled_tag() {
		$tag = 'strong';
		$text = "Example <$tag>preserve  multiple  spaces</$tag>";

		$this->assertEquals( $text, apply_filters( 'pcf_text', $text ) );
	}

	/**
	 * @dataProvider get_preserved_tags
	 */
	public function test_space_is_not_replaced_with_nbsp_if_false_for_setting_use_nbsp_for_spaces( $tag ) {
		$this->set_option( array( 'use_nbsp_for_spaces' => false ) );

		$text = "Example <$tag>preserve  multiple  spaces</$tag>";

		$this->assertEquals( $text, $this->preserve( $text ) );
	}

	public function test_multiline_code_gets_wrapped_in_pre() {
		$text = "<code>some code\nanother line\n yet another</code>";

		$this->assertEquals( "Example <pre>$text</pre>", $this->preserve( 'Example ' . $text ) );
	}

	public function test_multiline_pre_does_not_get_wrapped_in_pre() {
		$text = "Example <pre>some code\nanother line\n yet another</pre>";

		$this->assertEquals( $text, $this->preserve( $text ) );
	}

	public function test_multiline_code_not_wrapped_in_pre_if_setting_wrap_multiline_code_in_pre_is_false() {
		$this->set_option( array( 'wrap_multiline_code_in_pre' => false ) );

		$text = "Example <code>some code\nanother line\n yet another</code>";

		$this->assertEquals( $text, $this->preserve( $text ) );
	}

	public function test_nl2br_setting() {
		$this->set_option( array( 'nl2br' => true ) );

		$text = "<code>some code\nanother line\n yet another</code>";

		$this->assertEquals( str_replace( "\n", "<br />\n", "Example <pre>$text</pre>" ), $this->preserve( 'Example ' . $text ) );
	}

	public function test_code_preserving_honors_setting_preserve_tags() {
		$this->set_option( array( 'preserve_tags' => array( 'pre', 'strong' ) ) );
		$text = "<TAG>preserve  multiple  spaces</TAG>";

		// 'code' typically is preserved, but the setting un-does that
		$t = str_replace( 'TAG', 'code', $text );
		$this->assertEquals( $t, $this->preserve( $t ) );

		// it should now handle 'strong'
		$t = str_replace( 'TAG', 'strong', $text );
		$this->assertEquals( str_replace( ' ', '&nbsp;', $t ), $this->preserve( $t ) );
	}

	/**
	 * @dataProvider get_default_filters
	 */
	public function test_filters_default_filters( $filter ) {
		$code = '<strong>bold</strong> other markup <i>here</i>';
		$text = "Example <code>$code</code>";

		$this->assertEquals(
			wpautop( 'Example <code>' . htmlspecialchars( $code, ENT_QUOTES ) . '</code>' ),
			$this->preserve( $text, $filter )
		);
	}

	public function test_does_not_process_text_containing_code_block() {
		$text = <<<HTML
<!-- wp:paragraph -->
<p>This post has a code block:</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>if ( \$cat && \$dog < 1 ) {
	echo "<strong>Some code.</strong>";
}</code></pre>
<!-- /wp:code -->
HTML;

		$this->assertEquals( $text, $this->preserve( $text ) );
	}

	public function test_does_not_immediately_store_default_settings_in_db() {
		$option_name = c2c_PreserveCodeFormatting::SETTING_NAME;
		// Get the options just to see if they may get saved.
		$options     = $this->obj->get_options();

		$this->assertFalse( get_option( $option_name ) );
	}

	public function test_uninstall_deletes_option() {
		$option_name = c2c_PreserveCodeFormatting::SETTING_NAME;
		$options     = $this->obj->get_options();

		// Explicitly set an option to ensure options get saved to the database.
		$this->set_option( array( 'preserve_tags' => 'pre' ) );

		$this->assertNotEmpty( $options );
		$this->assertNotFalse( get_option( $option_name ) );

		c2c_PreserveCodeFormatting::uninstall();

		$this->assertFalse( get_option( $option_name ) );
	}

	/**
	 * Test that regex pattern injection vulnerabilities are prevented.
	 */
	public function test_regex_pattern_injection_prevention() {
		$malicious_tags = array(
			'code[^>]*',
			'pre.*',
			'code|pre',
			'code+',
			'code?',
			'code{2}',
			'code$',
			'^code',
			// Potential regex syntax errors.
			'code\\',
			'code[',
			'code]',
			'code(',
			'code)',
		);

		foreach ( $malicious_tags as $malicious_tag ) {
			$this->set_option( array( 'preserve_tags' => array( $malicious_tag ) ) );

			$content = "<{$malicious_tag}>test content</{$malicious_tag}>";
			$result = $this->preserve( $content );

			$this->assertEquals( $content, $result, "Failed to properly escape tag: {$malicious_tag}" );
		}
	}

	public function test_regex_injection_with_mixed_content() {
		// Test malicious tag in content with other legitimate tags.
		$malicious_tag = 'code[^>]*';
		$this->set_option( array( 'preserve_tags' => array( $malicious_tag, 'pre' ) ) );

		$content = "<pre>legitimate content</pre><{$malicious_tag}>malicious content</{$malicious_tag}><code>normal content</code>";
		$result = $this->preserve( $content );

		$this->assertIsString( $result );
		$this->assertStringContainsString( 'legitimate content', $result );
		$this->assertStringContainsString( 'malicious content', $result );
		$this->assertStringContainsString( 'normal content', $result );
	}

	public function test_regex_injection_in_comments() {
		// Test malicious tag when comments are enabled.
		$malicious_tag = 'code|pre';
		$this->set_option( array(
			'preserve_tags' => array( $malicious_tag ),
			'preserve_in_comments' => true
		) );

		$content = "<{$malicious_tag}>comment content</{$malicious_tag}>";
		$result = $this->preserve( $content );

		$this->assertIsString( $result );
		$this->assertStringContainsString( 'comment content', $result );
	}

	public function test_regex_injection_edge_characters() {
		// Test tags with edge case characters that might break regex.
		$edge_tags = array(
			'code.',
			'.code',
			'code-',
			'-code',
			'code_',
			'_code',
			'code#',
			'#code',
		);

		foreach ( $edge_tags as $edge_tag ) {
			$this->set_option( array( 'preserve_tags' => array( $edge_tag ) ) );
			$content = "<{$edge_tag}>test</{$edge_tag}>";
			$result = $this->preserve( $content );

			$this->assertIsString( $result );
			$this->assertStringContainsString( 'test', $result );
		}
	}

	public function test_regex_injection_unicode_tags() {
		// Test tags with Unicode characters that might affect regex.
		$unicode_tags = array(
			'código',
			'代码',
			'код',
			'código[^>]*',
		);

		foreach ( $unicode_tags as $unicode_tag ) {
			$this->set_option( array( 'preserve_tags' => array( $unicode_tag ) ) );
			$content = "<{$unicode_tag}>test</{$unicode_tag}>";
			$result = $this->preserve( $content );

			$this->assertIsString( $result );
			$this->assertStringContainsString( 'test', $result );
		}
	}

	public function test_catastrophic_backtracking_prevention() {
		// Test with nested quantifiers that can cause catastrophic backtracking
		$malicious_tag = 'code+++';

		$this->set_option( array( 'preserve_tags' => array( $malicious_tag ) ) );
		$content = "<{$malicious_tag}>test content</{$malicious_tag}>";

		$result = $this->preserve( $content );

		$this->assertIsString( $result );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'test content', $result );
	}

	public function test_object_injection_vulnerability_prevented() {
		// Test that malicious object injection payloads are safely handled.
		// The payload should still be present in output, but safely ignored.
		$malicious_payload = 'O:8:"stdClass":1:{s:4:"test";s:4:"test";}';
		$content = "<code>{$malicious_payload}</code>";

		$result = $this->preserve( $content );

		// The malicious payload should still be present (safely ignored).
		// Note: WordPress HTML-encodes the output, so quotes become &quot;.
		$this->assertStringContainsString( 'stdClass', $result );
		$this->assertStringContainsString( 'O:8:&quot;stdClass&quot;', $result );

		$expected = '<code>O:8:&quot;stdClass&quot;:1:{s:4:&quot;test&quot;;s:4:&quot;test&quot;;}</code>';
		$this->assertEquals( $expected, $result );
	}

	public function test_json_encoding_decoding_of_code() {
		$normal_code = "function test() { return 'hello'; }";
		$content = "<code>{$normal_code}</code>";

		$result = $this->preserve( $content );

		$this->assertStringContainsString( "return &#039;hello&#039;", $result );
		$expected = '<code>function test() { return &#039;hello&#039;; }</code>';
		$this->assertEquals( $expected, $result );
	}

	public function test_malicious_json_payloads_handled_safely() {
		// Test that malicious JSON payloads are safely handled.
		// The payload should still be present in output, but safely ignored.
		$malicious_json = '{"__proto__": {"isAdmin": true}}';
		$content = "<code>{$malicious_json}</code>";

		$result = $this->preserve( $content );

		$this->assertStringContainsString( "isAdmin", $result );
		$this->assertStringContainsString( "__proto__", $result );

		$expected = '<code>{&quot;__proto__&quot;: {&quot;isAdmin&quot;: true}}</code>';
		$this->assertEquals( $expected, $result );
	}

	public function test_no_serialize_unserialize_vulnerability() {
		// Test that old serialized data structures are safely handled.
		// The payload should still be present in output, but safely ignored.
		$serialized_data = 'a:2:{i:0;s:4:"test";i:1;s:4:"test";}';
		$content = "<code>{$serialized_data}</code>";

		$result = $this->preserve( $content );

		$this->assertStringContainsString( "a:2:{", $result );
		$this->assertStringContainsString( "test", $result );

		$expected = '<code>a:2:{i:0;s:4:&quot;test&quot;;i:1;s:4:&quot;test&quot;;}</code>';
		$this->assertEquals( $expected, $result );
	}

	/**
	 * Test that complex code content is preserved correctly.
	 */
	public function test_complex_code_content_preserved() {
		$complex_code = <<<CODE
<code>
function complexFunction() {
    \$data = array(
        'key' => 'value',
        'nested' => array(
            'deep' => 'data'
        )
    );
    return json_encode(\$data);
}
</code>
CODE;

		$result = $this->preserve( $complex_code );

		$this->assertStringContainsString( 'function complexFunction()', $result );
		$this->assertStringContainsString( '$data = array(', $result );
		$this->assertStringContainsString( 'json_encode($data)', $result );
	}

	/*
	 * clean_pseudo_tags()
	 */

	public function test_clean_pseudo_tags_for_code_tag() {
		$content = "Normal text {!{code}!}malicious content{!{/code}!} more text";
		$result = $this->obj->clean_pseudo_tags( $content );
		$this->assertEquals( 'Normal text malicious content more text', $result );
	}

	public function test_clean_pseudo_tags_for_pre_tag() {
		$content = "Normal text {!{pre}!}malicious content{!{/pre}!} more text";
		$result = $this->obj->clean_pseudo_tags( $content );
		$this->assertEquals( 'Normal text malicious content more text', $result );
	}

	public function test_malicious_pseudo_tags_cleaned() {
		$malicious_content = "Normal text {!{code}!}malicious content{!{/code}!} more text";

		$result = $this->preserve( $malicious_content );

		$this->assertStringNotContainsString( '{!{code}!}', $result );
		$this->assertStringNotContainsString( '{!{/code}!}', $result );

		$this->assertStringContainsString( 'malicious content', $result );

		$this->assertEquals( 'Normal text malicious content more text', $result );
	}

	public function test_legitimate_code_tags_still_processed() {
		$legitimate_content = "Normal text <code>legitimate code</code> more text";

		$result = $this->preserve( $legitimate_content );

		$this->assertStringContainsString( '<code>', $result );
		$this->assertStringContainsString( 'legitimate code', $result );
		$this->assertStringContainsString( '</code>', $result );
	}

	public function test_pseudo_tags_with_different_tags_cleaned() {
		$malicious_content = "Text {!{pre}!}malicious pre{!{/pre}!} more {!{code}!}malicious code{!{/code}!}";

		$result = $this->preserve( $malicious_content );

		$this->assertStringNotContainsString( '{!{pre}!}', $result );
		$this->assertStringNotContainsString( '{!{/pre}!}', $result );
		$this->assertStringNotContainsString( '{!{code}!}', $result );
		$this->assertStringNotContainsString( '{!{/code}!}', $result );

		$this->assertStringContainsString( 'malicious pre', $result );
		$this->assertStringContainsString( 'malicious code', $result );

		$this->assertEquals( 'Text malicious pre more malicious code', $result );
	}

}
