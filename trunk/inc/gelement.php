<?php
/**
 * Element class, GDocs Wordpress plugin
 *
 * Handles shortcode parsing and document/spreadsheet/cell display
 *
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.9
 * @version		0.9
 */
 
/**
 * GElement class
 *
 * Uses factory and template pattern. Assembles and formats HTML to display document/spreadsheet
 * in <div> and <table> tags respectively.
 *
 * @abstract
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.9
 * @version		0.9
 */
abstract class GElement {

	protected $cache_session = NULL;
	protected $atts = NULL;
	protected $html = "";

	/**
	 * Returns appropriate element
	 * @static
	 * @param	array		$atts		shortcode attributes
	 * @return	GElement	$element	object
	 */
	public static function getElement ($atts){
		switch ($atts['type']){
			case 'document':
				return new GDoc ($atts);
				break;
			case 'spreadsheet':
				return new GSt ($atts);
				break;
			case 'cell':
				return new GCell ($atts);
				break;
			default:
				throw new Exception ();
		}
	}
	
	/**
	 * GElement constructor
	 *
	 * Parses shortcode attributes, assembles and formats HTML to display document/spreadsheet
	 * 
	 * @method	GElement	GElement()	GElement($atts)	creates and initializes new GElement object
	 * @param	array		$atts		Shortcode attributes
	 * @return	GElement	$obj		GElement object
	 */
	public function __construct ($atts){
		
		// init variables
		$this->atts = $atts;
		$this->html = "";

		// check required attributes exist
		$this->checkRequired ();
		
		// assemble
		$this->html .= $this->printStylesheets ();
		$this->html .= $this->printScripts ();
		$this->html .= $this->openTag ();
		
		try {
			// read from cache
			$this->html .= $this->printCache ();
			return;
		}catch (GElementException $e){
			// no cache or cache expired
			$body = $this->printBody ();
			$this->html .= $body;
						
			try {
				// write to cache
				$this->cache_session->write ($body);
			}catch (GCache_Exception $e){
				// cache not writable
				gdocs_error ($e);
			}
			
			return;
			
		}
	}

	protected abstract function checkRequired ();
	protected abstract function printScripts ();
	protected abstract function openTag ();
	protected abstract function printBody ();
	
	/**
	 * Print stylesheet <link>, not cached as the style attribute is variable
	 */
	private function printStylesheets (){
		return isset ($this->atts['style']) ? GDisplay::printStylesheet ($this->atts['style']) : "";
	}
	
	/**
	 * Try cache
	 */
	private function printCache (){
		if ($this->cache_session->read ($body)){
			return $body;
		}else throw new GElementException ('No Cache');
	}
	
	/**
	 * @method	string	string()	string()	displays GElement object as a string
	 * @return	string	$html		formatted HTML string containing Google Document/Spreadsheet
	 */
	private function __toString (){
		return $this->html;
	}

}

/**
 * Google Document
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.9
 * @version		0.9
 */
class GDoc extends GElement {

	/**
	 * Constructor
	 * @param	array	$atts	Shortcode attributes
	 * @return	GDoc	$obj	GDoc element
	 */
	public function __construct ($atts){
		$this->cache_session = new GCache ($atts['id']);
		parent::__construct ($atts);
	}
	
	/**
	 * Check required parameters exist, throws Exception otherwise
	 */
	protected function checkRequired (){
		if (is_null ($this->atts['id'])) throw new Exception ();
	}
	
	/**
	 * Dummy function
	 */
	protected function printScripts (){
		return "";
	}
	
	/**
	 * Print <div> tag
	 */
	protected function openTag (){
		return GDisplay::printDocTag ($this->atts['id'], $this->atts['style']);
	}
	
	/**
	 * Print document contents
	 */
	protected function printBody (){
		// get Http client
		$gClient = GClient::getInstance (GDOCS_DOCUMENT);
		
		// connect to Google, get feed, markup
		return $gClient->getDoc($this->atts['id']) . "\r\n</div>";
	}

}

/**
 * Google Spreadsheet
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.9
 * @version		0.9
 */
class GSt extends GElement{

	/**
	 * Constructor
	 * @param	array	$atts	Shortcode attributes
	 * @return	GSt		$obj	GDoc element
	 */
	public function __construct ($atts){
		$this->cache_session = new GCache ($atts['st_id'], $atts['wt_id']);
		parent::__construct ($atts);
	}
	
	/**
	 * Check required parameters exist, throws Exception otherwise
	 */
	protected function checkRequired (){
		if (is_null ($this->atts['wt_id']) || is_null ($this->atts['st_id'])) throw new Exception ();
	}
	
	/**
	 * Print tablesorter script
	 */
	protected function printScripts (){
		if ($this->atts['sort']){
			$params = $this->atts['sort'] === 'true' ? NULL : $this->atts['sort'];
			return GDisplay::printSortScript ($this->atts['st_id'], $this->atts['wt_id'], $params);
		}
		return "";
	}
	
	/**
	 * Print <table> tag
	 */
	protected function openTag (){
		return GDisplay::printStTblTag ($this->atts['st_id'], $this->atts['wt_id'], $this->atts['style']);
	}
	
	/**
	 * Print table
	 */
	protected function printBody (){
		// get GData client
		$gClient = GClient::getInstance (GDOCS_SPREADSHEET);
		
		// get list feed
		$feed = $gClient->getLists($this->atts['st_id'], $this->atts['wt_id']);
		if (!$feed) throw new Exception ();
		
		// format
		return GDisplay::printStTbl ($feed, $this->atts['headings']);
	}

}

/**
 * Cell in Google Spreadsheet
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.9
 * @version		0.9
 */
class GCell extends GElement {

	/**
	 * Constructor
	 * @param	array	$atts	Shortcode attributes
	 * @return	GSt		$obj	GDoc element
	 */
	public function __construct ($atts){
		$this->cache_session = new GCache ($atts['st_id'], $atts['wt_id'], $atts['cell_id']);
		parent::__construct ($atts);
	}
	
	/**
	 * Check required parameters exist, throws Exception otherwise
	 */
	protected function checkRequired (){
		if (is_null ($this->atts['wt_id']) || is_null ($this->atts['st_id']) || is_null ($this->atts['cell_id'])) throw new Exception ();
	}
	
	/**
	 * Print tablesorter script
	 */
	protected function printScripts (){
		return "";
	}
	
	/**
	 * Print <table> tag
	 */
	protected function openTag (){
		return GDisplay::printCellTag ($this->atts['st_id'], $this->atts['wt_id'], $this->atts['cell_id'], $this->atts['style']);
	}
	
	/**
	 * Print table
	 */
	protected function printBody (){
		// get GData client
		$gClient = GClient::getInstance (GDOCS_SPREADSHEET);
		
		// get cell entry
		$entry= $gClient->getCell($this->atts['st_id'], $this->atts['wt_id'], $this->atts['cell_id']);
		if (!$entry) throw new Exception ();
		
		// format
		return GDisplay::printCell ($entry);
	}
	
}

class GElementException extends Exception {
}

?>