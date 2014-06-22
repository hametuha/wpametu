<?php

namespace WPametu;

/**
 * Base directory of WPametu
 */
const BASE_DIR = __DIR__;

/**
 * WPametu Version
 */
const VERSION = '0.2';

/**
 * Make camelized string hpyenated
 *
 * @param string $class_name
 * @return string
 */
function deCamelize($class_name){
    return strtolower(preg_replace_callback('/(?<!^)([A-Z]+)/u', function($match){
        return '-'.strtolower($match[1]);
    }, (string)$class_name));
}

/**
 * Autoloader for class file.
 *
 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md PSR-0
 * @global array $wpametu
 * @param string $class_name
 */
function autoload($class_name){
    global $wpametu;
    $class_name = ltrim($class_name, '\\');
    switch($class_name){
        case 'Spyc':
            require BASE_DIR.'/vendor/spyc/Spyc.php';
            break;
        default:
            $file_name = '';
            $namespace = '';
            if( ($last_ns_pos = strrpos($class_name, '\\')) ){
                $namespace = substr($class_name, 0, $last_ns_pos);
                $class_name = substr($class_name, $last_ns_pos + 1);
                $file_name = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
            }
            // Change under score to directory name and add extension
            $class_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
            if( 0  === strpos($file_name, 'WPametu/') ){
                // If WPametu, load from app dir
                $file_name = str_replace('WPametu/', 'app'.DIRECTORY_SEPARATOR, $file_name);
            }else{
                $file_name = 'vendor'.DIRECTORY_SEPARATOR.$file_name;
            }

            $path = __DIR__.DIRECTORY_SEPARATOR.$file_name.$class_name;
            if ( file_exists($path) ){
                require $path;
            }
            break;
        }
    }
}
