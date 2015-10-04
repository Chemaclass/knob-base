<?php

namespace Knob\Config;

use Knob\I18n\I18n;
use Knob\Models\Post;
use Knob\Libs\Env;
use Knob\Models\User;

return [
	'adminEmail' => ADMIN_EMAIL,
	'atomUrl' => get_bloginfo('atom_url'),

	'blogAuthor' => '',
	'blogCharset' => get_bloginfo('charset'),
	'blogCommentsAtomUrl' => get_bloginfo('comments_atom_url'),
	'blogCommentsRss2Url' => get_bloginfo('comments_rss2_url'),
	'blogDescription' => get_bloginfo('description'),
	'blogHtmlType' => get_bloginfo('html_type'),
	'blogKeywords' => '',
	'blogLanguage' => get_bloginfo('language'),
	'blogLoginUrl' => wp_login_url($_SERVER['REQUEST_URI']),
	'blogPingbackUrl' => get_bloginfo('pingback_url'),
	'blogName' => get_bloginfo('name'),
	'blogRdfUrl' => get_bloginfo('rdf_url'),
	'blogRss2Url' => get_bloginfo('rss2_url'),
	'blogRssUrl' => get_bloginfo('rss_url'),
	'blogTitle' => BLOG_TITLE,
	'blogStylesheetDirectory' => get_bloginfo('stylesheet_directory'),
	'blogStylesheetUrl' => get_bloginfo('stylesheet_url'),
	'blogTemplateDirectory' => get_bloginfo('template_directory'),
	'blogTemplateUrl' => get_bloginfo('template_url'),
	'blogTextDirection' => get_bloginfo('text_direction'),
	'blogVersion' => get_bloginfo('version'),
	'blogWpurl' => get_bloginfo('wpurl'),

	'componentsDir' => COMPONENTS_DIR,
	'currentLang' => I18n::getLangBrowserByCurrentUser(),
	'currentLangFullname' => I18n::getLangFullnameBrowserByCurrentUser(),
	'currentUser' => User::getCurrent(),

	'homeUrl' => get_home_url(),

	'isEnvProd' => Env::isProd(),
	'isEnvDev' => Env::isDev(),
	'isEnvLoc' => Env::isLoc(),
	'isUserLoggedIn' => is_user_logged_in(),

	'optionPostsPerPage' => get_option('posts_per_page'),
	'optionCategoryBase' => ($c = get_option('category_base')) ? $c : Post::CATEGORY_BASE_DEFAULT,
	'optionTagBase' => ($t = get_option('tag_base')) ? $t : Post::TAG_BASE_DEFAULT,

	'publicDir' => PUBLIC_DIR
];
