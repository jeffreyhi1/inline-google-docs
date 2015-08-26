## Which PHP version does the plugin require? ##

PHP 5.

## Does it use iframes? ##

No, it doesn't.

## The plugin doesn't show up on my list of installed plugins. Why? ##

The most probable reason for this is because the user that your Web server runs as (usually `nobody` or `www-data`) does not have read permissions to the plugin files. To fix this, use `chgrp` to change the group of the files to one that your Web server runs as. For example,
```
    chgrp -R www-data .
```

## How do I create zebra patterns for my tables ? ##

As of v0.7, all rows and columns have an additional class that marks it as even or odd. Use these to create alternating patterns.

## How can I get the numbers to display as rounded, without decimal points? ##

The current plugin doesn't support any data manipulation (I would like to leave that to Google since they do such a great job.) To round your numbers, use the function ROUND (number, count) in your cell. Further documentation from Google is available [here](http://docs.google.com/support/bin/answer.py?answer=82712&funcType=Math&query=ROUND&searchSyntaxExact=1).