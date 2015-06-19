<?php
/**
 * Hooks
 */
add_action('admin_menu', 'post2ngg_action_admin_menu');
add_action('admin_enqueue_scripts', 'post2ngg_action_register_assets');

add_action('wp_ajax_post2ngg_action_process_categories', 'post2ngg_action_process_categories');
add_action('wp_ajax_post2ngg_action_create_post_gallery', 'post2ngg_action_create_post_gallery');
add_action('wp_ajax_post2ngg_action_import_images_to_gallery', 'post2ngg_action_import_images_to_gallery');