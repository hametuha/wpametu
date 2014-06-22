<?php

namespace WPametu\UI;


class Parts
{
    /**
     * Echo Font Awesome
     *
     * @param string $key
     * @return string
     */
    public static function icon($key){
        $str = sprintf('<i class="fa fa-%s"></i>', $key);
        return $str;
    }
} 