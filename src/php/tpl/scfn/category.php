<?php
if ($_GET['ajax']) {
  dolores_grid();
  die();
}

get_header();
?>

<main class="page wrap">
  <h2 class="archive-title"><?php single_cat_title(); ?></h2>
</main>

<?php
dolores_grid();
?>

<?php
get_footer();
?>
