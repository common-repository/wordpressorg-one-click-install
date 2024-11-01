<?php /*

**************************************************************************

Plugin Name:  WordPress.org One-Click Install
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/wordpressorg-one-click-install/
Version:      1.2.2
Description:  Allows you to one-click install plugins directly from WordPress.org.
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************/

class WPorgClickToInstall {
	var $version = '1.2.2';
	var $gmscriptver = '20090526';
	var $stub = 'wordpressorg-one-click-install';
	var $passkey = false;

	// Initalize the plugin
	function __construct() {
		if ( !current_user_can('install_plugins') )
			return;

		// Load localization domain
		load_plugin_textdomain( 'wordpressorg-one-click-install', false, '/wordpressorg-one-click-install/localization' );

		// Register the hooks
		add_action( 'admin_menu',   array(&$this, 'register_page') );
		add_action( 'admin_init',   array(&$this, 'maybe_redirect') );
		add_action( 'admin_init',   array(&$this, 'maybe_output_gmscript') );
		add_action( 'admin_footer', array(&$this, 'output_gmscriptver') );

		// Get or generate the security passkey
		$this->passkey = get_option( 'wporgoci_passkey' );
		if ( false === $this->passkey ) {
			$this->passkey = wp_generate_password( 24, false );
			update_option( 'wporgoci_passkey', $this->passkey );
			add_action( 'admin_notices', array(&$this, 'install_notice') );
		}

		// Version checking
		$dbver = get_option( 'wporgoci_version' );
		if ( !$dbver )
			$dbver = 0;
		// Prior to v1.2.0, the GM script didn't have built in version checking
		// It's "1.2.1" because the notice never showed up properly in v1.2.0
		if ( 1 == version_compare( '1.2.1', $dbver ) ) {
			add_action( 'admin_notices', array(&$this, 'upgrade_notice') );
		}
	}


	// Register the general usage page
	function register_page() {
		add_submenu_page( 'plugins.php', __('WordPress.org Click To Install', 'wordpressorg-click-to-install'), __('WP.org Installer', 'wordpressorg-click-to-install'), 'install_plugins', $this->stub, array(&$this, 'the_page') );
	}


	// Tell the user to install the Greasemonkey script
	function install_notice() { ?>
		<div class="updated fade"><p style="line-height:1.5em"><?php printf( __('In order to use <strong>WordPress.org One-Click Install</strong>, you will also need to install the accompanying Greasemonkey script or generic bookmarklet. For details, <a href="%s">click here</a>.', 'wordpressorg-click-to-install'), admin_url( 'plugins.php?page=' . $this->stub ) ); ?></p></div>
<?php
	}


	// Tell the user to upgrade the Greasemonkey script
	function upgrade_notice() { ?>
		<div class="updated fade"><p style="line-height:1.5em"><?php printf( __("The Greasemonkey script for <strong>WordPress.org One-Click Install</strong> has been updated. Please visit the plugin's <a href='%s'>information page</a> to update.", 'wordpressorg-click-to-install'), admin_url( 'plugins.php?page=' . $this->stub ) ); ?></p></div>
<?php
	}


	// Output the latest script version (for the GM script to use)
	function output_gmscriptver() {
		echo '<div id="wpoci-latestversion" class="hidden">' . $this->gmscriptver . "</div>\n";
	}


	// Maybe redirect to the plugin installer
	function maybe_redirect() {
		if ( current_user_can('install_plugins') && !empty($_GET['page']) && $_GET['page'] == $this->stub && !empty($_GET['passkey']) && $_GET['passkey'] === $this->passkey && !empty($_GET['plugin']) ) {
			// WordPress 2.8 vs. 2.7
			$url = ( function_exists('esc_attr') ) ? 'update.php?action=install-plugin' : 'plugin-install.php?tab=install';
			wp_redirect( add_query_arg( '_wpnonce', wp_create_nonce( 'install-plugin_' . $_GET['plugin']), admin_url( $url . '&plugin=' . $_GET['plugin'] ) ) );
			exit();
		}
	}


	// Maybe output the Greasemonkey script
	function maybe_output_gmscript() {
		if ( current_user_can('install_plugins') && !empty($_GET['page']) && $_GET['page'] == $this->stub && !empty($_GET['action']) && ( 'gmscript.user.js' == $_GET['action'] || 'gmscript.js' == $_GET['action'] ) ) {
			header( 'Content-Type: text/javascript; charset=' . get_bloginfo( 'charset' ) );
			nocache_headers();

			// The <script> tags are to make the following Javascript highlight in my stupid editor

?>// <script>
// ==UserScript==
// @name           WordPress.org One-Click Installer for <?php bloginfo('url'); echo "/\r\n"; ?>
// @namespace      http://www.viper007bond.com/
// @description    <?php echo __('Adds an "Install" button to WordPress.org plugin pages allowing you to one-click install plugins to your blog. Requires the accompanying WordPress plugin.', 'wordpressorg-one-click-install') . "\r\n"; ?>
// @version        <?php echo $this->gmscriptver . "\r\n"; ?>
// @include        http://wordpress.org/extend/plugins/*
// @include        <?php echo admin_url(); ?>*
// @require        http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js
// ==/UserScript==

var WPorgOCI_Stub;

// WordPress.org
if ( WPorgOCI_Stub = location.pathname.match( /\extend\/plugins\/([a-z0-9-]+)/i ) ) {
	WPorgOCI_Stub = WPorgOCI_Stub[1];

	// If the @require was successful in loading jQuery (i.e. we're using the real Greasemonkey)
	if ( typeof $ == 'function') {
		$("#fyi").before('<p class="button"><a href="<?php echo js_escape( admin_url( 'plugins.php?page=' . $this->stub ) ); ?>&amp;passkey=<?php echo $this->passkey; ?>&amp;plugin=' + WPorgOCI_Stub + '"><?php printf( __('Install to %s', 'wordpressorg-one-click-install'), js_escape( get_bloginfo('name') ) ); ?></a></p>');
	}

	// Otherwise the user must be using a Greasemonkey clone that doesn't support @require
	// So let's replicate the above code in normal Javascript (ugh)
	else {
		// Create the button
		var WPorgOCI_button = document.createElement("p");
		WPorgOCI_button.setAttribute("class", "button");

		// Create the button link
		var WPorgOCI_button_link = document.createElement("a");
		WPorgOCI_button_link.setAttribute("href", "<?php echo js_escape( admin_url( 'plugins.php?page=' . $this->stub ) ); ?>&passkey=<?php echo $this->passkey; ?>&plugin=" + WPorgOCI_Stub );

		// Create the text for the link
		var WPorgOCI_button_text = document.createTextNode("<?php echo js_escape( sprintf( __('Install to %s', 'wordpressorg-one-click-install'), get_bloginfo('name') ) ); ?>");

		// Nest the elements
		WPorgOCI_button_link.appendChild(WPorgOCI_button_text);
		WPorgOCI_button.appendChild(WPorgOCI_button_link);

		// Get the "FYI" box
		var WPorgOCI_InfoBox = document.getElementById("fyi");

		// Insert the button before the box
		WPorgOCI_InfoBox.parentNode.insertBefore( WPorgOCI_button, WPorgOCI_InfoBox );
	}
}

// Admin area most likely
else {
	if ( typeof $ == 'function') {
		$("#wpoci-not-installed").addClass("hidden");
		$("#wpoci-is-installed").removeClass("hidden");
	} else {
		if ( document.getElementById("wpoci-not-installed") ) {
			document.getElementById("wpoci-not-installed").className = "hidden";
			document.getElementById("wpoci-is-installed").className = "";
		}
	}

	// Version check
	if ( document.getElementById("wpoci-latestversion") ) {
		if ( <?php echo $this->gmscriptver; ?> < parseInt( document.getElementById("wpoci-latestversion").innerHTML ) ) {
			if ( confirm( '<?php echo js_escape( __( 'A newer version of the WordPress.org One-Click Installer Greasemonkey script is available. Do you wish to update?', 'wordpressorg-one-click-install' ) ); ?>' ) ) {
				location.assign( '<?php echo admin_url( 'plugins.php?page=wordpressorg-one-click-install&action=gmscript.user.js' ); ?>' );
			}
		}
	}
}

// </script><?php

			exit();
		}
	}


	// This is the general usage page including instructions, etc.
	function the_page() {
		update_option( 'wporgoci_version', $this->version );

		?>

<div class="wrap">
<?php if ( function_exists('screen_icon') ) screen_icon(); ?>
	<h2><?php _e( 'WordPress.org One-Click Install' , 'wordpressorg-one-click-install' ); ?></h2>

	<h3><?php _e( 'Browser Script Installation Instructions' , 'wordpressorg-one-click-install' ); ?></h3>

	<p><?php _e( 'In order to use this plugin, you must also install one of two scripts into your browser:' , 'wordpressorg-one-click-install' ); ?></p>

	<ul class="ul-disc">
		<li><?php printf( __( 'The first option (and the recommended one) is a Greasemonkey script. Greasemonkey is <a href="%s" title="Click here to go install Greasemonkey">an addon</a> for Firefox, but is also supported natively (with varying success) in other browsers such as Opera. Greasemonkey scripts allow you to manipulate how any website on the Internet displays in your browser. By using this option, a new orange button will show up on WordPress.org automatically.' , 'wordpressorg-one-click-install' ), 'https://addons.mozilla.org/en-US/firefox/addon/748' ); ?></li>
		<li><?php printf( __( 'The second option, which should only be used in browsers that cannot easily use Greasemonkey scripts (such as Internet Explorer), is a <a href="%s">bookmarklet</a>. It works like a bookmark (also known as a favorite) where you simply click it to initiate the process. Unlike the Greasemonkey script, it will be unable to add a new button to the WordPress.org site itself and you will instead have to click the bookmarklet.' , 'wordpressorg-one-click-install' ), 'http://en.wikipedia.org/wiki/Bookmarklet' ); ?></li>
	</ul>

	<div id="wpoci-not-installed">
		<h3><?php _e( 'Greasemonkey Script Installation', 'wordpressorg-one-click-install' ); ?></h3>

		<p><?php _e( 'Click the following button to install the Greasemonkey script.', 'wordpressorg-one-click-install' ); ?></p>
		
		<?php printf( __( "<strong>Firefox Users:</strong> Make sure you have <a href='%s' title='Click here to go install Greasemonkey'>installed the addon</a> and restarted Firefox (that's important!) before clicking." , 'wordpressorg-one-click-install' ), 'https://addons.mozilla.org/en-US/firefox/addon/748' ); ?></p>

		<p style="padding-top:10px"><a class="button" href="<?php echo htmlspecialchars( add_query_arg( 'action', 'gmscript.user.js', admin_url( 'plugins.php?page=' . $this->stub ) ) ); ?>"><?php _e( 'Install Greasemonkey Script' , 'wordpressorg-one-click-install' ); ?></a></p>
	</div>

	<div id="wpoci-is-installed" class="hidden">
		<h3><?php _e( 'Greasemonkey Script Status', 'wordpressorg-one-click-install' ); ?></h3>

		<p><?php _e( 'The Greasemonkey script is <strong style="color:#4E9E00">installed and working properly</strong>.', 'wordpressorg-one-click-install' ); ?></p>
	</div>

	<h3 style="margin-top:30px"><?php _e( 'Bookmarklet Script Installation' , 'wordpressorg-one-click-install' ); ?></h3>

	<p><?php _e( 'If you are unable to use the Greasemonkey script, you can use this bookmarklet instead. Simply drag-and-drop the following link to your bookmarks bar or right click it and add it to your favorites:' , 'wordpressorg-one-click-install' ); ?></p>

	<p><a href="javascript:WPorgOCIStub=location.href.match(/http:\/\/wordpress.org\/extend\/plugins\/([a-z0-9-]+)/i);if(!WPorgOCIStub||!WPorgOCIStub[1]){alert('<?php echo js_escape( __('This bookmarklet can only be used on individual plugin pages on WordPress.org.', 'wordpressorg-one-click-install') ); ?>')}else{location.assign('<?php echo admin_url( 'plugins.php?page=' . $this->stub ); ?>&amp;passkey=<?php echo $this->passkey; ?>&amp;plugin='+WPorgOCIStub[1])}"><?php printf( __( 'Install Plugin To %s' , 'wordpressorg-one-click-install' ), get_bloginfo('name') ); ?></a></p>

	<h3 style="margin-top:30px"><?php _e( 'Plugin Usage Instructions' , 'wordpressorg-one-click-install' ); ?></h3>

	<p><?php printf( __( "If you're already here and just wanting to install a new plugin, then you don't need this plugin. Just visit your <a href='%s'>built-in installer</a>." , 'wordpressorg-one-click-install' ), admin_url('plugin-install.php') ); ?></p>

	<p><?php _e( "However if someone sends you a link to a plugin at WordPress.org and you then want to install that plugin, this is where this plugin comes into play. Rather than having to either manually install that plugin or find it again in your admin area, just click the new button that'll now show up on WordPress.org or the bookmarklet.", 'wordpressorg-one-click-install' ); ?></p>
</div>
<?php
	}


	// PHP4 compatibility
	function WPorgClickToInstall() {
		$this->__construct();
	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'WPorgClickToInstall' ); function WPorgClickToInstall() { global $WPorgClickToInstall; $WPorgClickToInstall = new WPorgClickToInstall(); }

?>