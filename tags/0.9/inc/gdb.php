<?php
/**
 * Database class, GDocs Wordpress plugin
 *
 * Handles database access
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
 * GDB class
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
 * @version		0.9
 */
class GDB {

	/**#@+
	 * @static
	 */
	 
	/**
	 * Creates table in DB
	 * @global	mixed	used to access the database
	 * @return	boolean	$success	TRUE if create success
	 */
	public static function create (){
	
		// DB interface
		global $wpdb;
		
		$tbl = $wpdb->prefix . "gdocs";
			
		// create GDOCS (id, title, type, main_id, sub_id)
		$sql = "CREATE TABLE IF NOT EXISTS " . $tbl . " (
			title TINYTEXT NOT NULL,
			sub_title TINYTEXT NULL,
			type ENUM('document', 'spreadsheet') NOT NULL DEFAULT 'document',
			main_id VARCHAR(50) NOT NULL,
			sub_id VARCHAR(50) NULL,
			INDEX(type),
			PRIMARY KEY(main_id, sub_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		if ($wpdb->query ($sql) === FALSE) throw new GDB_Exception ("The plugin was unable to connect to the database.");
		
		return TRUE;
		
	}
	
	/**
	 * Drops table
	 * @global	mixed	used to access the database
	 */
	public static function drop (){
		global $wpdb;
		$wpdb->query ("DROP TABLE IF EXISTS " . $wpdb->prefix . "gdocs");
	}

	/**
	 * Reads data from database
	 * @return	mixed	$results data read from database
	 * @global	mixed	used to access the database
	 */
	public static function read (){
	
		global $wpdb;
		
		// retrieve list of documents and spreadsheets from DB
		$results = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}gdocs", 'OBJECT');
		
		return $results;
		
	}
	
	/**
	 * Writes data to database
	 * @global	mixed	used to access the database
	 * @param	array	$dataArr	array of data to write to database
	 */
	public static function write (array $dataArr){
	
		// if no data, stop here	
		if (is_null($dataArr[0])) return;
	
		global $wpdb;
		
		// clear table
		$wpdb->query ("TRUNCATE TABLE {$wpdb->prefix}gdocs");
		
		$query = "INSERT INTO {$wpdb->prefix}gdocs (title, sub_title, type, main_id, sub_id) VALUES ";
		
		// INSERT INTO tbl VALUES (...), (...), (...)
		foreach ($dataArr as $data){
			$sub_id = isset ($data->sub_id) ? $wpdb->escape ($data->sub_id) : NULL; // documents don't have sub_id
			$sub_title = isset ($data->sub_title) ? $wpdb->escape ($data->sub_title) : NULL; // documents don't have sub_title
			$query .= "
				('" . 
				$wpdb->escape ($data->title) . "', '" .
				$sub_title . "', '" .
				$data->type . "', '".  // don't worry, this is safe (enum)
				$wpdb->escape ($data->main_id) . "', '" . 
				$sub_id . "'),";
		}
		
		// remove last comma
		$query = substr ($query, 0, strlen ($query)-1);
		
		if ($wpdb->query ($query) === FALSE) throw new GDB_Exception ("The plugin was unable to connect to the database.");
	
	}
	
	/**
	 * Checks if database is OK
	 * @global	mixed	used to access the database
	 * @return	bool	$writable	TRUE if OK, FALSE on failure
	 */
	public static function isWritable (){
		global $wpdb;
		$writable = false;
		$query = "INSERT INTO {$wpdb->prefix}gdocs (title, type, main_id, sub_id) VALUES ('title', 'document', 'inline-google-docsxxx3541986513', 'inline-google-docsxxxasdf8451')";
		if ($wpdb->query ($query) !== FALSE) $writable = true;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}gdocs WHERE main_id='inline-google-docsxxx3541986513' AND sub_id='inline-google-docsxxxasdf8451'");
		return $writable;
	}
	
	/**#@-*/

}

/**
 * GDB Exception class
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
 * @version		0.7
 */
class GDB_Exception extends Exception {
}

?>