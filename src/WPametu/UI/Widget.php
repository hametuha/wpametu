<?php

namespace WPametu\UI;


use WPametu\Traits\i18n;
use WPametu\Utility\StringHelper;

/**
 * Widget class
 *
 * @package WPametu
 * @property-read StringHelper $str
 */
abstract class Widget extends \WP_Widget
{
    use i18n;

    /**
     * Used for widget's ID
     *
     * @var string
     */
    public $id_base = '';

    /**
     * Title of widget
     *
     * @var string
     */
    protected $title = '';

    /**
     * Widget's description
     *
     * @var array
     */
    protected $description = '';

    /**
     * Widget's class name
     *
     * @var string
     */
    protected $class_name = '';

    /**
     * Widget's width
     *
     * @var int
     */
    protected $width = 0;

    /**
     * Place holder
     *
     * @var array
     */
    protected $placeholders = [];

    /**
     * Constructor
     *
     */
    final public function __construct(){
        $this->placeholders = $this->get_placeholders();
        $option = [
            'description' => $this->description,
        ];
        if( !empty($this->class_name) ){
            $option['classname'] = $this->class_name;
        }
        parent::__construct($this->id_base, $this->title, $option, $this->width ? ['width' => $this->width] : []);
    }

    /**
     * Show widget content
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance){
        $content = $this->widget_content($instance);
        if( $content ){
            echo $args['before_widget'];
            if( isset($instance['title']) && !empty($instance['title']) ){
                echo $args['before_title'];
                echo $instance['title'];
                echo $args['after_title'];
            }
            echo $content;
            echo $args['after_widget'];
        }
    }

    /**
     * Show widget content
     *
     * @param array $instance
     * @return string|false
     */
    abstract protected function widget_content( array $instance = [] );

    /**
     * Replace content
     *
     * @param string $content
     * @param array $instance Widget's setting
     * @return string
     */
    protected function replace($content, $instance){
        foreach( $this->placeholders as $key => $desc ){
            $fill = $this->fill($key, $instance);
            if( $fill && false !== strpos($content, '%'.$key.'%') ){
                $content = str_replace("%{$key}%", $fill, $content);
            }
        }
        return $content;
    }

    /**
     * Set placeholder
     *
     * @return array
     */
    protected function get_placeholders(){
        return [
            'title' => $this->__('Post title'),
            'url' => $this->__('Permalink'),
            'excerpt' => $this->__('Excerpt'),
            'date' => $this->__('Published'),
            'modified' => $this->__('Last modified'),
            'author' => $this->__('Author name'),
            'category' => $this->__('Category'),
            'tag' => $this->__('Post tag'),
        ];
    }

    /**
     * Get placeholder's content
     *
     * @param string $placeholder
     * @param array $instance Widget's setting
     * @return bool|string
     */
    protected function fill($placeholder, $instance){
        switch( $placeholder ){
            case 'title':
                return get_the_title();
                break;
            case 'url':
                return get_permalink();
                break;
            case 'excerpt':
                return  get_the_excerpt();
                break;
            case 'date':
                return  get_the_date();
                break;
            case 'modified':
                return get_the_modified_date();
                break;
            case 'author':
                return get_the_author();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        switch( $name ){
            case 'str':
                return StringHelper::get_instance();
                break;
            default:
                // Do nothing
                return false;
                break;
        }
    }
}
