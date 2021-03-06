# Multiple Mailgun Plugin #

## Description ##

This plugin sends emails through Mailgun using their API and Supports adding multiple API to send from multiple domain!

## Installation ##

### Dependencies ###

This plugin is for phplist 3.3.0 or later and requires php version 5.4 or later.

It requires the Common Plugin to be installed. You must install, or upgrade to, the latest version. See <https://github.com/bramley/phplist-plugin-common>

It also requires the php curl extension to be installed.

### Set the plugin directory ###
The default plugin directory is `plugins` within the admin directory.

You can use a directory outside of the web root by changing the definition of `PLUGIN_ROOTDIR` in config.php.
The benefit of this is that plugins will not be affected when you upgrade phplist.

### Install through phplist ###
The recommended way to install is through the Plugins page (menu Config > Manage Plugins) using the package
URL `https://github.com/sh-sabbir/phplist-plugin-mailgun-multiple/archive/master.zip`.
The installation should create

* the file MultipleMailgun.php
* the directory MultipleMailgun

### Install manually ###
If the automatic installation does not work then you can install manually.
Download the plugin zip file from <https://github.com/sh-sabbir/phplist-plugin-mailgun-multiple/archive/master.zip>

Expand the zip file, then copy the contents of the plugins directory to your phplist plugins directory.
This should contain

* the file MultipleMailgun.php
* the directory MultipleMailgun

## Usage ##

For guidance on using the plugin see the plugin's page within the phplist documentation site <https://resources.phplist.com/plugin/mailgun>

## Support ##

Please raise any questions or problems in the user forum <https://discuss.phplist.org/>.

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version         Description
    1.1.2           Added Support for Multiple API
    1.0.1+20180609  Handle an exception when sending
    1.0.0+20170608  Initial release
