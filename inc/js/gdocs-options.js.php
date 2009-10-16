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
 * @version		0.9
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
		new Ajax.Request ("<?php echo ($url . "/../../../"); ?>/wp-admin/admin-ajax.php", {
			method: 'post',
			parameters: {action: 'gdocs_ajax_handler', method: 'update_list'},
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
	 *
	 */
	verify: function (ele){
	
		Element.extend (ele);	
		ele.setAttribute ('value', 'Verifying...');
		
		var args = ele.up().serialize (true);
		args['action'] = 'gdocs_ajax_handler';
		args['method'] = 'verify';
		
		// call php script to verify with Google
		new Ajax.Request ("<?php echo ($url . "/../../../"); ?>/wp-admin/admin-ajax.php", {
			method: 'post',
			parameters: args,
			onSuccess: function (transport, json){
				GDocsOptions._clearListExceptions ();
				GDocsOptions._updateListHTML (transport, json);
			},
			onException: function (transport, json){
				GDocsOptions._clearListExceptions ();
				GDocsOptions._updateListException (transport, json);		
			},
			onFailure: function (transport, json){
				GDocsOptions._clearListExceptions ();
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
		
		// parse JSON
		var docs = eval (transport.responseText);
		
		// populate table
		for (var i=0; i<docs.length; i++){
		
			var odd = i%2==0 ? ' odd' : 'even';
			var ele = docs[i];
			var type = ele.type;
			delete ele.type;
			
			var tr = new Element ('tr').addClassName (odd);
			Object.values (ele).each (function (prop){
				tr.appendChild (new Element ('td').update (prop));
			});
			
			parents[type].appendChild (tr);
			
		};
		
		// update count
		$$('code#dc span')[0].update (json.dc); $('dc').show();
		$$('code#sc span')[0].update (json.sc); $('sc').show();
		
		if (json.error){
			GDocsOptions._updateListException (null, json.error);
		}
	
	},
	
	/*
	 * Reset all exceptions
	 */
	_clearListExceptions: function (){
		var errors = $$('div.error');
		errors.each (function (ele){
			ele.remove();
		});
	},
	
	/*
	 * AJAX Exception Handler
	 */
	_updateListException: function (transport, json){
		GDocsOptions.error++;
		// get div
		var h2 = $$ ('div.wrap h2');
		h2 = h2[0];
		
		if (json == null){
			json = 'A connection error has occurred. Please try again later.';
		}
		
		var p = new Element ('p').update ("<strong>" + json + "</strong>");
		var ele = new Element ('div', {id: 'message_' + GDocsOptions.error}).addClassName ('error').setStyle ({backgroundColor: "rgb(255, 170, 150)"});
		ele.appendChild (p);

		h2.insert ({after: ele});
	
	}

}