<?php
/*
 * Modified from Disable Emojis plugin by Ryan Hellyer, released under GPLv2
 * https://geek.hellyer.kiwi/plugins/disable-emojis/
 */

function dolores_tinymce_disable_emojis($plugins) {
  if (is_array($plugins)) {
    return array_diff($plugins, array('wpemoji'));
  } else {
    return array();
  }
}

function dolores_disable_emojis() {
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('admin_print_scripts', 'print_emoji_detection_script');
  remove_action('wp_print_styles', 'print_emoji_styles');
  remove_action('admin_print_styles', 'print_emoji_styles');
  remove_filter('the_content_feed', 'wp_staticize_emoji');
  remove_filter('comment_text_rss', 'wp_staticize_emoji');
  remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
  add_filter('tiny_mce_plugins', 'dolores_tinymce_disable_emojis');
}

add_action('init', 'dolores_disable_emojis');
