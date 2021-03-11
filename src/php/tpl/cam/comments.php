<?php
require_once(DOLORES_PATH . '/dlib/interact.php');
require_once(DOLORES_PATH . '/dlib/posts.php');
require_once(DOLORES_PATH . '/dlib/wp_util/user_meta.php');

function dolores_ideia_comment($comment, $args, $depth) {
  echo DoloresPosts::get_comment_html($comment);
}
?>

<div id="comments">
  <div class="ideia-comments-header">
    <?php
    $interact = DoloresInteract::get_instance();
    list($up, $down, $voted) = $interact->get_post_votes($post->ID);
    $data = "href=\"#vote\" data-vote=\"post_id|{$post->ID}\"";

    $upvoted = $downvoted = "";

    $up_string = '0';
    if (count($up) > 0) {
      $up_string = preg_replace('/ .*/', '', $up[0]['name']);
      if ($voted === "up") {
        $up_string = "Você";
        $upvoted = " voted";
      }
      if (count($up) > 1) {
        $up_string.= ' + ' . (count($up) - 1);
      }
    }

    $down_string = '0';
    if (count($down) > 0) {
      $down_string = preg_replace('/ .*/', '', $down[0]['name']);
      if ($voted === "down") {
        $down_string = "Você";
        $downvoted = " voted";
      }
      if (count($down) > 1) {
        $down_string.= ' + ' . (count($down) - 1);
      }
    }
    ?>
    <a
        class="ideia-action ideia-upvote<?php echo $upvoted; ?>"
        <?php echo $data; ?>
        >
      <i class="fa fa-fw fa-lg fa-thumbs-up"></i>
    </a>
    <div class="ideia-votes-count">
      <span><?php echo $up_string; ?></span>
      <ul class="ideia-votes-list">
        <?php
        foreach ($up as $user) {
          ?>
          <li>
            <a href="<?php echo $user['url']; ?>">
              <div class="ideia-votes-list-pic-container">
                <div class="ideia-votes-list-pic"
                    style="background-image: url('<?php
                        echo $user['pic']; ?>');">
                </div>
              </div>
              <div class="ideia-votes-list-name">
                <?php echo $user['name']; ?>
              </div>
            </a>
          </li>
          <?php
        }
        ?>
      </ul>
    </div>
    <a
        class="ideia-action ideia-downvote<?php echo $downvoted; ?>"
        <?php echo $data; ?>
        >
      <i class="fa fa-fw fa-lg fa-thumbs-down"></i>
    </a>
    <div class="ideia-votes-count">
      <span><?php echo $down_string; ?></span>
      <ul class="ideia-votes-list">
        <?php
        foreach ($down as $user) {
          ?>
          <li>
            <a href="<?php echo $user['url']; ?>">
              <div class="ideia-votes-list-pic-container">
                <div class="ideia-votes-list-pic"
                    style="background-image: url('<?php
                        echo $user['pic']; ?>');">
                </div>
              </div>
              <div class="ideia-votes-list-name">
                <?php echo $user['name']; ?>
              </div>
            </a>
          </li>
          <?php
        }
        ?>
      </ul>
    </div>

    <?php dolores_print_share_buttons(); ?>
  </div>

  <ul class="ideia-comments-list">
    <li class="ideia-comment ideia-comment-form-container" id="respond">
      <?php
      if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $picture = dolores_get_profile_picture($user);
      } else {
        $hash = md5("nobody");
        $picture = "//gravatar.com/avatar/$hash?d=mm&s=300";
      }
      $style = ' style="background-image: url(\'' . $picture. '\');"';
      ?>
      <form class="ideia-comment-form">
        <div class="ideia-comment-picture-container">
          <span class="user-logged-picture"<?php echo $style; ?>></span>
        </div>
        <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
        <input type="hidden" name="parent" value="0" />
        <i class="post-comment-spinner fa fa-fw fa-lg fa-refresh fa-spin"></i>
        <textarea
          aria-required="true"
          class="comment-textarea"
          maxlength="600"
          name="text"
          placeholder="Escreva uma resposta"
          rows="1"
          ></textarea>
        <?php
        if (current_user_can('edit_posts')) {
          ?>
          <div class="switch-user">
            <label>
              <input type="radio" name="user" value="" checked="checked" />
              Postar como <strong><?php echo $user->display_name; ?></strong>
            </label>
            <label>
              <input type="radio" name="user" value="mod" />
              Postar como <strong><?php echo get_bloginfo('name'); ?></strong>
            </label>
          </div>
          <?php
        }
        ?>
      </form>
    </li>
    <?php
    wp_list_comments(array(
      'callback' => 'dolores_ideia_comment',
      'reverse_children' => true,
      'reverse_top_level' => true
    ));
    ?>
  </ul>
</div>
