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
define('DB_PASSWORD', 'W0RDPRE$$user504');

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
 define('AUTH_KEY',         'uOAyzrEF|$#f%5.%j^^7`+7&!7_jp+9x@3d,I)+BZ:z-@8LS]i^qWQ<6zX]m1g|2');
 define('SECURE_AUTH_KEY',  ':mLFBWE0*V`)ay^nWIS&< ;s-/QEI#mW`c_Y)`cuU7(|@@,`b<9Gelu7^hXGNb@Y');
 define('LOGGED_IN_KEY',    'r|s$_qjc2467FT~~{.T_(~XELV$U*@8tOE]RK^A-!D m@-fy71xWy-Qq@oML|;Kn');
 define('NONCE_KEY',        'vUBj(zdD._!J+ H|`iD|}r!|Pf+#&[}nXpk1vym2`KE/lo|qbM$jP@J;!:c(-FH;');
 define('AUTH_SALT',        'dmA9j$p;4VWY.Rh&,WSHXJ_cC;7ox[>8F&Mg-G:u}w2m+W-4P^dHzEW$>+TIWfN?');
 define('SECURE_AUTH_SALT', '^Z7gpk03!ERp[k%c1d^av&+).]{_-+#|_N]v3VXe XjgiXOft3rXq(pq:Bv5hSzs');
 define('LOGGED_IN_SALT',   ':cHLDbTQ27}^M?GLOKs4Mry=@c$-3{ElNlK.hQiGO+@;%~%6x3K;TPW5x&XyVo9)');
 define('NONCE_SALT',       'Y[+yyv#a3aN,D45ae:~pVS$NCO4sXNJyXY+LC|%bO%D3~?vZ`2 dW%U^0Y}#NAbd');

/* Updated 15 October to reestablish production settings

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
