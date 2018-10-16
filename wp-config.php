<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpressuser');

/** MySQL database password */
define('DB_PASSWORD', 'WORDPRE$$user504');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/** Allow FTP access from WordPress Site */
define('FS_METHOD', 'direct');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
 define('AUTH_KEY',         'Mwj$V|H*BI-i`L+J[(2FRh$||wsAsrlI8u%k|E^GQ}O4=9!!eW@h(8?3q:o|;GhK');
 define('SECURE_AUTH_KEY',  '{pC2~^:i--i/CftfXg|#0fYFJ^FwJKUCCsRg&X@L-|I+<3kia9P|k)7:-c.hA,oF');
 define('LOGGED_IN_KEY',    ']|v<c+v^VIqBJ>(srj(39 >x^*m]qkl~_v2DTkmJ$4BhC G?D!+Z}:dW`1,E.k_/');
 define('NONCE_KEY',        ' rm| 6#yFm&)j,iTSlAu}Yr`3#c1Az/R%^hntkdjJ{NO_j4$rD#wJ#vc2_=[uC[.');
 define('AUTH_SALT',        '5kZX.bkiN%W4pt#__-W,QTBU<4Q^pcwWf19,9=u=p4obR MRsMT3|{[e}0x[o|o)');
 define('SECURE_AUTH_SALT', 'XT^B[Bp7OwzHq-;H)]k[M5Y`*MlBe_1vufFz^^:IabH5#2RQy.Vf_r-D6+,.*}|Q');
 define('LOGGED_IN_SALT',   '_yiBpxuw>JDN!_9Wv{X)4KEw+pAhWVk6gqd*!*.hS?|Lf|:7R1p{jvn^+:tB,B%e');
 define('NONCE_SALT',       'HXZftj96C7Zu0xp`VhMB~9GhS+:hcaxyF6-A.C_m4G&(Q+|uQr !C}pprK;+Aw.M');
/**#@-*/

/* Updated 15 October 2018 to reestablish staging settings

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
