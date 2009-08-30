<?php
/**
 * Cache class, GDocs Wordpress plugin
 *
 * Handles cache access
 *
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
 * @version		0.7
 */

/**
 * GCache class
 *
 * All cache operations are carried out via this class.
 * It keeps a copy of the Google Doc/Spreadsheet in a simple html file
 * when the document is first displayed in a post or on a page. On
 * subsequent page requests, the document is loaded from the cache onto
 * the page, which is much faster than downloading the document data from
 * the Google server.
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
 * @version		0.7
 */
class GCache {
	
	/**
	 * Cache directory
	 * @var string
	 */
	private $dir;
	
	/**
	 * Name of HTML file to write to
	 * @var string
	 */
	private $file;
	
	/**
	 * Class constructor
	 * @method	GCache	GCache()	GCache ($doc_id) creates and initializes new GCache object (for Google Document)
	 * @method	GCache	GCache()	GCache ($st_id, $wt_id) creates and initializes new GCache object (for Google Document)
	 * @param	string	$main_id	Document ID or Spreadsheet ID
	 * @param	string	$sub_id		Worksheet ID
	 * @return	GCache
	 */
	public function __construct (){
		$this->dir = GDOCS_PATH . '/cache/';
		$this->file = $this->getPath (func_get_args());
	}

	/**
	 * Checks if the doc/spreadsheet exists in cache. If so, retrieve.
	 * @param 	string	&$html		passed by reference, used to store the HTML retrieved from cache
	 * @return	boolean	$cache_hit	TRUE if cache hit, FALSE if miss
	 */
	public function read (&$html){
	
		// if HIT, return true, set $html
		if (file_exists ($this->file) && (time () - get_option ('gdocs_cache_expiry') * 60 < filemtime ($this->file))) {
			/*
			 * 1. Check if the file exists in cache
			 * 2. Check if file has expired
			 */
			$tbl = file_get_contents ($this->file);
			if ($tbl) {
				// read OK
				$html .= $tbl;	 
				return true;
			}
		
		}
		
		// if MISS or read failure, return false
		return false;
	
	}
	
	/**
	 * Check if caching is enabled. If so, create and write cache file.
	 *
	 * Throws GCache_Exception if file operations are unsuccessful.
	 * @param	string	$html		contents of Google Doc/Spreadsheet, to be written to cache
	 * @return	boolean	$success	TRUE if write success, FALSE if cache disabled.
	 */
	public function write ($html){
		
		// check that caching is enabled
		if (!get_option ('gdocs_cache_expiry')) return FALSE;
		
		// check that cache is writable
		if (!$this->isWritable()) throw new GCache_Exception ('Cache directory is not writable.', GCache_Exception::DIR_NOT_WRITABLE);
		
		$cacheFile = @fopen ($this->file, 'wb');
		if ($cacheFile === FALSE) throw new GCache_Exception ('Unable to create new cache file.', GCache_Exception::DIR_NOT_WRITABLE);
		if (@fwrite ($cacheFile, $html) === FALSE) throw new GCache_Exception ('Unable to write to cache file.', GCache_Exception::FILE_NOT_WRITABLE);
		fclose ($cacheFile);
	
	}
	
	/**
	 * Check that cache is writable
	 * @return	bool	$writable	TRUE if writable, FALSE if not
	 */
	public function isWritable (){
		return is_writable (dirname($this->file));
	}
	
	/**
	 * Assembles path to cache file
	 * @param	array	$atts	array of ID's of Google Doc/Spreadsheet
	 * @return	string	$path	path to the cache file
	 */
	private function getPath ($atts){
		switch (count($atts)){
			case 1:
				// Google Document
				return $this->dir . $this->cleanFilename (GDOCS_DOCUMENT . "_".$atts[0].".html");
				break;
			case 2:
				// Google Spreadsheet
				return $this->dir . $this->cleanFilename (GDOCS_SPREADSHEET . "_".$atts[0]."_".$atts[1].".html");
				break;
		}
	}
	
	/**
	 * Sanitizes filename
	 * @param	string	$filename	raw filename
	 * @return	string	$filename	clean filename
	 */
	private function cleanFilename ($filename){
		$reserved = preg_quote('\/:*?"<>|', '/');//characters that are  illegal on any of the 3 major OS's
		//replaces all characters up through space and all past ~ along with the above reserved characters
		return preg_replace("/([\\x7f-\\xff{$reserved}])/e", "_", $filename);
	}

	
}

/**
 * GCache_Exception class
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
 * @version		0.7
 */
class GCache_Exception extends Exception {
	const DIR_NOT_WRITABLE = 70;
	const FILE_NOT_WRITABLE = 71;
}
?>