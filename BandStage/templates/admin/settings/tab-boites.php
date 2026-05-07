<?php defined('ABSPATH')||exit;?>
<table class="form-table">
<?php for($i=1;$i<=6;$i++):?>
  <tr>
    <th><?php printf(esc_html__('Boîte %d','bandstage'),$i);?></th>
    <td>
      <input type="text" name="bs_box_<?php echo $i;?>_title" class="regular-text"
             placeholder="<?php esc_attr_e('Titre','bandstage');?>"
             value="<?php echo esc_attr(get_option("bs_box_{$i}_title",''));?>">
      <input type="url"  name="bs_box_<?php echo $i;?>_link" class="regular-text"
             placeholder="<?php esc_attr_e('https://…','bandstage');?>"
             value="<?php echo esc_attr(get_option("bs_box_{$i}_link",''));?>">
      <input type="text" name="bs_box_<?php echo $i;?>_icon" class="small-text bs-icon-picker"
             placeholder="<?php esc_attr_e('slug icône','bandstage');?>"
             value="<?php echo esc_attr(get_option("bs_box_{$i}_icon",''));?>">
    </td>
  </tr>
<?php endfor;?>
</table>
