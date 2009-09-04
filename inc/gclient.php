<?php
/**
 * Client class, GDocs Wordpress plugin
 * 
 * Handles authentication and proxy settings
 *
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
 * @version		0.9
 */
 
/**
 * GClient class
 *
 * Uses singleton design pattern, so that not more than one
 * client exists at any one time. Clients are used to query
 * the Google Data API to retrieve the appropriate feeds.
 *
 * This class also uses the factory design pattern to create 
 * the child GClient_Doc and GClient_St classes.
 *
 * @abstract
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
 * @version		0.9
 */
abstract class GClient extends Zend_Gdata{
	
	/**
	 * Domain to connect to
	 * @var string
	 */
	const DOMAIN = 'google.com';
	
	/**
	 * Array of singletons
	 * @static
	 * @var	array
	 */
	private static $_singleton = array(GDOCS_DOCUMENT=> NULL, GDOCS_SPREADSHEET=> NULL, GDOCS_RAW=> NULL);
	
	/**
	 * Class constructor
	 *
	 * Creates authenticated Google client. Note that this is never called directly
	 * from public scope, but via the singleton function {@link GClient::getInstance()}.
	 *
	 * @method	GClient		GClient()	GClient($service)	creates and initializes new GClient object
	 * @param	string		$service	type of service access (either St, Doc, or Raw)
	 * @see		GClient::getInstance()
	 * @return	GClient
	 */
	public function __construct ($service, $token=NULL, $captcha=NULL){
		// get login credentials
		$user = get_option ('gdocs_user');
		$pwd = get_option ('gdocs_pwd');
		
		// create Zend Http Client
		$httpClient = Zend_Gdata_ClientLogin::getHttpClient($user, $pwd, $service, NULL, NULL, $token, $captcha);		
		$this->setProxy ($httpClient);
		
		// create Zend Gdata client
		parent::__construct ($httpClient);

	}
	
	/**
	 * Configures proxy settings for the Zend Http Client
	 * @param	Zend_Http_Client	&$httpClient	Zend Http Client
	 */
	private function setProxy (Zend_Http_Client &$httpClient){
	
		$proxy_host = get_option ('gdocs_proxy_host');
		
		// if user specified a proxy
		if ($proxy_host){
			// set proxy
			$httpClient->setConfig (array (
				'adapter' => 'Zend_Http_Client_Adapter_Proxy',
				'proxy_host' => $proxy_host,
				'proxy_port' => get_option ('gdocs_proxy_port'),
				'proxy_user' => get_option ('gdocs_proxy_user'),
				'proxy_pass' => get_option ('gdocs_proxy_pwd')
			));
		}
	
	}
	
	/**
	 * Factory function
	 *
	 * Checks if a Gdata client already exists.
	 * @param	string	$service	type of service access (either St, Doc)
	 * @return	GClient	$client		GClient object
	 * @static
	 */
	public static function getInstance ($service, $token=NULL, $captcha=NULL){
		if (!in_array ($service, array_keys (self::$_singleton))) throw new Exception();
		if (isset($token) || is_null(self::$_singleton[$service])){
			$class = 'GClient_' . ucfirst ($service);
			self::$_singleton[$service] = new $class ($token, $captcha);
		}
		return self::$_singleton[$service];
	}

}

/**
 * GClient_Doc class
 * 
 * For Google Documents (includes text docs and spreadsheets)
 * Used to get the entire list of documents and spreadsheets the user has in his/her account.
 * @package		gdocs
 * @subpackage	gdocs.inc
 */
class GClient_Doc extends GClient {
	
	/**
	 * Class constructor
	 * @method	GClient_Doc	GClient_Doc()	GClient_Doc()	creates and initializes new GClient object for Google Documents
	 * @return	GClient_Doc
	 */
	public function __construct ($token=NULL, $captcha=NULL){
		parent::__construct (Zend_Gdata_Docs::AUTH_SERVICE_NAME, $token, $captcha);
	}
	
	/**
	 * Get list of user's documents
	 * @return	GFeed	$feed	Iterable feed containing list of documents 
	 */
	public function getDocs (){
		return new GFeed ($this->getFeed ("http://docs." . parent::DOMAIN . "/feeds/documents/private/full/-/document/"), 'document');
	}
	
	/**
	 * Get list of user's spreadsheets
	 * @return	GFeed	$feed	Iterable feed containing list of spreadsheets 
	 */
	public function getSpreadsheets (){
		return new GFeed ($this->getFeed ("http://docs." . parent::DOMAIN . "/feeds/documents/private/full/-/spreadsheet/"), 'spreadsheet');
	}
	
	/**
	 * Retrieve a particular document
	 * @param	string	$id		ID of document to retrieve
	 * @return	string	$html	HTML of document, with image url's corrected
	 */
	public function getDoc ($id){
		return $this->rescueImg($this->performHttpRequest ('GET', "http://docs." . parent::DOMAIN . "/RawDocContents?docID={$id}&action=fetch&justBody=true&revision=_latest&editMode=false")->getBody());
	}
	
	/**
	 * Parse and correct img tags
	 * @param	$html	Document HTML
	 * @return	$html	Modified Document HTML
	 */
	private function rescueImg ($html){
		
		$pattern = "/<img\s[^>]*src\s?=\s?([\"\']{1})([^\" >]+)\\1[^>]*(\s\/)?>/siU";
		preg_match_all ($pattern, $html, $matches);
		
		for ($i=0; $i<count($matches[0]); $i++){
			$tag = $matches[0][$i];
			$url = $matches[2][$i];
			
			// skip drawings
			if (strpos ($url, 'drawings') !== FALSE) continue;
			
			$new_url = "http://docs." . parent::DOMAIN . "/" . $url;
			$new_tag = str_replace ($url, $new_url, $tag);
			$html = str_replace ($tag, $new_tag, $html);
		}
		
		return $html;
		
	}

}

/**
 * GClient_St class
 *
 * For Google Spreadsheets
 * @package		gdocs
 * @subpackage	gdocs.inc
 */
class GClient_St extends GClient {
	
	/**
	 * Class constructor
	 * @method	GClient_St	GClient_St()	GClient_St()	creates and initializes new GClient object for Google Spreadsheets
	 * @return	GClient_St
	 */
	public function __construct ($token=NULL, $captcha=NULL){
		parent::__construct (Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME, $token, $captcha);
	}
	
	/**
	 * Retrieve all worksheets in a spreadsheet
	 * @return	Zend_Gdata_Feed	$feed	Feed containing worksheet entries and spreadsheet data
	 * @param	string			$st_id	Spreadsheet ID
	 */
	public function getWorksheets ($st_id){
		return $this->getFeed ("http://spreadsheets." . parent::DOMAIN . "/feeds/worksheets/{$st_id}/private/full");
	}
	
	/**
	 * Retreive all lists in a worksheet
	 * @param	string			$st_id	Spreadsheet ID
	 * @param	string			$wt_id	Worksheet ID
	 * @return	Zend_Gdata_Feed	$feed	Feed containing list entries and worksheet data
	 */
	public function getLists ($st_id, $wt_id){
		return $this->getFeed ("http://spreadsheets." . parent::DOMAIN . "/feeds/list/{$st_id}/{$wt_id}/private/full");
	}
	
	/**
	 * Retrieve a particular cell
	 * @param	string								$st_id	Spreadsheet ID
	 * @param	string								$wt_id	Worksheet ID
	 * @return	Zend_Gdata_Spreadsheets_CellEntry	$entry	CellEntry object containing cell data
	 */
	public function getCell ($st_id, $wt_id, $cell_id){
		$query = new Zend_Gdata_Spreadsheets_CellQuery ();
		$query->setSpreadsheetKey ($st_id);
		$query->setWorksheetId ($wt_id);
		
		// parse cell id
		if (strlen ($cell_id) != 4) throw new Exception ();
		$row = $cell_id[1];
		$col = $cell_id[3];
		
		$query->setMaxRow($row)->setMinRow($row)->setMaxCol($col)->setMinCol($col);
		
		return $this->getFeed ($query->getQueryUrl());
	}
	
}

?>
