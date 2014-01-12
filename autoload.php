<?php
/** 
 * This file is used for setting up WPametu 
 * 
 * @package WPametu
 * @author Takahashi Fumiki
 * @since 0.1
 */

namespace WPametu;

// Prepend direct loading.
defined('ABSPATH') or die();

// Load config file
require __DIR__.'/config.php';

// Load Global functions
require __DIR__.'/globals.php';

// Load i18n files
load_plugin_textdomain( 'wpametu', false, BASE_DIR.'/i18n/' );

// Register Main autoloader
spl_autoload_register('\WPametu\autoload');

// Configure
Igniter::getInstance();
