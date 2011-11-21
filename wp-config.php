<?php
/**
 * Il file base di configurazione di WordPress.
 *
 * Questo file definisce le seguenti configurazioni: impostazioni MySQL,
 * Prefisso Tabella, Chiavi Segrete, Lingua di WordPress e ABSPATH.
 * E' possibile trovare ultetriori informazioni visitando la pagina: del
 * Codex {@link http://codex.wordpress.org/Editing_wp-config.php
 * Editing wp-config.php}. E' possibile ottenere le impostazioni per 
 * MySQL dal proprio fornitore di hosting.
 *
 * Questo file viene utilizzato, durante l'installazione, dallo script
 * di creazione di wp-config.php. Non è necessario utilizzarlo solo via
 * web,è anche possibile copiare questo file in "wp-config.php" e
 * rimepire i valori corretti.
 *
 * @package WordPress
 */

// ** Impostazioni MySQL - E? possibile ottenere questoe informazioni
// ** dal proprio fornitore di hosting ** //
/** Il nome del database di WordPress */
//define('DB_NAME', 'wp_lendvai');

/** Nome utente del database MySQL */
//define('DB_USER', 'root');

/** Password del database MySQL */
//define('DB_PASSWORD', 'root');

/** Hostname MySQL  */
//define('DB_HOST', 'localhost');

/** Charset del Database da utilizare nella creazione delle tabelle. */
//define('DB_CHARSET', 'utf8');


if (isset($_SERVER['PLATFORM']) && $_SERVER['PLATFORM'] == 'PAGODABOX') {
    define('DB_NAME', $_SERVER['DB1_NAME']);
    define('DB_USER', $_SERVER['DB1_USER']);
    define('DB_PASSWORD', $_SERVER['DB1_PASS']);
    define('DB_HOST', $_SERVER['DB1_HOST']);
}
else {
    define('DB_NAME', 'wp_lendvai');
    define('DB_USER', 'root');
    define('DB_PASSWORD', 'root');
    define('DB_HOST', 'localhost');
}

/** Il tipo di Collazione del Database. Da non modificare se non si ha
idea di cosa sia. */
define('DB_COLLATE', '');

/**#@+
 * Chivi Univoche di Autenticazione e di Salatura.
 *
 * Modificarle con frasi univoche differenti!
 * E' possibile generare tali chiavi utilizzando {@link https://api.wordpress.org/secret-key/1.1/salt/ servizio di chiavi-segrete di WordPress.org}
 * E' possibile cambiare queste chiavi in qualsiasi momento, per invalidare tuttii cookie esistenti. Ciò forzerà tutti gli utenti ad effettuare nuovamente il login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'HwiUq4d{Wgn7+ &jY>{;5#n.)9q3yVY~,zr.zK)31Jb!LgNfPqp^9-GgzzWG)Ug/');
define('SECURE_AUTH_KEY',  'WpjY{,pjKKfGwGB$?7wG ]p&cek)EFwit uDT9(:(m11?iggrKn)9=yQ$W!2N-]p');
define('LOGGED_IN_KEY',    'U9=N=_s[,[utlxC(0Z~j_DG*7jze@*b@9aAEjhLLZr{qvtAAOhD[Iq?&Rrw0WpSk');
define('NONCE_KEY',        '/}1Iiq^G@i3!RLAD>981b(:!XwiM~S Aa/(S;tp1h2Xp>jD(YGCkx)w9<[in]:D{');
define('AUTH_SALT',        'f,vmk(3EU?P6Rh$F%L2}7PY 01/YyPmr{yR} m8)A#V$E75K8#3eCQ&~z,_y5T:]');
define('SECURE_AUTH_SALT', '3Ds7|Bi&A^c;.Q._Ue;kM85G>nH<{9 uXiW{;t7}`O@2*O:/MEcouxZ4K-B0}yPR');
define('LOGGED_IN_SALT',   '2%Qm2-Nom2G_Tnng)52jNLPwRzL^MD4NT#&3K#8EL)mns~=.t_rxrq,eHEhT2S;:');
define('NONCE_SALT',       '-V,?Uk?YyR]IY56C<E+Y9T>?2)u>5y]6Ik(I}H-4Ba,ZAEW(^ectSFZ[ t1]iXSq');

/**#@-*/

/**
 * Prefisso Tabella del Database WordPress .
 *
 * E' possibile avere installazioni multiple su di un unico database if you give each a unique
 * fornendo a ciascuna installazione un prefisso univoco.
 * Solo numeri, lettere e sottolineatura!
 */
$table_prefix  = 'wp_lendvai_';

/**
 * Lingua di Localizzazione di WordPress, di base Inglese.
 *
 * Modificare questa voce per localizzare WordPress. Occorre che nella cartella
 * wp-content/languages sia installato un file MO corrispondente alla lingua
 * selezionata. Ad esempio, installare de_DE.mo in to wp-content/languages ed
 * impostare WPLANG a 'de_DE' per abilitare il supporto alla lingua tedesca.
 * 
 * Tale valore è già impostato per la lingua italiana
 */
define('WPLANG', 'it_IT');

/**
 * Per gli sviluppatori: modalità di debug di WordPress.
 *
 * Modificare questa voce a TRUE per abilitare la visualizzazione degli avvisi
 * durante lo sviluppo.
 * E' fortemente raccomandato agli svilupaptori di temi e plugin di utilizare
 * WP_DEBUG all'interno dei loro ambienti di sviluppo.
 */
define('WP_DEBUG', true); //rainwiz

/* Finito, interrompere le modifiche! Buon blogging. */

/** Path assoluto alla directory di WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Imposta lle variabili di WordPress ed include i file. */
require_once(ABSPATH . 'wp-settings.php');
