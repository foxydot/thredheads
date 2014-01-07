<?php
/*
Plugin Name: Inline Login
Plugin URI: 
Description: Provides an inline login form with the shortcode [inline-login]. All wp_login_form() parameters are supported as shortcode arguments. See <a href="http://codex.wordpress.org/Function_Reference/wp_login_form" target="_blank">the codex</a> for parameters. 
Author: Catherine Sandrick
Version: 0.1
Author URI: http://MadScienceDept.com
*/   
   
/*  Copyright 2011  

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
function msd_inline_login($atts){
	extract( shortcode_atts( array(
		'echo' => false,
        'redirect' => site_url( $_SERVER['REQUEST_URI'] ), 
        'form_id' => 'loginform',
        'label_username' => __( 'Username' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in' => __( 'Log In' ),
        'id_username' => 'user_login',
        'id_password' => 'user_pass',
        'id_remember' => 'rememberme',
        'id_submit' => 'wp-submit',
        'remember' => true,
        'value_username' => NULL,
        'value_remember' => false
	), $atts ) );
	$args = array(
		'echo' => $echo,
        'redirect' => $redirect, 
        'form_id' => $form_id,
        'label_username' => $label_username,
        'label_password' => $label_password,
        'label_remember' => $label_remember,
        'label_log_in' => $label_log_in,
        'id_username' => $id_username,
        'id_password' => $id_password,
        'id_remember' => $id_remember,
        'id_submit' => $id_submit,
        'remember' => $remember,
        'value_username' => $value_username,
        'value_remember' => $value_remember
	);
	global $current_user;
	if(is_user_logged_in()){
		$ret = '<div class="button black">'.wp_loginout('',FALSE).'</div>';
	} else {
		$ret = '<div>'.wp_login_form($args).'</div>';
	}
	return $ret;
}

add_shortcode('inline-login','msd_inline_login');