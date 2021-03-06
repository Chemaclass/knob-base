<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Knob\Models;

use Knob\App;
use Knob\Libs\Utils;
use Knob\Libs\Ajax;
use Knob\Libs\IteratorPresenter;
use Models\Term;

/**
 * Post Model
 *
 * @author José María Valera Reales
 */
class Post extends Image
{
    public static $table = "posts";

    /*
     * Default values
     */
    const CATEGORY_BASE_DEFAULT = 'category';

    const TAG_BASE_DEFAULT = 'tag';

    /*
     * Images sizes
     */
    const IMG_SIZE_THUMBNAIL = 'thumbnail';

    const IMG_SIZE_MEDIUM = 'medium';

    const IMG_SIZE_LARGE = 'large';

    const IMG_SIZE_FULL = 'full';

    /*
     * STATUS
     */
    const STATUS_PUBLISH = "publish";

    const STATUS_PENDING = "pending";

    const STATUS_APPROVE = 'approve';

    /*
     * Counts
     */
    const COUNT_EXCERPT = 20;

    /*
     * Types
     */
    const TYPE_POST = 'post';

    /**
     *
     * @var \WP_Post
     *
     * @see https://codex.wordpress.org/Class_Reference/WP_Post
     */
    protected $wpPost = null;

    /**
     * Constructor
     *
     * @param integer $ID
     * @param bool $withWPPost
     *            load into the Post object all members from \WP_Post
     *
     * @see https://developer.wordpress.org/reference/classes/wp_post/
     */
    public function __construct($ID = 0, $withWPPost = false)
    {
        parent::__construct($ID);
        if ($withWPPost) {
            $this->wpPost = \WP_Post::get_instance($this->ID);
        }
    }

    /**
     *
     * @return \WP_Post
     */
    public function getWPPost()
    {
        if (null === $this->wpPost) {
            $this->wpPost = \WP_Post::get_instance($this->ID);
        }

        return $this->wpPost;
    }

    /**
     * Return all pages.
     * (Post type page)
     *
     * @return array<Post>
     */
    public static function getPages($withoutEmpty = true)
    {
        foreach (get_all_page_ids() as $id) {
            $p = Post::find($id);
            if ($p->ID) {
                $pages[] = $p;
            }
        }

        if ($withoutEmpty) {
            $pages = array_filter($pages, function ($page) {
                return strlen($page->getContent());
            });
        }

        /*
         * Sort by title
         */
        /** @var Post $a */
        /** @var Post $b */
        usort($pages, function ($a, $b) {
            strcmp($a->getTitle(), $b->getTitle());
        });

        return $pages;
    }

    /**
     *
     * @return Post|null
     */
    public static function getCurrent()
    {
        if ($postId = get_the_ID()) {
            return new Post($postId);
        }

        return null;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Models\Image::getImageSizesToDelete()
     */
    protected function getImageSizesToDelete()
    {
        // TODO:
        return [];
    }

    /**
     * Return the post_name
     */
    public function getSlug()
    {
        return $this->post_name;
    }

    /**
     * Return the author
     *
     * @return User
     */
    public function getAuthor()
    {
        return User::find($this->post_author);
    }

    /**
     *
     * @param string $taxonomy
     */
    public function getTerms($taxonomy = Term::TYPE_TAG, $args = [])
    {
        return wp_get_post_terms($this->getId(), $taxonomy, $args);
    }

    /**
     *
     * @return array
     */
    public function getCategories()
    {
        if (!$categories = get_the_category($this->ID)) {
            return [];
        }
        foreach ($categories as $category) {
            $category->category_link = get_category_link($category->term_id);
            $array[] = $category;
        }

        return new IteratorPresenter($array);
    }

    /**
     * Return the content
     *
     * @return string
     */
    public function getContent()
    {
        $content = apply_filters('the_content', $this->post_content);
        $content = str_replace(']]>', ']]&gt;', $content);
        return $content;
    }

    /**
     * Return all comments
     *
     * @see http://codex.wordpress.org/Function_Reference/get_comments
     */
    public function getComments()
    {
        $args_comments = [
            'post_id' => $this->ID,
            'orderby' => 'comment_date_gmt',
            'status' => static::STATUS_APPROVE,
        ];
        $comments = [];
        foreach (get_comments($args_comments, $this->ID) as $c) {
            $comments[] = Comment::find($c->comment_ID);
        }
        return $comments;
    }

    /**
     * Return the publish date
     *
     * @return string
     */
    public function getDate()
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare('SELECT post_date FROM ' . $wpdb->prefix . 'posts WHERE ID = %d',
            $this->ID));
    }

    /**
     * Return the form for comments
     *
     * @return string
     */
    public function getFormComments()
    {
        ob_start();

        $placeTextarea = App::get('i18n')->transu('post.share_comment');

        $params = [
            'comment_notes_after' => '',
            'author' => '<p class="comment-form-author">' . '<label for="author">' . __('Your Name') . '</label>
					<input id="author" name="author" type="text"  value="Your First and Last Name" size="30" /></p>',
            'comment_field' => '
				<div class="form-group comment-form-comment">
		            <label for="comment">' . _x('Comment', 'noun') . '</label>
		            <textarea class="form-control" id="comment" name="comment" cols="45" rows="2"
							maxlength="1000" aria-required="true" placeholder="' . $placeTextarea . '"></textarea>
		        </div>',
        ];

        $placeAuthor = App::get('i18n')->transu('name');
        $placeEmail = App::get('i18n')->transu('email');
        $placeUrl = App::get('i18n')->transu('website');

        comment_form($params, $this->ID);
        $comment_form = ob_get_clean();
        $comment_form = str_replace('id="author"', 'class="author form-control" placeholder="' . $placeAuthor . '"',
            $comment_form);
        $comment_form = str_replace('id="email"', 'class="email form-control" placeholder="' . $placeEmail . '"',
            $comment_form);
        $comment_form = str_replace('id="url"', 'class="url form-control" placeholder="' . $placeUrl . '"',
            $comment_form);
        $comment_form = str_replace('id="submit"', 'class="btn btn-default"', $comment_form);
        return $comment_form;
    }

    /**
     * Return the modified date
     *
     * @return string
     */
    public function getDateModified()
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare('SELECT post_modified FROM ' . $wpdb->prefix . 'posts WHERE ID = %d',
            $this->ID));
    }

    /**
     * Return
     *
     * @return string
     */
    public function getExcerpt()
    {
        $excerpt = $this->post_excerpt;
        if (!Utils::isValidStr($excerpt)) {
            $excerpt = strip_tags(strip_shortcodes($this->post_content));
            $excerpt = trim(preg_replace('/\s\s+/', ' ', $excerpt));
        }
        return Utils::getWordsByStr($excerpt, self::COUNT_EXCERPT);
    }

    /**
     * Return the first Category from this Post
     * http://codex.wordpress.org/Function_Reference/get_the_category
     *
     * @return object
     */
    public function getFirstCategory()
    {
        $categories = get_the_category($this->ID);
        if (!isset($categories[0])) {
            return new \StdClass;
        }

        return $categories[0];
    }

    /**
     * Return the public link.
     *
     * @return string
     */
    public function getPermalink()
    {
        return get_permalink($this->ID);
    }

    /**
     * Return the edit url.
     *
     * @return string
     */
    public function getEditLink()
    {
        return get_edit_post_link($this->ID);
    }

    /**
     *
     * @return Post
     */
    public function getPreviousPost()
    {
        if ($p = get_previous_post()) {
            return Post::find($p->ID);
        }

        return new Post();
    }

    /**
     * @return Post
     */
    public function getNextPost()
    {
        if ($p = get_next_post()) {
            return Post::find($p->ID);
        }

        return new Post();
    }

    /**
     * Return the title Post
     *
     * @return string
     */
    public function getTitle()
    {
        return get_the_title($this->ID);
    }

    /**
     * Return the thumbnail medium
     *
     * @return string src
     */
    public function getThumbnailMedium()
    {
        return $this->getThumbnail(self::IMG_SIZE_MEDIUM);
    }

    /**
     * Devuelve el src del thumbnail del post
     *
     * @param string $size
     *            size
     */
    public function getThumbnail($size = self::IMG_SIZE_THUMBNAIL)
    {
        /*
         * Define a func for to get the attachment-src from the post_id
         */
        $getSrc = function ($_id) use ($size) {
            $imageObject = wp_get_attachment_image_src(get_post_thumbnail_id($_id), $size);
            if (empty($imageObject)) {
                return false;
            }
            return $imageObject[0];
        };

        if (($imageObject = $getSrc($this->ID))) {
            return $imageObject;
        }
        // if they aren't, we get the first img from the post, and let it as thumbnail
        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $this->post_content, $matches);
        $src = isset($matches[1]) ? $matches[1] : '';
        $attachmentId = Utils::getAttachmentIdFromUrl($src);
        // try to set the first img as thumbnail
        set_post_thumbnail($this->ID, $attachmentId);
        /*
         * In case we found it return the img src from wp_get_attachment_image_src()
         * Othercase return the raw source
         */
        if (($imageObject = $getSrc($this->ID))) {
            return $imageObject;
        }

        return $src;
    }

    /**
     * Return a clousure
     *
     * @param string $by
     */
    public static function getFuncBy($by)
    {
        return function ($value = false, $limit = false, $offset = false, $moreQuerySettings = []) use ($by) {
            switch ($by) {
                case Ajax::ARCHIVE:
                    return static::getByArchive($value, $limit, $offset, $moreQuerySettings);
                case Ajax::AUTHOR:
                    return static::getByAuthor($value, $limit, $offset, $moreQuerySettings);
                case Ajax::CATEGORY:
                    return static::getByCategory($value, $limit, $offset, $moreQuerySettings);
                case Ajax::SEARCH:
                    return static::getBySearch($value, $limit, $offset, $moreQuerySettings);
                case Ajax::TAG:
                    return static::getByTag($value, $limit, $offset, $moreQuerySettings);
                case Ajax::HOME:
                default:
                    return static::getAll($limit, $offset, $moreQuerySettings);
            }
        };
    }

    /**
     * Get Posts
     *
     * @param integer $limit
     * @param string $offset
     * @param array $moreQuerySettings
     * @param string $postType
     * @param string $oddOrEven
     *
     * @return array<Post>
     *
     * @link https://codex.wordpress.org/Class_Reference/WP_Query
     */
    public static function getAll($limit = -1, $offset = false, $moreQuerySettings = [])
    {
        // Get all fixed posts.
        $posts = self::getStickyPosts($limit, $offset, $moreQuerySettings);
        $isCat = isset($moreQuerySettings['cat']);
        $postsStickyIds = [];
        // Check all fixed posts with the category we're searching.
        foreach (Option::get('sticky_posts') as $postId) {
            if ($isCat && ($post = Post::find($postId)) && $post->getCategory()->term_id == $moreQuerySettings['cat']) {
                $postsStickyIds[] = $postId;
            }
        }
        // Check the total fixed posts with the total post we got it.
        $countSticky = count($postsStickyIds);
        // if it's the same doesn't matter. If it's different we have to rest the different.
        $limit = (count($posts) == $countSticky) ? $limit - $countSticky : $limit;

        if (!isset($moreQuerySettings['post_type'])) {
            $moreQuerySettings['post_type'] = Post::TYPE_POST;
        }

        $querySettings = [
            'orderby' => [
                'date' => 'DESC',
            ],
            'post_type' => [
                $moreQuerySettings['post_type'],
            ],
            'post__not_in' => $postsStickyIds,
            'posts_per_page' => $limit,
            'post_status' => Post::STATUS_PUBLISH,
        ];
        if ($offset) {
            $querySettings['offset'] = $offset;
        }
        $querySettings = array_merge($querySettings, $moreQuerySettings);
        $loop = new \WP_Query($querySettings);
        return array_merge($posts, self::loopQueryPosts($loop));
    }

    /**
     * Return the fixed posts
     *
     * @return array<Post>
     */
    private static function getStickyPosts($limit = -1, $offset = false, $moreQuerySettings = [])
    {
        $sticky_posts = Option::get('sticky_posts');
        if (!$sticky_posts) {
            return [];
        }
        if (!isset($moreQuerySettings['post_type'])) {
            $moreQuerySettings['post_type'] = Post::TYPE_POST;
        }
        $querySettings = [
            'post_type' => [
                $moreQuerySettings['post_type'],
            ],
            'post__in' => $sticky_posts,
            'posts_per_page' => $limit,
        ];
        if ($offset) {
            $querySettings['offset'] = $offset;
        }
        $querySettings = array_merge($querySettings, $moreQuerySettings);
        $loop = new \WP_Query($querySettings);

        return self::loopQueryPosts($loop);
    }

    /**
     * Loop the query and mount the Post objects
     *
     * @param WP_Query $loop
     * @return array<Post>
     */
    private static function loopQueryPosts($loop)
    {
        $posts = [];
        for ($index = 0; $loop->have_posts(); $index++) {
            $loop->the_post();
            $posts[] = Post::find(get_the_ID());
        }
        return $posts;
    }

    /**
     * Get posts from an archive
     *
     * @param string $value
     * @param integer $limit
     * @param array $moreQuerySettings
     * @return array<Post>
     */
    public static function getByArchive($value, $limit = false, $offset = false, $moreQuerySettings = [])
    {
        return self::getBy(Ajax::ARCHIVE, $value, $limit, $offset, $moreQuerySettings);
    }

    /**
     * Get posts from an author
     *
     * @param integer $autorId
     * @param integer $limit
     * @param array $moreQuerySettings
     * @return array<Post>
     */
    public static function getByAuthor($autorId, $limit = false, $offset = false, $moreQuerySettings = [])
    {
        return self::getBy(Ajax::AUTHOR, $autorId, $limit, $offset, $moreQuerySettings);
    }

    /**
     * Get posts from query search
     *
     * @param string $searchQuery
     * @param integer $limit
     * @param array $moreQuerySettings
     * @return array<Post>
     */
    public static function getBySearch($searchQuery, $limit = false, $offset = false, $moreQuerySettings = [])
    {
        return self::getBy(Ajax::SEARCH, $searchQuery, $limit, $offset, $moreQuerySettings);
    }

    /**
     * Get posts from a category
     *
     * @param integer $catId
     * @param integer $limit
     * @param array $moreQuerySettings
     * @return array<Post>
     */
    public static function getByCategory($catId, $limit = false, $offset = false, $moreQuerySettings = [])
    {
        return self::getBy(Ajax::CATEGORY, $catId, $limit, $offset, $moreQuerySettings);
    }

    /**
     *
     * @param integer $tagId
     * @param integer $limit
     * @param array $moreQuerySettings
     * @return array<Post>
     */
    public static function getByTag($tagId, $limit = false, $offset = false, $moreQuerySettings = [])
    {
        return self::getBy(Ajax::TAG, $tagId, $limit, $offset, $moreQuerySettings);
    }

    /**
     * @param string $type
     * @param integer|string $by
     * @param bool|int $limit
     * @param bool|int $offset
     * @param array $moreQuerySettings
     * @return Post[]
     */
    private static function getBy($type, $by, $limit = false, $offset = false, $moreQuerySettings = [])
    {
        if (!$limit) {
            $limit = Option::get('posts_per_page');
        }

        if ($type == Ajax::TAG) {
            $tagId = is_numeric($by) ? $by : get_term_by('name', $by, 'post_tag')->term_id;
            $moreQuerySettings['tag_id'] = "$tagId";
        } elseif ($type == Ajax::CATEGORY) {
            $catId = is_numeric($by) ? $by : get_cat_ID($by);
            $moreQuerySettings['cat'] = "$catId";
        } elseif ($type == Ajax::SEARCH) {
            $moreQuerySettings['s'] = "$by";
        } elseif ($type == Ajax::AUTHOR) {
            $moreQuerySettings['author'] = $by;
        } elseif ($type == Ajax::ARCHIVE) {
            list($year, $monthnum) = explode(Archive::DELIMITER, trim($by, Archive::DELIMITER));
            if (!isset($moreQuerySettings['year'])) {
                $moreQuerySettings['year'] = $year;
                $moreQuerySettings['monthnum'] = $monthnum;
            }
        }
        return self::getAll($limit, $offset, $moreQuerySettings);
    }
}
