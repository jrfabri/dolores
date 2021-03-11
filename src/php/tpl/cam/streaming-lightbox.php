<?php
$shown = false;
if (!is_page_template('streaming.php') && DoloresStreaming::get_active()) {
  $title = esc_attr(DoloresStreaming::get_title());
  $youtube_id = DoloresStreaming::get_youtube_id();
  $seen = 'seen-' . md5($title . $youtube_id);
  if (is_front_page() || !$_SESSION[$seen]) {
    $_SESSION[$seen] = true;
    $link = '//youtu.be/' . $youtube_id;
    $shown = true;
    ?>
    <div
      id="streaming-lightbox"
      ref="<?php echo $link; ?>"
      title="<?php echo $title; ?>">
    </div>
    <?php
  }
}

if (is_user_logged_in()) {
  $_SESSION['seen-subscribe-lightbox'] = true;
}

if (!$shown && !$_SESSION['seen-subscribe-lightbox']) {
  $_SESSION['seen-subscribe-lightbox'] = true;
  ?>
  <div id="subscribe-lightbox"></div>
  <?php
}
