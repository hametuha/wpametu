<?php

namespace WPametu\API\Rest;


/**
 * REST API for JSON
 *
 * @package WPametu
 */
abstract class RestJSON extends RestFormat {

	/**
	 * Content Type JSON
	 *
	 * @var string
	 */
	protected $content_type = 'application/json';

	/**
	 * Convert result object to some format
	 *
	 * @param $result
	 * @return string
	 */
	protected function convert_result( $result ) {
		return json_encode( $result );
	}
}
