<?php
/**
 * Routing interne BandStage.
 * Mapping bs_view → chemin absolu du template.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$tpl = BANDSTAGE_PLUGIN_DIR . 'templates/public/studio/';

return [
	'studio' => [
		'dashboard' => $tpl . 'dashboard.php',
		'edit'      => $tpl . 'news-edit.php',
	],
	'partenaires' => [
		'list' => $tpl . 'partenaire-list.php',
		'edit' => $tpl . 'partenaire-edit.php',
	],
	'groupe' => [
		'list' => $tpl . 'lineup-list.php',
		'edit' => $tpl . 'lineup-edit.php',
	],
];
