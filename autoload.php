<?php
/** 
 * This file is used for setting up WPametu 
 * 
 * @package WPametu
 * @since 0.1
 */

namespace WPametu;

// Prepend direct loading.
defined('ABSPATH') or die();

// Load config file
require __DIR__.'/config.php';

// Load i18n files
load_plugin_textdomain( 'wpametu', false, BASE_DIR.'/i18n/' );

// Register Main autoloader
spl_autoload_register('\WPametu\autoload');

// Configure
Config::getInstance();
