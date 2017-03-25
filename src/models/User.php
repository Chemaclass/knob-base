<?php declare(strict_types=1);
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knob\Models;

/**
 * User Model
 *
 * @author José María Valera Reales
 */
abstract class User extends Image
{
    const EMPTY_VALUE = '';
    const AVATAR_SIZE_ICO = 26;

    /*
     * Avatar Size
     */
    const AVATAR_SIZE_SMALL = 64;
    const AVATAR_SIZE_DEFAULT = 96;
    const AVATAR_SIZE_PROFILE = 190;
    const KEY_FIRST_NAME = 'first_name';

    /*
     * Const
     */
    const KEY_LAST_NAME = 'last_name';
    const KEY_AVATAR = 'img_avatar';
    const KEY_HEADER = 'img_header';
    const KEY_LANGUAGE = 'language';
    const KEY_TWITTER = 'twitter';
    const KEY_TWITTER_URL = 'twitter_url';
    const KEY_FACEBOOK = 'facebook';
    const KEY_FACEBOOK_URL = 'facebook_url';
    const KEY_GOOGLE_PLUS = 'google_plus';
    const KEY_GOOGLE_PLUS_URL = 'google_plus_url';
    const KEY_TYPE = 'user_type';
    const ROL_ADMIN = 'administrator';

    /*
     * Possible roles
     */
    const ROL_EDITOR = 'editor';
    const ROL_AUTHOR = 'author';
    const ROL_CONTRIBUTOR = 'contributor';
    const ROL_SUBSCRIBER = 'subscriber';
    const TYPE_AUTHOR = 'author';

    /*
     * Possible kind of users
     */
    const TYPE_USER = 'user';
    const TYPE_DEFAULT = self::TYPE_USER;
    const HEADER_WIDTH = 1200;

    /*
     * Header sizes
     */
    const HEADER_HEIGHT = 270;
    const WITH_SIDEBAR_DEFAULT = true;

    /*
     * Sidebar
     */
    public static $table = "users";
    /**
     *
     * @var \WP_User
     *
     * @see https://codex.wordpress.org/Class_Reference/WP_User
     */
    protected $wpUser = null;

    /**
     * @param integer $ID
     * @param bool $withWPUser load into the User object all members from \WP_User
     *
     * @see https://developer.wordpress.org/reference/classes/wp_user/
     */
    public function __construct($ID = 0, $withWPUser = false)
    {
        parent::__construct($ID);
        if ($withWPUser) {
            $this->wpUser = new \WP_User($this->ID);
        }
    }

    /**
     * Return all valid user types
     *
     * @return string[]
     */
    public static function getValidTypes()
    {
        return [
            self::TYPE_AUTHOR,
            self::TYPE_USER,
        ];
    }

    /**
     * @see https://developer.wordpress.org/reference/classes/wp_user/
     */
    public function getWPUser(): \WP_User
    {
        return $this->wpUser;
    }

    public function isTypeAuthor(): bool
    {
        return $this->isType(self::TYPE_AUTHOR);
    }

    private function isType(string $type): bool
    {
        return ($this->getType() == $type);
    }

    /**
     * @see https://codex.wordpress.org/Function_Reference/get_user_meta
     */
    public function getType(): string
    {
        $userType = get_user_meta($this->ID, self::KEY_TYPE, true);
        if (!$userType) {
            return self::TYPE_DEFAULT;
        }
        return $userType;
    }

    public function isTypeUser(): bool
    {
        return $this->isType(self::TYPE_USER);
    }

    public function canSubscriber(array $args = []): bool
    {
        $args[] = self::ROL_SUBSCRIBER;
        return $this->canContributor($args);
    }

    public function canContributor(array $args = []): bool
    {
        $args[] = self::ROL_CONTRIBUTOR;
        return $this->canAuthor($args);
    }

    public function canAuthor(array $args = []): bool
    {
        $args[] = self::ROL_AUTHOR;
        return $this->canEditor($args);
    }

    public function canEditor(array $args = []): bool
    {
        $args[] = self::ROL_EDITOR;
        return $this->canAdmin($args);
    }

    public function canAdmin(array $args = []): bool
    {
        $args[] = self::ROL_ADMIN;
        return count(array_intersect($args, self::getRoles())) > 0;
    }

    /**
     * @see https://codex.wordpress.org/Function_Reference/get_user_meta
     */
    public function getRoles(): array
    {
        global $wpdb;
        $capabilities = get_user_meta($this->ID, $wpdb->prefix . 'capabilities', true);

        return is_array($capabilities) ? array_keys($capabilities) : ['non-user'];
    }

    public function isCurrentUserOrAdmin(): bool
    {
        return $this->isCurrentUser()
            || (wp_get_current_user()->roles[0] == self::ROL_ADMIN);
    }

    public function isCurrentUser(): bool
    {
        return ($this->ID == wp_get_current_user()->ID);
    }

    public function getFirstRol(): string
    {
        $roles = $this->getRoles();
        return $roles[0];
    }

    public function isAdmin(): bool
    {
        return in_array(self::ROL_ADMIN, $this->getRoles());
    }

    public function isEditor(): bool
    {
        return in_array(self::ROL_EDITOR, $this->getRoles());
    }

    public function isAuthor(): bool
    {
        return in_array(self::ROL_AUTHOR, $this->getRoles());
    }

    public function isContributor(): bool
    {
        return in_array(self::ROL_CONTRIBUTOR, $this->getRoles());
    }

    public function isSubscriber(): bool
    {
        return in_array(self::ROL_SUBSCRIBER, $this->getRoles());
    }

    public function getAvatarProfile(): string
    {
        return $this->avatarUrl(self::AVATAR_SIZE_PROFILE);
    }

    public function avatarUrl(int $size = self::AVATAR_SIZE_DEFAULT): string
    {
        $avatar = $this->getImage(self::KEY_AVATAR, $size, $size);
        if (empty($avatar)) {
            return static::defaultAvatarUrl($size);
        }
        return $avatar;
    }

    private static function defaultAvatarUrl(int $size = User::AVATAR_SIZE_DEFAULT): string
    {
        return PUBLIC_DIR . '/img/avatar/avatar_' . $size . '.png';
    }

    public function getAvatarIco(): string
    {
        return $this->avatarUrl(self::AVATAR_SIZE_ICO);
    }

    public function getAvatarSmall(): string
    {
        return $this->avatarUrl(self::AVATAR_SIZE_SMALL);
    }

    /**
     * @param $newAvatar
     * @return bool|int
     */
    public function setAvatar($newAvatar)
    {
        return $this->setImage(self::KEY_AVATAR, $newAvatar);
    }

    /**
     * Return the path from his header
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->getImage(
            self::KEY_HEADER,
            self::HEADER_WIDTH,
            self::HEADER_HEIGHT
        );
    }

    /**
     * Set the new header
     *
     * @param $newHeader
     * @return boolean
     */
    public function setHeader($newHeader = false)
    {
        return $this->setImage(self::KEY_HEADER, $newHeader);
    }

    /**
     * Return all comment
     *
     * @return array<Comment>
     */
    public function getComments($postId = false)
    {
        $cArgs = [
            'author__in' => $this->ID,
            'orderby' => 'comment_date_gmt',
        ];
        if ($postId) {
            $cArgs['post_id'] = $postId;
        }
        $comments = [];
        foreach (get_comments($cArgs) as $c) {
            $comments[] = Comment::find($c->comment_ID);
        }
        return $comments;
    }

    /**
     * Get all posts
     *
     * @param integer $max total posts to show
     * @return array<Post>
     */
    public function getPosts($limit = false, $offset = false)
    {
        if (!$limit) {
            $limit = Option::get('posts_per_page');
        }
        return Post::getByAuthor($this->ID, $limit, $offset);
    }

    /**
     * Return the login user
     *
     * @return string
     */
    public function getUserLogin()
    {
        return stripslashes($this->user_login);
    }

    /**
     * Return the email
     *
     * @return string
     */
    public function getUserEmail()
    {
        return stripslashes($this->user_email);
    }

    /**
     * Return the name and surname from the User.
     *
     * If doesn't exists we just return his alias.
     *
     * @return string
     */
    public function getFullName()
    {
        $fullName = $this->getFirstName() . ' ' . $this->getLastName();
        if (strlen(trim($fullName))) {
            return $fullName;
        }
        return $this->getDisplayName();
    }

    /**
     * Return the first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return get_user_meta($this->ID, self::KEY_FIRST_NAME, true);
    }

    /**
     * Return the last name
     *
     * @return string
     */
    public function getLastName()
    {
        return get_user_meta($this->ID, self::KEY_LAST_NAME, true);
    }

    /**
     * Return the public name
     *
     * @return string
     */
    public function getDisplayName()
    {
        if (!isset($this->display_name)) {
            return self::EMPTY_VALUE;
        }

        return stripslashes($this->display_name);
    }

    /**
     * Return the URL for to edit the User
     *
     * @return string
     */
    public function getEditUrl()
    {
        return admin_url('user-edit.php?user_id=' . $this->ID, 'http');
    }

    /**
     * Return the user URL
     *
     * @return string
     */
    public function getUserUrl()
    {
        return get_the_author_meta('user_url', $this->ID);
    }

    /**
     * Return the author posts url
     */
    public function getPostsUrl()
    {
        return get_author_posts_url($this->ID);
    }

    /**
     * Return Twitter
     *
     * @return string
     */
    public function getTwitter()
    {
        return get_user_meta($this->ID, self::KEY_TWITTER, true);
    }

    /**
     * Return Twitter
     *
     * @return string
     */
    public function getTwitterUrl()
    {
        return get_user_meta($this->ID, self::KEY_TWITTER_URL, true);
    }

    /**
     * Set new Twitter
     *
     * @param string $value Can be the nickname or the absolute url
     */
    public function setTwitter($value)
    {
        $nickname = $url = '';
        if (strlen($value)) {
            if (strpos($value, 'http') !== false) {
                $url = $value;
                $nickname = '@' . substr($value, strrpos($value, '/') + 1);
            } else {
                $nickname = $value;
                if (strpos($value, '@') !== false) {
                    $value = substr($value, 1);
                } else {
                    $nickname = '@' . $value;
                }
                $url = 'https://twitter.com/' . $value;
            }
        }
        update_user_meta($this->ID, User::KEY_TWITTER, $nickname);
        update_user_meta($this->ID, User::KEY_TWITTER_URL, $url);
    }

    /**
     * Return Facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return get_user_meta($this->ID, self::KEY_FACEBOOK, true);
    }

    /**
     * Return Facebook url
     *
     * @return string
     */
    public function getFacebookUrl()
    {
        return get_user_meta($this->ID, self::KEY_FACEBOOK_URL, true);
    }

    /**
     * Set new Facebook
     *
     * @param string $value Can be the nickname or the absolute url
     */
    public function setFacebook($value)
    {
        $nickname = $url = '';
        if (strlen($value)) {
            if (strpos($value, 'http') !== false) {
                $url = $value;
                $nickname = substr($value, strrpos($value, '/') + 1);
            } else {
                $nickname = $value;
                $url = 'https://facebook.com/' . $value;
            }
        }
        update_user_meta($this->ID, User::KEY_FACEBOOK, $nickname);
        update_user_meta($this->ID, User::KEY_FACEBOOK_URL, $url);
    }

    /**
     * Return Google+
     *
     * @return string
     */
    public function getGooglePlus()
    {
        return get_user_meta($this->ID, self::KEY_GOOGLE_PLUS, true);
    }

    /**
     * Return Google+ Url
     *
     * @return string
     */
    public function getGooglePlusUrl()
    {
        return get_user_meta($this->ID, self::KEY_GOOGLE_PLUS_URL, true);
    }

    /**
     * Set new Twitter
     *
     * @param string $value Can be the nickname or the absolute url
     */
    public function setGooglePlus($value)
    {
        $nickname = $url = '';
        if (strlen($value)) {
            if (strpos($value, 'http') !== false) {
                $url = $value;
                $nickname = substr($value, strrpos($value, '/') + 1);
            } else {
                $nickname = $value;
                if (strpos($value, '+') !== false) {
                    $nickname = substr($value, 1);
                } else {
                    $value = '+' . $value;
                }
                $url = 'https://plus.google.com/' . $value;
            }
        }
        update_user_meta($this->ID, User::KEY_GOOGLE_PLUS, $nickname);
        update_user_meta($this->ID, User::KEY_GOOGLE_PLUS_URL, $url);
    }

    /**
     * Set a rol to User
     *
     * @param string $rol
     */
    public function setRol($rol)
    {
        if (in_array($rol, self::getAllowedRoles())) {
            $u = new \WP_User($this->ID);
            $u->set_role($rol);
            return true;
        }
        return false;
    }

    /**
     * Return true if the User can see the sidebar.
     *
     * @return boolean
     */
    public function isWithSidebar()
    {
        return self::WITH_SIDEBAR_DEFAULT;
    }

    /**
     * Return the description
     *
     * @return string
     */
    public function getDescription()
    {
        $string = get_the_author_meta('description', $this->ID);
        if (strlen($string)) {
            return $string;
        }
        return false;
    }

    /**
     * Return language
     *
     * @return string
     */
    public function getLang()
    {
        return get_user_meta($this->ID, self::KEY_LANGUAGE, true);
    }

    /**
     * Set new lang
     *
     * @param string $value
     */
    public function setLang($value)
    {
        update_user_meta($this->ID, User::KEY_LANGUAGE, $value);
    }

    /**
     * Return the total of publish posts
     *
     * @return integer
     */
    public function getTotalPosts()
    {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare('SELECT COUNT(*)
				FROM ' . $wpdb->prefix . 'posts
				WHERE post_author = %d
				AND post_type = %s
				AND post_status = %s', $this->ID, Post::TYPE_POST, Post::STATUS_PUBLISH));
    }

    /**
     * @see \Models\Image::getImageSizesToDelete()
     */
    protected function getImageSizesToDelete(): array
    {
        return $this->getSizesAvatar();
    }

    /**
     * Return a list with the possible sizes for the avatar
     *
     * @return array<integer>
     */
    public function getSizesAvatar()
    {
        return [
            self::AVATAR_SIZE_ICO,
            self::AVATAR_SIZE_SMALL,
            self::AVATAR_SIZE_DEFAULT,
            self::AVATAR_SIZE_PROFILE,
        ];
    }
}