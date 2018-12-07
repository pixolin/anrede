<?php
/**
 * Plugin Name: Anrede
 * Description: Fügt der Startseite eine persönliche Anrede hinzu
 * Plugin URI: https://github.com/pixolin/anrede
 * Author: Bego Mario Garde <pixolin@pixolin.de>
 * Author URI: https://pixolin.de
 * Version: 0.1
 * License: GPL2
 * Text Domain: anrede
 * Domain Path: /languages
 */

/*
	Copyright (C) 2018  Bego Mario Garde <pixolin@pixolin.de>

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'the_content', 'pix_persoenliche_anrede', 10, 1 );


function pix_persoenliche_anrede( $content ) {
	$out = '';

	$current_user = wp_get_current_user();

	if ( ! $current_user->exists() & ! is_front_page() ) {
		return;
	}
	if ( $current_user->last_name ) {
		switch ( get_user_meta( $current_user->ID, $key = 'anrede', $single = true ) ) {
			case 1:
				$out = '<p>Sehr geehrte Frau ' . $current_user->last_name . ',</p>' . $content;
				break;
			case 2:
				$out = '<p>Sehr geehrter Herr ' . $current_user->last_name . ',</p>' . $content;
				break;
			default:
				$out = '<p>Sehr geehrte Damen und Herren,</p>' . $content;
		}
		return $out;
	} else {
		return $content;
	}
}

function pix_usermeta_form_field_anrede( $user ) {
	$anrede = esc_attr( get_user_meta( $user->ID, 'anrede', true ) );
	$name   = esc_attr( $user->last_name );
	?>
	<h3>Anrede</h3>
	<table class="form-table">
		<tr>
			<th>
				<label for="anrede">Anrede</label>
			</th>
			<td>
				<select name="anrede">
					<option value="" <?php selected( $anrede, '' ); ?>>Sehr geehrte Damen und Herren,</option>
					<option value="1" <?php selected( $anrede, 1 ); ?>>Sehr geehrte Frau <?php echo $name; ?>,</option>
					<option value="2" <?php selected( $anrede, 2 ); ?>>Sehr geehrter Herr <?php echo $name; ?>,</option>
				</select>
				<p class="description">
					Bitte Anrede auswählen.
				</p>
			</td>
		</tr>
	</table>
	<?php
}

function pix_usermeta_form_field_anrede_update( $user_id ) {
	// check that the current user have the capability to edit the $user_id
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	// create/update user meta for the $user_id
	return update_user_meta(
		$user_id,
		'anrede',
		$_POST['anrede']
	);
}

add_action(
	'edit_user_profile',
	'pix_usermeta_form_field_anrede'
);

add_action(
	'show_user_profile',
	'pix_usermeta_form_field_anrede'
);

add_action(
	'personal_options_update',
	'pix_usermeta_form_field_anrede_update'
);

add_action(
	'edit_user_profile_update',
	'pix_usermeta_form_field_anrede_update'
);


function pix_settings_init() {
	// register a new setting for "reading" page
	register_setting( 'writing', 'pix_anrede' );

	// register a new section in the "reading" page
	add_settings_section(
		'pix_settings_section',
		'Startseite für Abonnenten',
		'pix_settings_section_cb',
		'reading'
	);

	// register a new field in the "wporg_settings_section" section, inside the "reading" page
	add_settings_field(
		'pix_settings_field',
		'Weiterleitung',
		'wporg_settings_field_cb',
		'reading',
		'pix_settings_section'
	);
}

add_action( 'admin_init', 'pix_settings_init' );


function pix_settings_section_cb() {
	echo '<p>Sollen Abonennten nach der Anmeldung automatisch auf die Startseite weitergeleitet werden?</p>';
}

// field content cb
function wporg_settings_field_cb() {
	// get the value of the setting we've registered with register_setting()
	$setting = get_option( 'pix_anrede' );
	// output the field
	?>
	<input type='checkbox' name='pix_anrede' <?php checked( $setting, 1 ); ?> value='1'>
	<?php
}

add_filter( 'login_redirect', 'pix_anrede_redirect' );
function pix_anrede_redirect() {
	if ( 1 == get_option( 'pix_anrede' ) ) {
		return get_site_url();
	}
}

