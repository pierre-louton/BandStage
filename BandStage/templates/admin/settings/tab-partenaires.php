<?php
/**
 * Onglet Partenaires — gestion des types (table bandstage_partenaire_types).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Domain\Partenaires\PartenaireService;

$service = new PartenaireService();
$types   = $service->get_types();
?>
<h2><?php esc_html_e( 'Types de partenaires', 'bandstage' ); ?></h2>

<table class="bs-admin-types-table widefat striped">
  <thead>
    <tr>
      <th><?php esc_html_e( 'Icône', 'bandstage' ); ?></th>
      <th><?php esc_html_e( 'Nom', 'bandstage' ); ?></th>
      <th><?php esc_html_e( 'Slug', 'bandstage' ); ?></th>
      <th></th>
    </tr>
  </thead>
  <tbody id="bs-types-list">
    <?php foreach ( $types as $t ) : ?>
      <tr data-id="<?php echo esc_attr( $t->id ); ?>">
        <td><?php echo esc_html( $t->icon ); ?></td>
        <td><?php echo esc_html( $t->name ); ?></td>
        <td><code><?php echo esc_html( $t->slug ); ?></code></td>
        <td>
          <button type="button" class="button js-type-delete"
                  data-id="<?php echo esc_attr( $t->id ); ?>"
                  data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
            <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
          </button>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3><?php esc_html_e( 'Ajouter un type', 'bandstage' ); ?></h3>
<form id="bs-type-form">
  <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
  <input type="hidden" name="type_id" value="0">
  <table class="form-table">
    <tr>
      <th><label for="bs-type-name"><?php esc_html_e( 'Nom', 'bandstage' ); ?></label></th>
      <td><input type="text" id="bs-type-name" name="type_name" class="regular-text" required></td>
    </tr>
    <tr>
      <th><label for="bs-type-icon"><?php esc_html_e( 'Icône (emoji)', 'bandstage' ); ?></label></th>
      <td><input type="text" id="bs-type-icon" name="type_icon" class="small-text" placeholder="🎸"></td>
    </tr>
  </table>
  <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Ajouter', 'bandstage' ); ?></button></p>
</form>

<script>
(function($){
  const ajaxUrl = '<?php echo esc_js( admin_url( "admin-ajax.php" ) ); ?>';
  const nonce   = '<?php echo esc_js( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>';

  // Ajouter un type
  $('#bs-type-form').on('submit', async function(e){
    e.preventDefault();
    const data = new FormData(this);
    data.set('action', 'bs_partenaire_type_save');
    data.set('nonce', nonce);
    try {
      const res  = await fetch(ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' });
      const json = await res.json();
      if (json.success) {
        const d = json.data;
        $('#bs-types-list').append(
          `<tr data-id="${d.type_id}">
             <td>${$('<span>').text(d.icon).html()}</td>
             <td>${$('<span>').text(d.name).html()}</td>
             <td><code>${$('<span>').text(d.slug).html()}</code></td>
             <td><button type="button" class="button js-type-delete" data-id="${d.type_id}"
                 data-nonce="${nonce}"><?php echo esc_js( __('Supprimer','bandstage') ); ?></button></td>
           </tr>`
        );
        this.reset();
      } else {
        alert(json.data?.message || '<?php echo esc_js( __( "Erreur.", "bandstage" ) ); ?>');
      }
    } catch {
      alert('<?php echo esc_js( __( "Erreur réseau.", "bandstage" ) ); ?>');
    }
  });

  // Supprimer un type
  $(document).on('click', '.js-type-delete', async function(){
    if (!confirm('<?php echo esc_js( __( "Supprimer ce type ?", "bandstage" ) ); ?>')) return;
    const btn = $(this);
    const form = new FormData();
    form.append('action', 'bs_partenaire_type_delete');
    form.append('type_id', btn.data('id'));
    form.append('nonce', btn.data('nonce'));
    try {
      const res  = await fetch(ajaxUrl, { method: 'POST', body: form, credentials: 'same-origin' });
      const json = await res.json();
      if (json.success) {
        btn.closest('tr').remove();
      } else {
        alert(json.data?.message || '<?php echo esc_js( __( "Erreur.", "bandstage" ) ); ?>');
      }
    } catch {
      alert('<?php echo esc_js( __( "Erreur réseau.", "bandstage" ) ); ?>');
    }
  });
})(jQuery);
</script>
