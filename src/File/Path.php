<?php

namespace WPametu\File;


trait Path
{
    protected function is_child_theme(){
        return WPAMETU_CHILD;
    }

    /**
     * Get frameworks root dir
     *
     * @return string
     */
    protected function get_root_dir(){
        return dirname(dirname(__DIR__));
    }

    /**
     * Returns vendor's directory
     *
     * @return string
     */
    protected function get_vendor_dir(){
        return $this->get_root_dir().'/vendor';
    }

    /**
     * Returns this theme's root directory
     *
     * @return string
     */
    protected function theme_dir(){
        if( $this->is_child_theme() ){
            return get_stylesheet_directory();
        }else{
            return get_template_directory();
        }
    }
}
