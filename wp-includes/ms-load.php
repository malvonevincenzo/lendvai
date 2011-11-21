<?php
/**
 * These functions are needed to load Multisite.
 *
 * @since 3.0.0
 *
 * @package WordPress
 * @subpackage Multisite
 */

/**
 * Whether a subdomain configuration is enabled.
 *
 * @since 3.0.0
 *
 * @return bool True if subdomain configuration is enabled, false otherwise.
 */
function is_subdomain_install() {
	if ( defined('SUBDOMAIN_INSTALL') )
		return SUBDOMAIN_INSTALL;

	if ( defined('VHOST') && VHOST == 'yes' )
		return true;

	return false;
}

/**
 * Returns array of network plugin files to be included in global scope.
 *
 * The default directory is wp-content/plugins. To change the default directory
 * manually, define <code>WP_PLUGIN_DIR</code> and <code>WP_PLUGIN_URL</code>
 * in wp-config.php.
 *
 * @access private
 * @since 3.1.0
 * @return array Files to include
 */
function wp_get_active_network_plugins() {
	$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
	if ( empty( $active_plugins ) )
		return array();

	$plugins = array();
	$active_plugins = array_keys( $active_plugins );
	sort( $active_plugins );

	foreach ( $active_plugins as $plugin ) {
		if ( ! validate_file( $plugin ) // $plugin must validate as file
			&& '.php' == substr( $plugin, -4 ) // $plugin must end with '.php'
			&& file_exists( WP_PLUGIN_DIR . '/' . $plugin ) // $plugin must exist
			)
		$plugins[] = WP_PLUGIN_DIR . '/' . $plugin;
	}
	return $plugins;
}

/**
 * Checks status of current blog.
 *
 * Checks if the blog is deleted, inactive, archived, or spammed.
 *
 * Dies with a default message if the blog does not pass the check.
 *
 * To change the default message when a blog does not pass the check,
 * use the wp-content/blog-deleted.php, blog-inactive.php and
 * blog-suspended.php drop-ins.
 *
 * @return bool|string Returns true on success, or drop-in file to include.
 */
function ms_site_check() {
	global $wpdb, $current_blog;

	// Allow short-circuiting
	$check = apply_filters('ms_site_check', null);
	if ( null !== $check )
		return true;

	// Allow super admins to see blocked sites
	if ( is_super_admin() )
		return true;

	if ( '1' == $current_blog->deleted ) {
		if ( file_exists( WP_CONTENT_DIR . '/blog-deleted.php' ) )
			return WP_CONTENT_DIR . '/blog-deleted.php';
		else
			wp_die( __( 'This user has elected to delete their account and the content is no longer available.' ), '', array( 'response' => 410 ) );
	}

	if ( '2' == $current_blog->deleted ) {
		if ( file_exists( WP_CONTENT_DIR . '/blog-inactive.php' ) )
			return WP_CONTENT_DIR . '/blog-inactive.php';
		else
			wp_die( sprintf( __( 'This site has not been activated yet. If you are having problems activating your site, please contact <a href="mailto:%1$s">%1$s</a>.' ), str_replace( '@', ' AT ', get_site_option( 'admin_email', "support@{$current_site->domain}" ) ) ) );
	}

	if ( $current_blog->archived == '1' || $current_blog->spam == '1' ) {
		if ( file_exists( WP_CONTENT_DIR . '/blog-suspended.php' ) )
			return WP_CONTENT_DIR . '/blog-suspended.php';
		else
			wp_die( __( 'This site has been archived or suspended.' ), '', array( 'response' => 410 ) );
	}

	return true;
}

/**
 * Sets current site name.
 *
 * @access private
 * @since 3.0.0
 * @return object $current_site object with site_name
 */
function get_current_site_name( $current_site ) {
	global $wpdb;

	$current_site->site_name = wp_cache_get( $current_site->id . ':site_name', 'site-options' );
	if ( ! $current_site->site_name ) {
		$current_site->site_name = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->sitemeta WHERE site_id = %d AND meta_key = 'site_name'", $current_site->id ) );
		if ( ! $current_site->site_name )
			$current_site->site_name = ucfirst( $current_site->domain );
	}
	wp_cache_set( $current_site->id . ':site_name', $current_site->site_name, 'site-options' );

	return $current_site;
}

/**
 * Sets current_site object.
 *
 * @access private
 * @since 3.0.0
 * @return object $current_site object
 */
function wpmu_current_site() {
	global $wpdb, $current_site, $domain, $path, $sites, $cookie_domain;
	if ( defined( 'DOMAIN_CURRENT_SITE' ) && defined( 'PATH_CURRENT_SITE' ) ) {
		$current_site->id = defined( 'SITE_ID_CURRENT_SITE' ) ? SITE_ID_CURRENT_SITE : 1;
		$current_site->domain = DOMAIN_CURRENT_SITE;
		$current_site->path   = $path = PATH_CURRENT_SITE;
		if ( defined( 'BLOG_ID_CURRENT_SITE' ) )
			$current_site->blog_id = BLOG_ID_CURRENT_SITE;
		elseif ( defined( 'BLOGID_CURRENT_SITE' ) ) // deprecated.
			$current_site->blog_id = BLOGID_CURRENT_SITE;
		if ( DOMAIN_CURRENT_SITE == $domain )
			$current_site->cookie_domain = $cookie_domain;
		elseif ( substr( $current_site->domain, 0, 4 ) == 'www.' )
			$current_site->cookie_domain = substr( $current_site->domain, 4 );
		else
			$current_site->cookie_domain = $current_site->domain;

		wp_load_core_site_options( $current_site->id );

		return $current_site;
	}

	$current_site = wp_cache_get( 'current_site', 'site-options' );
	if ( $current_site )
		return $current_site;

	$sites = $wpdb->get_results( "SELECT * FROM $wpdb->site" ); // usually only one site
	if ( 1 == count( $sites ) ) {
		$current_site = $sites[0];
		wp_load_core_site_options( $current_site->id );
		$path = $current_site->path;
		$current_site->blog_id = $wpdb->get_var( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s AND path = %s", $current_site->domain, $current_site->path ) );
		$current_site = get_current_site_name( $current_site );
		if ( substr( $current_site->domain, 0, 4 ) == 'www.' )
			$current_site->cookie_domain = substr( $current_site->domain, 4 );
		wp_cache_set( 'current_site', $current_site, 'site-options' );
		return $current_site;
	}
	$path = substr( $_SERVER[ 'REQUEST_URI' ], 0, 1 + strpos( $_SERVER[ 'REQUEST_URI' ], '/', 1 ) );

	if ( $domain == $cookie_domain )
		$current_site = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->site WHERE domain = %s AND path = %s", $domain, $path ) );
	else
		$current_site = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->site WHERE domain IN ( %s, %s ) AND path = %s ORDER BY CHAR_LENGTH( domain ) DESC LIMIT 1", $domain, $cookie_domain, $path ) );

	if ( ! $current_site ) {
		if ( $domain == $cookie_domain )
			$current_site = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->site WHERE domain = %s AND path='/'", $domain ) );
		else
			$current_site = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->site WHERE domain IN ( %s, %s ) AND path = '/' ORDER BY CHAR_LENGTH( domain ) DESC LIMIT 1", $domain, $cookie_domain, $path ) );
	}

	if ( $current_site ) {
		$path = $current_site->path;
		$current_site->cookie_domain = $cookie_domain;
		return $current_site;
	}

	if ( is_subdomain_install() ) {
		$sitedomain = substr( $domain, 1 + strpos( $domain, '.' ) );
		$current_site = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->site WHERE domain = %s AND path = %s", $sitedomain, $path) );
		if ( $current_site ) {
			$current_site->cookie_domain = $current_site->domain;
			return $current_site;
		}

		$current_site = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->site WHERE domain = %s AND path='/'", $sitedomain) );
	}

	if ( $current_site || defined( 'WP_INSTALLING' ) ) {
		$path = '/';
		return $current_site;
	}

	// Still no dice.
	if ( 1 == count( $sites ) )
		wp_die( sprintf( /*WP_I18N_BLOG_DOESNT_EXIST*/'Questo sito  non esiste. Provare <a href="%s">%s</a>.'/*/WP_I18N_BLOG_DOESNT_EXIST*/, $sites[0]->domain . $sites[0]->path ) );
	else
		wp_die( /*WP_I18N_NO_SITE_DEFINED*/'Nessun sito definito per questo host. Se si è i proprietari di questo sito, per ottenere aiuto, verificare <a href="http://codex.wordpress.org/Debugging_a_WordPress_Network">Debugging di un network WordPress</a> (in inglese)'/*/WP_I18N_NO_SITE_DEFINED*/ );
}

/**
 * Displays a failure message.
 *
 * Used when a blog's tables do not exist. Checks for a missing $wpdb->site table as well.
 *
 * @access private
 * @since 3.0.0
 */
function ms_not_installed() {
	global $wpdb, $domain, $path;

	$title = /*WP_I18N_FATAL_ERROR*/'Errore nello stabilire una connessione al database'/*/WP_I18N_FATAL_ERROR*/;
	$msg  = '<h1>' . $title . '</h1>';
	if ( ! is_admin() )
		die( $msg );
	$msg .= '<p>' . /*WP_I18N_CONTACT_OWNER*/'Se il proprio sito non si visualizza contattare il proprietario di questo network.'/*/WP_I18N_CONTACT_OWNER*/ . '';
	$msg .= ' ' . /*WP_I18N_CHECK_MYSQL*/'Se sei il proprietario di questo network verifica che MySQL sia in esecuzione e che non ci siano errori nelle tabelle del database.'/*/WP_I18N_CHECK_MYSQL*/ . '</p>';
	if ( false && !$wpdb->get_var( "SHOW TABLES LIKE '$wpdb->site'" ) )
		$msg .= '<p>' . sprintf( /*WP_I18N_TABLES_MISSING_LONG*/'<strong>Tabelle del database mancanti.</strong> Ciò significa che MySQL non è in esecuzione, WordPress non è stato installato correttamente oppure qualcuno ha cancellato <code>%s</code>. Occorre assolutamente controllare il database.'/*/WP_I18N_TABLES_MISSING_LONG*/, $wpdb->site ) . '</p>';
	else
		$msg .= '<p>' . sprintf( /*WP_I18N_NO_SITE_FOUND*/'<strong>Impossibile trovare il sito <code>%1$s</code>.</strong> Si è cercata la tabella <code>%2$s</code> nel database <code>%3$s</code>. &Egrave; corretto?'/*/WP_I18N_NO_SITE_FOUND*/, rtrim( $domain . $path, '/' ), $wpdb->blogs, DB_NAME ) . '</p>';
	$msg .= '<p><strong>' . /*WP_I18N_WHAT_DO_I_DO*/'What do I do now?'/*WP_I18N_WHAT_DO_I_DO*/ . '</strong> ';
	$msg .= /*WP_I18N_RTFM*/'Si veda la pagina <a target="_blank" href="http://codex.wordpress.org/Debugging_a_WordPress_Network">bug report</a>. Alcune delle linee guida lì presenti potrebbero aiutare a capire cosa è andato storto.'/*/WP_I18N_RTFM*/;
	$msg .= ' ' . /*WP_I18N_STUCK*/'Se si continua a visualizzare questo messaggio, verificare che il database contenga le seguenti tabelle:'/*/WP_I18N_STUCK*/ . '</p><ul>';
	foreach ( $wpdb->tables('global') as $t => $table ) {
		if ( 'sitecategories' == $t )
			continue;
		$msg .= '<li>' . $table . '</li>';
	}
	$msg .= '</ul>';

	wp_die( $msg, $title );
}

?>