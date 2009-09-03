//<script type="text/javascript"> this does nothing, really. just to cheat dreamweaver.

<?php
/**
 * PostBox JS file, GDocs Wordpress Plugin
 * @author		Lim Jiunn Haur <codex.is.poetry@gmail.com>
 * @copyright	Copyright (c) 2008, Lim Jiunn Haur
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package		gdocs
 * @subpackage	gdocs.inc
 * @since		0.5
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
	GDocs.init();
});

var GDocs = {
	
	error:0,
	
	/*
	 * Initialize
	 */
	init: function (){
		
		this.initDraggables ();
		
		var target = $$('textarea#content')[0];
		target.observe ('drop', function (ev){
			$('content').focus();
			return true;
		});
		
	},
	
	initDraggables: function (){
		var elements = $$('div#gdocs_helper div.inside span');
		elements.each (function (ele){
			ele.setAttribute ('draggable', true);
			ele.observe ('dragstart', function (ev){
				var dt = ev.dataTransfer;
				var seps = this.id.split('+');
				
				if (seps[0].length == 0){
					seps = this.select('a')[0].id.split('+');
					var type = this.hasClassName ('gdocs_document') ? 'document' : 'spreadsheet';
				}else {
					var type = this.parentNode.hasClassName ('gdocs_document') ? 'document' : 'spreadsheet';
				}			
				
				dt.setData ('text/plain', GDocs.prepString (seps[0], seps[1], type));
				dt.setData ('text/html', GDocs.prepString (seps[0], seps[1], type));
				return true;
			});
		});
	},
	
	/*
	 * CAPTCHA handler
	 */
	verify: function (ele){
	
		Element.extend (ele);
		ele.setAttribute ('value', 'Verifying...');
		
		// call php script to verify with Google
		new Ajax.Request ("<?php echo $url ?>/ajax-functions.php?action=verify", {
			method: 'post',
			parameters: ele.up().serialize(true),
			onSuccess: function (transport, json){
				GDocs._clearListExceptions ();
				GDocs._updateListHTML (transport, json);
			},
			onException: function (transport, json){
				GDocs._clearListExceptions ();
				GDocs._updateListException (transport, json);		
			},
			onFailure: function (transport, json){
				GDocs._clearListExceptions ();
				GDocs._updateListException (transport, json);
			}
		});
		
	},
	
	/*
	 * Click handler
	 */
	ring: function (id, type){	// id: main_id+sub_id, type: document or spreadsheet
	
		/*
		 * The user has rung, now add the appropriate tag to the post/page content
		 */
		 
		// prepare string
		var seps = id.split('+');
		var str = this.prepString (seps[0], seps[1], type);
		
		// add to textarea
		if ($('content').visible()){
			// if IE
			if (document.selection){
				$('content').focus();
				var sel = document.selection.createRange();
				sel.text = str;
				$('content').focus();
			}else { // anything else
				var pos = this.getCaretPos ($('content'));
				if (pos != false){ // Firefox, Opera
					var left = $('content').value.substring (0, pos);
					var right = $('content').value.substring (pos);
					$('content').value = left + str + right;
				}else {
					// non ff, non IE, non opera
					$('content').value += str;
				}
			}
			$('content').focus();
		}else {
			// the really easy way out
			tinyMCE.activeEditor.execCommand ('mceInsertContent', false, str);
		}
		
		return false;
	
	},
	
	/*
	 * Prepare string
	 */
	prepString: function (main_id, sub_id, type){
	
		var str = "[gdocs type='";
							
		switch (type){
			
			case 'document': // Google Document
			
				str += "document' id='" + main_id + "']";
				break;
			
			case 'spreadsheet': // Google Spreadsheet
			
				str += "spreadsheet' st_id='" + main_id + "' wt_id='" + sub_id + "']";
				break;
			
			default:
				
				return;
		}
		
		return str;
	},
	
	/*
	 * Find caret position
	 * For firefox and opera
	 */
	getCaretPos: function (ctrl) {

		try {
			
			if (ctrl.selectionStart || ctrl.selectionStart == '0') {
				return ctrl.selectionStart;
			}
			
			return false;
			
		}catch (e){
			return false;
		}
	
	},
	
	/*
	 * Update list of documents and spreadsheets
	 * AJAX request
	 */
	updateList: function (){
		
		// call php script to retrieve list of documents and spreadsheets
		new Ajax.Request ('<?php echo $url ?>/ajax-functions.php', {
			method: 'get',
			parameters: {action: 'update_list'},
			onSuccess: function (transport, json){
				GDocs._updateListHTML (transport, json);
				GDocs.initDraggables ();
			},
			onException: function (transport, json){
				GDocs._updateListException (transport, json);		
			},
			onFailure: function (transport, json){
				GDocs._updateListException (transport, json);
			}
		});
		
	},
	
	/*
	 * AJAX Post-request handler
	 * Update HTML to display new list
	 */
	_updateListHTML: function (transport, json){
		
		// get div
		var div = $$ ('div#gdocs_helper div.inside');
		div = div[0];
		
		// clear
		div.update ('');
		
		if (json.error){
			// database error
			GDocs._updateListException (null, json.error);
		}
		
		
		// parse JSON
		var docs = eval (transport.responseText);
		
		// add tags
		docs.each (function(ele){
		
			var sub_title = ele.sub_title ? "<br/>[" + ele.sub_title + "]" : "";
		
			a = new Element ('a', {href: '#', id: ele.main_id + "+" + ele.sub_id}).update (ele.title + sub_title);
			
			var cmd = "GDocs.ring ('" + ele.main_id + "+" + ele.sub_id + "', '" + ele.type + "');";
			a.onclick = function() {eval(cmd); return false;};
			
			var span = new Element ('span').addClassName ('gdocs_' + ele.type);
			
			span.appendChild (a);
			div.appendChild (span);
		
		});
		
		// update count
		var c = $$('code#count span');
		c[0].update (json.dc);
		c[1].update (json.sc);
	
	},
	
	/*
	 * Clear errors
	 */
	_clearListExceptions: function (){
		var div = $$ ('div#gdocs_helper div.inside');
		div = div[0];
		div.update ();
	},
	
	/*
	 * AJAX Exception Handler
	 */
	_updateListException: function (transport, json){
		GDocs.error++;
		// get div
		var div = $$ ('div#gdocs_helper div.inside');
		div = div[0];
		
		if (json == null){
			json = 'A connection error has occurred. Please try again later.';
		}
		
		var p = new Element ('p').update ("<strong>" + json + "</strong>");
		var ele = new Element ('div', {id: 'message_' + GDocs.error}).addClassName ('error').setStyle ({backgroundColor: "rgb(255, 170, 150)"});
		ele.appendChild (p);
		
		div.update ();
		div.appendChild (ele);
	
	}

	
}

// add ajax loading gif
Ajax.Responders.register ({
	onCreate: function (){
		// appear
		$('gdocs_helper_ajax').setAttribute ('src', '<?php echo $url ?>/inc/img/ajax-loader.gif');
	},
	onComplete: function (){
		// hide
		$('gdocs_helper_ajax').setAttribute ('src', '<?php echo $url ?>/inc/img/ajax-refresh.png');
	}
});