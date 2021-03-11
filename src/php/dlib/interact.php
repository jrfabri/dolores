<?php
class DoloresInteract {
  private static $instance;

  public static function get_instance() {
    if (null === static::$instance) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  public function __construct() {
    global $wpdb;
    $this->table_name = $wpdb->prefix . 'dolores_interact';

    if (!$this->table_exists()) {
      $this->create_table();
    }
  }

  private function create_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = <<<SQL
CREATE TABLE {$this->table_name} (
  user_id BIGINT(20) UNSIGNED NOT NULL,
  post_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
  comment_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
  action ENUM('up', 'down') NOT NULL,
  time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, post_id, comment_id)
) {$charset_collate};
SQL;
    $wpdb->query($sql);
  }

  private function table_exists() {
    global $wpdb;
    $sql = "SHOW TABLES LIKE '{$this->table_name}'";
    return $wpdb->get_var($sql) === $this->table_name;
  }

  public function get_post_votes($post_id, $days = 0) {
    global $wpdb;
    $post_id = intval($post_id);

    $voted = "";
    if (is_user_logged_in()) {
      $voted = $this->voted($post_id, 0);
    }

    $and_days = "";
    if ($days != 0) {
      $and_days = "AND time >= NOW() - INTERVAL $days DAY";
    }

    $sql = <<<SQL
SELECT action, user_id FROM {$this->table_name} WHERE
  post_id = '$post_id' $and_days ORDER BY time DESC
SQL;

    $votes = array('up' => array(), 'down' => array());
    $results = $wpdb->get_results($sql);
    foreach ($results as $result) {
      $user = get_user_by('id', $result->user_id);
      $votes[$result->action][] = array(
        'pic' => dolores_get_profile_picture($user),
        'url' => get_author_posts_url($user->ID),
        'name' => $user->display_name
      );
    }

    return array($votes['up'], $votes['down'], $voted);
  }

  public function get_comment_votes($comment_id) {
    global $wpdb;
    $comment_id = intval($comment_id);

    $voted = "";
    if (is_user_logged_in()) {
      $voted = $this->voted(0, $comment_id);
    }

    $sql = <<<SQL
SELECT action, user_id FROM {$this->table_name} WHERE
  comment_id = '$comment_id' ORDER BY time DESC
SQL;

    $votes = array('up' => array(), 'down' => array());
    $results = $wpdb->get_results($sql);
    foreach ($results as $result) {
      $user = get_user_by('id', $result->user_id);
      $votes[$result->action][] = array(
        'pic' => dolores_get_profile_picture($user),
        'url' => get_author_posts_url($user->ID),
        'name' => $user->display_name
      );
    }

    return array($votes['up'], $votes['down'], $voted);
  }

  public function vote($post_id, $comment_id, $action) {
    global $wpdb;

    if (!is_user_logged_in()) {
      return array('error' => 'Você precisa estar loggado para fazer isto.');
    }

    if ($action !== 'up' && $action !== 'down') {
      return array('error' => 'Ação não encontrada.');
    }

    $post_id = intval($post_id);
    $comment_id = intval($comment_id);

    $fields = array(
      'user_id' => wp_get_current_user()->ID,
      'post_id' => $post_id,
      'comment_id' => $comment_id,
      'action' => $action
    );

    $voted = $this->voted($post_id, $comment_id);

    if ($voted) {
      $this->unvote($post_id, $comment_id);
    }

    if ($voted !== $action) {
      $wpdb->insert($this->table_name, $fields);
    }
  }

  private function voted($post_id, $comment_id) {
    global $wpdb;

    if (!is_user_logged_in()) {
      return array('error' => 'Você precisa estar loggado para fazer isto.');
    }

    $user_id = wp_get_current_user()->ID;
    $post_id = intval($post_id);
    $comment_id = intval($comment_id);

    $sql = <<<SQL
SELECT action FROM {$this->table_name} WHERE
  user_id = '$user_id' AND
  post_id = '$post_id' AND
  comment_id = '$comment_id'
SQL;

    return $wpdb->get_var($sql);
  }

  private function unvote($post_id, $comment_id) {
    global $wpdb;

    if (!is_user_logged_in()) {
      return array('error' => 'Você precisa estar loggado para fazer isto.');
    }

    $fields = array(
      'user_id' => wp_get_current_user()->ID,
      'post_id' => $post_id,
      'comment_id' => $comment_id
    );

    $wpdb->delete($this->table_name, $fields);
  }

  public function remove($post_id, $comment_id) {
    if (!is_user_logged_in()) {
      return array('error' => 'Você precisa estar loggado para fazer isto.');
    }

    $cur_user = wp_get_current_user()->ID;

    $post_id = intval($post_id);
    $comment_id = intval($comment_id);

    if ($post_id) {
      $post = get_post($post_id);
      if (!$post) {
        return array('error' => 'Post não encontrado.');
      }
      $author = $post->post_author;
      if ($cur_user != $author) {
        return array('error' => 'Sem permissões suficientes.');
      }

      wp_delete_post($post_id);
    } else if ($comment_id) {
      $comment = get_comment($comment_id);
      if (!$comment) {
        return array('error' => 'Comentário não encontrado.');
      }
      $author = $comment->user_id;
      if ($cur_user != $author) {
        return array('error' => 'Sem permissões suficientes.');
      }

      wp_delete_comment($comment_id);
    }

    return array();
  }

  public function get_recent_interacted_posts($days) {
    global $wpdb;

    // TODO: Move constant elsewhere.
    $seconds_per_day = 86400;

    $time = time() - $days * $seconds_per_day;
    $sql = <<<SQL
SELECT DISTINCT(post_id) FROM {$this->table_name}
  WHERE post_id != 0 AND time >= NOW() - INTERVAL $days DAY
SQL;

    $results = $wpdb->get_results($sql, ARRAY_N);
    $map_function = function ($row) {
      return $row[0];
    };

    return array_map($map_function, $results);
  }
};
