<?php
// Define DOLORES_TEMPLATE in wp-config.php to override this
if (!defined('DOLORES_TEMPLATE')) {
  define('DOLORES_TEMPLATE', 'scfn');
}

define('DOLORES_PATH', TEMPLATEPATH);
define('DOLORES_TEMPLATE_PATH', DOLORES_PATH . '/tpl/' . DOLORES_TEMPLATE);

if (!defined('DOLORES_ACTIVE_LOCATION_THRESHOLD')) {
  define('DOLORES_ACTIVE_LOCATION_THRESHOLD', 0);
}

require_once(DOLORES_PATH . '/dlib/wp_util/disable_admin_bar.php');
require_once(DOLORES_PATH . '/dlib/wp_util/disable_embed_js.php');
require_once(DOLORES_PATH . '/dlib/wp_util/disable_emojis.php');
require_once(DOLORES_PATH . '/dlib/wp_util/disable_yarpp_css.php');
require_once(DOLORES_PATH . '/dlib/wp_util/modify_queries.php');
require_once(DOLORES_PATH . '/dlib/wp_util/register_menus.php');
require_once(DOLORES_PATH . '/dlib/wp_util/register_post_types.php');
require_once(DOLORES_PATH . '/dlib/wp_util/setup_admin_css.php');
require_once(DOLORES_PATH . '/dlib/wp_util/setup_avatar.php');
require_once(DOLORES_PATH . '/dlib/wp_util/setup_editor.php');
require_once(DOLORES_PATH . '/dlib/wp_util/setup_opengraph.php');
require_once(DOLORES_PATH . '/dlib/wp_util/setup_permalinks.php');
require_once(DOLORES_PATH . '/dlib/wp_util/setup_thumbnails.php');
require_once(DOLORES_PATH . '/dlib/wp_util/share_buttons.php');

require_once(DOLORES_PATH . '/dlib/wp_admin/locais.php');
require_once(DOLORES_PATH . '/dlib/wp_admin/posts_stats.php');
require_once(DOLORES_PATH . '/dlib/wp_admin/settings.php');
require_once(DOLORES_PATH . '/dlib/wp_admin/temas.php');
require_once(DOLORES_PATH . '/dlib/wp_admin/users.php');
require_once(DOLORES_PATH . '/dlib/wp_admin/users_stats.php');

if (file_exists(DOLORES_TEMPLATE_PATH . '/functions.php')) {
  require_once(DOLORES_TEMPLATE_PATH . '/functions.php');
}
