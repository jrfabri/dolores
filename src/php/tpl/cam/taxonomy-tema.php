<?php
$term = get_queried_object();
$active = get_term_meta($term->term_id, 'active', true);
$headline = get_term_meta($term->term_id, 'headline', true);
$credit = get_term_meta($term->term_id, 'credit', true);
$image = get_term_meta($term->term_id, 'image', true);
$more = get_term_meta($term->term_id, 'more', true);
$outline = get_term_meta($term->term_id, 'outline', true);
$video = get_term_meta($term->term_id, 'video', true);

get_header();
?>

<main class="tema-header">
  <div class="wrap default-wrap">
    <?php
    if ($video) {
      $vparams = "rel=0&amp;controls=0&amp;showinfo=0";
      ?>
      <div class="tema-video">
        <iframe
          allowfullscreen
          frameborder="0"
          height="480"
          src="https://youtube.com/embed/<?php echo $video . "?" . $vparams; ?>"
          width="640"
          >
        </iframe>
      </div>
      <?php
    } else if ($image) {
      $style = ' style="background-image: url(\'' . $image . '\');"';
      ?>
      <div class="tema-video">
        <div class="tema-video-image"<?php echo $style;?>>
          <?php
          if ($credit) {
            ?>
            <div class="tema-credit">Foto: <?php echo $credit; ?></div>
            <?php
          }
          ?>
        </div>
      </div>
      <?php
    }
    ?>

    <div class="tema-info">
      <h2 class="tema-name">
        <span>
          <?php
          if ($headline) {
            echo $headline;
          } else {
            if ($term->parent != 0) {
                      echo "#";
            }
            single_cat_title();
          }
          ?>
        </span>
      </h2>
      <?php
      $description = category_description();
      $parts = explode("\n", $description);
      $first = array_shift($parts) . "\n" . array_shift($parts);
      $rest = implode("\n", $parts);

      echo $first;
      if ($rest) {
        ?>
        <p><a class="load-more tema-link-more" href="#tema-description-more">
          <span>Leia mais</span>
        </a></p>

        <div class="hidden" id="tema-description-more">
          <?php echo $rest; ?>
        </div>
        <?php
      }
      ?>
    </div>
  </div>
</main>

<?php
if (is_array($outline) && count($outline) > 0) {
  ?>
  <section class="tema-outline">
    <div class="wrap default-wrap">
      <h3 class="tema-outline-title">
        Ideias para iniciar a discussão
      </h3>
      <ul class="outline-list">
      <?php
      for ($i = 0; $i < count($outline); $i++) {
        $text = $outline[$i];
        if (!$text) {
          continue;
        }
        $parts = explode("\n", $text);
        $title = array_shift($parts);
        $text = nl2br(trim(implode("\n", $parts)));
        ?>
        <li>
          <i class="fa fa-2x fa-lightbulb-o"></i>
          <div class="info">
            <h3 class="info-title"><?php echo $title; ?></h3>
            <?php
            if ($text) {
              ?>
              <p><a class="load-more" href="#outline-<?php echo $i; ?>">
                Leia mais &raquo;
              </a></p>
              <p class="hidden" id="outline-<?php echo $i; ?>">
                <?php echo $text; ?>
              </p>
              <?php
            }
            ?>
          </div>
        </li>
        <?php
      }
      ?>
      </ul>
    </div>
  </section>
  <?php
}
?>

<?php
if ($term->parent == 0) {
  ?>
  <section class="tema-form">
    <div class="wrap default-wrap">
      <?php
      if (!$active) {
        ?>
        <h2 class="tema-form-closed-title">
          Ops...
        </h2>
        <p class="tema-form-closed-description">
          Este tema não está aberto para debate no momento.
          Para mais informações, <a href="/contato">entre em contato</a>.
        </p>
        <?php
      } else {
        ?>
        <div class="tema-form-explanation">
          <div class="tema-form-ball">
            <div class="tema-form-ball-wrap">
              <h2 class="tema-form-ball-title">
                Que mudança você quer para
                <?php single_cat_title(); ?>?
              </h2>
              <p class="tema-form-ball-description">
                Compartilhe sua proposta usando o formulário.
              </p>
            </div>
          </div>
        </div>

        <form class="tema-form-form" id="form-tema">
          <p class="tema-form-item">
            <label class="tema-form-label" for="tema-form-title">
              Título
            </label>
            <input
                type="text"
                name="title"
                class="tema-form-input"
                id="tema-form-title"
                maxlength="100"
                placeholder="Título (max. 100 caracteres)"
                />
          </p>
          <p class="tema-form-item">
            <label class="tema-form-label" for="tema-form-content">
              Escreva sua proposta
            </label>
            <textarea
                class="tema-form-textarea"
                name="text"
                id="tema-form-content"
                maxlength="600"
                placeholder="Explique com mais detalhes a sua proposta (max. 600 caracteres)"
                ></textarea>
          </p>
          <?php
          $subterms = get_categories(array(
            'taxonomy' => 'tema',
            'child_of' => $term->term_id,
            'hide_empty' => false
          ));

          $tags = array();
          foreach ($subterms as $subterm) {
            $tags[] = $subterm->slug . "::" . $subterm->name;
          }
          $available_tags = implode("|", $tags);

          if ($available_tags) {
            ?>
            <p class="tema-form-item">
              <label class="tema-form-label" for="tema-form-tags">
                Tags
              </label>
              <input
                  type="hidden"
                  class="available-tags"
                  value="<?php echo $available_tags; ?>"
                  />
              <span class="tema-tags-container">
                <input
                    type="text"
                    class="tema-form-input"
                    name="dummy-tags"
                    id="tema-form-tags"
                    placeholder="Escolha algumas palavras-chave"
                    />
              </span>
            </p>
            <?php
          }
          ?>
          <p class="tema-form-item" style="margin-top: 5px; text-align: right;">
            <input type="hidden" name="tema" value="<?php echo $term->slug; ?>" />
            <button class="tema-form-button" type="submit">
              <span class="if-not-sent">Enviar</span>
              <i class="if-sending fa fa-fw fa-refresh fa-spin"></i>
            </button>
          </p>
        </form>
      </div>
      <?php
    }
    ?>
  </section>
  <?php
}

dolores_grid_ideias();
?>

<?php
get_footer();
?>
