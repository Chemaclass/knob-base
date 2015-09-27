<?php

namespace Knob\Controllers;

/**
 * Home Controller Interface
 *
 * @author José María Valera Reales
 */
interface HomeControllerInterface {

	/**
	 * author.php
	 */
	public function getAuthor();

	/**
	 * archive.php
	 */
	public function getArchive();

	/**
	 * category.php
	 */
	public function getCategory();

	/**
	 * home.php
	 */
	public function getHome();

	/**
	 * index.php
	 */
	public function getIndex();

	/**
	 * 404.php
	 */
	public function get404();

	/**
	 * search.php
	 */
	public function getSearch();

	/**
	 * single.php
	 */
	public function getSingle($type = 'post');

	/**
	 * tag.php
	 */
	public function getTag();
}
