<?php defined('ABSPATH')||exit; ?>
<table class="form-table">
  <tr><th><?php esc_html_e('Dégradé fond — début','bandstage');?></th>
      <td><input type="text" name="bs_bg_color_start" class="bs-color-picker" value="<?php echo esc_attr(get_option('bs_bg_color_start','#1535A8'));?>"></td></tr>
  <tr><th><?php esc_html_e('Dégradé fond — fin','bandstage');?></th>
      <td><input type="text" name="bs_bg_color_end" class="bs-color-picker" value="<?php echo esc_attr(get_option('bs_bg_color_end','#020828'));?>"></td></tr>
  <tr><th><?php esc_html_e('Couleur accent','bandstage');?></th>
      <td><input type="text" name="bs_accent_color" class="bs-color-picker" value="<?php echo esc_attr(get_option('bs_accent_color','#D4A820'));?>"></td></tr>
  <tr><th><?php esc_html_e('Couleur crème','bandstage');?></th>
      <td><input type="text" name="bs_cream_color" class="bs-color-picker" value="<?php echo esc_attr(get_option('bs_cream_color','#FAF6EB'));?>"></td></tr>
</table>
