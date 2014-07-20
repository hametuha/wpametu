<?php

namespace WPametu\Utility;

use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;

/**
 * String utility
 *
 * @package WPametu\String
 */
class StringHelper extends Singleton
{

    use i18n;

    /**
     * Return hyphenated letter to snake case
     *
     * @param string $string
     * @return string
     */
    public function to_snake_case($string){
        return str_replace('-', '_', $string);
    }

    /**
     * Make hyphenated string to camel case
     *
     * @param string $string
     * @param bool $upper_first Retuns Uppercase first letter if true. Defalt false.
     * @return string
     */
    public function hyphen_to_camel($string, $upper_first = false){
        $str = preg_replace_callback('/-(.)/u', function($match){
            return strtoupper($match[1]);
        }, strtolower($string));
        if($upper_first){
            $str = ucfirst($str);
        }
        return $str;
    }

    /**
     * Detect if string is MySQL Date
     *
     * @param string $string
     * @return bool
     */
    public function is_date($string){
        return (bool) preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/u', $string);
    }

    /**
     * Fetch URL from string and apply callback
     *
     * @param string $string
     * @param callable $callback
     * @return mixed
     */
    public function fetch_link($string, callable $callback){
        return preg_replace_callback("/(https?)(:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/iu", $callback, $string);
    }

    /**
     * Linkify strings
     *
     * @param string $string
     * @param bool $nofollow If true, target=_blank and rel=nofollow will append. Default true.
     * @return mixed
     */
    public function auto_link($string, $nofollow = true){
        return $this->fetch_link($string, function($match) use ($nofollow) {
            return sprintf('<a href="%1$s"%2$s>%1$s</a>',
                           ($nofollow ? ' target="_blank" rel="nofollow,external"' : ''), $match[0]);
        });
    }

    /**
     * Returns post type label shorthand.
     *
     * @param string $singular_name
     * @param string $multiple_name Optional.
     * @param bool $as_object
     * @return array|\stdClass
     */
    public function post_type_label($singular_name, $multiple_name = '', $as_object = false){
        if( !$multiple_name ){
            $multiple_name = $singular_name;
        }
        $labels = [
            'name' => $multiple_name,
            'singular_name' => $singular_name,
            'add_new' => $this->__('Add new'),
            'add_new_item' => sprintf($this->__('Add new %s'), $singular_name),
            'edit_item' => sprintf($this->__('Edit %s'), $singular_name),
            'new_item' => sprintf($this->__('New %s'), $singular_name),
            'view_item' => sprintf($this->__('Vew %s'), $singular_name),
            'search_items' => sprintf($this->__('Search %s'), $multiple_name),
            'not_found' => sprintf($this->__('No %s found.'), $multiple_name),
            'not_found_in_trash' => sprintf($this->__('No %s in trash.'), $singular_name),
            'parent_item_colon' => sprintf($this->__('Parent %s:'), $singular_name),
            'all_items' => sprintf($this->__('All %s'), $multiple_name),
            'menu_name' => $singular_name,
            'name_admin_bar' => $singular_name,
        ];
        return $as_object ? (object)$labels : $labels;
    }


    /**
     * Returns taxonomy label shorthand.
     *
     * @param string $singular_name
     * @param string $multiple_name If empty, singular_name will be used.
     * @param bool $hierarchical Default false.
     * @param bool $as_object Default false. If true, returns as Object
     * @return array|\stdClass
     */
    public function get_taxonomy_label($singular_name, $multiple_name = '', $hierarchical = false, $as_object = false){
        if( !$multiple_name ){
            $multiple_name = $singular_name;
        }
        $labels = [
            'name'                       => $multiple_name,
            'singular_name'              => $singular_name,
            'search_items'               => sprintf($this->__( 'Search %s' ), $singular_name),
            'popular_items'              => sprintf($this->__( 'Popular %s' ), $multiple_name),
            'all_items'                  => sprintf($this->__( 'All %s' ), $multiple_name),
            'parent_item'                => $hierarchical ? sprintf($this->__( 'Parent %s' ), $singular_name) : null,
            'parent_item_colon'          => $hierarchical ? sprintf($this->__( 'Parent %s:' ), $singular_name) : null,
            'edit_item'                  => sprintf($this->__( 'Edit %s' ), $singular_name),
            'update_item'                => sprintf($this->__( 'Update %s' ), $singular_name),
            'add_new_item'               => sprintf($this->__( 'Add New %s' ), $singular_name),
            'new_item_name'              => sprintf($this->__( 'New %s Name' ), $singular_name),
            'separate_items_with_commas' => sprintf($this->__( 'Separate %s with commas' ), $multiple_name),
            'add_or_remove_items'        => sprintf($this->__( 'Add or remove %s' ), $multiple_name),
            'choose_from_most_used'      => sprintf($this->__( 'Choose from the most used %s' ), $multiple_name),
            'not_found'                  => sprintf($this->__( 'No %s found.' ), $multiple_name),
            'menu_name'                  => $multiple_name,
        ];
        return $as_object ? (object)$labels : $labels;
    }

}
