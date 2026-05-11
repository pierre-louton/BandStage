<?php defined('ABSPATH')||exit;
$pages=[
  'accueil'     =>'BandStage — Accueil',
  'tchache'     =>'BandStage — Tchache',
  'profil'      =>'BandStage — Mon Compte',
  'studio'      =>'BandStage — Studio',
  'partenaires' =>'BandStage — Partenaires',
  'concerts'    =>'BandStage — Concerts',
  'references'  =>'BandStage — Répertoire',
  'groupe'      =>'BandStage — Le groupe',
];
?>
<h3><?php esc_html_e('Pages BandStage','bandstage');?></h3>
<table class="form-table">
<?php foreach($pages as $slug=>$title):
  $id=(int)get_option("bs_page_{$slug}");
  $ok=$id&&get_post($id);?>
  <tr>
    <th><?php echo esc_html($title);?></th>
    <td>
      <?php if($ok): ?>
        ✅ <a href="<?php echo esc_url(get_edit_post_link($id));?>">#<?php echo $id;?></a>
      <?php else:?>
        ❌ <?php esc_html_e('Manquante','bandstage');?>
      <?php endif;?>
    </td>
  </tr>
<?php endforeach;?>
</table>
<p>
  <button type="button" class="button button-secondary js-create-pages"
          data-nonce="<?php echo esc_attr(wp_create_nonce(BANDSTAGE_NONCE));?>">
    <?php esc_html_e('Créer les pages manquantes','bandstage');?>
  </button>
</p>
