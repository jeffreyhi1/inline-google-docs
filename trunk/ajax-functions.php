<?php
/**
 * AJAX Functions, GDocs Wordpress plugin
 *
 * This file contains all the ajax functions required by this plugin.
 * Accessed by user from post/page edit form and plugin settings form.
 * Returns value to browser via http headers.
 *
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @since		0.5
 * @version		0.7
 */

/**
 * Loads Wordpress configuration options.
 */
require_once ('../../../wp-config.php');
/**
 * Loads WP database functions.
 */
require_once ('../../../wp-includes/wp-db.php');
/**
 * Loads WP formatting functions.
 */
require_once ('../../../wp-includes/formatting.php');

##################### BEGIN Global Execution Space ################################

/**
 * Data regarding current user
 * @global	mixed	object containing user data
 * @name	$userdata
 */
global $userdata;
get_currentuserinfo();

if ($userdata->user_level < 8){
	// insufficient rights
	header ('HTTP/1.0 403 Forbidden');
	$error = 'You do not have sufficient access rights to use this plugin.';
	header('X-JSON: (' . json_encode ($error) . ')');
	return NULL;
}

// check if action is set
$action = NULL;
if (isset($_GET['action'])){
	$action = $_GET['action'];
	// execute
	$func = 'gdocs_' . $action;
	try {
		$func ();
	} catch (Zend_Http_Client_Adapter_Exception $e){
		// connnection problem , probably proxy
		$error = "A connection error has occurred. Are you behind a proxy?";
		header ('HTTP/1.0 400 Bad Request');
		header('X-JSON: (' . json_encode ($error) . ')');
	} catch (Zend_Gdata_App_CaptchaRequiredException $e){
		// google requested captcha
		$error = "Google requested CAPTCHA verification. Please try again later.";
		header ('HTTP/1.0 400 Bad Request');
		header('X-JSON: (' . json_encode ($error) . ')');
	} catch (Zend_Gdata_App_AuthException $e) {
		// google docs login problem
		if (!get_option ('gdocs_user') || !get_option ('gdocs_pwd')){ 
			$error = "Please enter your username and password in the plugin configuration form under <em>Settings</em>.";
		}else { 
			$error = "The plugin was unable to login to the Google service. Did you give us the wrong password/username?";
		}
		header ('HTTP/1.0 400 Bad Request');
		header('X-JSON: (' . json_encode ($error) . ')');
	} catch (Zend_Gdata_App_HttpException $e){
		// HTTP Error
		$error = "A HTTP error has occurred: " . $e->getMessage() . ". Please contact the plugin author with <a href='" . get_bloginfo('url') . "/wp-content/plugins/inline-google-docs/cache/error.log.php'><em>error.log.php</em></a> for assistance.";
		header ('HTTP/1.0 502 Bad Gateway');
		header('X-JSON: (' . json_encode ($error) . ')');
		gdocs_error ($e);
	} catch (Exception $e){
		$error = "An error has occurred: " . $e->getMessage() . ". Please contact the plugin author with <a href='" . get_bloginfo('url') . "/wp-content/plugins/inline-google-docs/cache/error.log.php'><em>error.log.php</em></a> for assistance.";
		header ('HTTP/1.0 400 Bad Request');
		header('X-JSON: (' . json_encode ($error) . ')');
		gdocs_error ($e);
	}
}else {
	// missing paramter
	$error = "Required parameters missing.";
	header ('HTTP/1.0 400 Bad Request');
	header('X-JSON: (' . json_encode ($error) . ')');
	return NULL;
}

##################### END Global Execution Space ##################################
 
/**
 * Updates list of Google Documents and Spreadsheets
 * 
 * Connects to Google and retrieves list of documents and spreadsheets.
 * Updates table in Wordpress database.
 * Returns data to browser in a JSON variable via http headers.
 */
function gdocs_update_list (){
	
	// get Google Documents client
	$gdClient = GClient::getInstance(GDOCS_DOCUMENT);
	
	// initialize collector stack
	$docs = array();
	
	// update document list
	gdocs_update_documents ($gdClient, &$docs);
	
	// get Google Spreadsheets client
	$gsClient = GClient::getInstance(GDOCS_SPREADSHEET);
	
	// update spreadsheet list
	gdocs_update_sts ($gdClient, $gsClient, &$docs);
	
	$json = array ('docs' => $docs);
	
	// update DB
	try {
		GDB::write ($docs);
	} catch (GDB_Exception $e){
		$json['db_error'] = $e->getMessage();
		gdocs_error ($e);
	}
	
	// return json data
	header('X-JSON: (' . json_encode ($json) . ')');
	
}

/**
 * Retrieves document list
 * @param	GClient	$gdClient	gdata client, used to query the Google API
 * @param	array	$docs		array used to collect all document entries
 */
function gdocs_update_documents (GClient_Doc $gdClient, array $docs){

	// get documents feed
	$feed = $gdClient->getDocs ();
	
	foreach ($feed as $entry){
		// push to stack
		$docs[] = $entry;
	}
	
}

/**
 * Retrieves spreadsheet list
 * @param	GClient_Doc	$gdClient	client, used to retrieve list of spreadsheets
 * @param	GClient_St	$gsClient	client, used to retrieve list of worksheets
 * @param	array		$docs		array used to collect all worksheet entries
 */
function gdocs_update_sts (GClient_Doc $gdClient, GClient_St $gsClient, array $docs){

	// get spreadsheets feed
	$feed = $gdClient->getSpreadsheets ();
	
	foreach ($feed as $entry){
		
		// get worksheets feed
		$wtFeed = $gsClient->getWorksheets ($entry->main_id);
				
		if ($wtFeed) foreach ($wtFeed->entries as $wtEntry){
		
			// extract worksheet id
			$wtId = split ('/', $wtEntry->getId()->getText());
			$entry->sub_id = $wtId[8];
			
			// extract worksheet title
			$entry->sub_title = $wtEntry->getTitleValue ();
			
			// push to stack
			$docs[] = clone $entry;
			
		}
	
	}
}

?>