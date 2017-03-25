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
 * TODO: This is a bad design.
 * TODO: Remember: Composition instead of inheritance!
 * TODO: A redesign is need it here.
 *
 * @author José María Valera Reales
 */
abstract class Image extends ModelBase
{

    /**
     * @param string $keyImg Key from the image
     * @param $imgFile The image
     *
     * @throws \Exception
     *
     * @return int|bool
     * @see https://codex.wordpress.org/Function_Reference/wp_get_attachment_url
     * @see https://codex.wordpress.org/Function_Reference/update_user_meta
     */
    protected function setImage(string $keyImg, $imgFile)
    {
        // If it's false or null we have to remove it from the server
        if (!$imgFile || is_null($imgFile)) {
            return $this->removeImage($keyImg);
        }
        if (strpos($imgFile['name'], '.php') !== false) {
            throw new \Exception('For security reasons, the extension ".php" cannot be in your file name.');
        }
        $avatar = $this->avatarWPHandler($keyImg);

        // Remove the last image
        $this->removeImage($keyImg);

        $url_or_media_id = $avatar['url'];
        // Set the new image
        $metaValue = [];
        if (is_int($url_or_media_id)) {
            $metaValue['media_id'] = $url_or_media_id;
            $url_or_media_id = wp_get_attachment_url($url_or_media_id);
        }
        $metaValue['full'] = $url_or_media_id;
        return update_user_meta($this->ID, $keyImg, $metaValue);
    }

    /**
     * Remove the image
     *
     * @return int|bool
     * @see https://codex.wordpress.org/Function_Reference/delete_user_meta
     */
    private function removeImage($keyImg)
    {
        // Save the path in one temporal var
        $getImagePath = $this->getImagePath($keyImg);
        $sizes = $this->getImageSizesToDelete();
        foreach ($sizes as $size) {
            $imgPath = $getImagePath['virgen'] . "-{$size}x{$size}" . $getImagePath['ext'];
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }
        }

        if (file_exists($getImagePath['base'])) {
            unlink($getImagePath['base']);
        }

        if (file_exists($getImagePath['current'])) {
            unlink($getImagePath['current']);
        }

        // remove his meta info
        return delete_user_meta($this->ID, $keyImg);
    }

    /**
     * Return the base name of the img and the name of the current img.
     *
     * for example [
     * 'current' => 'Chemaclass_avatar-26x26.png',
     * 'base' => 'Chemaclass_avatar.png',
     * 'virgen' => 'Chemaclass_avatar',
     * 'ext' => '.png',
     * ];
     *
     * @return array List with the current name, base, virgen and extension of the image
     */
    private function getImagePath($keyImg)
    {
        $upload_path = wp_upload_dir();
        $img = $this->getImage($keyImg, User::AVATAR_SIZE_ICO);
        $path = str_replace($upload_path['baseurl'], $upload_path['basedir'], $img);
        $current = $base = basename($path);
        $pathBase = '';
        $ext = [''];
        if (strpos($base, '-') !== false) {
            preg_match('/\.[^\.]+$/i', $current, $ext);
            $_base = substr($base, 0, strpos($base, "-")) . $ext[0];
            $pathBase = str_replace($current, $_base, $path);
        }
        // and the virgen path
        $_base_virgen = substr($base, 0, strpos($base, "-"));
        $virgen = str_replace($current, $_base_virgen, $path);
        return [
            'current' => $path,
            'base' => $pathBase,
            'virgen' => $virgen,
            'ext' => $ext[0],
        ];
    }

    /**
     * @param string $keyImg Key image
     * @param int $sizeW weight
     * @param int $sizeH height
     * @return string URL to the image
     * @see https://codex.wordpress.org/Function_Reference/update_user_meta
     * @see https://codex.wordpress.org/Function_Reference/wp_upload_dir
     * @see https://developer.wordpress.org/reference/functions/update_user_meta/
     */
    protected function getImage(string $keyImg, int $sizeW, int $sizeH = 0): string
    {
        $sizeH = empty($sizeH) ? $sizeW : $sizeH;
        // fetch local avatar from meta and make sure it's properly ste
        $local_avatars = get_user_meta($this->ID, $keyImg, true);
        if (empty($local_avatars['full'])) {
            return '';
        }
        // generate a new size
        if (!array_key_exists($sizeW, $local_avatars)) {
            $local_avatars[$sizeW] = $local_avatars['full']; // just in case of failure elsewhere
            $upload_path = wp_upload_dir();
            // get path for image by converting URL, unless its already been set, thanks to using
            // media library approach
            if (!isset($avatar_full_path)) {
                $avatar_full_path = str_replace($upload_path['baseurl'], $upload_path['basedir'],
                    $local_avatars['full']);
            }
            // generate the new size
            $editor = wp_get_image_editor($avatar_full_path);
            if (!is_wp_error($editor)) {
                $resized = $editor->resize($sizeW, $sizeH, true);
                if (!is_wp_error($resized)) {
                    $dest_file = $editor->generate_filename();
                    $saved = $editor->save($dest_file);
                    if (!is_wp_error($saved)) {
                        $local_avatars[$sizeW] = str_replace($upload_path['basedir'], $upload_path['baseurl'],
                            $dest_file);
                    }
                }
            }
            // save updated avatar sizes
            update_user_meta($this->ID, $keyImg, $local_avatars);
        }
        if ('http' != substr($local_avatars[$sizeW], 0, 4)) {
            $local_avatars[$sizeW] = home_url($local_avatars[$sizeW]);
        }
        return esc_url($local_avatars[$sizeW]);
    }

    /**
     * Return a list with the sizes of img to delete.
     */
    protected abstract function getImageSizesToDelete();

    /**
     * @param string $keyImg
     * @return mixed
     * @see https://codex.wordpress.org/Function_Reference/wp_handle_upload
     */
    private function avatarWPHandler(string $keyImg)
    {
        return wp_handle_upload(
            $_FILES[$keyImg],
            [
                'mimes' => [
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'png' => 'image/png',
                ],
                'test_form' => false,
                'unique_filename_callback' => function ($dir, $name, $ext) use ($keyImg) {
                    $base_name = sanitize_file_name($this->user_login . '_' . $keyImg);
                    for ($i = 1; file_exists($dir . "/$name$ext"); $i++) {
                        $name = $base_name . '_' . $i;
                    }
                    return $name . $ext;
                },
            ]
        );
    }
}