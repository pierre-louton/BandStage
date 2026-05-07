<?php defined('ABSPATH')||exit; ?>
<table class="form-table">
  <tr><th><label for="bs_band_name"><?php esc_html_e('Nom du groupe','bandstage');?></label></th>
      <td><input type="text" id="bs_band_name" name="bs_band_name" class="regular-text" value="<?php echo esc_attr(get_option('bs_band_name',''));?>"></td></tr>
  <tr><th><label for="bs_band_tagline"><?php esc_html_e('Slogan','bandstage');?></label></th>
      <td><input type="text" id="bs_band_tagline" name="bs_band_tagline" class="regular-text" value="<?php echo esc_attr(get_option('bs_band_tagline',''));?>"></td></tr>
  <tr><th><label for="bs_band_city"><?php esc_html_e('Ville','bandstage');?></label></th>
      <td><input type="text" id="bs_band_city" name="bs_band_city" class="regular-text" value="<?php echo esc_attr(get_option('bs_band_city',''));?>"></td></tr>
  <tr><th><label for="bs_band_email"><?php esc_html_e('Email de contact','bandstage');?></label></th>
      <td><input type="email" id="bs_band_email" name="bs_band_email" class="regular-text" value="<?php echo esc_attr(get_option('bs_band_email',''));?>"></td></tr>
</table>
