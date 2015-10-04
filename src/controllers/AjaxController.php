<?php

namespace Knob\Controllers;

// Load WP.
// We have to require this file, in other case we cant call to the WP functions
require_once (APP_DIR . '/../../../../wp-load.php');

/**
 * AJAX Controller
 *
 * @author José María Valera Reales
 */
abstract class AjaxController extends BaseController {

	/**
	 *
	 * @param string $submit
	 * @param array $_REQUEST
	 *
	 * @return array[content] result
	 *         array[code] code result
	 */
	public abstract function getJsonBySubmit($submit, $_datas);

	/**
	 * -------------------------------------
	 * Main Controller for AJAX request
	 * -------------------------------------
	 */
	public function main() {
		$json = [
			'code' => 504
		]; // Error default

		$submit = $_REQUEST['submit'];

		// check if we don't have any submit
		if (!$submit) {
			die('');
		}

		$json = $this->getJsonBySubmit($submit, $_REQUEST);

		// cast the content to UTF-8
		$json['content'] = mb_convert_encoding($json['content'], "UTF-8");
		echo json_encode($json);
	}
}
