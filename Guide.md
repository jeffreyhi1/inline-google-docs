

# Shortcode #
After you have installed the plugin, go to the **Write Page** or **Write Post** form on your Wordpress blog and type the following where you want your document to appear:
```
[gdocs id='<doc-id>' type='document']
```

```
[gdocs st_id='<spreadsheet-id>' wt_id='<worksheet-id>' type='spreadsheet']
```
or
```
[gdocs st_id='<spreadsheet-id>' wt_id='<worksheet-id>' cell_id='<cell-id>' type='cell']
```

Replace `<doc-id>`, `<spreadsheet-id>`, and `<worksheet-id>` with the ID of the document or worksheet that you wish to embed. The ID’s are available at the plugin’s configuration page. Don't forget to enclose them in quotes.

When embedding a single cell, specify the coordinates of the cell in the worksheet as `R<row number>C<column number`, such as `R6C4` for the cell in the sixth row and fourth column of the worksheet.

For example, the following shortcode:<br />
![http://img188.imageshack.us/img188/2062/posttextarea.png](http://img188.imageshack.us/img188/2062/posttextarea.png)

results in the following:<br />
![http://img40.imageshack.us/img40/9343/sampledoc.png](http://img40.imageshack.us/img40/9343/sampledoc.png)

[More»](Shortcode.md)

# Post Helper #
As of v0.5, you may embed a document or spreadsheet by simply clicking on its link in the **Google Documents/Spreadsheets** panel at the bottom of the page. This may be the preferred method as it relieves you of the technical task of typing the shortcode. Just place your caret where you want your document to appear and click on the title of your chosen document in the panel. This works in both Visual and HTML modes.

![http://img188.imageshack.us/img188/339/postbox.png](http://img188.imageshack.us/img188/339/postbox.png)

If the list of documents and spreadsheets shown in the panel is outdated, just click on the refresh button located at the top left-hand corner of the panel and an updated list will be retrieved immediately.

## HTML 5 Drag & Drop ##
v0.7 introduces a new drag and drop functionality based on HTML 5 specifications. If your browser has native support for HTML 5 drag and drop events (at the time of writing, only Firefox 3.5), you can drag the document/spreadsheet from the panel and drop it in the textarea where you want it to appear.

_Javascript needs to be enabled for this to work._

# Column Headings #
v0.5 also introduced 2 optional attributes for spreadsheets, namely `headings` and `style` (`style` will be described in detail in a later section.) Due to technical reasons, all the column headings retrieved from Google Spreadsheets have their spaces removed and characters converted to the lowercase. Furthermore, if a column heading is left blank on the Google Spreadsheet, the Google API will replace it with a random string of characters.

![http://img25.imageshack.us/img25/9594/badheadings.png](http://img25.imageshack.us/img25/9594/badheadings.png)

As a workaround, the user may supply the plugin with a string of comma-separated headings that will be displayed in place of the headings retrieved from Google Spreadsheets. The replacement works from left to right i.e. if you provide only one heading and the spreadsheet has 3 columns, the heading of the leftmost column will be replaced.

For example, the following:
```
[gdocs st_id='...' wt_id='...' type='spreadsheet' headings='N, N Power, Multiply']
```

results in:<br />
![http://img156.imageshack.us/img156/2109/goodheadings.png](http://img156.imageshack.us/img156/2109/goodheadings.png)

# CSS Styling for Spreadsheets #
## Selectors ##
All embedded worksheets are formatted as valid tables with the following structure:
```
	<table>
		<thead>
			<tr><th>...</th></tr>
		</tbody>
		<tbody>
			<tr><td>...</td></tr>
		</tbody>
	</table>
```

A typical `table` element has the following attributes:
```
<table id="gdocs_<spreadsheet-id>_<worksheet-id>" class="gdocs gdocs_<spreadsheet-id>">
```

A typical `tr` element has the following attributes:
```
<tr class="row_<x> <odd|even>">
```

A `th` element:
```
<th class="col_<x> <odd|even>">
```

Finally, a `td` element:
```
<td class="col_<x> <odd|even>">
```

Given the above markup, the following selectors are available:
|Select|Syntax|Example|
|:-----|:-----|:------|
|All spreadsheets|`table.gdocs`|       |
|All worksheets of a particular spreadsheet|`table.gdocs_<spreadsheet_id>`|`table.gdocs_pkW3HTGwg6SDbucCgANRiPw`|
|A particular worksheet|`table#gdocs_<spreadsheet-id>_<worksheet-id>`|`table#gdocs_pkW3HTGwg6SDbucCgANRiPw_od7`|
|A column|`td.col_<x>`|`td.col_3`|
|A row |`tr.row_<x>`|`tr.row_7`|
|A cell|`tr.row_<x> td.col_<y>`|`tr.row_3 td.col_7`|
|All odd rows|`tr.odd`|       |
|All even columns|`td.even`|       |

## Stylesheets ##
You may also define a new style **class** and specify it using the `style` attribute in the shortcode. Refer to the stylesheets in `inline-google-docs/styles/` for examples.

For example, suppose you would like to define a new class named _my-class_ for your tables. First, create a new CSS file in `inline-google-docs/styles/` and name it _my-class.css_. (If you would like to use your own directory, specify the location of the directory in the plugin options page.) Then, specify the class in your shortcode, as follows:
```
	[gdocs st_id='...' wt_id='...' type='spreadsheet' style='my-class']
```

![http://img195.imageshack.us/img195/986/tablefqe.png](http://img195.imageshack.us/img195/986/tablefqe.png)

If you need to use images, create a new folder in `inline-google-docs/styles` and move _my-class.css_ as well as all your images into this folder. Name this folder after your class.

![http://img156.imageshack.us/img156/8563/styles.png](http://img156.imageshack.us/img156/8563/styles.png)


# Table Sorter #
Inline Google Docs now includes [tablesorter](http://tablesorter.com/)!

To use tablesorter with the default options, set `sort` to `true` in your shortcode, as follows:
```
	[gdocs ... sort="true"]
```

If you wish to configure tablesorter, simply include the parameters within braces in the `sort` attribute, as follows:
```
	[gdocs ... sort="{cancelSelection:false,...}"]
```

Certain characters, such as square brackets, will confuse the shortcode parser. If you have complex parameters, pass them through a Javascript variable, as follows:
```
	<script type='text/javascript'>
	  var properties = {cancelSelection:false, sortList:[[1,1]]};
	</script>
	[gdocs type='spreadsheet' st_id='twRDk9_BEs9E6Jevb82ETvw' wt_id='od7' style='tablesorter' sort="properties" headings='A, B, C, D, E, F, G, H']
```

_Note: You only need to use the `sort` attribute once. If you use `sort="properties"`, you don't have to use `sort="true"`._

v0.8 also includes the default blue theme; set `style="tablesorter"` to use it to style your tables.

[tablesorter Configuration»](http://tablesorter.com/docs/#Configuration)

# Supported Browsers #
This plugin has been tested on IE 7, IE 8, Chrome, Firefox 2, Firefox 3, Firefox 3.5, Opera 9, and Safari 3. If you are using another browser, please update this wiki if it works and post a new issue if it doesn’t.

# Dependencies #
Requires
  * PHP 5
  * Javascript
  * Wordpress (>2.7 recommended)
  * Prototype (usu. included in Wordpress package)
  * jQuery (usu. included in Wordpress package)