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
        case 'Facebook':
            require BASE_DIR.'/vendor/facebook/src/facebook.php';
            break;
        case 'TwitterOAuth':
            require BASE_DIR.'/vendor/twitteroauth/twitteroauth/twitteroauth.php';
            break;
        case 'OAuth':
            require BASE_DIR.'/vendor/twitteroauth/twitteroauth/OAuth.php';
            break;
        case 'JWT':
            require BASE_DIR.'/vendor/jwt/JWT.php';
            break;
        default:
            // Google API Client
            if( 0 === strpos($class_name, 'Google_')){
                $base_dir = BASE_DIR.'/vendor/google-api/src/';
                $class_path = $base_dir.str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
                if( file_exists($class_path) ){
                    require $class_path;
                }
            //YConnect
            }elseif( 0 === strpos($class_name, 'YConnect\\')){
                $base_dir = BASE_DIR.'/vendor/yconnect/lib/';
                $path = str_replace('YConnect\\', $base_dir, $class_name).'.php';
                if( file_exists($path) ){
                    require $path;
                }
            // Default WPametu Classes
            }else{
                $base_dir = dirname($wpametu['file']);
                // Allow base name to hypenated
                $segments = explode('\\', $class_name);
                $segments[count($segments) - 1] = deCamelize($segments[count($segments) - 1]);
                $class_name = implode('\\', $segments);
                // Change to path
                $class_name = strtolower($class_name);
                $file_name = '';
                $namespace = '';
                if( ($last_ns_pos = strrpos($class_name, '\\')) ){
                    $namespace = substr($class_name, 0, $last_ns_pos);
                    $class_name = substr($class_name, $last_ns_pos + 1);
                    $file_name = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
                    $file_name = str_replace('wpametu/', $base_dir.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR, $file_name);
                }
                $path = $file_name.str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
                if ( file_exists($path) ){
                    require $path;
                }
            }
            break;
    }
}
