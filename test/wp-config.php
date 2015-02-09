<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'adroit');

/** MySQL database username */
define('DB_USER', 'adroit');

/** MySQL database password */
define('DB_PASSWORD', 'have_fun');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         's4FRvuB@:R1[}+6+`k6gMe[Z|KS@cV>w|&ZE^QT!xohA;ulXELJL7p{+y:wwJlVo');
define('SECURE_AUTH_KEY',  'nZT@fIED0=t(Fs;W[Zo/-knMw-0f#YgNq603g?^P]288K!Z+B]7=%ns|HK+qQ-<Y');
define('LOGGED_IN_KEY',    'Ed-~tb^EnxjX+=8o*yoLUJKRy7w|zQCqqotn&_oxN]-b6/5*HrVHJnCtqz$7|u9t');
define('NONCE_KEY',        'R8CVuTij_WSAgk{LW48`s*?|:yEktECu)g=l%UXT=hXR.Q6ozXHRB^HrVv6mh&3a');
define('AUTH_SALT',        '2tO)wmzvmnX-Q.`.idE71I_@XLon[)EhNLfZAnDcUF10DD6b];oK6]/`!N#o2Tr1');
define('SECURE_AUTH_SALT', '8U72j6SQ={nHdy^W|0-6xF!_ntvV^6^Rg#o.II`E-[jdx<=!y$%&<V-r.h!Jm*to');
define('LOGGED_IN_SALT',   'ZFK&Fo]`=tfu*C&-RC[/w=cu6rPXS|.B)[~n/Ca)}RNw0fV?StYcsxe`Sq3|>4fa');
define('NONCE_SALT',       'Vx3k|F>viYM_9YN{ oDN5O`?)SX,gubaD^[gLD<G$u*B!eEoNV3=(~=n/1pAuj[F');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

