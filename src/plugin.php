<?php
/**
 * Plugin Name: Post2Ngg
 * Description: Migrate a post to a NextGen Gallery
 * Plugin URI: http://www.dacure.com
 * Author: Jorge Cortes
 * Author URI: http://www.dacure.com
 * Version: 1.0
 * License: GPL2
 *
 * Copyright (C) 2014 Jorge Cortes jorge.cortes@gmail.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Constants
 */
define('POST2NGG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('POST2NGG_PLUGIN_URL',  plugin_dir_url( __FILE__ ));
define('POST2NGG_VERSION',     '1.0');

require_once (POST2NGG_PLUGIN_PATH . 'common.php');

/**
 * Action functions
 */
function post2ngg_action_admin_menu() {
    add_management_page('Post2Ngg', 'Post2Ngg', 'manage_options', 'post2ngg', 'post2ngg_render_management_page');
}

function post2ngg_action_register_assets() {
    wp_enqueue_script('post2ngg_js', POST2NGG_PLUGIN_URL . 'js/script.js', array('jquery'), '1.1');
    wp_enqueue_script('handlebars', POST2NGG_PLUGIN_URL . 'js/handlebars-v1.3.0.js', null, null, false);
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script('jquery-ui-progressbar');

    wp_enqueue_style('post2ngg_css', POST2NGG_PLUGIN_URL . 'css/style.css');
    wp_enqueue_style('custom-jquery-ui', POST2NGG_PLUGIN_URL . 'css/jquery-ui.css');
}

function post2ngg_action_create_post_gallery() {
    header('Content-Type: application/json');

    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

    if ($post_id) {
        $data = post2ngg_create_post_gallery($post_id);
    } else {
        $data['error'] = "Not valid post id.";
    }

    post2ngg_output('', $data, true);
}

function post2ngg_action_import_images_to_gallery() {
    $post_id      = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $gallery_id   = isset($_POST['gallery_id']) ? (int) $_POST['gallery_id'] : 0;
    $image_src    = isset($_POST['image_src']) ? $_POST['image_src'] : '';
    $total_images = isset($_POST['total_images']) ? $_POST['total_images'] : 0;

    if ($image_src && $gallery_id && $post_id && $total_images) {
        $data = post2ngg_import_images_to_gallery($post_id, $gallery_id, $image_src, $total_images);
        post2ngg_update_post($post_id, $gallery_id, $total_images);
    } else {
        $data['error'] = "Something is wrong with the provided data.";
    }

    post2ngg_output('', $data, true);
}

function post2ngg_action_process_categories() {
    $categories = isset($_POST['categories']) ? $_POST['categories'] : array();

    query_posts(
        array(
            'category__in'   => $categories,
            'posts_per_page' => -1
        )
    );

    $posts_ids = array();

    if (have_posts()) {
        while (have_posts()) {
            the_post();
            $posts_ids[] = get_the_ID();
        }
    }

    post2ngg_output('', array('posts' => $posts_ids), true);
}

/**
 * Helper functions
 */

function post2ngg_create_post_gallery($post_id) {
    $data = array();
    $post = get_post($post_id);

    if (!$post) {
        $data['error'] = "The post $post_id does not exists.";
        post2ngg_output('', $data, true);
    }

    $category = get_the_category($post_id);
    $category = current($category);

    $data['post_id'] = $post_id;
    $data['post_title'] = $post->post_title;
    $data['post_date'] = $post->post_date;
    $data['post_status'] = $post->post_status;
    $data['post_category'] = $category->cat_name;

    $permalink = get_permalink($post_id);
    $data['post_permalink'] = $permalink;

    $post_featured_image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id));
    $post_featured_image = $post_featured_image[0];
    $data['post_featured_image'] = $post_featured_image;

    $content_images = post2ngg_get_post_images_from_content($post);
    $total_images = count($content_images);
    if ($total_images < 2) {
        $data['error'] = "The post $post_id does not contains enough images.";
        post2ngg_output('', $data, true);
    }
    $data['post_total_images'] = $total_images;
    $data['post_images'] = $content_images;

    // Verify if we already have a gallery for this post.
    $gallery_name   = wptexturize($post->post_title) . '-' . $post_id;

    $gallery_mapper = C_Gallery_Mapper::get_instance();
    $gallery = $gallery_mapper
        ->select()
        ->where(array('title = %s', $gallery_name))
        ->limit(1)
        ->run_query(false, false);

    if (empty($gallery)) {
        $gallery = $gallery_mapper->create(array('title' =>  $gallery_name));
        $gallery->save();
    } else {
        $gallery = current($gallery);
    }

    $data['gallery_id'] = $gallery->gid;
    $data['gallery_title'] = $gallery->title;

    return $data;
}

function post2ngg_import_images_to_gallery($post_id, $gallery_id, $image_src, $total_images) {
    $gallery_mapper = C_Gallery_Mapper::get_instance();
    $gallery = $gallery_mapper->find($gallery_id);

    if (!$gallery) {
        post2ngg_output("The gallery $gallery_id does not exists", array(), true);
    }

    // Verify if the image already exists inside the gallery
    $image_name = post2ngg_extract_filename_from_url($image_src);
    $image_name = apply_filters('the_title', $image_name);

    $image_mapper = C_Image_Mapper::get_instance();
    $image = $image_mapper
        ->select()
        ->where_and(
            array(
                array('galleryid = %d', $gallery_id),
                array('alttext = %s', $image_name)
            ))
        ->limit(1)
        ->run_query();

    if (empty($image)) {
        $storage_mapper = C_Gallery_Storage::get_instance();
        $image_location = post2ngg_filter_local_urls($image_src);
        $image = $storage_mapper->upload_base64_image(
            $gallery_id,
            file_get_contents($image_location),
            $image_name
        );
        $message = 'Image was stored';
    } else {
        $message = 'Image already exists in gallery';
    }


    return array ('message' => $message);
}

function post2ngg_extract_filename_from_url($url) {
     $keys = parse_url($url);
     $path = explode("/", $keys['path']);
     $last = end($path);
     return preg_replace("/\\.[^.\\s]{3,4}$/", "", $last);
}

/**
 * Transform local urls to local paths
 */
function post2ngg_filter_local_urls($url) {
    $pieces = parse_url($url);
    if ($pieces['host'] == $_SERVER['HTTP_HOST']) {
        $url = POST2NGG_PLUGIN_PATH . '../../..' . $pieces['path'];
    }
    return $url;
}

function post2ngg_update_post($post_id, $gallery_id, $total_images) {
    $updated = false;
    $image_mapper = C_Image_Mapper::get_instance();
    $counter = $image_mapper
        ->select('COUNT(*) as counter')
        ->where(array('galleryid = %d', $gallery_id))
        ->limit(1)
        ->run_query(false, true);

    if (!empty($counter)) {
        $counter = current($counter);
        if ($counter->counter == $total_images) {
            $post = get_post($post_id);
            $post->post_content = "[nggallery id='{$gallery_id}' template='galleryview']";
            wp_update_post($post->to_array());
            set_post_format($post_id, 'gallery');
            $updated = true;
        }
    }
    return $updated;
}

function post2ngg_get_post_images_from_content($post) {
    $html = new DOMDocument();
    $html->loadHTML($post->post_content);

    $images = array();

    $dom_images = $html->getElementsByTagName('img');
    foreach ($dom_images as $image) {
        $images[] = $image->getAttribute('src');
    }

    return $images;
}

function post2ngg_render_management_page() {
    $transform_type = isset($_POST['type']) ? $_POST['type'] : '';

    switch ($transform_type) {
        case 'single-post':
            $post_id = (int) $_POST['post_to_transform'];
            post2ngg_update_post($post_id);
            include_once (POST2NGG_PLUGIN_PATH . 'views/admin-single-done.php');
            break;
        case 'category':
            query_posts(
                array(
                    'category__in'   => $_POST['post_category'],
                    'posts_per_page' => -1
                )
            );
            if (have_posts()) :
                while (have_posts()) :
                    the_post();
                    post2ngg_update_post(get_the_ID());
                endwhile;
            endif;
            include_once (POST2NGG_PLUGIN_PATH . 'views/admin-done.php');
            break;
        default:
            include_once (POST2NGG_PLUGIN_PATH . 'views/admin.php');
            break;
    }
}

function post2ngg_output($message, $data = array(), $die = false) {
    if ($message !== '') {
        $data['message'] = $message;
    }
    echo json_encode($data);
    if ($die) {
        die();
    }
}