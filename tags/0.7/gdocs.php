<?php
/*
Plugin Name: Inline Google Docs
Plugin URI: http://code.google.com/p/inline-google-docs/
Description: Inline Google Docs allows the user to display contents of his google documents in his pages/posts, using shortcode for markup. Requires PHP 5.
Version: 0.7
Author: Lim Jiunn Haur
Author URI: http://broken-watch.info/
*/

/*  Copyright 2008  Seven Lim Jiunn Haur  (email : codex.is.poetry@gmail.com)

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

/**
 * Plugin File, GDocs Wordpress Plugin
 *
 * Verifies PHP version. If insufficient, kills execution. Else, includes and executes {@link inc/gdocs.php}.
 *
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version		0.7
 * @package		gdocs
 */

if (strnatcmp (phpversion(), "5.0.0") < 0){
	// old PHP
	?>
	<div class='error' id='message' style='background-color: rgb(255, 170, 150);'><p><?php _e("This plugin requires PHP 5 and above to work. Your server currently runs PHP version " . phpversion() . ". Please badger your host to update the software.") ?></p></div>
	<?php
	die();
}else {
	try {
		/**
		 * Load and run the main execution file
		 */
		require_once ('inc/gdocs.php');
	}catch (Exception $e){
		file_put_contents (dirname(__FILE__) . '/cache/error.log.php', (String)$e, FILE_APPEND);
		?>
		<div class='error' id='message' style='background-color: rgb(255, 170, 150);'><p><?php _e("A possible error has been detected. Please contact plugin author with <a href='" . get_bloginfo('url') . "/wp-content/plugins/inline-google-docs/cache/error.log.php'><em>error.log.php</em></a> for assistance.") ?></p></div>
		<?php
	}
	
	// add installation hooks
	register_activation_hook (__FILE__, 'gdocs_install');
	register_deactivation_hook (__FILE__, 'gdocs_uninstall');
}
?>