**This project has been deprecated in favor of better, more secure ways to embed your google document. The author apologizes for not responding to fix requests as he does not have the resources to test the plugin on all server configurations (which are almost infinite).**

Many people maintain dynamic content on Google Documents or hold volatile data on Google Spreadsheets. These change when collaborators save an update or users submit a form. Occasionally, one may wish to embed the contents of one's Google Documents or Spreadsheets in a post or page to reflect the latest updates on one's blog. This plugin seeks to provide this functionality without using an `<iframe>`. In addition, it caches contents of the Google Documents or Spreadsheets to speed up page loading.

_Currently, the plugin can only access published documents, but can access both private and public spreadsheets._

![http://img40.imageshack.us/img40/9343/sampledoc.png](http://img40.imageshack.us/img40/9343/sampledoc.png)<br />
_Embedding a document in a blog post, inline._

## Features ##
  * Document, Spreadsheet, and cell embedding
  * Table styling, including alternating patterns
  * Document styling
  * Caching
  * Tablesorter
  * Custom column headings

[More»](Guide.md)

## Blogs Using IGD ##
  * [Newbucs.com](http://newbucs.com/org-stats/3a-pitching/)

## Important ##
Should you decide to checkout a copy of the plugin using SVN, make sure you correct the file permissions on your own as SVN does not preserve file permissions. I recommend the following settings (assuming FTP user as codex and Web user as www-data):
```
-rw-r----- codex www-data some-file.php
drwxr-x--- codex www-data some-directory
```

As for the cache and error log:
```
drwxrwx--- codex www-data cache
-rw-rw---- codex www-data error.log.php
```

[More»](Installation.md)

## Changlog `[0.9]` ##
  1. Capability to embed single cell added
  1. Capability to import stylesheets from user-specified directory added
  1. Bug caused by Wordpress URL fixed
  1. Document styling added
  1. Custom classes for documents and spreadsheets added
  1. Other minor bug fixes

## Changelog `[0.8]` ##
  1. Zend library reduced
  1. Support for WPMU added
  1. Migrated to v2.7, implemented Settings API
  1. Tablesorter functionality added (blue skin included)
  1. Error handling improved
  1. Links within plugin modified, folder name may now be changed by user
  1. Zend not found error fixed

## Changelog `[0.7]` ##
  1. Improved error and exception management
  1. Support for Google Apps
  1. Document images enabled
  1. File permissions corrected for improved security
  1. Additional class for `tr` and `td` to allow alternating (zebra) styling


---


[Discuss](http://groups.google.com/group/inline-google-docs) | [Submit a Bug](http://code.google.com/p/inline-google-docs/issues/list) | [Donate](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=codex%2eis%2epoetry%40gmail%2ecom&item_name=Inline%20Google%20Docs&no_shipping=0&no_note=1&tax=0&currency_code=SGD&lc=SG&bn=PP%2dDonationsBF&charset=UTF%2d8)