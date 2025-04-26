<?php
/**
 * Unit tests for the c2c_Plugin framework.
 *
 * Use: Customize the instance variables found grouped together at the top of the test class.
 */
defined( 'ABSPATH' ) or die();

class c2c_Plugin extends WP_UnitTestCase {

	/* These values need to be customized for any c2c_Plugin-based plugin. */
	protected $class           = 'c2c_PreserveCodeFormatting';
	protected $file            = PRESERVE_CODE_FORMATTING_PLUGIN_FILE;
	protected $slug            = 'preserve-code-formatting';
	protected $underscore_slug = 'preserve_code_formatting';
	protected $framework_ver   = '070';

	// Configured on instantiation.
	protected $obj;
	protected $dir             = '';

	protected static $example_option = [
		'input'    => 'short_text',
		'default'  => 25,
		'datatype' => 'int',
		'label'    => 'Short text field',
		'input_attributes' => [],
	];

	public function setUp(): void {
		parent::setUp();

		$this->dir = dirname( $this->file );

		add_filter( 'gettext_' . $this->slug, array( $this, 'translate_text' ), 10, 2 );

		$this->obj = method_exists( $this->class, 'get_instance' ) ? $this->class::get_instance() : $this->class::instance();
	}

	public function tearDown(): void {
		parent::tearDown();
	}


	//
	//
	// HELPERS
	//
	//


	public function return_true( $opt ) {
		return true;
	}

	public function return_false( $opt ) {
		return false;
	}

	public function translate_text( $translation, $text ) {
		if ( 'Donate' === $text ) {
			$translation = 'Donar';
		}

		return $translation;
	}

	public function count_function_calls( $filename, $function_name ) {
		$content = file_get_contents( $filename );
		$count = 0;

		if ( false === $content ) {
			new Exception( 'Unable to locate or open file: ' . $filename );
			return 0;
		}

		$pattern = '/\b' . preg_quote( $function_name ) . '\(/';
		preg_match_all( $pattern, $content, $matches );

		if ( isset( $matches[0] ) ) {
			$count = count( $matches[0] );
		}

		// Exclude the function declaration.
		if ( preg_match( '/\bfunction\s+' . preg_quote( $function_name ) . '\(/', $content ) ) {
			$count--;
		}

		// TODO: Exclude mentions in comments.

		return $count;
	}

	public function create_option( $name, $attribs = [] ) {
		$this->obj->add_option( $name, array_merge( self::$example_option, $attribs ) );
	}

	//
	//
	// DATA PROVIDERS
	//
	//


	public static function wp_version_comparisons() {
		return array(
			//[ WP ver, version to compare to, operator, expected result ]
			[ '5.5', '5.5.1', '>=', false ],
			[ '5.5', '5.6',   '>=', false ],
			[ '5.5', '5.5',   '>=', true ],
			[ '5.5', '5.4.3', '>=', true ],
			[ '5.5', '5.5.1', '',   false ],
			[ '5.5', '5.6',   '',   false ],
			[ '5.5', '5.5',   '',   true ],
			[ '5.5', '5.5',   '>',  false ],
			[ '5.5', '5.5',   '<',  false ],
			[ '5.5', '5.5.1', '<=', true ],
			[ '5.5', '5.6',   '<=', true ],
			[ '5.5', '5.5',   '<=', true ],
			[ '5.5', '5.4.3', '<=', false ],
			[ '5.5', '5.5',   '=',  true ],
			[ '5.5', '5.5.1', '=',  false ],
			[ '5.5', '5.5',   '!=', false ],
		);
	}

	public static function invalid_config_attributes() {
		return [
			// [ attribute name, value with invalid datatype for attribute ]
			[ 'allow_html',       '' ], // supposed to be... bool
			[ 'class',            '' ], // array
			[ 'datatype',         [] ], // string
			[ 'help',             1 ], // string
			[ 'inline_help',      15 ], // string
			[ 'input',            13.4 ], // string
			[ 'input_attributes', '' ], // array
			[ 'input_attributes', 7 ], // array
			[ 'input_attributes', 'row="40"' ], // array
			[ 'label',            false ], // string
			[ 'more_help',        [] ], // string
			[ 'no_wrap',          '0' ], // bool
			[ 'numbered',         5 ], // bool
			[ 'options',          'cat' ], // array
			[ 'raw_help',         [ 'cat', 'emu' ] ], // string
			[ 'required',         [] ], // bool
		];
	}



	//
	//
	// TESTS
	//
	//


	public function test_plugin_framework_class_name() {
		$this->assertTrue( class_exists( 'c2c_Plugin_' . $this->framework_ver ) );
	}

	/*
	 * c2c_plugin_version()
	 */

	public function test_plugin_framework_version() {
		$this->assertEquals( $this->framework_ver, $this->obj->c2c_plugin_version() );
	}

	/*
	 * __clone()
	 */

	public function test_unable_to_clone_object() {
		$this->expectException( Error::class );
		$clone = clone $this->obj;
		$this->assertEquals( $clone, $this->obj );
	}

	/*
	 * __wakeup()
	 */

	public function test_unable_to_instantiate_object_from_class() {
		$this->expectException( Error::class );
		$class = get_class( $this->obj );
		new $class();
	}

	public function test_unable_to_unserialize_an_instance_of_the_class() {
		$this->expectException( Error::class );
		$class = get_class( $this->obj );
		$data = 'O:' . strlen( $class ) . ':"' . $class . '":0:{}';

		unserialize( $data );
	}

	/*
	 * is_wp_version_cmp()
	 */

	/**
	 * @dataProvider wp_version_comparisons
	 */
	public function test_is_wp_version_cmp( $wp_ver, $ver, $op, $expected ) {
		global $wp_version;
		$orig_wp_verion = $wp_version;

		$wp_version = $wp_ver;
		$this->{ $expected ? 'assertTrue' : 'assertFalse' }( $this->obj->is_wp_version_cmp( $ver, $op ) );

		$wp_version = $orig_wp_verion;
	}

	/*
	 * get_c2c_string()
	 */

	/**
	 * Ensure that each translatable string is translated by plugin.
	 *
	 * This assumes a lot and is quite brittle.
	 */
	public function test_get_c2c_string_has_correct_number_of_strings() {
		$this->assertEquals(
			$this->count_function_calls( $this->dir . '/c2c-plugin.php', 'get_c2c_string' ),
			count( $this->obj->get_c2c_string() )
		);
	}

	public function test_get_c2c_string_for_unknown_string() {
		$str = 'unknown string';

		$this->assertEquals( $str, $this->obj->get_c2c_string( $str ) );
	}

	public function test_get_c2c_string_for_known_string_translated() {
		$this->assertEquals( 'Donar', $this->obj->get_c2c_string( 'Donate' ) );
	}

	public function test_get_c2c_string_for_known_string_untranslated() {
		$str = 'A value is required for: "%s"';

		$this->assertEquals( $str, $this->obj->get_c2c_string( $str ) );
	}

	public function test_get_c2c_string_contains_all_strings() {
		remove_filter( 'gettext_' . $this->slug, array( $this, 'translate_text' ) );
		$this->assertSame( $this->obj->get_c2c_strings(), $this->obj->get_c2c_string() );
	}

	/*
	 * get_manage_options_capability()
	 */

	public function test_get_manage_options_capability() {
		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_filtered() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), function ( $cap ) { return 'unfiltered_html'; } );

		$this->assertEquals( 'unfiltered_html', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_if_filtered_to_be_blank_string() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), '__return_empty_string' );

		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_if_filtered_to_be_boolean() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), '__return_true' );

		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_if_filtered_to_be_array() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), function ( $cap ) { return [ 'capa', 'capb' ]; } );

		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_if_filtered_to_be_int() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), function ( $cap ) { return 5; } );

		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	/*
	 * get_hook()
	 */

	public function test_get_hook() {
		$this->assertEquals( $this->underscore_slug . '__example-hook', $this->obj->get_hook( 'example-hook' ) );
	}

	/*
	 * add_option()
	 */

	public function test_valid_option_attribute() {
		$this->create_option( 'testoption' );
		$this->assertTrue( true );
	}

	/**
	 * @expectedIncorrectUsage c2c_Plugin::verify_options
	 * @dataProvider invalid_config_attributes
	 */
	public function test_invalid_option_attribute( $key, $val ) {
		$this->create_option( 'testoption', [ $key => $val ] );
	}

	public function test_config_attribute_default_can_be_any_datatype() {
		$this->create_option( 'testoption', [ 'default' => false ] );
		$this->create_option( 'testoption', [ 'default' => true ] );
		$this->create_option( 'testoption', [ 'default' => 0 ] );
		$this->create_option( 'testoption', [ 'default' => 1 ] );
		$this->create_option( 'testoption', [ 'default' => 9 ] );
		$this->create_option( 'testoption', [ 'default' => 9.5 ] );
		$this->create_option( 'testoption', [ 'default' => '' ] );
		$this->create_option( 'testoption', [ 'default' => 'cat' ] );
		$this->create_option( 'testoption', [ 'default' => [] ] );
		$this->create_option( 'testoption', [ 'default' => [ 'cat', 'dog' ] ] );
		$this->create_option( 'testoption', [ 'default' => [ 'doctor' => 11, 'quirk' => [ 'bowtie', 'fex' ] ] ] );
		// Implicitly asserting that none of the above triggered a warning/error.
		$this->assertTrue( true );
	}

	/*
	 * esc_attributes()
	 */

	public function test_esc_attributes() {
		$this->assertEquals( 'vehicle="Tardis" doctor="10"', $this->obj->esc_attributes( [ 'vehicle' => 'Tardis', 'doctor' => 10 ] ) );
		$this->assertEquals( 'title="This is a &quot;quoted string&quot;"', $this->obj->esc_attributes( [ 'title' => 'This is a "quoted string"' ] ) );
		$this->assertEquals( 'title="This shan&#039;t not be apostrophed."', $this->obj->esc_attributes( [ 'title' => "This shan't not be apostrophed." ] ) );
		$this->assertEquals( 'title="HTML tags are a no go."', $this->obj->esc_attributes( [ 'title' => 'HTML tags are a <strong>no go</strong>.' ] ) );
	}

	/*
	 * is_option_required()
	 */

	public function test_is_option_required_when_not_explicitly_defined() {
		$opt = 'shorttextfield';
		$this->obj->add_option( $opt, [
			'input'    => 'short_text',
			'default'  => 25,
			'datatype' => 'int',
			'label'    => 'Short text field',
		] );

		$this->assertFalse( $this->obj->is_option_required( $opt ) );
	}

	public function test_is_option_required_when_true() {
		$opt = 'shorttextfield';
		$this->create_option( $opt, [ 'required' => true ] );

		$this->assertTrue( $this->obj->is_option_required( $opt ) );
	}

	public function test_is_option_required_when_false() {
		$opt = 'shorttextfield';
		$this->create_option( $opt, [ 'required' => false ] );

		$this->assertFalse( $this->obj->is_option_required( $opt ) );
	}

	/*
	 * is_option_disabled()
	 */

	public function test_is_option_disabled_defaults_to_false() {
		$opt = 'shorttextfield';
		$this->obj->add_option( $opt, [
			'input'    => 'short_text',
			'default'  => 25,
			'datatype' => 'int',
			'label'    => 'Short text field',
		] );

		$this->assertFalse( $this->obj->is_option_disabled( $opt ) );
	}

	public function test_is_option_disabled_accepts_true() {
		$opt = 'shorttextfield';
		$this->create_option( $opt, [ 'disabled' => true ] );

		$this->assertTrue( $this->obj->is_option_disabled( $opt ) );
	}

	public function test_is_option_disabled_accepts_false() {
		$opt = 'shorttextfield';
		$this->create_option( $opt, [ 'disabled' => false ] );

		$this->assertFalse( $this->obj->is_option_disabled( $opt ) );
	}

	public function test_is_option_disabled_accepts_empty_string() {
		$opt = 'shorttextfield';
		$this->create_option( $opt, [ 'disabled' => '' ] );

		$this->assertFalse( $this->obj->is_option_disabled( $opt ) );
	}

	/**
	 * @expectedIncorrectUsage c2c_Plugin::is_option_disabled
	 */
	public function test_is_option_disabled_with_invalid_callback() {
		$opt = 'shorttextfield';
		$this->create_option( $opt, [ 'disabled' => 'bogus_function' ] );

		$this->assertFalse( $this->obj->is_option_disabled( $opt ) );
	}

	public function test_is_option_disabled_with_valid_callback_as_string() {
		$opt = 'shorttextfield';
		$this->create_option( $opt, [ 'disabled' => '__return_true' ] );

		$this->assertTrue( $this->obj->is_option_disabled( $opt ) );
	}

	public function test_is_option_disabled_with_valid_callback_as_array() {
		$opt = 'shorttextfield';
		$this->create_option( $opt, [ 'disabled' => array( $this, 'return_true' ) ] );

		$this->assertTrue( $this->obj->is_option_disabled( $opt ) );
	}

	/*
	 * display_option()
	 */

	public function test_display_option_short_text_field() {
		$this->create_option( 'shorttextfield', [ 'help' => 'This is help.', 'required' => true ] );

		// $this->obj->add_option( 'shorttextfield', [
		// 	'input'    => 'short_text',
		// 	'default'  => 25,
		// 	'datatype' => 'int',
		// 	'required' => true,
		// 	'label'    => 'Short text field',
		// 	'help'     => 'This is help.',
		// 	'input_attributes' => [],
		// ] );

		$expected = '<input type="text" class="c2c-short_text small-text" id="shorttextfield" name="c2c_' . $this->underscore_slug . '[shorttextfield]" value="25" />'
			. "\n"
			. '<p class="description">This is help.</p>'
			. "\n";

		$this->expectOutputRegex( '~^' . preg_quote( $expected ) . '$~', $this->obj->display_option( 'shorttextfield' ) );
	}

	public function test_display_option_password_field() {
		$this->obj->add_option( 'passwordfield', [
			'input'    => 'password',
			'default'  => 'somepassword',
			'required' => true,
			'label'    => 'Password field',
			'help'     => 'This is help.',
			'input_attributes' => [],
		] );

		$expected = '<div id="c2c_' . $this->underscore_slug . '[passwordfield]-password-field">
<input type="password" class="c2c-password regular-text" id="passwordfield" name="c2c_' . $this->underscore_slug . '[passwordfield]" value="somepassword" aria-describedby="pass-strength-result" />
<button type="button" class="button wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
<span class="dashicons dashicons-visibility"></span>
<span class="text">Show</span>
</button></div>
<p class="description">This is help.</p>';

		$this->expectOutputRegex( '~^' . preg_quote( $expected ) . '$~', $this->obj->display_option( 'passwordfield' ) );
	}

	public function test_display_option_disabled_input_field_via_string_callback_returning_false() {
		$opt = 'tobedisabled';
		$this->obj->add_option( $opt, [
			'input'    => 'text',
			'default'  => 'doctorwho',
			'datatype' => 'string',
			'disabled' => '__return_false',
			'required' => false,
			'label'    => 'text field',
			'help'     => 'This is help.',
			'input_attributes' => [],
		] );

		$expected = sprintf(
			'<input type="text" class="c2c-text regular-text" id="%s" name="c2c_%s[%s]" value="doctorwho" />',
			$opt,
			$this->underscore_slug,
			$opt
			)
		. "\n"
		. '<p class="description">This is help.</p>'
		. "\n";

		$this->assertFalse( $this->obj->is_option_disabled( $opt ) );
		$this->expectOutputRegex( '~^' . preg_quote( $expected ) . '$~', $this->obj->display_option( $opt ) );
	}

	public function test_display_option_disabled_input_field_via_string_callback() {
		$opt = 'tobedisabled';
		$this->obj->add_option( $opt, [
			'input'    => 'text',
			'default'  => 'doctorwho',
			'datatype' => 'string',
			'disabled' => '__return_true',
			'required' => false,
			'label'    => 'text field',
			'help'     => 'This is help.',
			'input_attributes' => [],
		] );

		$expected = sprintf(
			'<input type="text" disabled="disabled" class="c2c-text regular-text" id="%s" name="c2c_%s[%s]" value="doctorwho" />',
			$opt,
			$this->underscore_slug,
			$opt
			)
		. "\n"
		. '<p class="description">This is help.</p>'
		. "\n";

		$this->assertTrue( $this->obj->is_option_disabled( $opt ) );
		$this->expectOutputRegex( '~^' . preg_quote( $expected ) . '$~', $this->obj->display_option( $opt ) );
	}

	public function test_display_option_disabled_input_field_via_callback_via_array_callback() {
		$opt = 'tobedisabled';
		$this->obj->add_option( $opt, [
			'input'    => 'text',
			'default'  => 'doctorwho',
			'datatype' => 'string',
			'disabled' => array( $this, 'return_true' ),
			'required' => false,
			'label'    => 'Text field',
			'help'     => 'This is help.',
			'input_attributes' => [],
		] );

		$expected = sprintf(
			'<input type="text" disabled="disabled" class="c2c-text regular-text" id="%s" name="c2c_%s[%s]" value="doctorwho" />',
			$opt,
			$this->underscore_slug,
			$opt
			)
		. "\n"
		. '<p class="description">This is help.</p>'
		. "\n";

		$this->assertTrue( $this->obj->is_option_disabled( $opt ) );
		$this->expectOutputRegex( '~^' . preg_quote( $expected ) . '$~', $this->obj->display_option( $opt ) );
	}

}
