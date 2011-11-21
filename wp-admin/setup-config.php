<?php
/**
 * Retrieves and creates the wp-config.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the wp-config.php to be created using this page.
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * We are installing.
 *
 * @package WordPress
 */
define('WP_INSTALLING', true);

/**
 * We are blissfully unaware of anything.
 */
define('WP_SETUP_CONFIG', true);

/**
 * Disable error reporting
 *
 * Set this to error_reporting( E_ALL ) or error_reporting( E_ALL | E_STRICT ) for debugging
 */
error_reporting(0);

/**#@+
 * These three defines are required to allow us to use require_wp_db() to load
 * the database class while being wp-content/db.php aware.
 * @ignore
 */
define('ABSPATH', dirname(dirname(__FILE__)).'/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_DEBUG', false);
/**#@-*/

require_once(ABSPATH . WPINC . '/load.php');
require_once(ABSPATH . WPINC . '/version.php');
wp_check_php_mysql_versions();

require_once(ABSPATH . WPINC . '/compat.php');
require_once(ABSPATH . WPINC . '/functions.php');
require_once(ABSPATH . WPINC . '/class-wp-error.php');

if (!file_exists(ABSPATH . 'wp-config-sample.php'))
	wp_die('Sorry, occorre poter lavorare sul file wp-config-sample.php. Ricaricare questo file dalla propria installazione di WordPress.');

$configFile = file(ABSPATH . 'wp-config-sample.php');

// Check if wp-config.php has been created
if (file_exists(ABSPATH . 'wp-config.php'))
	wp_die("<p>Il file 'wp-config.php' esiste già. Se si desidera azzerare un qualsiasi elemento di configurazione presente in questo file, per prima cosa cancellarlo. &Egrave; possibile ora provare <a href='install.php'>una nuova installazione</a>.</p>");

// Check if wp-config.php exists above the root directory but is not part of another install
if (file_exists(ABSPATH . '../wp-config.php') && ! file_exists(ABSPATH . '../wp-settings.php'))
	wp_die("<p>Il file 'wp-config.php' esiste già ad un livello superiore rispetto quello di installazione di WordPress. Se si desidera azzerare un qualsiasi elemento di configurazione presente in questo file, per prima cosa cancellarlo. &Egrave; possibile ora provare <a href='install.php'>una nuova installazione</a>.</p>");

if ( version_compare( $required_php_version, phpversion(), '>' ) )
	wp_die( sprintf( /*WP_I18N_OLD_PHP*/'Your server is running PHP version %1$s but WordPress requires at least %2$s.'/*/WP_I18N_OLD_PHP*/, phpversion(), $required_php_version ) );

if ( !extension_loaded('mysql') && !file_exists(ABSPATH . 'wp-content/db.php') )
	wp_die( /*WP_I18N_OLD_MYSQL*/'L\'installazione PHP appare mancante dell\'estensione MySQL che Ã¨ necessario per utilizzare WordPress.'/*/WP_I18N_OLD_MYSQL*/ );

if (isset($_GET['step']))
	$step = $_GET['step'];
else
	$step = 0;

/**
 * Display setup wp-config.php file header.
 *
 * @ignore
 * @since 2.3.0
 * @package WordPress
 * @subpackage Installer_WP_Config
 */
function display_header() {
	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>WordPress &rsaquo; Impostazioni di configurazione</title>
<link rel="stylesheet" href="css/install.css" type="text/css" />

</head>
<body>
<h1 id="logo"><img alt="WordPress" src="images/wordpress-logo.png" /></h1>
<?php
}//end function display_header();

switch($step) {
	case 0:
		display_header();
?>

<p>Benvenuti in WordPress. Prima di iniziare. occorre fornire alcune informazioni sul database. Prima di poter proseguire si dovranno conoscere i seguenti dati.</p>
<ol>
	<li>Nome del database</li>
	<li>Nome utente del atabase</li>
	<li>Password del database</li>
	<li>Host del database</li>
	<li>Prefisso tabelle (se si desidera eseguire più copie di WordPress su un singolo database) </li>
</ol>
<p><strong>Se per qualsiasi ragione questo file di ccreazione automatica non dovesse funzionare, non preoccupatevi. Tutto ci&ograve; che fa &egrave; compilare il file di configurazione con le informazioni sul database. &egrave; possibile anche in maniera semplice aprire il file  <code>wp-config-sample.php</code> in un qualsiasi editor di testi, compilare manualmente le informazioni e salvarlo come <code>wp-config.php</code>. </strong></p>
<p>Con tutta probabilit&agrave;, queste informazioni vi sono state fornite dal forstro fornitore di hosting. Se non si dispone di queste informazioni, occorrerà contattare il vostro fornitore prima di proseguire. Se si è pronti a proseguire&hellip;</p>

<p class="step"><a href="setup-config.php?step=1<?php if ( isset( $_GET['noapi'] ) ) echo '&amp;noapi'; ?>" class="button">Iniziamo!</a></p>
<?php
	break;

	case 1:
		display_header();
	?>
<form method="post" action="setup-config.php?step=2">
	<p>Di seguito occorrer&agrave; inserire le informazioni sulla connessione al database connection details. Se non si è sicuri del significato o del valore di queste impostazioni, contattare il proprio fornitore di hosting. </p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname">Nome del database</label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="wordpress" /></td>
			<td>Il nome del database nel quale far girare WP. </td>
		</tr>
		<tr>
			<th scope="row"><label for="uname">Nome utente</label></th>
			<td><input name="uname" id="uname" type="text" size="25" value="username" /></td>
			<td>Il nome utente MySQL</td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd">Password</label></th>
			<td><input name="pwd" id="pwd" type="text" size="25" value="password" /></td>
			<td>... e la password MySQL.</td>
		</tr>
		<tr>
			<th scope="row"><label for="dbhost">Host del database</label></th>
			<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
			<td>Se il valore <code>localhost</code> non funziona, occorre ottenere questa informazione dal proprio fornitore di hosting.</td>
		</tr>
		<tr>
			<th scope="row"><label for="prefix">Prefisso tabelle</label></th>
			<td><input name="prefix" id="prefix" type="text" id="prefix" value="wp_" size="25" /></td>
			<td>Cambiare questo valore se si desidera eseguire più installazioni di WordPress su di un singolo database.</td>
		</tr>
	</table>
	<?php if ( isset( $_GET['noapi'] ) ) { ?><input name="noapi" type="hidden" value="true" /><?php } ?>
	<p class="step"><input name="submit" type="submit" value="Invio" class="button" /></p>
</form>
<?php
	break;

	case 2:
	$dbname  = trim($_POST['dbname']);
	$uname   = trim($_POST['uname']);
	$passwrd = trim($_POST['pwd']);
	$dbhost  = trim($_POST['dbhost']);
	$prefix  = trim($_POST['prefix']);
	if ( empty($prefix) )
		$prefix = 'wp_';

	// Validate $prefix: it can only contain letters, numbers and underscores
	if ( preg_match( '|[^a-z0-9_]|i', $prefix ) )
		wp_die( /*WP_I18N_BAD_PREFIX*/'<strong>ERRORE</strong>: la "Table Prefix" puÃ² contenere solo numeri, lettere e carattere di sottolineatura.'/*/WP_I18N_BAD_PREFIX*/ );

	// Test the db connection.
	/**#@+
	 * @ignore
	 */
	define('DB_NAME', $dbname);
	define('DB_USER', $uname);
	define('DB_PASSWORD', $passwrd);
	define('DB_HOST', $dbhost);
	/**#@-*/

	// We'll fail here if the values are no good.
	require_wp_db();
	if ( ! empty( $wpdb->error ) ) {
		$back = '<p class="step"><a href="setup-config.php?step=1" onclick="javascript:history.go(-1);return false;" class="button">Try Again</a></p>';
		wp_die( $wpdb->error->get_error_message() . $back );
	}

	// Fetch or generate keys and salts.
	$no_api = isset( $_POST['noapi'] );
	require_once( ABSPATH . WPINC . '/plugin.php' );
	require_once( ABSPATH . WPINC . '/l10n.php' );
	require_once( ABSPATH . WPINC . '/pomo/translations.php' );
	if ( ! $no_api ) {
		require_once( ABSPATH . WPINC . '/class-http.php' );
		require_once( ABSPATH . WPINC . '/http.php' );
		wp_fix_server_vars();
		/**#@+
		 * @ignore
		 */
		function get_bloginfo() {
			return ( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . str_replace( $_SERVER['PHP_SELF'], '/wp-admin/setup-config.php', '' ) );
		}
		/**#@-*/
		$secret_keys = wp_remote_get( 'https://api.wordpress.org/secret-key/1.1/salt/' );
	}

	if ( $no_api || is_wp_error( $secret_keys ) ) {
		$secret_keys = array();
		require_once( ABSPATH . WPINC . '/pluggable.php' );
		for ( $i = 0; $i < 8; $i++ ) {
			$secret_keys[] = wp_generate_password( 64, true, true );
		}
	} else {
		$secret_keys = explode( "\n", wp_remote_retrieve_body( $secret_keys ) );
		foreach ( $secret_keys as $k => $v ) {
			$secret_keys[$k] = substr( $v, 28, 64 );
		}
	}
	$key = 0;

	foreach ($configFile as $line_num => $line) {
		switch (substr($line,0,16)) {
			case "define('DB_NAME'":
				$configFile[$line_num] = str_replace("database_name_here", $dbname, $line);
				break;
			case "define('DB_USER'":
				$configFile[$line_num] = str_replace("'username_here'", "'$uname'", $line);
				break;
			case "define('DB_PASSW":
				$configFile[$line_num] = str_replace("'password_here'", "'$passwrd'", $line);
				break;
			case "define('DB_HOST'":
				$configFile[$line_num] = str_replace("localhost", $dbhost, $line);
				break;
			case '$table_prefix  =':
				$configFile[$line_num] = str_replace('wp_', $prefix, $line);
				break;
			case "define('AUTH_KEY":
			case "define('SECURE_A":
			case "define('LOGGED_I":
			case "define('NONCE_KE":
			case "define('AUTH_SAL":
			case "define('SECURE_A":
			case "define('LOGGED_I":
			case "define('NONCE_SA":
				$configFile[$line_num] = str_replace('put your unique phrase here', $secret_keys[$key++], $line );
				break;
		}
	}
	if ( ! is_writable(ABSPATH) ) :
		display_header();
?>
<p>Spiacente, non è possibile scrivere il file <code>wp-config.php</code>.</p>
<p>&egrave; possibile crare manualmente il file <code>wp-config.php</code> ed incollare in esso il seguente testo.</p>
<textarea cols="98" rows="15" class="code"><?php
		foreach( $configFile as $line ) {
			echo htmlentities($line, ENT_COMPAT, 'UTF-8');
		}
?></textarea>
<p>Una volta fatto questo, fare clic su "Esegui installazione."</p>
<p class="step"><a href="install.php" class="button">Esegui installazione</a></p>
<?php
	else :
		$handle = fopen(ABSPATH . 'wp-config.php', 'w');
		foreach( $configFile as $line ) {
			fwrite($handle, $line);
		}
		fclose($handle);
		chmod(ABSPATH . 'wp-config.php', 0666);
		display_header();
?>
<p>Tutto ok! Si è conclusa la prima parte dell'installazione. Ora WordPress pu&ograve; comunicare con il database. Se si &egrave; pronti ora è&egrave; il momento di&hellip;</p>

<p class="step"><a href="install.php" class="button">Eseguire l'installazione</a></p>
<?php
	endif;
	break;
}
?>
</body>
</html>
