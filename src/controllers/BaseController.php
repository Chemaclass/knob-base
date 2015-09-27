<?php

namespace Knob\Controllers;

use Knob\Config\Params;
use Knob\Libs\Template;
use Knob\Models\Archive;
use Knob\Models\Post;
use Knob\Models\Term;
use Knob\Models\User;

/**
 *
 * @author José María Valera Reales
 */
abstract class BaseController {

	/*
	 * Members
	 */
	protected $configParams = [ ];
	protected $currentUser = null;
	protected $template = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		/*
		 * Params.
		 */
		$this->configParams = Params::getInstance()->getAll();

		/*
		 * Current User.
		 */
		$this->currentUser = User::getCurrent();

		/*
		 * Template Render Engine.
		 */
		$this->template = Template::getInstance();
	}

	/**
	 * Add the global variables for all controllers
	 *
	 * @param array $templateVars
	 */
	public abstract function getGlobalVariables() ;

	/**
	 * Render a partial
	 *
	 * @param string $templateName
	 * @param array $templateVars
	 */
	public function render($templateName, $templateVars = [], $addGlobalVariables = true) {
		if ($addGlobalVariables) {
			$templateVars = array_merge($templateVars, $this->getGlobalVariables());
		}
		return $this->template->getRenderEngine()->render($templateName, $templateVars);
	}

	/**
	 * Print head + template + footer
	 *
	 * @param string $templateName
	 *        	Template name to print
	 * @param array $templateVars
	 *        	Parameters to template
	 */
	public function renderPage($templateName, $templateVars = []) {
		$templateVars = array_merge($templateVars, $this->getGlobalVariables());
		$addGlobalVariablesToVars = false; // cause we already did it.
		echo $this->render('head', $templateVars, $addGlobalVariablesToVars);
		wp_head();
		echo '</head>';
		echo $this->render($templateName, $templateVars, $addGlobalVariablesToVars);
		wp_footer();
		echo $this->render('footer', $templateVars, $addGlobalVariablesToVars);
	}
}
