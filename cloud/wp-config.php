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
define('DB_NAME', 'cloud');

/** MySQL database username */
define('DB_USER', 'cloud');

/** MySQL database password */
define('DB_PASSWORD', 'have_fun123');

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
define('AUTH_KEY',         'Li-(N?@C7rl0dS>;i[}ls~F$S&l7Lc,Mi+$7&@~0G3-FV:f3({1Y/m?F]}6{z78!');
define('SECURE_AUTH_KEY',  'oagtp{Wo/N;T~%N*;rv.Q){IS=KnlT Scgs%deXGuqUsWM:M)BgbfCdn~p?-&nSD');
define('LOGGED_IN_KEY',    '+LzJC#y?S?[[[rw#sUMm>^Zk0I/+_=6&u{3_hI8$<|J`~--P4mg9Q|w;~f+-=0NW');
define('NONCE_KEY',        '_Y?^:ZcyFJ:lv~7}v(+N)]nst ,!$DUz>edmkw58L`#zgm#`(JuOY~6E@C <$aLU');
define('AUTH_SALT',        'Dn;9lDo#3*N:n:yT&uZ4{h8)W/|TZ+!$tngKrwsJz?B|{UP{|B@;F7VP*h32ii&#');
define('SECURE_AUTH_SALT', 'Z,pmh.lGS-zR9Rg;)6_{lpsv>iqhpL}aSRv&Y#xa4FkORmF7PXR$-9FZ)H#(Jsu-');
define('LOGGED_IN_SALT',   ' n?oz|NQ+?` _b|(3|X8(Dn8P#wE4%.6-^MB+lZ<y_=<F,dH3/_~%}$?{);Xan|7');
define('NONCE_SALT',       't-[A~`~C9QM%sqdP+3W9|MB}/]+}i52|t] ?89.m3*I|j*!wwjh#3sh#$U%1rP~H');

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
