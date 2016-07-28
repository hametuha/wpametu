<?php

namespace WPametu\Utility;

use Masterminds\HTML5;

/**
 * Formatter
 *
 * @package Hametuha\Utility
 */
class Formatter {

	/**
	 * Create HTML 5
	 *
	 * @param string $segment
	 *
	 * @return \DOMDocument
	 */
	public static function get_dom( $segment ) {
		$html5 = new HTML5();
		$xml   = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
</head>
<body>
{$segment}
</body>
</html>
HTML;

		return $html5->loadHTML( $xml );
	}

	/**
	 * Return body as string
	 *
	 * @param \DOMDocument $dom
	 *
	 * @return mixed
	 */
	public static function to_string( \DOMDocument $dom ) {
		$html5 = new HTML5();
		preg_match( '/<body>(.*)<\/body>/s', $html5->saveHTML( $dom ), $match );

		return $match[1];
	}

}
