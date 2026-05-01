<?php
/**
 * Icônes SVG des boîtes — fonction globale accessible depuis les templates.
 *
 * @package BandStage
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retourne le markup SVG d'une icône de boîte.
 *
 * @param string $icon Identifiant de l'icône.
 * @return string SVG HTML.
 */
function bandstage_get_icon( string $icon ): string {
	$icons = array(
		'groupe' => '<svg class="bs-icon" viewBox="0 0 62 62" fill="none" aria-hidden="true">
			<circle cx="31" cy="14" r="7" fill="currentColor" opacity=".9"/>
			<path d="M18 50Q18 35 31 35Q44 35 44 50" fill="currentColor" opacity=".85"/>
			<circle cx="13" cy="18" r="5.5" fill="currentColor" opacity=".6"/>
			<path d="M3 50Q3 38 13 38Q19 38 22 43" fill="currentColor" opacity=".5"/>
			<circle cx="49" cy="18" r="5.5" fill="currentColor" opacity=".6"/>
			<path d="M40 43Q43 38 49 38Q59 38 59 50" fill="currentColor" opacity=".5"/>
		</svg>',

		'concerts' => '<svg class="bs-icon" viewBox="0 0 56 56" fill="none" aria-hidden="true">
			<ellipse cx="28" cy="14" rx="7" ry="9" stroke="currentColor" stroke-width="2" fill="none" opacity=".85"/>
			<path d="M20 18Q14 20 14 28Q14 30 28 30Q42 30 42 28Q42 20 36 18"
			      stroke="currentColor" stroke-width="1.5" fill="none"/>
			<line x1="28" y1="30" x2="28" y2="46" stroke="currentColor" stroke-width="2"/>
			<path d="M20 46L36 46" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
			<circle cx="8" cy="5" r="2.5" fill="currentColor" opacity=".5"/>
			<line x1="8" y1="7" x2="18" y2="22" stroke="currentColor" stroke-width="1" opacity=".4"/>
			<circle cx="48" cy="5" r="2.5" fill="currentColor" opacity=".5"/>
			<line x1="48" y1="7" x2="38" y2="22" stroke="currentColor" stroke-width="1" opacity=".4"/>
		</svg>',

		'references' => '<svg class="bs-icon" viewBox="0 0 58 58" fill="none" aria-hidden="true">
			<path d="M12 30Q12 14 29 14Q46 14 46 30" stroke="currentColor" stroke-width="2.5" fill="none"/>
			<rect x="6"  y="27" width="8" height="14" rx="4" fill="currentColor" opacity=".9"/>
			<rect x="44" y="27" width="8" height="14" rx="4" fill="currentColor" opacity=".9"/>
		</svg>',

		'partenaires' => '<svg class="bs-icon" viewBox="0 0 58 58" fill="none" aria-hidden="true">
			<rect x="10" y="26" width="38" height="24" rx="2" stroke="currentColor" stroke-width="1.8" fill="none" opacity=".85"/>
			<path d="M10 26L14 12L44 12L48 26" stroke="currentColor" stroke-width="1.8" fill="none"/>
			<rect x="24" y="36" width="10" height="14" rx="1" stroke="currentColor" stroke-width="1.2" fill="none"/>
			<rect x="13" y="30" width="8"  height="8" rx="1" stroke="currentColor" stroke-width="1.2" fill="none" opacity=".7"/>
			<rect x="37" y="30" width="8"  height="8" rx="1" stroke="currentColor" stroke-width="1.2" fill="none" opacity=".7"/>
		</svg>',

		'humeurs' => '<svg class="bs-icon" viewBox="0 0 56 56" fill="none" aria-hidden="true">
			<rect x="10" y="12" width="36" height="30" rx="2" stroke="currentColor" stroke-width="1.5" fill="none" opacity=".85"/>
			<line x1="28" y1="12" x2="28" y2="42" stroke="currentColor" stroke-width="1" opacity=".35"/>
			<line x1="14" y1="19" x2="24" y2="19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".7"/>
			<line x1="14" y1="24" x2="25" y2="24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".5"/>
			<line x1="14" y1="29" x2="22" y2="29" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".4"/>
			<path d="M38 16L44 10L48 14L42 20Z" fill="currentColor" opacity=".85"/>
			<path d="M38 16L34 36L42 20" fill="currentColor" opacity=".7"/>
		</svg>',

		'tchache' => '<svg class="bs-icon" viewBox="0 0 58 58" fill="none" aria-hidden="true">
			<path d="M8 14C8 11 10 9 13 9L45 9C48 9 50 11 50 14L50 30C50 33 48 35 45 35L34 35L26 45L26 35L13 35C10 35 8 33 8 30Z"
			      stroke="currentColor" stroke-width="1.8" fill="none" opacity=".85"/>
			<line x1="16" y1="18" x2="42" y2="18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".7"/>
			<line x1="16" y1="24" x2="36" y2="24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".5"/>
			<line x1="16" y1="29" x2="28" y2="29" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".35"/>
		</svg>',
	);

	return $icons[ $icon ] ?? '<svg class="bs-icon" viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="12" stroke="currentColor" stroke-width="1.5" fill="none" opacity=".6"/></svg>';
}
