<?php
/**
 * Plugin Name: WordPress Attack Protection
 * Plugin URI:  https://www.edapthosting.com/
 * Description: Add password protecting for wp-login.php file.
 * Version:     1.0.0
 * Author:      Kevin Suen
 * Author URI:  https://mixcodes.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$a = rand( 1, 10 );
$b = rand( 1, 10 );

$username = 'chrispug';
$captcha = $a + $b;

$transient_id = $username . '_' . str_replace( '.', '_', $_SERVER['REMOTE_ADDR'] );

function wp_login_protect_check() {
	global $username, $captcha, $transient_id;

	if ( ! get_transient( $transient_id ) ) {
		if ( $username != $_POST['wp_protect_username'] || ! wp_verify_nonce( $_POST['wp_protect_password_nonce'], 'wp_protect_password' ) ) {
			return false;
		} else {
			set_transient( $transient_id, 'true', 72 * HOUR_IN_SECONDS );

			return true;
		}
	} else {
		return true;
	}
}

function wp_login_protect_reomve_style() {
	if ( ! wp_login_protect_check() ) {
		wp_dequeue_style( 'login' );
		wp_enqueue_script( 'jquery' );
	}
}
add_action( 'login_enqueue_scripts', 'wp_login_protect_reomve_style' );

function wp_login_protect_head() {
	if ( ! wp_login_protect_check() ) {
		echo '<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">';
		echo '<style>body{font-family:"Open Sans",sans-serif;font-size:16px;background:#3498db;width:100%;text-align:center}h1{font-size:1em;font-weight:400;margin-bottom:10px}p{font-size:.75em;margin-bottom:5px}p span{color:#f44336;font-weight: 600}.box{display:none;background:#fff;width:350px;border-radius:5px;padding:10px;border:#2980b9 3px solid;position:absolute;top:50%;left:50%;-webkit-transform:translate(-50%, -50%);transform:translate(-50%, -50%)}.username,.password{background:#ecf0f1;border:#ccc 1px solid;padding:8px;width:250px;margin-top:10px;font-size:1em;border-radius:4px}.password{margin-bottom:10px}.error{display:none;margin:0;color:#f44336}.submit{background:#3498db;width:100px;padding:10px 0;color:#fff;border-radius:3px;border:#2980b9 1px solid;margin:10px 0 15px;font-size:1em}.submit:hover{background:#3594d2;cursor:pointer}</style>';
	}
}
add_action( 'login_head', 'wp_login_protect_head' );


function wp_login_protect_header() {
	if ( ! wp_login_protect_check() ) {
		global $a, $b, $username, $captcha;

		$current_link = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		echo '</head>';
		echo '<body>';
		echo '<form method="post" action="' . $current_link . '">';
		echo '<div class="box">';
		echo '<h1>WordPress Attack Protection</h1>';
		echo '<p>Enter username: <span>' . $username . '</span><br>Password: The result of math <span>' . $a . '+' . $b . '</span></p>';
		echo '<input type="text" name="wp_protect_username" placeholder="username" id="un-input" class="username" />';
		echo '<input type="password" name="wp_protect_password" placeholder="password" id="pwd-input" class="password" />';
		echo '<p class="error">Authorization failed! Please try again.</p>';

		wp_nonce_field( 'wp_protect_password', 'wp_protect_password_nonce' );

		echo '<input type="hidden" id="un" value="' . $username . '">';
		echo '<input type="hidden" id="pwd" value="' . $captcha . '">';
		echo '<input type="submit" value="Submit" class="submit"/>';
		echo '</div>';
		echo '</form>';
		echo '<script>jQuery(document).ready(function(){jQuery(".box").fadeIn(1e3),jQuery(document).on("submit","form",function(){return jQuery("#un-input").val()==jQuery("#un").val()&&jQuery("#pwd-input").val()==jQuery("#pwd").val()||(jQuery(".error").fadeIn(500),!1)})});</script>';
		echo '</body>';
		echo '</html>';

		exit();
	}
}
add_action( 'login_head', 'wp_login_protect_header' );
