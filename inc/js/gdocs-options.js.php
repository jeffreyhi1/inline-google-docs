//<script type="text/javascript"> this does nothing, really. just to cheat dreamweaver.

<?php
/**
 * Options JS file, GDocs Wordpress Plugin
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.7
 * @version		0.7
 */
$url = NULL;
if (isset ($_GET ['url'])){
	$url = $_GET ['url'];
}else {
	die ();
}			
?>

// initialize on load
document.observe ('dom:loaded', function (){
	GDocsOptions.init();
});

var GDocsOptions = {

	error:0,

	init: function (){
	
		// call php script to retrieve list of documents and spreadsheets
		new Ajax.Request ('<?php echo $url ?>/ajax-functions.php', {
			method: 'get',
			parameters: {action: 'update_list'},
			onSuccess: function (transport, json){
				GDocsOptions._updateListHTML (transport, json);
			},
			onException: function (transport, json){
				GDocsOptions._updateListException (transport, json);		
			},
			onFailure: function (transport, json){
				GDocsOptions._updateListException (transport, json);
			}
		});
		
	},
	
	/*
	 * AJAX Post-request handler
	 * Update HTML to display new list
	 */
	_updateListHTML: function (transport, json){
		
		var parents = Array ();
		
		// clear contents
		parents['document'] = $('gdocs_list_document').update ("");
		parents['spreadsheet'] = $('gdocs_list_spreadsheet').update ("");
		
		var docs = json.docs;
		
		// populate table
		var i;
		for (i=0; i<docs.length && docs[i].type == 'document'; i++){
			var odd = i%2==0 ? ' odd' : 'even';
			var tr = new Element ('tr').addClassName (odd);
			tr.appendChild (new Element ('td').update (docs[i].title));
			tr.appendChild (new Element ('td').update (docs[i].main_id));
			parents[docs[i].type].appendChild (tr);
		};
		
		// collapse similar spreadsheets
		var prev = null; var str = Array (); var j = 0;
		for (i=i; i < docs.length && docs[i].type == 'spreadsheet'; i++){
			if (prev) {			// not 1st element
				if (docs[i].main_id == prev.main_id){
					str.push (docs[i].sub_id);
				}else {
					var odd = j%2==0 ? ' odd' : 'even';
					var tr = new Element ('tr').addClassName (odd);
					tr.appendChild (new Element ('td').update (prev.title));
					tr.appendChild (new Element ('td').update (prev.main_id));
					tr.appendChild (new Element ('td').update (str.join (', ')));
					parents[docs[i].type].appendChild (tr);
					j++;
					str = Array ();
					str.push (docs[i].sub_id);
				}
			}else {
				// 1st element
				str.push (docs[i].sub_id);
			}
			prev = docs[i];
		}
		
		// only entry or last entry
		if (prev){
			var odd = j%2==0 ? ' odd' : 'even';
			var tr = new Element ('tr').addClassName (odd);
			tr.appendChild (new Element ('td').update (prev.title));
			tr.appendChild (new Element ('td').update (prev.main_id));
			tr.appendChild (new Element ('td').update (str.join (', ')));
			parents['spreadsheet'].appendChild (tr);
		}
		
		if (json.db_error){
			GDocsOptions._updateListException (null, json.db_error);
		}
	
	},
	
	/*
	 * AJAX Exception Handler
	 */
	_updateListException: function (transport, json){
		GDocsOptions.error++;
		// get div
		var h2 = $$ ('div.wrap h2');
		h2 = h2[0];
		
		var p = new Element ('p').update ("<strong>" + json + "</strong>");
		var ele = new Element ('div', {id: 'message_' + GDocsOptions.error}).addClassName ('error').setStyle ({backgroundColor: "rgb(255, 170, 150)"});
		ele.appendChild (p);

		h2.insert ({after: ele});
	
	}

}