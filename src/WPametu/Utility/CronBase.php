<?php

namespace WPametu\Utility;


use WPametu\File\Path;
use WPametu\Http\Input;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;

/**
 * Class CronBase
 *
 * @package WPametu
 * @property-read Input $input
 * @property-read StringHelper $str
 */
abstract class CronBase extends Singleton {


	use i18n, Path;

	/**
	 * Schedule name
	 *
	 * @var string.
	 */
	protected $schedule = 'daily';

	/**
	 * Schedule name
	 * @var string Event name.
	 */
	protected $event = '';

	/**
	 * @var bool This cron is disabled.
	 */
	protected $disabled = false;

	/**
	 * CronBase constructor.
	 *
	 * @param array $setting Unused.
	 */
	public function __construct( array $setting = array() ) {
		add_filter( 'cron_schedules', array( $this, 'cron_schedule' ) );
		add_action( 'init', array( $this, 'register_cron' ) );
		add_action( $this->event, array( $this, 'process' ) );
	}

	/**
	 * Override this if schedules required.
	 *
	 * @see http://codex.wordpress.org/wp_schedule_event
	 * @param array $schedule Schedule array.
	 *
	 * @return mixed
	 */
	public function cron_schedule( $schedule ) {
		return $schedule;
	}

	/**
	 * Register cron event
	 */
	public function register_cron() {
		if ( $this->disabled ) {
			if ( wp_next_scheduled( $this->event ) ) {
				// This cron is disabled, remove event.
				wp_unschedule_event( wp_get_scheduled_event( $this->event )->timestamp, $this->event );
			}
		} else {
			if ( ! wp_next_scheduled( $this->event ) ) {
				// This cron is activated, register event.
				wp_schedule_event( $this->start_at(), $this->schedule, $this->event, $this->args() );
			}
		}
	}

	/**
	 * Process cron
	 *
	 * @return void
	 */
	abstract public function process();

	/**
	 * Arguments to register
	 *
	 * @return array
	 */
	public function args() {
		return array();
	}

	/**
	 * Cron schedule time
	 *
	 * @return int|string
	 */
	public function start_at() {
		return time();
	}

	/**
	 * Getter
	 *
	 * @param string $name Property name.
	 *
	 * @return null|static
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'input':
				return Input::get_instance();
				break;
			case 'str':
				return StringHelper::get_instance();
				break;
			default:
				return null;
				break;
		}
	}
}
