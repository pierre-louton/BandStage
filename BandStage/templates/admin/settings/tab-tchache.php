<?php defined('ABSPATH')||exit; ?>
<table class="form-table">
  <tr><th><?php esc_html_e('Activer la Tchache','bandstage');?></th>
      <td><input type="checkbox" name="bs_tchache_enabled" value="1" <?php checked(get_option('bs_tchache_enabled','1'),'1');?>></td></tr>
  <tr><th><label for="bs_tchache_moderation"><?php esc_html_e('Modération','bandstage');?></label></th>
      <td><select id="bs_tchache_moderation" name="bs_tchache_moderation">
        <option value="manual" <?php selected(get_option('bs_tchache_moderation'),'manual');?>><?php esc_html_e('Manuelle','bandstage');?></option>
        <option value="auto"   <?php selected(get_option('bs_tchache_moderation'),'auto');?>><?php esc_html_e('Automatique','bandstage');?></option>
      </select></td></tr>
  <tr><th><label for="bs_tchache_max_length"><?php esc_html_e('Longueur max (caractères)','bandstage');?></label></th>
      <td><input type="number" id="bs_tchache_max_length" name="bs_tchache_max_length" min="50" max="2000" value="<?php echo esc_attr(get_option('bs_tchache_max_length',500));?>"></td></tr>
</table>
