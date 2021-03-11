<?php
the_post();
get_header();
?>

<main class="wrap default-wrap">
  <article class="single-content">
    <h2 class="single-title">
      <a href="<?php the_permalink(); ?>">
        <?php the_title(); ?>
      </a>
    </h2>

    <div class="single-meta social-media">
      <?php
      $autor = get_post_meta(get_the_ID(), 'autor', true);
      if ($autor) {
        ?>
        <span class="single-author">
          <?php echo $autor; ?>
        </span>
        <?php
      }
      ?>

      <span class="single-date">
        <?php the_time('d \d\e F \d\e Y'); ?>
      </span>

      <span class="social-sep">
        <hr />
      </span>

      <?php dolores_print_share_buttons(); ?>
    </div>

    <div class="entry">
      <?php the_content(); ?>
    </div>

    <?php
    comments_template('/comments.php');
    ?>
  </article>

  <?php
  get_sidebar();
  ?>
</main>

<?php
get_footer();
?>
