<?php

namespace WPametu\Data\Models;

use WPametu\Data\Tables;

abstract class CustomAbstract extends \WPametu\Data\Model
{

    public function __get($name){
        switch($name){
            case 'datetime':
            case 'decimal':
            case 'geom':
                return
                break;
            default:
                return parent::__get($name);
                break;
        }
    }
} 