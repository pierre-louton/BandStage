<?php defined('ABSPATH')||exit; ?>
<table class="form-table">
  <tr><th><?php esc_html_e('Activer le ticker','bandstage');?></th>
      <td><input type="checkbox" name="bs_ticker_enabled" value="1" <?php checked(get_option('bs_ticker_enabled','1'),'1');?>></td></tr>
  <tr><th><label for="bs_ticker_source"><?php esc_html_e('Source','bandstage');?></label></th>
      <td><select id="bs_ticker_source" name="bs_ticker_source">
        <option value="bs_news" <?php selected(get_option('bs_ticker_source'),'bs_news');?>><?php esc_html_e('Actualités BandStage','bandstage');?></option>
        <option value="posts"   <?php selected(get_option('bs_ticker_source'),'posts');?>><?php esc_html_e('Articles WordPress','bandstage');?></option>
        <option value="manual"  <?php selected(get_option('bs_ticker_source'),'manual');?>><?php esc_html_e('Saisie manuelle','bandstage');?></option>
      </select></td></tr>
  <tr><th><label for="bs_ticker_items"><?php esc_html_e('Items manuels (un par ligne)','bandstage');?></label></th>
      <td><textarea id="bs_ticker_items" name="bs_ticker_items" class="large-text" rows="5"><?php echo esc_textarea(get_option('bs_ticker_items',''));?></textarea></td></tr>
  <tr><th><label for="bs_ticker_speed"><?php esc_html_e('Vitesse (secondes/cycle)','bandstage');?></label></th>
      <td><input type="number" id="bs_ticker_speed" name="bs_ticker_speed" min="5" max="120" value="<?php echo esc_attr(get_option('bs_ticker_speed',24));?>"></td></tr>
</table>
