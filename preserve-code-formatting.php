<?php
/*
Plugin Name: Preserve Code Formatting
Version: 1.0
Plugin URI: http://www.coffee2code.com/wp-plugins/
Author: Scott Reilly
Author URI: http://www.coffee2code.com
Description: Preserve formatting for text within &lt;code> and &lt;pre> tags (other tags can be defined as well).  Helps to preserve code indentation, multiple spaces, prevents WP's fancification of text (ie. ensures quotes don't become curly, etc).

NOTE: Use of the rich text editor will pose problems as it can mangle your intent in terms of &lt;code> tags.  I do not offer
any support for those who have the rich editor active.

=>> Visit the plugin's homepage for more information and latest updates  <<=


Installation:

1. Download the file http://www.coffee2code.com/wp-plugins/preserve-code-formatting.zip and unzip it into your 
wp-content/plugins/ directory.
-OR-
Copy and paste the contents of http://www.coffee2code.com/wp-plugins/preserve-code-formatting.phps into a file 
called preserve-code-formatting.php, and put that file into your wp-content/plugins/ directory.

2. Optional: There are three settings in the preserve-code-formatting.php file that you can customize:
 a. If you want other HTML tags (in addition to 'code' and 'pre') to be processed by this function, add them
    to the $c2c_preserve_tags array found just after the copyright information.
 b. By default, this plugin will wrap 'code' tags that contain multiline text within a 'pre' tag producing
	something that would look like:
	<pre><code>
	  function your_code() {
		// That goes on
		// multiple lines
	  }
	</code></pre>
	If you don't like that behavior, change the $wrap_multiline_code_in_pre argument to the
	c2c_preserve_postprocess() function to false.
 c. In the function c2c_prep_code(), if you do NOT wish for this plugin to help preserve spacing/indentation 
	in the 'code'/'pre'/etc tags, then set $use_nbsp_for_spaces to be 'false'.
    
3. Activate the plugin from your WordPress admin 'Plugins' page.


Notes:

Bascially, you can just paste code into 'code', 'pre', and/or other tags you additionally specify and this plugin will:
* prevent all "wptexturization" of text (i.e. single- and double-quotes will not become curly; "--" and "---" will not become en dash and em dash, 
respectively; "..." will not become a horizontal ellipsis, etc)
* optionally preserve multiple spaces (including indentations) (for the most part, that is; it changes 2+ consecutive "\n" to "\n\n" and "\t" to "  ")

Keep these things in mind:
* ALL embedded HTML tags and HTML entities will be rendered as text to browsers, appearing exactly as you wrote them (including any <br />).
* By default this plugin filters both 'the_content' (post contents), 'the_excerpt' (post excerpts), and 'get_comment_text'.

Example:
A post containing this within <code></code>:
$wpdb->query("
        INSERT INTO $tablepostmeta
        (post_id,meta_key,meta_value)
        VALUES ('$post_id','link','$extended')
");

Would, with this plugin enabled, look in a browser pretty much how it does above, instead of like:
$wpdb->query("
INSERT INTO $tablepostmeta
(post_id,meta_key,meta_value)
VALUES ('$post_id','link','$extended')
");

*/

/*
Copyright (c) 2004-2006 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation 
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, 
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the 
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// Array of HTML tags whose contents should be preserved.
$c2c_preserve_tags = array('code', 'pre');

function c2c_prep_code( $text ) {
	$use_nbsp_for_spaces = true;
	
	$text = preg_replace("/(\r\n|\n|\r)/", "\n", $text);
	$text = preg_replace("/\n\n+/", "\n\n", $text);
	$text = str_replace(array("&#36&;", "&#39&;"), array("$", "'"), $text);
	$text = htmlspecialchars($text, ENT_QUOTES);
	$text = str_replace("\t", '  ', $text);
	if ($use_nbsp_for_spaces)  $text = str_replace('  ', '&nbsp;&nbsp;', $text);
	// Change other special characters before wptexturize() gets to them
	$text = c2c_anti_wptexturize($text);
	$text = nl2br($text);
	return $text;
} //end c2c_prep_code()

// This short-circuits wptexturize process by making ASCII substitutions before wptexturize sees the text
function c2c_anti_wptexturize( $text ) {
	$text = str_replace('---', '&#45;&#45;-', $text);
	$text = str_replace('--', '&#45;-', $text);
	$text = str_replace('...', '&#46;..', $text);
	$text = str_replace('``', '&#96;`', $text);

	// This is a hack, look at this more later. It works pretty well though.
	$cockney = array("'tain't","'twere","'twas","'tis","'twill","'til","'bout","'nuff","'round","'cause");
	$cockneyreplace = array("&#39;tain&#39;t","&#39;twere","&#39;twas","&#39;tis","&#39;twill","&#39;til","&#39;bout","&#39;nuff","&#39;round","&#39;cause");
	$text = str_replace($cockney, $cockneyreplace, $text);

	$text = preg_replace("/'s/", '&#39;s', $text);
	$text = preg_replace("/'(\d\d(?:&#8217;|')?s)/", "&#39;$1", $text);
	$text = preg_replace('/(\s|\A|")\'/', '$1&#39;', $text);
	$text = preg_replace('/(\d+)"/', '$1&quot;', $text);
	$text = preg_replace("/(\d+)'/", '$1&#39;', $text);
	$text = preg_replace("/(\S)'([^'\s])/", "$1&#39;$2", $text);
	$text = preg_replace('/(\s|\A)"(?!\s)/', '$1&quot;$2', $text);
	$text = preg_replace('/"(\s|\S|\Z)/', '&quot;$1', $text);
	$text = preg_replace("/'([\s.]|\Z)/", '&#39;$1', $text);
	$text = preg_replace("/ \(tm\)/i", ' &#40;tm)', $text);
	$text = str_replace("''", '&#39;&#39;', $text);

	$text = preg_replace('/(d+)x(\d+)/', "$1&#120;$2", $text);
	
	$text = str_replace("\n\n", "\n&nbsp;\n", $text);
	return $text;
} //end c2c_anti_wptexturize()

function c2c_preserve_code_formatting( $text ) {
	$text = str_replace(array('$', "'"), array('&#36&;', '&#39&;'), stripslashes_deep($text));
	$text = c2c_prep_code($text);
	$text = str_replace(array('&#36&;', '&#39&;', '&lt; ?php'), array('$', "'", '&lt;?php'), $text);
	return $text;
} //end c2c_preserve_code_formatting()

function c2c_preserve_preprocess($content) {
	global $c2c_preserve_tags;
	$result = '';
	foreach ($c2c_preserve_tags as $tag) {
		if (!empty($result)) {
			$content = $result;
			$result = '';
		}
		$codes = preg_split("/(<{$tag}[^>]*>.*<\\/{$tag}>)/Us", $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach ($codes as $code) {
			if (preg_match("/^<{$tag}[^>]*>(.*)<\\/{$tag}>/Us", $code, $match))
				$code = "[[{$tag}]]" . base64_encode(stripslashes($match[1])) . "[[/{$tag}]]";
			$result .= $code;
		}
	}
	return $result;
} //end c2c_preserve_preprocess()

function c2c_preserve_postprocess($content, $preserve = false, $wrap_multiline_code_in_pre = true) {
	global $wpdb, $c2c_preserve_tags;
	$result = '';
	foreach ($c2c_preserve_tags as $tag) {
		if (!empty($result)) {
			$content = $result;
			$result = '';
		}
		$codes = preg_split("/(\\[\\[{$tag}\\]\\].*\\[\\[\\/{$tag}\\]\\])/Us", $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach ($codes as $code) {
			if (preg_match("/\\[\\[{$tag}\\]\\](.*)\\[\\[\\/{$tag}\\]\\]/Us", $code, $match)) {
				$data = base64_decode($match[1]);
				if ($preserve) $data = c2c_preserve_code_formatting($data);
				else $data = $wpdb->escape($data);
				$code = "<$tag>$data</$tag>\n";
				if ( $preserve && $wrap_multiline_code_in_pre && ('code' == $tag) && preg_match('/\\n/', $data) )
					$code = '<pre>' . $code . '</pre>';
			}
			$result .= $code;
		}
	}
	return $result;
} //end c2c_preserve_postprocess()

function c2c_preserve_postprocess_and_preserve($content) {
	return c2c_preserve_postprocess($content, true);
}

add_filter('the_content', 'c2c_preserve_preprocess', 1);
add_filter('the_content', 'c2c_preserve_postprocess_and_preserve', 100);
add_filter('content_save_pre', 'c2c_preserve_preprocess', 1);
add_filter('content_save_pre', 'c2c_preserve_postprocess', 100);

add_filter('the_excerpt', 'c2c_preserve_preprocess', 1);
add_filter('the_excerpt', 'c2c_preserve_postprocess_and_preserve', 100);
add_filter('excerpt_save_pre', 'c2c_preserve_preprocess', 1);
add_filter('excerpt_save_pre', 'c2c_preserve_postprocess', 100);

// Comment out these next lines if you don't want to allow preserve code formatting for comments.
add_filter('comment_text', 'c2c_preserve_preprocess', 1);
add_filter('comment_text', 'c2c_preserve_postprocess_and_preserve', 100);
add_filter('pre_comment_content', 'c2c_preserve_preprocess', 1);
add_filter('pre_comment_content', 'c2c_preserve_postprocess', 100);

?>