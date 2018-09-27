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
define('DB_NAME', 'wordpress_dev');

/** MySQL database username */
define('DB_USER', 'wordpressdev');

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
define('AUTH_KEY',         '#rRJ9r s:F$_V*iB.MCT0Q:[]- `qp`7mEw6Im&7#+QB?99n%;]exF.wQM!8vgN!');
define('SECURE_AUTH_KEY',  'r[A euaR<|bV,|gI1`n}jZa<1`,A^+HC7*_ua&$=A 8L;pOndUURQ#l>},|0++Mw');
define('LOGGED_IN_KEY',    '9CIt^g(<NW8-Is>ap3rA|eGG~O1z86+|9_&8&NbUNC>O`].?6rA_*Z~TQz{u=G?|');
define('NONCE_KEY',        '+LYC $<$8-S$)9^^5vdRmZDN~->/zy3yY}sOx=[l-x,iU/Y+||;|S} BTESKFaG,');
define('AUTH_SALT',        'Dg-Vf. 8uL1Z6U-A=kX_&9nI1T4UCWTB2i#=yYQ%-<mq(z e[81r5}(!;fZ4Bbs4');
define('SECURE_AUTH_SALT', 'BJ[n@bW)r&OILdGUBrbSL(Rr2APMM (J|&;?-O}`1I/+@!/%p&^#/$RJ+ZCxP~Af');
define('LOGGED_IN_SALT',   ',p6+:9gD,.eT^V?FvAU C4CxAtMUt1eb0D_`ahFCo<mRl%Uqw)d%mAfS?#olnoh-');
define('NONCE_SALT',       'g=0p#Es~){@iK]zF10V,A9&yv~9+u+s!R>u{kk6{POBQVN]{FA:=8%@Fuh|x7_cM');
/**#@-*/

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
