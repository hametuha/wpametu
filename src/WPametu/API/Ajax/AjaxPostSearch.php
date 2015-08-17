<?php

namespace WPametu\API\Ajax;


class AjaxPostSearch extends AjaxBase
{

    protected $no_auth = true;

    protected $action = 'post_search';

    protected $required = ['post_type', 's'];

    protected $always_nocache = true;

    /**
     * If this variable is true, public search will occur
     *
     * @var bool
     */
    protected $is_public_search = false;

    /**
     * Returns data as array.
     *
     * @return array
     */
    protected function get_data()
    {
        $post_type = $this->input->get('post_type');
        if( !post_type_exists($post_type) ){
            $this->error(sprintf($this->__('Post type %s does not exist.'), $post_type), 404);
        }
        if( !current_user_can('edit_posts') ){
            $this->error($this->__('Sorry, but you have no permission.'), 403);
        }
        $paged = max(intval($this->input->get('paged')), 1) - 1;
        $args = [
            'post_type' => $post_type,
            'suppress_filters' => false,
            'orderby' => 'title',
            'order' => 'ASC',
            'offset' => $paged * get_option('posts_per_page'),
            's' => $this->input->get('s'),
        ];
        if( $this->is_public_search ){
            // This is public search.
            $args = array_merge($args, [
                'post_status' => ['publish']
            ]);
        }else{
            // This is private search.
            $args = array_merge($args, [
                'post_status' => ['publish', 'draft', 'future'],
            ]);
            if( !current_user_can('edit_others_posts') ){
                $args['author'] = get_current_user_id();
            }
        }
        $data = [];
        foreach( get_posts($args) as $post){
            $data[] = [
                'id' => $post->ID,
                'name' => get_the_title($post),
            ];
        }
        return $data;
    }
}