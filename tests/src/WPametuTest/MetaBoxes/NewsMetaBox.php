<?php

namespace WPametuTest\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Date;
use WPametu\UI\Field\GeoChecker;
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
		'_event_date'   => [
			'class'       => Date::class,
			'type'        => 'date',
			'label'       => 'Event Date',
			'description' => 'Event data to be held.',
		],
		'_event_address' => [
			'class'       => Text::class,
			'label'       => 'Address',
			'placeholder' => 'e.g. 東京都千代田区永田町2-4-11',
		],
		'_event_point'   => [
			'class'       => GeoChecker::class,
			'label'       => 'Address Checker',
			'target'      => '_event_address',
			'description' => 'Address field displayed like this.',
		],
		'_event_url'   => [
			'class'       => Text::class,
			'input_type'  => 'url',
			'label'       => 'Event URL',
			'description' => 'Original Event Page',
		],
		'_event_pass'   => [
			'class'       => Text::class,
			'input_type'  => 'password',
			'label'       => 'Event Password',
			'description' => 'Used for event registration.',
		],
	];
}
