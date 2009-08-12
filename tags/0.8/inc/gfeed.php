<?php
/**
 * Feed class, GDocs Wordpress plugin
 *
 * Handles feed iteration and parsing
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
 * GFeed class
 *
 * Implements Iterator from SPL, which makes objects of this class enumerable.
 * Before returning current element, all necessary data is extracted and formatted.
 * This hides the parsing details from whatever loops that use this class.
 *
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
 * @version		0.7
 */
class GFeed implements Iterator{

	/**
	 * Feed entries
	 * @var mixed
	 */
	private $entries;
	
	/**
	 * Iteration pointer
	 * @var integer
	 */
	private $curr = 0;
	
	/**
	 * Spreadsheet or Document
	 * @var string
	 */
	private $type;
	
	/**
	 * Class constructor
	 * @method	GFeed	GFeed()	GFeed($feed, $type) creates and initializes new GFeed object
	 * @param	Zend_Gdata_Feed	$feed				raw feed returned by Google
	 * @param	string			$type				gdoc type (either 'doc' or 'st')
	 * @return	GFeed			$feeed				Iterable feed object
	 */
	public function __construct (Zend_Gdata_Feed $feed, $type){
		if (!$feed) throw new Exception ();
		$this->entries = $feed->entries;
		$this->type = $type;
	}
	
	/**
	 * Iterator function: access current element
	 *
	 * Accesses current element from array.
	 * Extracts and parses data.
	 * Returns relevant data in a nice array.
	 *
	 * @return 	mixed	$entry	'objectified' array of entry attributes
	 */
	public function current (){
	
		$entry = $this->entries[$this->curr];
		$title = $entry->getTitleValue();
		
		preg_match ('/^.+%3A(.+)$/', $entry->getId()->getText(), $matches);
		$id = $matches[1];
		
		return (object) array ('title' => $title, 'main_id' => $id, 'type' => $this->type);
		
	}
	
	/**
	 * Iterator function: move to next element
	 */
	public function next (){
		$this->curr += 1;
	}
	
	/** 
	 * Iterator function: get the current key
	 * @return	int	$curr	array index of current element
	 */
	public function key (){
		return $this->curr;
	}
	
	/**
	 * Iterator function: reset pointer
	 */
	public function rewind (){
		$this->curr = 0;
	}
	
	/**
	 * Iterator function: test if end-of-iteration
	 * @return	bool	$valid	FALSE if end is reached, TRUE is still more to loop
	 */
	public function valid (){
		return $this->entries[$this->curr];			
	}

}
?>
