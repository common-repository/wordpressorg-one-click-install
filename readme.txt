=== WordPress.org One-Click Install ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: plugin, install
Requires at least: 2.7
Tested up to: 2.8
Stable tag: trunk

Allows you to one-click install plugins directly from WordPress.org.

== Description ==

If you've ever been sent a link to a plugin hosted on WordPress.org and wanted to install that plugin, you currently have two options:

1. Download the plugin manually, upload it to your blog either via the admin interface or via FTP, and then activate it.
1. Visit your admin area and find the plugin again via the Add New Plugin interface.

Both options are annoying, especially since you're already staring at the plugin you want to install.

Well now there's a much easier solution! Simply install this plugin to your blog and then either install it's [Greasemonkey](https://addons.mozilla.org/en-US/firefox/addon/748) script or it's [bookmarklet](http://en.wikipedia.org/wiki/Bookmarklet) from the plugin's page and you're all set. You will then be able to literally one-click install any plugin directly from WordPress.org.

== Installation ==

Sadly you'll need to install this plugin the old fashioned way. Manually upload the plugin files or install it via Plugins -> Add New and then activate it.

Once activated, visit Plugins -> WP.org Installer for details on how to install the script into your browser.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

== Frequently Asked Questions ==

= I don't see a new orange button below the normal "Download" button on WordPress.org like in your screenshot. What gives? =

Did you make sure to install the [Greasemonkey](https://addons.mozilla.org/en-US/firefox/addon/748) script? It's what adds the button to WordPress.org. To install it, go to your admin area and visit Plugins -> WP.org Installer and then click the button there.

= Can I use this plugin with multiple blogs? Will multiple buttons show up? =

Yes, you can and they will!

== Screenshots ==

1. The new button that'll show up on WordPress.org. Click it to install the plugin.

== ChangeLog ==

**Version 1.2.2**

* Add a missing closing bracket. Odd that it didn't throw a parse error for me on any of my servers.

**Version 1.2.1**

* Code that had been intended to make an upgrade notice pop up didn't work. This should fix it.

**Version 1.2.0**

Please upgrade your Greasemonkey script!

* Added version checking to the Greasemonkey script. From now on whenever you visit your WordPress admin area, the Greasemonkey script will do a version compare to make sure it's the latest version of the script.
* Added non-jQuery fallback code. jQuery will still be used if possible (i.e. if you're using the official Greasemonkey Firefox addon), but for Opera users and others who only have limited Greasemonkey-style script support (i.e. no `@require` support), the script should now work for you.
* The Greasemonkey script will now modify the plugin's information page to let you know that it's installed and working properly (it'll replace the install buton with the notice).
* Bookmarklet bugfixes.

**Version 1.1.0**

* Added a bookmarklet for those poor non-Firefox users.

**Version 1.0.0**

* Inital release.
