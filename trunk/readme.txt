=== Inline Google Docs ===
Contributors: codex.is.poetry
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=codex%2eis%2epoetry%40gmail%2ecom&item_name=Inline%20Google%20Docs&no_shipping=0&no_note=1&tax=0&currency_code=SGD&lc=SG&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: google, documents, gdoc, inline, embed, spreadsheets
Requires at least: 2.5
Tested up to: 2.8.3
Stable tag: 0.9

This plugin allows you to embed Google Document/Spreadsheet content in posts and pages using shortcode.

== Description ==

Many people maintain dynamic content on Google Documents or hold volatile data on Google Spreadsheets. These change when collaborators save an update or users submit a form. Occasionally, one may wish to embed the contents of one's Google Documents or Spreadsheets in a post or page to reflect the latest updates on one's blog. This plugin seeks to provide this functionality without using an `<iframe>`. In addition, it caches contents of the Google Documents or Spreadsheets to speed up page loading.

_Currently, the plugin can only access published documents, but can access both private and public spreadsheets._

#### Shortcode ####
After you have installed the plugin, go to the **Write Page** or **Write Post** form on your Wordpress blog and type the following where you want your document to appear:

	[gdocs id='<doc-id>' type='document']
	
or

	[gdocs st_id='<spreadsheet-id>' wt_id='<worksheet-id>' type='spreadsheet']
	
or

	[gdocs st_id='<spreadsheet-id>' wt_id='<worksheet-id>' cell_id='<cell-id>' type='cell']

Replace `<doc-id>`, `<spreadsheet-id>`, and `<worksheet-id>` with the ID of the document or worksheet that you wish to embed. The ID's are available at the plugin's configuration page. Don't forget to enclose them in quotes.

When embedding a single cell, specify the coordinates of the cell in the worksheet as R<row number>C<column number, such as R6C4 for the cell in the sixth row and fourth column of the worksheet. 

#### Post Helper ####
As of v0.5, you may embed a document or spreadsheet by simply clicking on its link in the **Google Documents/Spreadsheets** panel at the bottom of the page. This may be the preferred method as it relieves you of the technical task of typing the shortcode. Just place your caret where you want your document to appear and click on the title of your chosen document in the panel. This works in both Visual and HTML modes.

If the list of documents and spreadsheets shown in the panel is outdated, just click on the refresh button located at the top left-hand corner of the panel and an updated list will be retrieved immediately.

**HTML 5 Drag & Drop**

v0.7 introduces a new drag and drop functionality based on HTML 5 specifications. If your browser has native support for HTML 5 drag and drop events (at the time of writing, only Firefox 3.5), you can drag the document/spreadsheet from the panel and drop it in the textarea where you want it to appear. 

_Javascript needs to be enabled for this to work._

#### Column Headings ####
v0.5 also introduced 2 optional attributes for spreadsheets, namely `headings` and `style` (`style` will be described in detail in a later section.) Due to technical reasons, all the column headings retrieved from Google Spreadsheets have their spaces removed and characters converted to the lowercase. Furthermore, if a column heading is left blank on the Google Spreadsheet, the Google API will replace it with a random string of characters.

As a workaround, the user may supply the plugin with a string of comma-separated headings that will be displayed in place of the headings retrieved from Google Spreadsheets. The replacement works from left to right i.e. if you provide only one heading and the spreadsheet has 3 columns, the heading of the leftmost column will be replaced.

#### CSS Styling for Spreadsheets ####
**Selectors**

All embedded worksheets are formatted as valid tables with the following structure:

	<table>
		<thead>
			<tr><th>...</th></tr>
		</tbody>
		<tbody>
			<tr><td>...</td></tr>
		</tbody>
	</table>

A typical `table` element has the following attributes:
	
	<table id="gdocs_<spreadsheet-id>_<worksheet-id>" class="gdocs gdocs_<spreadsheet-id>">

A typical `tr` element has the following attributes:
	
	<tr class="row_<x> <odd|even>">

A `th` element:
	
	<th class="col_<x> <odd|even>">

Finally, a `td` element:
	
	<td class="col_<x> <odd|even>">

Given the above markup, the following selectors are available:

1. All spreadsheets: `table.gdocs`	
1. All worksheets of a particular spreadsheet: `table.gdocs_<spreadsheet_id>`
1. A particular worksheet: `table#gdocs_<spreadsheet-id>_<worksheet-id>`
1. A column: `td.col_<x>`
1. A row: `tr.row_<x>`
1. A cell: `tr.row_<x> td.col_<y>`
1. All odd rows: `tr.odd	`
1. All even columns: `td.even`	

**Stylesheets**

You may also define a new style **class** and specify it using the `style` attribute in the shortcode. Refer to the stylesheets in `inline-google-docs/styles/` for examples.

For example, suppose you would like to define a new class named _my-class_ for your tables. First, create a new CSS file in `inline-google-docs/styles/` and name it _my-class.css_. Then, specify the class in your shortcode, as follows:

	[gdocs st_id='...' wt_id='...' type='spreadsheet' style='my-class']

If you need to use images, create a new folder in `inline-google-docs/styles` and move _my-class.css_ as well as all your images into this folder. Name this folder after your class.

####Table Sorter####
Inline Google Docs now includes [tablesorter](http://tablesorter.com)!

To use tablesorter without passing any parameters, set `sort` to `true` in your shortcode, as follows:

        [gdocs ... sort="true"]

To pass simple parameters to tablesorter, simply include them within braces in the `sort` attribute, as follows:

        [gdocs ... sort="{cancelSelection:false,...}"]

Certain characters, such as square brackets, will confuse the shortcode parser. If you have complex parameters, pass them through a Javascript variable, as follows:

        <script type='text/javascript'>
          var properties = {cancelSelection:false, sortList:[[1,1]]};
        </script>
        [gdocs type='spreadsheet' st_id='twRDk9_BEs9E6Jevb82ETvw' wt_id='od7' style='tablesorter' sort="properties" headings='A, B, C, D, E, F, G, H']

v0.8 also includes the default blue theme; set `style` to `tablesorter` to use it to style your tables. 

####Supported Browsers####
This plugin has been tested on IE 7, IE 8, Chrome, Firefox 2, Firefox 3, Firefox 3.5, Opera 9, and Safari 3. If you are using another browser, please update this [wiki](http://code.google.com/p/inline-google-docs/wiki/Guide) if it works and post a new issue if it doesn't.

[Leave Feedback](http://groups.google.com/group/inline-google-docs "Leave praises and criticism") | [Bug Report](http://code.google.com/p/inline-google-docs/ "Report a bug")

== Installation ==

1. Download and extract the plugin
1. Upload the `inline-google-docs` folder into the `/wp-content/plugins/` directory
1. Edit file permissions through your FTP client to make the `inline-google-docs/cache/` and `inline-google-docs/cache/error.log.php` writable.
1. Activate the plugin through the **Plugins** menu in WordPress
1. Go to **Settings**, then navigate to **Inline Google Docs**
1. Provide the plugin with your Google account login credentials
1. Input proxy settings if you are behind a proxy, then click on **Save Changes**
1. The plugin will display the document/spreadsheet id's for your documents and spreadsheets

= File Permissions =

Unfortunately, you need to correct the file permissions on your own as SVN automatically resets all file permissions on uploaded files. I recommend the following settings (assuming FTP user as codex and Web user as www-data):

	-rw-r----- codex www-data some-file.php
	drwxr-x--- codex www-data some-directory

As for the cache and error log:

	drwxrwx--- codex www-data cache
	-rw-rw---- codex www-data error.log.php
	
The UNIX commands are provided below:

	cd /path/to/Wordpress/wp-content/plugins/inline-google-docs;
	find . -type d -exec chmod 750 {} \;
	find . -type f -exec chmod 640 {} \;
	chmod -R g+w ./cache;

== Frequently Asked Questions ==

= Which PHP version does the plugin require? =

PHP 5.

= Does it use iframes? =

No, it doesn't.

= How do I create zebra patterns for my tables? =

As of v0.7, all rows and columns have an additional class that marks it as even or odd. Use these to create alternating patterns.

== Changelog ==

#### v0.9

1. Capability to embed single cell added
1. Capability to import stylesheets from user-specified directory added
1. Bug caused by Wordpress URL fixed
1. Document styling added
1. Custom classes for documents and spreadsheets added
1. Other minor bug fixes

#### v0.8

1. Critical fix for "Zend not found"

#### v0.7.5

1. Zend library reduced
1. Support for WPMU added
1. Migrated to v2.7, implemented Settings API
1. Tablesorter functionality added (blue skin included)
1. Error handling improved
1. Links within plugin modified, folder name may now be changed by user 

####02/08/09
1. Improved error and exception management
1. Support for Google Apps
1. Document images enabled
1. File permissions corrected for improved security
1. Additional class for `tr` and `td` to allow alternating (zebra) styling

####13/09/08
1. Easier table styling implemented.
1. Improved CSS selectors: rows are now selected by class instead of id.
1. Helper panel added to post/page form.
1. Custom headings implemented.

####22/07/08
1. Caching facility added
1. And other general debugging and code cleaning

####20/07/08
1. Contents of Google Documents are now housed in a `<div>` instead of a `<p>`
1. All div's and tables generated by GDocs belong to the `gdocs` class
1. All div's and tables generated by GDocs have unique id's corresponding to their document id, prefixed by `gdocs_`
1. Column headings for Spreadsheets are now displayed
1. All cells are class'ed according to their respective columns e.g. all cells in the first column belong to the class `col_0`
1. All rows are given unique id's e.g. the first row has the id `row_0`
1. Due to above, if you wish to style the cell at (8,4), simply use `tr#row_3 td.col_7` as the CSS selector
1. Improved error handling
1. And other general debugging and code cleaning

== Screenshots ==

1. Document content replaces shortcode and appears in the post.
2. Insert the shortcode where you want the document to be.
3. Using this helper, just point and click.
4. Corrupted column headings
5. Custom headings
6. Styling tables
7. Creating new styles
8. Possible CSS selectors