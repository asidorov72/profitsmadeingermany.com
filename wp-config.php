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
define('DB_NAME', 'profitsmadeingermany_com');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         '4)@ep@-B~dE$}VU%MifzyV_lNuUn)>|i@D~Rz83&fKs1V#=Y`swi}e=G>K;b`615');
define('SECURE_AUTH_KEY',  '9?q0=VC.skQuKO:~15FzMV|D&COb1)<|5K91GZN2Mf/lV%Kd;g_#RT=i?ckqbq#I');
define('LOGGED_IN_KEY',    '=F:m{P/m8s@q*V?/Pef@:WPA1+P.KV4&<wL#up6ib-;>o`I8Ikjpvt7..m]BC%%Z');
define('NONCE_KEY',        'R)k &T;kV-r`wK_^VCImu3=~fBjudu#kd2W9..pYChG=KS?-T85ch|qHfc;gg%#&');
define('AUTH_SALT',        '-a<% D^?3rYrg-Q/*m[^(z8/tk|f-O>)-i9*H2h4wl=!x89f1}P`UX@--AgNHdZ)');
define('SECURE_AUTH_SALT', '*CCSN/W3zg-wdR%1bRw}>&z7OH2MJ?<y<l?J]M1bs@C:+l)bb!4Ivr?EmY)2*i7A');
define('LOGGED_IN_SALT',   '(5?/aW9i9HS@ilkA(MP*VuBEd4fX+hlno3:o:-:&DZ_eb5^0M^bWa&P=uO#!ll#8');
define('NONCE_SALT',       'vuZKs *o-6&Sop62<YFEt%$M[kZY!DqNU1^IpR/Y9Qjk86OOu+_WlW>]&@@.H&+[');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'proen_';

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
