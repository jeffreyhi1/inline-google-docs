# gdocs #

|<strong>Type</strong>|<strong>Required Attributes</strong>|<strong>Optional Attributes</strong>|
|:--------------------|:-----------------------------------|:-----------------------------------|
|Document             |[type](#type.md), [id](#id.md)      |[style](#style.md)                  |
|Spreadsheet          |[type](#type.md), [st\_id](#st_id.md), [wt\_id](#wt_id.md)|[style](#style.md), [headings](#headings.md), [sort](#sort.md)|
|Cell                 |[type](#type.md), [st\_id](#st_id.md), [wt\_id](#wt_id.md), [cell\_id](#cell_id.md)|[style](#style.md)                  |


General syntax:
```
[gdocs]default text[/gdocs]
```

Wordpress will parse the above shortcode and pass the attributes to the plugin. If the plugin fails to retrieve the document/spreadsheet from Google, it will display the text within the `gdocs` tags. Note that there must be at least one space between shortcodes.

## Examples ##
```
<!-- Embedding a Google Document -->
I want my document to appear in between this line
[gdocs type='document' id='ddk7f9g2_15g3w6q9fk']OOPS... [/gdocs]
and this line.
<!-- The string "OOPS... " will be displayed in event of failure -->
```

results in the following:

![http://img40.imageshack.us/img40/9343/sampledoc.png](http://img40.imageshack.us/img40/9343/sampledoc.png)

Add classes to the documents this way:
```
<!-- Styling a Google Document -->
I want my document to appear in between this line
[gdocs type='document' id='ddk7f9g2_15g3w6q9fk' style='x, y, z']
and this line.
```

results in
```
<div id="gdocs_ddk7f9g2_15g3w6q9fk" class="gdocs x y z">
  <!-- Content from Google Document -->
  <h4 style="color: rgb(11, 83, 148);">HI! I am a <span style="background-color: rgb(255, 153, 0);">Google</span> Document.</h4><br/>
</div>
```

---

# Attributes #

## type ##
**Required** <br />
_enum_ `document|spreadsheet`

Indicates the type of file to embed.
```
[gdocs type='document' ...]
```
```
[gdocs type='spreadsheet' ...]
```

## id ##
**Required for Documents** <br />
_string_

Google generated document ID. Available at plugin configuration page.
```
[gdocs id='ddk7f9g2_15g3w6q9fk' ...]
```

## st\_id ##
**Required for Spreadsheets** <br />
_string_

Google generated spreadsheet ID. Available at plugin configuration page.
```
[gdocs st_id='ddk7f9g2_15g3w6q9fk' ...]
```

## wt\_id ##
**Required for Spreadsheets** <br />
_string_

Google generated worksheet ID. Available at plugin configuration page.
```
[gdocs wt_id='od6' ...]
```

## cell\_id ##
**Required for cells** <br />
_string_ `R<row>C<col>`

Cell coordinates in the worksheet. Specify in the form `R<row>C<col>`. For example, the following
```
[gdocs cell_id='R2C2' ...]
```
displays the cell in the second row and second column of the specified worksheet.

## headings ##
_comma-separated values_

Custom column headings to display in tables. These will replace the headings retrieved from Google. Headings are replaced from left to right, i.e. if there are 4 columns and you provide only 3 values, the 4th column heading will not be replaced.
```
[gdocs headings='First, Second, Third' type='spreadsheet' ...]
```

results in
```
<!-- Embedded worksheet -->
<thead>
  <tr>
    <th>First</th>
    <th>Second</th>
    <th>Third</th>
    <th>cxh4</th>    <!-- Not replaced -->
  </tr>
</thead>
```

## sort ##
_string_ `true | <javascript variable>`

Specifies parameters for tablesorter.

To use tablesorter with the default options, set sort to true in your shortcode, as follows:
```
[gdocs sort="true" ...]
```

To use tablesorter with custom options, include the parameters within braces in the sort attribute:
```
[gdocs sort="{cancelSelection:false}" ...]
```

Certain characters, such as square brackets, will confuse the shortcode parser. If you have complex parameters, pass them through a Javascript variable, as follows:
```
<script type='text/javascript'>
  var properties = {cancelSelection:false, sortList:[[1,1]]};
</script>
[gdocs sort="properties" ...]
```

[tablesorter Configuration»](http://tablesorter.com/docs/#Configuration)

## style ##
_comma-separated values_

Specifies CSS classes for documents and spreadsheets. If a corresponding stylesheet is found in `inline-google-docs/styles/`, it will be imported via a `<link>` tag.

For example,
```
[gdocs style='x, y, nice' ...]
```

If the plugin locates a readable file `inline-google-docs/styles/nice.css` or `inline-google-docs/styles/nice/nice.css`, the file will be imported into the HTML page. Furthermore, the DIV or TABLE element housing the Google Document or Spreadsheet will be given these values as classes.
```
<div id="..." class="gdocs x y nice">
```