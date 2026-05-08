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
	'concerts' => [
		'list' => $tpl . 'concert-list.php',
		'edit' => $tpl . 'concert-edit.php',
	],
	'references' => [
		'list' => $tpl . 'repertoire-list.php',
		'edit' => $tpl . 'repertoire-edit.php',
	],
	'groupe' => [
		'list' => $tpl . 'lineup-list.php',
		'edit' => $tpl . 'lineup-edit.php',
	],
];
