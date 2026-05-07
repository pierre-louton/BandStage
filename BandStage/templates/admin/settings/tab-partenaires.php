<?php defined('ABSPATH')||exit;
$types = get_terms(['taxonomy'=>'bs_type_partenaire','hide_empty'=>false]);
?>
<h3><?php esc_html_e('Types de partenaires','bandstage');?></h3>
<ul class="bs-admin-type-list" id="bs-type-list">
<?php foreach($types as $term):
  $icon=get_term_meta($term->term_id,'bs_term_icon',true);?>
  <li><?php echo esc_html($icon.' '.$term->name);?> <code><?php echo esc_html($term->slug);?></code></li>
<?php endforeach;?>
</ul>
<h4><?php esc_html_e('Ajouter un type','bandstage');?></h4>
<div class="bs-admin-add-type">
  <input type="text" id="bs-new-type-name" placeholder="<?php esc_attr_e('Nom du type','bandstage');?>" class="regular-text">
  <input type="text" id="bs-new-type-icon" placeholder="<?php esc_attr_e('Emoji 🎸','bandstage');?>" style="width:60px">
  <button type="button" class="button button-primary js-add-type"
          data-nonce="<?php echo esc_attr(wp_create_nonce(BANDSTAGE_NONCE));?>">
    <?php esc_html_e('Ajouter','bandstage');?>
  </button>
</div>
