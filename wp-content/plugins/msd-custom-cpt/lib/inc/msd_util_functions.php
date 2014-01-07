<?php
if ( ! function_exists( 'msd_trim_headline' ) ) :
	function msd_trim_headline($text, $length = 35) {
		$raw_excerpt = $text;
		if ( '' == $text ) {
			$text = get_the_content('');
		}
			$text = strip_shortcodes( $text );
			$text = preg_replace("/<img[^>]+\>/i", "", $text); 
			$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]&gt;', $text);
			$text = strip_tags($text);
			$excerpt_length = apply_filters('excerpt_length', $length);
			$excerpt_more = apply_filters('excerpt_more',false);
			$words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
			if ( count($words) > $excerpt_length ) {
				array_pop($words);
				$text = implode(' ', $words);
				$text = $text . $excerpt_more;
			} else {
				$text = implode(' ', $words);
			}
	
		
		return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
		//return $text;
	}
endif;
/**
 * @desc Checks to see if the given plugin is active.
 * @return boolean
 */
if( ! function_exists('msd_is_plugin_active')){
	function msd_is_plugin_active($plugin) {
		return in_array($plugin, (array) get_option('active_plugins', array()));
	}
}
/*
 * A useful troubleshooting function. Displays arrays in an easy to follow format in a textarea.
*/
if ( ! function_exists( 'ts_data' ) ) :
function ts_data($data){
	$ret = '<textarea class="troubleshoot" cols="100" rows="20">';
	$ret .= print_r($data,true);
	$ret .= '</textarea>';
	print $ret;
}
endif;
/*
 * A useful troubleshooting function. Dumps variable info in an easy to follow format in a textarea.
*/
if ( ! function_exists( 'ts_var' ) && function_exists( 'ts_data' ) ) :
function ts_var($var){
	ts_data(var_export( $var , true ));
}
endif;