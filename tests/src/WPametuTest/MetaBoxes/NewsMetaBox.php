<?php

namespace WPametuTest\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\Select;
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
		'_show_title'   => [
			'class'       => Radio::class,
			'label'       => 'How to display Title',
			'options'     => [
				2 => 'Trim in 20 letters',
				1 => 'Hide',
				0 => 'Display',
			],
			'default'     => 0,
		],
		'published_to'   => [
			'class'       => Select::class,
			'label'       => 'Published To',
			'options'     => [
				2 => 'News Media',
				1 => 'Fax',
				0 => 'No publish',
			],
			'default'     => 0,
		],
	];
}
