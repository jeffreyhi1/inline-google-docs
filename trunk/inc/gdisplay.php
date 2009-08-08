<?php
/**
 * Display class, GDocs Wordpress plugin
 *
 * Handles HTML formatting
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
 * GDisplay class
 *
 * No special OOP techniques used here. This class just
 * groups all display-related functions together.
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
 * @version		0.7
 */
class GDisplay {

	/**#@+
	 * @static
	 */
	
	/**
	 * Array of all stylesheets used on this page
	 *
	 * Keeps a record so that any given stylesheet
	 * isn't imported more than once on the same page
	 * @var array
	 */
	private static $stylesheets = array ();
	 
	/**
	 * Prints head of configuration page
	 */
	public static function printHead (){
	?>
	<style type="text/css">
		div#gdocs_left {
			float:left;
			width:50%;
		}
		div#gdocs_right td.gdocs_loader {
			background:#cfebf7 url("<?php echo get_bloginfo ('url'); ?>/wp-content/plugins/inline-google-docs/inc/ajax-loader.gif") center right no-repeat;
			padding:8px 8px 8px 20px;
		}
		div#gdocs_right tr.gdocs_loader td{
			background-color:#cfebf7;
		}
		div#gdocs_right table.hor-zebra {
			font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
			font-size: 10px;
			margin: 15px;
			width: 35%;
			text-align: left;
			border-collapse: collapse;
		}
		div#gdocs_right table.hor-zebra th {
			font-size: 12px;
			font-weight: normal;
			padding: 10px 8px;
			color: #039;
		}
		div#gdocs_right table.hor-zebra td {
			padding: 8px;
			color: #669;
		}
		div#gdocs_right table.hor-zebra .odd {
			background: #e8edff;
		}
	</style>
	
	<div class='wrap'>
		<h2>Inline Google Docs</h2>
		<div id='gdocs_left'>
			<form method='post' action='options.php'>
				<?php
					if (function_exists ('settings_fields')){
						settings_fields ('gdocs-options');
					}else {
				?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="gdocs_user,gdocs_pwd,gdocs_proxy_host,gdocs_proxy_port,gdocs_proxy_user,gdocs_proxy_pwd, gdocs_cache_expiry" />
				<?php 
						wp_nonce_field ('update-options');	
					}
				?>
			
	<?php
	
	}

	/**
	 * Prints login credentials input form
	 */
	public static function printLogin (){
	?>
	
				<!-- Login Credentials -->
				<h3>Google Account Login</h3>
				<table class='form-table'>
					<tbody>
						<tr valign="top">
							<th scope="row"><label for='gdocs_user'>Username</label></th>
							<td><input id='gdocs_user' type="text" size="40" name="gdocs_user" value="<?php echo get_option ('gdocs_user'); ?>" /><br/><span class='description'>For Google Apps users, append your username with <code>@yourdomain.com</code></span></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for='gdocs_pwd'>Password</label></th>
							<td><input id='gdocs_pwd' type="password" size="40" name="gdocs_pwd" value="<?php echo get_option ('gdocs_pwd'); ?>" /></td>
						</tr>
					</tbody>
				</table>
	
	<?php
	}
	
	/**
	 * Prints proxy settings input form
	 */
	public static function printProxy (){
	?>
	
				<!-- Proxy Settings -->
				<h3>Proxy Settings</h3>
				<span class='description'>Leave this blank if your host is not behind a proxy.</span>
				<table class='form-table'>
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="gdocs_proxy_host">Host</label></th>
							<td><input type="text" size="40" name="gdocs_proxy_host" id="gdocs_proxy_host" value="<?php echo get_option ('gdocs_proxy_host'); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="gdocs_proxy_port">Port</label></th>
							<td><input type="text" size="40" name="gdocs_proxy_port" id="gdocs_proxy_port" value="<?php echo get_option ('gdocs_proxy_port'); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="gdocs_proxy_user">Username</label></th>
							<td><input type="text" size="40" name="gdocs_proxy_user" id="gdocs_proxy_user" value="<?php echo get_option ('gdocs_proxy_user'); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="gdocs_proxy_pwd">Password</label></th>
							<td><input type="password" size="40" name="gdocs_proxy_pwd" id="gdocs_proxy_pwd" value="<?php echo get_option ('gdocs_proxy_pwd'); ?>" /></td>
						</tr>
					</tbody>
				</table>
	
	<?php
	}
	
	/**
	 * Prints cache settings input form
	 */
	public static function printCache (){
	?>
	
				<!-- Cache Settings -->
				<h3>Cache Settings</h3>
				<table class='form-table'>
					<tbody>
						<tr valign="top">
							<th scope="row"><label for='gdocs_cache_expiry'>Lifespan</label></th>
							<td><input type="text" size="40" id='gdocs_cache_expiry' name="gdocs_cache_expiry" value="<?php echo get_option ('gdocs_cache_expiry'); ?>" /><span class='description'>minutes</span><br/><span class='description' style="font-style:normal"><strong>Set to <code>0</code> to turn off caching.</strong></span></td>
						</tr>
					</tbody>
				</table>
	
	<?php
	}
	
	/**
	 * Prints foot of configuration page
	 */
	public static function printFoot (){
	?>		
				<p class='submit'>
					<input type='submit' name='Submit' value="<?php _e('Save changes') ?>" class='button-primary' />
				</p>				
				
			</form>
		</div>
		
	<?php
	}
	
	/**
	 * Prints list of documents
	 */
	public static function printDocList (){
		
	?>
		<div id='gdocs_right'>
			<!-- Document List -->
			<h3>Google Documents</h3>
			<small>Use the corresponding Document ID in your shortcode.<br />
			<span style="color: blue">[gdocs id=<em>doc_id</em> type='document']</span></small>
			<table class='hor-zebra'>
				<thead>
					<tr><th>Title</th><th>Document ID</th></tr>
				</thead>
				<tbody id='gdocs_list_document'>
					<tr class='gdocs_loader'><td class='gdocs_loader'>Loading...</td><td></td></tr>
				</tbody>
			</table>
	<?php
		
	}
	
	/**
	 * Prints list of spreadsheets
	 */
	public static function printStList (){
		
	?>
			<!-- Spreadsheet List -->
			<h3>Google Spreadsheets</h3>
			<small>Use the corresponding Spreadsheet ID and Worksheet ID in your shortcode.<br />
			<span style="color: blue">[gdocs st_id=<em>spreadsheet_id</em> wt_id=<em>worksheet_id</em> type='spreadsheet']</span></small>
	
			<table class='hor-zebra'>
				<thead>
					<tr><th>Title</th><th>Spreadsheet ID</th><th>Worksheets</th></tr>
				</thead>
				<tbody id='gdocs_list_spreadsheet'>
					<tr class='gdocs_loader'><td class='gdocs_loader'>Loading...</td><td></td><td></td></tr>
				</tbody>
			</table>
		</div>
		<p style='clear:both'></p>
	</div>
		
	<?php
	
	}
	
	/**
	 * Prints cache not writable
	 */
	public static function printCacheNotWritableError (){
		$user = exec ('whoami');
		$grp = exec ("groups {$user}");
		$grp = str_replace (' ', ', ', $grp);
	?>
		<div class='error' id='message_100' style='background-color: rgb(255, 170, 150);'><p><strong><?php _e("The cache folder is not writable.<br/>Current user: <code>{$user}</code> | Groups: <code>$grp</code>") ?></strong></p></div>
	<?php
	}
	
	/**
	 * Prints log not writable
	 */
	public static function printLogNotWritableError (){
		$user = exec ('whoami');
		$grp = exec ("groups {$user}");
		$grp = str_replace (' ', ', ', $grp);
	?>
		<div class='error' id='message_101' style='background-color: rgb(255, 170, 150);'><p><strong><?php _e("The <a href='" . get_bloginfo ('url') . "/wp-content/plugins/inline-google-docs/cache/error.log.php'>log file</a> is not writable.<br/>Current user: <code>{$user}</code> | Groups: <code>$grp</code>") ?></strong></p></div>
	<?php
	}
	
	/**
	 * Prints a postbox in the edit-post / edit-page page
	 * Lists all Google documents and Google Spreadsheets
	 * Lets user add shortcode tag to post by clicking
	 * @param	array	$results	array of rows returned from the database
	 */
	public static function printHelper ($results){
	
	?>
		<!-- Begin G Docs Helper -->
		<style type='text/css'>
			div#gdocs_helper span{
				display:block;
				float:left;
				color:#333;
				margin:3px;
				padding:3px;
				min-height:70px;
				min-width:70px;
				text-align:center;
			}
			
			div#gdocs_helper span a{
				background:white;
				position:relative;
				top:20px;
				line-height:1.3em;
			}
			
			div#gdocs_helper img{
				border: 0 none;
			}
			div#gdocs_helper h3 a{
				float: right;
			}
			
			.gdocs_error {
				border-width: 1px;
				border-style: solid;
				border-color: red;
				padding: 0 0.6em;
				margin: 5px 15px 2px;
				-moz-border-radius: 3px;
				-khtml-border-radius: 3px;
				-webkit-border-radius: 3px;
				border-radius: 3px;
			}
			
			.gdocs_error p {
				margin: 0.5em 0;
				line-height: 1;
				padding: 2px;
			}
			
			span.gdocs_document {
				background:transparent url("<?php echo get_bloginfo ('url') ?>/wp-content/plugins/inline-google-docs/inc/document.png") no-repeat center;
			}
			
			span.gdocs_spreadsheet {
				background:transparent url("<?php echo get_bloginfo ('url') ?>/wp-content/plugins/inline-google-docs/inc/spreadsheet.png") no-repeat center;
			}
		</style>
		<div id='gdocs_helper' class='postbox open'>
			<h3><a href="#" onclick="javascript: GDocs.updateList(); return false;"><img id='gdocs_helper_ajax' src='<?php echo get_bloginfo ('url') . "/wp-content/plugins/inline-google-docs/inc/ajax-refresh.png" ?>' /></a>Google Documents/Spreadsheets</h3>			
			<div class='inside'>
				<noscript><div class='gdocs_error' id='gdocs_js_error' style='background-color: rgb(255, 170, 150);'><p><strong><?php _e("Enable Javascript to use this panel.") ?></strong></p></div></noscript>
				<?php
			
			if ($results) {
			
				foreach ($results as $row){
					// <a href='#' onclick="javascript: GDocs.ring('MAIN_ID+SUB_ID', 'TYPE', 'TITLE'); return false;" id='MAIN_ID+SUB_ID+type'>
					$sub = $row->sub_title ? "<br/>[" . $row->sub_title . "]" : "";
					printf ("<span class='gdocs_%s'><a href='#' onclick=\"javascript: GDocs.ring ('%s+%s', '%s'); return false;\" id='%s+%s'>%s{$sub}</a></span>", $row->type, $row->main_id, $row->sub_id, $row->type, $row->main_id, $row->sub_id, $row->title, $row->sub_title);
				}
				
			}else {
			?>
				<div class='gdocs_error' id='message' style='background-color: rgb(255, 170, 150);'><p><strong><?php _e("The plugin was unable to connect to the database. Refresh this box to see the list of documents and spreadsheets available.") ?></strong></p></div>
			<?php
			}
			
			?></div>
			<div style="clear:both"></div>
		</div>
		<!-- End G Docs helper -->
	<?php
	}
	
	/**
	 * Formats table tag
	 * @param	string			$st_id		spreadsheet id
	 * @param	string			$wt_id		worksheet id
	 * @param	string			$style		predefined stylesheet
	 * @return	string			$html		formatted table tag
	 */
	public static function printStTblTag ($st_id, $wt_id, $style = NULL){
		/*
		 * class=$st_id to identify with a certain spreadsheet
		 * id=$st_id $wt_id to make this worksheet unique
		 * class=gdocs allows global styles to be set across all Google Docs/Spreadsheets
		 * class=$style to associate this table with a predefined stylesheet
		 */
		return "<table class='gdocs gdocs_{$st_id} {$style}' id='gdocs_{$st_id}_{$wt_id}'>\r\n";
	}
	
	/**
	 * Formats HTML to display spreadsheet content in a table
	 * @param	Zend_Gdata_Feed	$feed		list feed
	 * @param	string			$headers	comma-separated custom column titles to replace original titles with
	 */
	public static function printStTbl (Zend_Gdata_Feed $feed, $headers = NULL){
		
		// convert to spreadsheet list feed object
		$feed = new Zend_Gdata_Spreadsheets_ListFeed ($feed->getDom ());
		
		// if headers are specified or entries are given
		if (isset ($headers) || $feed->entries[0]){
			
			// start <thead>
			$html .= "\t<thead>\r\n";
			$html .= "\t\t<tr class='row_0'>\r\n\t\t\t";
				
			$k = 0;
			
			if (isset ($headers)){
				// custom headers given
				$colHeads = preg_split ("/,\s+/", $headers);
				
				// get all the headers specified by user
				foreach ($colHeads as $colHead){
					$colax = $k%2==0 ?'odd' : 'even';
					$html .= "<th class='col_{$k} {$colax}'>" . $colHead . "</th>";
					$k++;
				}
			
			}
				
			if ($feed->entries[0]){
				// custom headers not given
				
				// extract column headings
				$firstRow = $feed->entries[0]->getCustom ();
				
				// if  user did not specify all, get rest from list feed
				while ($colHead = $firstRow [$k]){
					$colax = $k%2==0 ?'odd' : 'even';
					$html .= "<th class='col_{$k} {$colax}'>" . $colHead->getColumnName () . "</th>";
					$k++;
				}
				
				
			}
			
			// end <thead>
			$html .= "\r\n\t\t</tr>\r\n";
			$html .= "\t</thead>\r\n";
			
		}
		
		$html .= "\t<tbody>\r\n";
	
		// for every row
		$i = 1;
		foreach ($feed->entries as $entry){
		
			$rowlax = $i%2!=0 ?'odd' : 'even';
			
			// start table row
			$html .= "\t\t<tr class='row_{$i} {$rowlax}'>\r\n\t\t\t";
			
			// get all the cells in this row
			$cells = $entry->getCustom ();
			
			// for every cell, display the contents
			$j = 0;
			foreach ($cells as $cell){
				$colax = $j%2!=0 ?'even' : 'odd';			
				$html .= "<td class='col_{$j} {$colax}'>" . $cell->getText () . "</td>";
				$j++;
			}
			
			// end table row
			$html .= "\r\n\t\t</tr>\r\n";
			$i++;
			
		}
		
		$html .= "\t</tbody>\r\n";		
		$html .= "</table>\r\n";
		
		return $html;
	
	}
	
	/**
	 * Prints stylesheet <link>
	 * @param	string	$style	Style class for this spreadsheet
	 * @return	string	$html	<style> tag to add to the HTML output
	 */
	public static function printStylesheet ($style){
		
		if (in_array ($style, self::$stylesheets)) return NULL;
		self::$stylesheets[] = $style;
		
		if (file_exists (dirname(__FILE__) . "/../styles/{$style}.css")){
			$path = get_bloginfo ('url') . "/wp-content/plugins/inline-google-docs/styles/{$style}.css";
		}else {
			$path = get_bloginfo ('url') . "/wp-content/plugins/inline-google-docs/styles/{$style}/{$style}.css";
		}
		
		return "<link href='{$path}' rel='stylesheet' type='text/css' />";
	
	}
	
	/**#@-*/

}
?>
