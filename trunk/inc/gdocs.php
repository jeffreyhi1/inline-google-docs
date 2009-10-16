<?php
/**
 * Main execution file, GDocs Wordpress plugin
 *
 * This file contains all the hooks and actions required for the abovementioned plugin.
 * Includes:
 * - activation and deactivation handlers
 * - shortcode handler
 * - options (plugin configuration) page handler
 * - error handler
 *
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version		0.9
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @todo
 * - presentations, files, others, forms
 * - multipage feeds
 * - image caching
 */

/*** REMOVE THIS ***/
/*require_once('FirePHPCore/FirePHP.class.php');
ob_start ();
$firephp = FirePHP::getInstance (true);
$firephp->setEnabled (true);
$firephp->registerErrorHandler($throwErrorExceptions=true);
$firephp->registerExceptionHandler();*/
/******************/

/**
 * @name	GDOCS_DOCUMENT
 */
define ('GDOCS_DOCUMENT', 'doc');
/**
 * @name	GDOCS_SPREADSHEET
 */
define ('GDOCS_SPREADSHEET', 'st');
/**
 * @name	GDOCS_CELL
 */
define ('GDOCS_CELL', 'cell');
/**
 * Absolute path to this plugin
 * 
 * Plugin folder = basename (GDOCS_PATH)
 * @name	GDOCS_PATH
 */
define ('GDOCS_PATH', realpath (ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . "/.."));
/**
 * Web path to this plugin
 * @name	GDOCS_ADDRESS
 */
define ('GDOCS_ADDRESS', get_bloginfo ('wpurl') . '/wp-content/plugins/' . basename (GDOCS_PATH));

// add Zend library to path
$path = GDOCS_PATH . "/library";
set_include_path (get_include_path () . PATH_SEPARATOR . $path);

/**
 * Load the Zend library to use classes from the Zend_Gdata and Zend_Http packages
 *
 * Uses autoloading (or lazy-loading) to automatically load all required classes.
 * This autoloader only autoloads files in the Zend namespace.
 * @link	http://framework.zend.com/apidoc/core/
 */
require_once ('Zend/Loader/Autoloader.php');
$autoloader = Zend_Loader_Autoloader::getInstance();

// load all other gdocs classes
require_once ('gclient.php');
require_once ('gcache.php');
require_once ('gdb.php');
require_once ('gdisplay.php');
require_once ('gfeed.php');
require_once ('gelement.php');
require_once (GDOCS_PATH . "/ajax-functions.php");

/**
 * Shortcode handler
 *
 * Determines if the gdoc is a spreadsheet or a document, and
 * calls the corresponding display functions.
 * @param	array	$atts		contains attributes given by user in shortcode
 * @param	string	$content	the raw string enclosed by the shortcode tags (optional, ignored)
 * @return	string	$html		html-formatted contents of the gdoc to be displayed in place of the shortcode
 */
function gdocs_display ($atts, $content=NULL){

	// check parameter exists
	if (is_null ($atts['type'])) return $content;
	
	try {
		return (String)GElement::getElement ($atts);
	} catch (Exception $e) {
		// any error at all, return text
		return $content;
	}
	
}

/**
 * Hook for admin initialization
 */
function gdocs_admin (){
	if (function_exists ('register_setting')){
		$options = array ("gdocs_user","gdocs_pwd","gdocs_proxy_host","gdocs_proxy_port","gdocs_proxy_user","gdocs_proxy_pwd", "gdocs_cache_expiry", "gdocs_style_dir");
		foreach ($options as $option){
			register_setting ('gdocs-options', $option);
		}
	}
}

/**
 * Hook for options page
 */
function gdocs_options (){

	if (function_exists ('add_options_page')){
		// add an options page that is printed by gdocs_options_setup
		add_options_page ('Inline Google Docs', 'Inline Google Docs', 'manage_options', basename(__FILE__), 'gdocs_options_setup');
		
	} 

}

/**
 * Setup and print options page
 */
function gdocs_options_setup (){

	// check if cache is writable
	$x = new GCache ('x');
	if (!$x->isWritable()) GDisplay::printCacheNotWritableError ();
	
	// check if error log is writable
	$error_log = GDOCS_PATH . '/cache/error.log.php';
	if (!is_writable ($error_log)){
		GDisplay::printLogNotWritableError ();
	}

	GDisplay::printHead ();	
	GDisplay::printLogin ();
	GDisplay::printCache ();
	GDisplay::printStyle ();
	GDisplay::printProxy ();
	GDisplay::printFoot ();
	
	GDisplay::printDocList ($docFeed, $docs);
	GDisplay::printStList ($stFeed, $gsClient, $docs);

}

/**
 * Logs suspicious, unknown errors to file.
 * @param	Exception	$e	Exception to log to file
 */
function gdocs_error (Exception $e){
	$error_log = GDOCS_PATH . '/cache/error.log.php';
	@file_put_contents ($error_log, (String)$e . "\r\n", FILE_APPEND);
}

/**
 * Prints postbox
 */
function gdocs_helper (){
	GDisplay::printHelper (GDB::read());
}

/**
 * Install plugin
 *
 * Adds configuration options
 * Creates table in Wordpress database
 */
function gdocs_install (){
	
	// login credentials
	add_option ('gdocs_user');
	add_option ('gdocs_pwd');
	
	// proxy config
	add_option ('gdocs_proxy_host');
	add_option ('gdocs_proxy_port');
	add_option ('gdocs_proxy_user');
	add_option ('gdocs_proxy_pwd');
	
	// cache config
	add_option ('gdocs_cache_expiry', 0);
	
	// stylesheets config
	add_option ('gdocs_style_dir');
	
	// create table in DB to store shortcode tags
	try {
		GDB::drop ();
		GDB::create ();
	}catch (GDB_Exception $e){
		gdocs_error ($e);
	}
}

/**
 * Uninstall plugin
 *
 * Removes configuration options
 * Drops table from Wordpress database
 */
function gdocs_uninstall (){

	delete_option ('gdocs_user');
	delete_option ('gdocs_pwd');
	delete_option ('gdocs_proxy_host');
	delete_option ('gdocs_proxy_port');
	delete_option ('gdocs_proxy_user');
	delete_option ('gdocs_proxy_pwd');
	delete_option ('gdocs_cache_expiry');
	delete_option ('gdocs_style_dir');
	
	// remove table from DB
	GDB::drop ();
}

######################## BEGIN Global Execution Space ################################

// add actions
add_action ('admin_menu', 'gdocs_options');
add_action ('admin_init', 'gdocs_admin');
add_action ('edit_form_advanced', 'gdocs_helper');
add_action ('edit_page_form', 'gdocs_helper');
add_action ('wp_ajax_gdocs_ajax_handler', 'gdocs_ajax_handler');

// add shortcode
add_shortcode ('gdocs', 'gdocs_display');

// add admin javascript (gdocs.js, prototype)
global $pagenow;
$wp_pages = array ('post.php', 'post-new.php', 'page.php', 'page-new.php');
if (in_array ($pagenow, $wp_pages)){
	wp_enqueue_script ('gdocs', '/wp-content/plugins/' . basename (GDOCS_PATH) . '/inc/js/gdocs.js.php?url=' . GDOCS_ADDRESS, array ('prototype'));
}else if ($pagenow == 'options-general.php' && $_GET['page'] === 'gdocs.php'){
	wp_enqueue_script ('gdocs', '/wp-content/plugins/' . basename (GDOCS_PATH) . '/inc/js/gdocs-options.js.php?url=' . GDOCS_ADDRESS, array ('prototype'));
}

// add post/page view javascript (tablesorter.js, jquery)
if (!is_admin()){
	wp_enqueue_script ('gdocs-tablesorter', '/wp-content/plugins/' . basename (GDOCS_PATH) . '/inc/js/jquery.tablesorter.js', array ('jquery'));
}
######################## END Global Execution Space ##################################
?>