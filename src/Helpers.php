<?php
/****************************************
 * Theme Helpers for Axe
 * https://github.com/adampatterson/Axe
 *****************************************/

use Axe\Exception\MissingTemplateException;
use Carbon\Carbon;
use Illuminate\Support\Arr;

if (!function_exists('__t')) {
    /**
     * The root template directory, this can be over written in the child theme.
     *
     * @return string
     */
    function __t()
    {
        return get_template_directory_uri().'/';
    }
}

if (!function_exists('__p')) {
    /**
     * Returns the Parent template path.
     *
     * @return string
     */
    function __p()
    {
        return get_template_directory().'/';
    }
}

if (!function_exists('__a')) {
    /**
     * Assets relative to the template directory.
     *
     * @return string
     */
    function __a($useParent = false)
    {
        return __t($useParent).'assets/';
    }
}

if (!function_exists('__j')) {
    /**
     * Echoes the Javascript path.
     */
    function __j($useParent = false)
    {
        echo __a($useParent).'js/';
    }
}

if (!function_exists('__i')) {
    /**
     * Echoes the Images path.
     */
    function __i($useParent = false)
    {
        echo __a($useParent).'img/';
    }
}

if (!function_exists('__c')) {
    /**
     * Echoes the CSS path.
     */
    function __c($useParent = false)
    {
        echo __a($useParent).'css/';
    }
}

if (!function_exists('__v')) {
    /**
     * Echoes the Vendor path.
     */
    function __v($useParent = false)
    {
        echo __a($useParent).'vendor/';
    }
}

if (!function_exists('__lib')) {
    /**
     * Returns the Lib path.
     */
    function __lib($path)
    {
        return template_directory('/lib/'.$path);
    }
}

if (!function_exists('mix')) {
    /**
     * @param $path
     *
     * @return string
     */
    function mix($path, $useParent = false)
    {
        $pathWithOutSlash = ltrim($path, '/');
        $pathWithSlash = '/'.ltrim($path, '/');
        $manifestFile = __m($useParent);

//        No manifest file was found so return whatever was passed to mix().
        if (!$manifestFile) {
            return __t($useParent).$pathWithOutSlash;
        }

        $manifestArray = json_decode(file_get_contents($manifestFile), true);

        /*
                $pathWithOutAssets = '/'.ltrim($pathWithSlash, '/assets');
                if (array_key_exists($pathWithOutAssets, $manifestArray)) {
                    return __t($useParent).'assets/'.ltrim($manifestArray[$pathWithOutAssets], '/');
                }
        */

        if (array_key_exists($pathWithSlash, $manifestArray)) {
            return __t($useParent).ltrim($manifestArray[$pathWithSlash], '/');
        }
//        No file was found in the manifest, return whatever was passed to mix().
        return __t($useParent).$pathWithOutSlash;
    }
}

if (!function_exists('__video')) {
    /**
     *  Echos the video path.
     */
    function __video($useParent = false)
    {
        echo __a($useParent).'video/';
    }
}


if (!function_exists('underscore')) {
    /**
     * @param $string
     *
     * @return string
     */
    function underscore($string)
    {
        return strtolower(preg_replace('/[[:space:]]+/', '_', $string));
    }
}

if (!function_exists('dash')) {
    /**
     * @param $string
     *
     * @return string
     */
    function dash($string)
    {
        return strtolower(preg_replace('/[[:space:]]+/', '-', $string));
    }
}

if (!function_exists('get_cat_hierarchy')) {
    /**
     * @param $parent
     * @param $args
     *
     * Category list
     *
     * @return array
     */
    function get_cat_hierarchy($parent, $args)
    {
        $cats = get_categories($args);
        $ret = new stdClass;

        foreach ($cats as $cat) {
            if ($cat->parent == $parent) {
                $id = $cat->cat_ID;
                $ret->$id = $cat;
                $ret->$id->children = get_cat_hierarchy($id, $args);
            }
        }

        return (array) $ret;
    }
}

if (!function_exists('get_acf_part')) {
    /**
     * @param $slug
     * @param $name
     * @param $data
     * @param $block
     *
     * Allows the pass through of data to template partials.
     *
     */
    function get_acf_part($slug, $name = null, $data = null, $block = null)
    {
	    $include = get_template_part_acf( $slug, $name );

	    try {
		    if ( $include ) {
			    include( $include );
		    } else {
			    throw new MissingTemplateException( "<p>Missing Template: {$slug} {$name}</p>" );
		    }
	    } catch ( MissingTemplateException $e ) {
		    echo $e->getMessage();
	    }
    }
}

if (!function_exists('get_acf_block')) {
    /**
     * @param $block
     * @param $data
     * @param $path
     * @return void
     *
     * Example Usage:
     * get_acf_block($block)
     * get_acf_block(path: 'templates/blocks/', block: $block); // custom block path
     */
    function get_acf_block(array $block, $data = null, $path = 'templates/blocks/',)
    {
        $layout = $block['acf_fc_layout'] ?? '';

        $file = get_template_part_acf($path.$layout);

        $block = $block['data'] ?? $block;

        if (file_exists($file)) {
            echo "<!-- template: {$path}{$layout} -->";
            include($file);
        } else {
            echo "Missing block {$layout}";
        }
    }
}

if (!function_exists('get_template_part_acf')) {
    /**
     * @param      $slug
     * @param  null  $name
     * @param  null  $data
     *
     * Allows the pass through of data to template partials.
     *
     * @return string
     */
    function get_template_part_acf($slug, $name = null)
    {
        $templates = [];
        $name = (string) $name;

        if ($name == null) {
            $templates[] = "{$slug}.php";
        } else {
            $templates[] = "{$slug}-{$name}.php";
        }

        $located = '';
        foreach ((array) $templates as $template_name) {
            if (!$template_name) {
                continue;
            }
            if (template_directory($template_name)) {
                $located = template_directory($template_name);
            }
        }

        return $located;
    }
}

if (!function_exists('check_path')) {
    /**
     * @param $template_name
     *
     * @return string
     */
    function check_path($template_name)
    {
        if (file_exists(get_stylesheet_directory().'/'.$template_name) or file_exists(get_template_directory().'/'.$template_name)) {
            return get_template_directory().'/'.$template_name;
        }

        return false;
    }
}

if (!function_exists('template_directory')) {
    /**
     * @param $template_name
     *
     * @return bool|string
     */
    function template_directory($template_name)
    {
        $template_name = trim($template_name, "/");

        if (file_exists(get_stylesheet_directory().'/'.$template_name)) {
            return get_stylesheet_directory().'/'.$template_name;
        }

        if (file_exists(get_template_directory().'/'.$template_name)) {
            return get_template_directory().'/'.$template_name;
        }

        return false;
    }
}

if (!function_exists('__m')) {
    /**
     * Returns the mix-manifest.json file
     *
     * @return bool|string
     */
    function __m($useParent)
    {
        $template_name = "mix-manifest.json";

        // Force the Parent Manifest
        if ($useParent && file_exists(get_template_directory().'/'.$template_name)) {
            return get_template_directory().'/'.$template_name;
        }

        // Check the Child Manifest
        if (file_exists(get_stylesheet_directory().'/'.$template_name)) {
            return get_stylesheet_directory().'/'.$template_name;
        }

        // Return to the Core Manifest.
        if (file_exists(get_template_directory().'/'.$template_name)) {
            return get_template_directory().'/'.$template_name;
        }

        return false;
    }
}

if (!function_exists('is_sub_page')) {
    /**
     * @param $post
     *
     * @return bool
     */
    function is_sub_page($post)
    {
        return is_page() && $post->post_parent > 0;
    }
}

if (!function_exists('show_template')) {
    /**
     * Returns the local WordPress template path.
     *
     * @return mixed
     */
    function show_template()
    {
        if (is_super_admin()) {
            global $template;

            return str_replace(get_theme_root(), "", $template);
        }
    }
}

if (!function_exists('show_woo_listing')) {
    /**
     * Returns the local WordPress template path.
     *
     * @return mixed
     */
    function show_woo_listing()
    {
        return class_exists('WooCommerce') and is_shop() and !is_product();
    }
}

if (!function_exists('show_woo_category')) {
    /**
     * Returns the local WordPress template path.
     *
     * @return mixed
     */
    function show_woo_category()
    {
        return class_exists('WooCommerce') and is_product_category() and !is_product();
    }
}

if (!function_exists('show_woo_single_product')) {
    /**
     * Returns the local WordPress template path.
     *
     * @return mixed
     */
    function show_woo_single_product()
    {
        return class_exists('WooCommerce') and is_product();
    }
}

if (!function_exists('get_the_logo')) {
    /**
     * @param  bool  $include_link
     * @param  string  $custom_logo_css
     * @param  string  $custom_link_css
     * @param  string  $size
     *
     * @return bool|string
     *
     * Returns an HTML link including the logo, Or just the path to the logo image.
     */
    function get_the_logo(
        $include_link = false,
        $custom_logo_css = 'site-logo custom-logo img-fluid',
        $custom_link_css = 'logo custom-logo-link',
        $size = 'full'
    ) {
        $logo = wp_get_attachment_image(get_theme_mod('custom_logo'), $size, false, ['class' => $custom_logo_css]);

        if (!$logo) {
            return false;
        }

        $url = esc_url(home_url('/'));

        if ($include_link) {
            return sprintf('<a href="%s" class="%s" rel="home">%s</a>', $url, $custom_link_css, $logo);
        }

        return $logo;
    }
}

if (!function_exists('get_the_logo_url')) {
    /**
     * @param  string  $size
     *
     * @return bool|string
     *
     * Returns the site lgoo image path.
     */
    function get_the_logo_url($size)
    {
        $logo = wp_get_attachment_image_url(get_theme_mod('custom_logo'), $size, false);

        if (!$logo) {
            return false;
        }

        return $logo;
    }
}

if (!function_exists('if_custom_logo')) {
    /**
     * @return bool
     *
     * Simple function to adjust the template if there is a custom logo or not.
     */
    function if_custom_logo()
    {
        $logo = wp_get_attachment_image(get_theme_mod('custom_logo'), 'full');

        if (!$logo) {
            return false;
        }

        return true;
    }
}

if (!function_exists('word_count')) {
    function word_count()
    {
        global $post;
        //Variable: Additional characters which will be considered as a 'word'
        $char_list = '';
        /** MODIFY IF YOU LIKE.  Add characters inside the single quotes. **/ //$char_list = '0123456789'; /** If you want to count numbers as 'words' **/
        //$char_list = '&@'; /** If you want count certain symbols as 'words' **/
        return str_word_count(strip_tags($post->post_content), 0, $char_list);
    }
}

if (!function_exists('read_time')) {
    /**
     * @return float
     *
     * <p><?= read_time() ?> minute read</p>
     */
    function read_time()
    {
        $words = word_count();

        return ceil($words / 200);
    }
}

if (!function_exists('is_developer')) {
    function is_developer()
    {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();

            return in_array('developer', $user->roles);
        }

        return false;
    }
}

if (!function_exists('hide_adminbar_for_developers')) {
    function hide_adminbar_for_developers()
    {
        if (is_developer()) {
            add_filter('show_admin_bar', '__return_false');
        }
    }
}

if (!function_exists('make_slug')) {
    /**
     * @param $string
     *
     * @return string
     */
    function make_slug($string)
    {
        $string = str_replace('#', '', $string);

        // Remove accented letters
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

        return strtolower(preg_replace('/[[:space:]]+/', '_', $string));
    }
}

if (!function_exists('format_date')) {
    /**
     * @param $date
     * @param  bool  $format
     *
     * @return string
     */
    function format_date($date, $format = false)
    {
        $timezone = get_option('timezone_string');
        if (!$format) {
            return Carbon::parse($format, $timezone)->toFormattedDateString();
        }

        return Carbon::parse($date, $timezone)->format($format);
    }
}

/*
 * Helpers for working with ACF data objects
 */
if (!function_exists('_get')) {
    /**
     * @param $haystack
     * @param $needle
     * @param  null  $default
     * @param  bool  $showDefaultIfEmpty
     * @return mixed
     */
    function _get($haystack, $needle, $default = null, bool $defaultIfEmpty = false)
    {
        if ($defaultIfEmpty && Arr::get($haystack, $needle, false) === '') {
            return $default;
        }

        return Arr::get($haystack, $needle, $default);
    }
}

if (!function_exists('_has')) {
    /**
     * @param $haystack
     * @param $needle
     * @param  false  $default
     *
     * @return bool|mixed
     */
    function _has($haystack, $needle, $default = false)
    {
        if (Arr::has($haystack, $needle, false) && Arr::get($haystack, $needle, false) !== '') {
            return true;
        }

        return $default;
    }
}

if (!function_exists('setBaseDataPath')) {
    function setBaseDataPath()
    {
        /*
         * Current active theme, this could be the child theme
         */
        if (file_exists(get_stylesheet_directory().'/lib/data.php')) {
            define('__THEME_DATA__', get_stylesheet_directory());
            return;
        }

        /*
         * If the child theme does not have a data.php file
         * then we would default to the parent theme.
         */
        if (file_exists(get_template_directory().'/lib/data.php')) {
            define('__THEME_DATA__', get_template_directory());
            return;
        }
    }
}

if (!function_exists('getFeaturedImage')) {

    /**
     * @param $post_id
     * @param $size
     * @return mixed
     *
     * getFeaturedImage($post->ID, 'featured');
     *
     */
    function getFeaturedImage($post_id, $size = 'featured')
    {
        $thumb_id = get_post_thumbnail_id($post_id);
        $img = wp_get_attachment_image_src($thumb_id, $size);
        $meta = get_post_meta($thumb_id);

        // If there's no image, then there's no post meta, return false.
        if (!$meta && !$img) {
            return false;
        }

        $meta_attachment = unserialize($meta['_wp_attachment_metadata'][0]);

        return [
            'url'        => $img[0],
            'width'      => $img[1],
            'height'     => $img[2],
            'meta'       => $meta_attachment,
            'image_meta' => $meta_attachment['image_meta'],
            //'image_sizes' => $meta_attachment['sizes'],
            'alt'        => $meta['_wp_attachment_image_alt'][0]
        ];
    }
}
