<?php

/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */

// Our PHP wrapper doesn't have realpath defined, so we hack it a bit

define('WP_USE_THEMES', true);
// define('WP_DEFAULT_THEME', 'mesmerize');
define('USE_CACHE', true);
define('DB_ENGINE', 'sqlite');
// define('DB_DIR', '/db/');
define('DB_DIR', dirname(dirname(__FILE__)) . '/db/');
// define('DB_FILE', "db.sqlite");
define('SERVE_STATIC', true);
