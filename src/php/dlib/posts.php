<?php
require_once(DOLORES_PATH . '/dlib/assets.php');
require_once(DOLORES_PATH . '/dlib/interact.php');
require_once(DOLORES_PATH . '/dlib/mailer.php');
require_once(DOLORES_PATH . '/dlib/wp_util/user_meta.php');

class DoloresPosts {
  const type = 'ideia';

  public static function add_new_post($title, $text, $tema, $local, $tags) {
    if (!is_user_logged_in()) {
      return array('error' => 'Apenas usuários cadastrados podem fazer isto.');
    }

    $user = wp_get_current_user();

    $title = trim($title);
    if (strlen($title) < 10 || strlen($title) > 100) {
      return array('error' => 'O título deve ter entre 10 e 100 caracteres.');
    }
    $title = str_replace('<', '&lt;', $title);
    $title = str_replace('>', '&gt;', $title);

    $text = trim($text);
    if (strlen($text) > 600) {
      return array('error' => 'O texto deve ter no máximo 600 caracteres.');
    }
    $text = str_replace('<', '&lt;', $text);
    $text = str_replace('>', '&gt;', $text);

    $post = array(
      'post_title' => $title,
      'post_content' => $text,
      'post_status' => 'publish',
      'post_type' => DoloresPosts::type,
      'post_author' => $user->ID,
      'ping_status' => 'closed'
    );

    $taxonomy = null;
    if ($tema && !$local) {
      $taxonomy = 'tema';
      $term = $tema;
    } else if (!$tema && $local) {
      $taxonomy = 'local';
      $term = $local;
    } else {
      return array('error' => 'Não foi selecionada uma categoria.');
    }

    if (!is_array($tags)) {
      $tags = array();
    }

    $terms = array_merge(array($term), $tags);

    $term = get_term_by('slug', $term, $taxonomy);
    if ($term === false ||
        $term->parent != 0 ||
        !get_term_meta($term->term_id, 'active', true)) {
      return array('error' => 'Este tema não está aberto no momento.');
    }
    $subterms = get_categories(array(
        'taxonomy' => $taxonomy,
        'child_of' => $term->term_id
    ));
    $valid = array();
    foreach ($subterms as $subterm) {
      $valid[$subterm->slug] = 1;
    }
    foreach ($tags as $tag) {
      if (!array_key_exists($tag, $valid)) {
        return array('error' => 'Foi selecionada uma tag que não existe.');
      }
    }

    $inserted = wp_insert_post($post);
    if (!$inserted) {
      return array('error' => 'Erro ao cadastrar.');
    }

    wp_set_object_terms($inserted, $terms, $taxonomy);
    $permalink = get_permalink($inserted);

    $args = array(
      'NAME' => $user->display_name,
      'LINK' => $permalink
    );
    dolores_mail($user->user_email, 'new_post.html', $args);

    return array('url' => get_permalink($inserted));
  }

  public static function add_new_comment($text, $post_id, $parent, $who) {
    global $_SERVER;

    if (!is_user_logged_in()) {
      return array('error' => 'Apenas usuários cadastrados podem fazer isto.');
    }

    $user = wp_get_current_user();

    if ($who && !current_user_can('edit_posts')) {
      return array('error' => 'Sem permissões suficientes.');
    }

    if (!comments_open($post_id)) {
      return array('error' => 'Os comentários estão fechados.');
    }

    if ($parent) {
      $parent_comment = get_comment($parent);
      if ($parent_comment->comment_parent) {
        return array('error' => 'Suportamos apenas 2 níveis de respostas.');
      }
    }

    $text = trim($text);
    if (strlen($text) > 600) {
      return array('error' => 'O texto deve ter no máximo 600 caracteres.');
    }
    $text = str_replace('<', '&lt;', $text);
    $text = str_replace('>', '&gt;', $text);
    $text = nl2br($text);

    $comment = array(
      'user_id' => $user->ID,
      'comment_author' => $user->display_name,
      'comment_author_email' => $user->user_email,
      'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
      'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
      'comment_post_ID' => $post_id,
      'comment_parent' => $parent,
      'comment_content' => $text
    );

    $inserted = wp_insert_comment($comment);
    if (!$inserted) {
      return array('error' => 'Erro ao cadastrar comentário.');
    }

    if ($who) {
      update_comment_meta($inserted, 'mod', true);
    }

    $post_user = get_user_by('id', get_post_field('post_author', $post_id));
    $args = array(
      'NAME' => $post_user->display_name,
      'COMMENT' => htmlspecialchars($text),
      'POSTER' => $who ? get_bloginfo('name') : $user->display_name,
      'IDEIA' => htmlspecialchars(get_post_field('post_title', $post_id)),
      'LINK' => get_permalink($post_id)
    );
    dolores_mail($post_user->user_email, 'new_comment.html', $args);

    return array("html" => static::get_comment_html(get_comment($inserted)));
  }

  public static function get_comment_html($comment) {
    $interact = DoloresInteract::get_instance();
    list($up, $down, $voted) =
      $interact->get_comment_votes($comment->comment_ID);
    $data = "href=\"#vote\" data-vote=\"comment_id|{$comment->comment_ID}\"";

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

    $user = get_user_by('id', $comment->user_id);
    $format = get_option('date_format') . ' à\s ' . get_option('time_format');
    $datetime = get_comment_date($format, $comment->comment_ID);

    $remove = "";
    if (is_user_logged_in()) {
      $cur_user = wp_get_current_user();
      if ($cur_user->ID == $comment->user_id) {
        $remove = <<<HTML
<a class="ideia-comment-action ideia-comment-remove"
    href="#remove" data-remove="comment_id|{$comment->comment_ID}">
  <i class="fa fa-fw fa-lg fa-remove"></i> Remover
</a>
HTML;
      }
    }

    $mod = get_comment_meta($comment->comment_ID, 'mod', true);

    $url = get_author_posts_url($comment->user_id);
    if ($mod) {
      $url = '/';
    }

    $user_name = $user->display_name;
    if ($mod) {
      $user_name = get_bloginfo('site_name');
    }

    $picture = dolores_get_profile_picture($user);
    if ($mod) {
      $picture = DoloresAssets::get_image_uri('cam/logo-square.png');
    }

    $style = ' style="background-image: url(\'' . $picture. '\');"';

    if (!$comment->comment_parent) {
      $reply = <<<HTML
<a class="ideia-comment-action ideia-comment-reply" href="#reply">
  <i class="fa fa-fw fa-lg fa-comments"></i> Responder
</a>
HTML;
    } else {
      $reply = "";
    }

    $content = <<<HTML
<li class="ideia-comment" id="comment-{$comment->comment_ID}">
  <div class="ideia-comment-table">
    <a href="{$url}" class="ideia-comment-picture">
      <span class="author-picture" {$style}>
      </span>
    </a>
    <div class="ideia-comment-block">
      <div class="ideia-comment-text">
        <span class="ideia-comment-author">
          <a href="{$url}">
            {$user_name}
          </a>
        </span>
        <span class="ideia-comment-content">
          {$comment->comment_content}
        </span>
      </div>
      <div class="ideia-comment-meta">
        <a class="ideia-comment-action ideia-upvote{$upvoted}" {$data}>
          <i class="fa fa-fw fa-lg fa-thumbs-up"></i>
        </a>
        <div class="ideia-votes-count">
          <span>{$up_string}</span>
          <ul class="ideia-votes-list">
HTML;

    foreach ($up as $user) {
      $content.= <<<HTML
        <li>
          <a href="{$user['url']}">
            <div class="ideia-votes-list-pic-container">
              <div class="ideia-votes-list-pic"
                  style="background-image: url('{$user['pic']}');">
              </div>
            </div>
            <div class="ideia-votes-list-name">
              {$user['name']}
            </div>
          </a>
        </li>
HTML;
    }

    $content.= <<<HTML
          </ul>
        </div>

        <a class="ideia-comment-action ideia-downvote{$downvoted}" {$data}>
          <i class="fa fa-fw fa-lg fa-thumbs-down"></i>
        </a>
        <div class="ideia-votes-count">
          <span>{$down_string}</span>
          <ul class="ideia-votes-list">
HTML;

    foreach ($down as $user) {
      $content.= <<<HTML
        <li>
          <a href="{$user['url']}">
            <div class="ideia-votes-list-pic-container">
              <div class="ideia-votes-list-pic"
                  style="background-image: url('{$user['pic']}');">
              </div>
            </div>
            <div class="ideia-votes-list-name">
              {$user['name']}
            </div>
          </a>
        </li>
HTML;
    }

    $content.= <<<HTML
          </ul>
        </div>
        {$remove}
        {$reply}
        <span class="ideia-comment-date">
          {$datetime}
        </span>
      </div>
    </div>
  </div>
HTML;
    return $content;
  }

  public static function get_post_terms($id) {
    $temas = get_the_terms($id, 'tema');
    $locais = get_the_terms($id, 'local');
    if (!is_array($temas)) {
      $temas = array();
    }
    if (!is_array($locais)) {
      $locais = array();
    }
    $terms = array_merge($temas, $locais);

    $cat = null;
    $tags = array();
    foreach ($terms as $term) {
      if ($term->parent == 0) {
        $cat = $term;
      } else {
        $tags[] = $term;
      }
    }

    return array($cat, $tags);
  }
};
