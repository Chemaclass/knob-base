<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Knob\Controllers;

/**
 * Home Controller Interface.
 *
 * @author José María Valera Reales
 */
interface HomeControllerInterface
{

    /**
     * author.php
     *
     * @return string
     */
    public function getAuthor();

    /**
     * archive.php
     *
     * @return string
     */
    public function getArchive();

    /**
     * category.php
     *
     * @return string
     */
    public function getCategory();

    /**
     * home.php
     *
     * @return string
     */
    public function getHome();

    /**
     * index.php
     *
     * @return string
     */
    public function getIndex();

    /**
     * 404.php
     *
     * @return string
     */
    public function get404();

    /**
     * search.php
     *
     * @return string
     */
    public function getSearch();

    /**
     * single.php
     *
     * @param string $type
     * @return string
     */
    public function getSingle($type = 'post');

    /**
     * tag.php
     *
     * @return string
     */
    public function getTag();
}
