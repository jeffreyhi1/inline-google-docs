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

header ('Cache-Control: no-cache');

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
		@$func ();
	} catch (Zend_Http_Client_Adapter_Exception $e){
		// connnection problem , probably proxy
		$error = "A connection error has occurred. Are you behind a proxy?";
		header ('HTTP/1.0 400 Bad Request');
		header('X-JSON: (' . json_encode ($error) . ')');
	} catch (Zend_Gdata_App_CaptchaRequiredException $e){
		// google requested captcha
		$error = GDisplay::printCaptchaError ($e);
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
		$error = "A HTTP error has occurred: " . $e->getMessage() . ". Please contact the plugin author with <a href='" . GDOCS_ADDRESS . "/cache/error.log.php'><em>error.log.php</em></a> for assistance.";
		header ('HTTP/1.0 502 Bad Gateway');
		header('X-JSON: (' . json_encode ($error) . ')');
		@gdocs_error ($e);
	} catch (Exception $e){
		$error = "An error has occurred: " . $e->getMessage() . ". Please contact the plugin author with <a href='" . GDOCS_ADDRESS . "/cache/error.log.php'><em>error.log.php</em></a> for assistance.";
		header ('HTTP/1.0 400 Bad Request');
		header('X-JSON: (' . json_encode ($error) . ')');
		@gdocs_error ($e);
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
	$doc_count = sizeof ($docs);
	
	// get Google Spreadsheets client
	$gsClient = GClient::getInstance(GDOCS_SPREADSHEET);
	
	// update spreadsheet list
	gdocs_update_sts ($gdClient, $gsClient, &$docs);
	$st_count = sizeof ($docs) - $doc_count;
	
	header ('HTTP/1.0 200 OK');
	header ('Content-Type: application/x-json');
	
	$json = array ('dc' => $doc_count, 'sc' => $st_count);
	
	// update DB
	try {
		GDB::write ($docs);
	} catch (GDB_Exception $e){
		$json['error'] = $e->getMessage();
		gdocs_error ($e);
	}
	
	header ('X-JSON: (' . json_encode ($json) . ')');
	
	echo json_encode ($docs);
	
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

	$obj = strpos ($_SERVER['HTTP_REFERER'], 'options-general.php') === FALSE ? FALSE : TRUE;

	// get spreadsheets feed
	$feed = $gdClient->getSpreadsheets ();
	
	foreach ($feed as $entry){
		
		try {
			// get worksheets feed
			$wtFeed = $gsClient->getWorksheets ($entry->main_id);
					
			if ($wtFeed){ 
			
				$worksheets = array ();
				$sub_ids = array ();
				foreach ($wtFeed->entries as $wtEntry){
				
					// extract worksheet id
					$wtId = split ('/', $wtEntry->getId()->getText());
					$entry->sub_id = $wtId[8];
					
					// extract worksheet title
					$entry->sub_title = $wtEntry->getTitleValue ();
					
					// push to stack
					$worksheets[] = clone $entry;
					$sub_ids[] = $entry->sub_id;
					
				}
				
				if ($obj && sizeof ($worksheets) >= 1){
					$ele = $worksheets[0];
					$ele->sub_id = implode (', ', $sub_ids);
					unset ($ele->sub_title);
					$docs[] = $ele;
				}else {
					$docs = array_merge ($docs, $worksheets);				
				}
				
			}
			
		}catch (Zend_Gdata_App_HttpException $e){
			$res = $e->getResponse();
			if ($res->getStatus() === 403){ // Forbidden
				// Spreadsheet access denied. Google bug, this is a workaround
				continue;
			}
			throw $e;
		}

	}
	
}

/**
 * Verify CAPTCHA
 */
function gdocs_verify (){
	$captcha = NULL;
	$token = NULL;
	if (isset ($_POST['gdocs_captcha']) && isset ($_POST['gdocs_token'])){
		$captcha = $_POST['gdocs_captcha']; $token = $_POST['gdocs_token'];
		$gdClient = GClient::getInstance(GDOCS_DOCUMENT, $token, $captcha);
		gdocs_update_list ();
	}else {
		// missing paramter
		$error = "Required parameters missing.";
		header ('HTTP/1.0 400 Bad Request');
		header('X-JSON: (' . json_encode ($error) . ')');
		return NULL;
	}
}
?>
