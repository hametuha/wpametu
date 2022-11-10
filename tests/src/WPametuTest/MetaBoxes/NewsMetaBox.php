<?php

namespace WPametuTest\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Text;
use WPametu\UI\Field\Textarea;

class NewsMetaBox extends EditMetaBox {

	protected $post_types = [ 'post' ];

	protected $name = 'wpametu_news_meta_helper';

	protected $label = 'Setting';

	protected $context = 'normal';

	protected $fields = [
		'excerpt' => [
			'class'       => Textarea::class,
			'label'       => 'Lead Text',
			'required'    => true,
			'description' => 'This is a lead text for news article.',
			'rows'        => 3,
			'min'         => 40,
			'max'         => 200,
		],
		'subtitle' => [
			'class'       => Text::class,
			'label'       => 'Sub title',
			'required'    => false,
			'description' => 'Subtitle for a news article.',
		],
	];
}
