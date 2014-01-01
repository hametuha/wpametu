<?php

namespace Wpametu\UI\Admin\Enhancement;

use WPametu;

class Tag extends WPametu\Request\Ajax
{

	use WPametu\Traits\i18n;

	/**
	 * Action name used for Ajax and nonce
	 *
	 * @var string
	 */
	protected  $action = 'wpametu_tagedit';



	/**
	 * Nonce name
	 *
	 * @var string
	 */
	protected $nonce = '_tageditnonce';



	protected function initialized(){

	}



	/**
	 * Handle Ajax
	 *
	 * @global \wpdb $wpdb
	 */
	protected function ajax(){
		/** @var $wpdb \wpdb */
		global $wpdb;
		$return = array();
		switch($this->input->get('type')){
			case 'search':
				$taxonomy = $this->input->get('taxonomy');
				$query = implode(', ', array_map(function($term) use ($wpdb){
					return $wpdb->prepare('%s', trim($term));
				}, explode(',', $this->input->get('terms'))));
				if(!empty($query) && "''" != $query){
					$sql = <<<EOS
					SELECT t.term_id AS id, t.name, tt.count
					FROM {$wpdb->terms} AS t
					INNER JOIN {$wpdb->term_taxonomy} AS tt
					ON t.term_id = tt.term_id
					WHERE tt.taxonomy = %s
					  AND t.name IN ({$query})
					ORDER BY t.name ASC
EOS;
					$return = $wpdb->get_results($wpdb->prepare($sql, $taxonomy));
				}
				break;
			default:
				$query = '%'.$this->input->get('q').'%';
				$taxonomy = get_taxonomy($this->input->get('taxonomy'));
				if($taxonomy){
					$sql = <<<EOS
						SELECT t.term_id AS id, t.name, tt.count
						FROM {$wpdb->terms} AS t
						INNER JOIN {$wpdb->term_taxonomy} AS tt
						ON t.term_id = tt.term_id
						WHERE tt.taxonomy = %s
						  AND t.name LIKE %s
						ORDER BY t.name ASC
						LIMIT 20
EOS;
					$return = $wpdb->get_results($wpdb->prepare($sql, $taxonomy->name, $query));
					array_push($return, array(
						'id' => 0,
						'name' => '[+] '.sprintf($this->__('新規%sを作成'), $taxonomy->labels->name),
					));
				}
				break;
		}
		echo json_encode($return);
	}

	/**
	 * Enqueue scripts
	 */
	public function admin_enqueue_scripts(){
		$screen = get_current_screen();
		if('post' == $screen->base){
			wp_enqueue_script('wpametu-enhanced-tag-edit', $this->get_minified_js($this->lib_url('js/wpametu.tagenhancer.js')), array('jquery-token-input'), \WPametu\VERSION, true);
			wp_localize_script('wpametu-enhanced-tag-edit', 'WPametuEnhancedTags', array(
				'endpoint' => admin_url('admin-ajax.php'),
				'action' => $this->action,
				'nonceKey' => $this->nonce,
				'nonceValue' => $this->create_nonce(),
				'hintText' => $this->__('入力して検索してください'),
				'noResultsText' => $this->__('見つかりませんでした'),
				'searchingText' => $this->__('検索中...'),
				'searchType' => 'search',
			));
			wp_enqueue_style('wpametu-enhanced-tag-edit', $this->lib_url('css/tag-enhancer.css'), array('jquery-token-input-facebook'), \WPametu\VERSION);
			wp_enqueue_style('font-awesome');
		}
	}
} 