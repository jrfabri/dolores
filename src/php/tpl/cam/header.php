<?php
require_once(DOLORES_PATH . '/dlib/assets.php');
require_once(DOLORES_PATH . '/dlib/users.php');

$logo_img = DoloresAssets::get_image_uri('cam/logo-header.png');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" <?php
  language_attributes();
?>>

<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="theme-color" content="#000000" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<?php
if (defined('GOOGLE_CLIENT_ID')) {
  echo '<meta name="google-signin-client_id"' .
       ' content="' . GOOGLE_CLIENT_ID . '" />';
}
?>
<title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php
$favicon = DoloresAssets::get_image_uri('cam/favicon-192.png');
?>
<link rel="icon" sizes="192x192" href="<?php echo $favicon; ?>" />
<?php
DoloresAssets::print_style();
DoloresAssets::print_script();
wp_head();
?>
</head>

<body <?php body_class(); ?>>
<header class="site-header">
  <div class="wrap">
    <div class="header-toggle-menu"></div>

    <h1 class="header-logo">
      <a href="<?php echo site_url(); ?>" title="Página inicial">
        <img src="<?php echo $logo_img; ?>" alt="<?php bloginfo('name'); ?>" />
      </a>
    </h1>

    <nav class="header-nav">
      <?php
      if (DoloresStreaming::get_active()) {
        function dolores_show_streaming_link($items, $args) {
          if ($args->theme_location == 'header-menu') {
            $youtube_id = DoloresStreaming::get_youtube_id();
            $link = '//youtu.be/' . $youtube_id;
            $items .= <<<HTML
<li class="menu-item">
  <a href="$link" class="streaming" target="_blank">
    <i class="fa fa-play-circle"></i>Ao vivo
  </a>
</li>
HTML;
          }
          return $items;
        }
        add_filter('wp_nav_menu_items', 'dolores_show_streaming_link', 10, 2);
      }

      if (is_front_page()) {
        function dolores_show_explanation_link($items, $args) {
          if ($args->theme_location == 'header-menu') {
            $new_item = <<<HTML
<li class="menu-item">
  <a class="toggle-explanation" href="#">
    Apresentação
  </a>
</li>
HTML;
            $items = preg_replace('/<\/li>/', '</li>' . $new_item, $items, 1);
          }
          return $items;
        }
        add_filter('wp_nav_menu_items', 'dolores_show_explanation_link', 10, 2);
      }

      wp_nav_menu(Array(
        'theme_location' => 'header-menu',
        'container' => 'div',
        'container_class' => 'header-menu'
      ));
      ?>
    </nav>

    <ul class="header-search-user">
      <li class="header-search">
        <form
						class="header-search-form"
						method="get"
						action="//cse.google.com/cse"
						target="_blank">
					<input
						type="hidden"
						name="cx"
						value="015740526016573902934:1jok_zgstqg" />
          <i class="fa fa-lg fa-search"></i>
          <input
            class="header-search-input"
            type="text"
            name="q"
            placeholder="Buscar"
            value="<?php if (array_key_exists('s', $_GET)) echo $_GET['s']; ?>"
            />
        </form>
      </li>
      <?php
      echo DoloresUsers::getUserHeaderLi();
      ?>
    </ul>
  </div>
  <div class="header-overlay"></div>
</header>
