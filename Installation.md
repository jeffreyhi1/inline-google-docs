  1. Download and extract the plugin
  1. Upload the `inline-google-docs` folder into the `/wp-content/plugins/` directory
  1. Activate the plugin through the **Plugins** menu in WordPress
  1. Go to **Settings**, then navigate to **Inline Google Docs**
  1. Provide the plugin with your Google account login credentials
  1. Input proxy settings if you are behind a proxy, then click on **Save Changes**
  1. The plugin will display the document/spreadsheet id's for your documents and spreadsheets

# Important #

Should you decide to checkout a copy of the plugin using SVN, make sure you correct the file permissions on your own as SVN does not preserve file permissions. I recommend the following settings (assuming FTP user as `codex` and Web user as `www-data`):
```
-rw-r----- codex www-data some-file.php
drwxr-x--- codex www-data some-directory
```

As for the cache and error log:
```
drwxrwx--- codex www-data cache
-rw-rw---- codex www-data error.log.php
```

The UNIX commands are provided below:
```
cd /path/to/Wordpress/wp-content/plugins/inline-google-docs;
find . -type d -exec chmod 750 {} \;
find . -type f -exec chmod 640 {} \;
chmod -R g+w ./cache;
```