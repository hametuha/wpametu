<?php

namespace WPametu\Tool;

/**
 * Batch's result structure
 *
 * @package WPametu
 */
class BatchResult
{

	public $processed = 0;

	public $total = 0;

	public $has_next = false;

	public $message = '';

	/**
	 * Construcot
	 *
	 * @param int $processed
	 * @param int $total
	 * @param bool $has_next
	 * @param string $message
	 */
	public function __construct($processed, $total, $has_next, $message = ''){
		$this->processed = (int)$processed;
		$this->total = (int) $total;
		$this->has_next = (bool) $has_next;
		$this->message = (string) $message;
	}
}
