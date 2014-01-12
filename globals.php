<?php
/**
 * Global function used by user
 */


/**
 * Register framework
 *
 * @param string $group
 * @param string $dir
 */
function wpametu_add($group, $dir){
    \WPametu\Igniter::getInstance()->addFramework($group, $dir);
}
