<?php
/**
 * Registre de hooks — enregistre actions et filtres WordPress.
 *
 * @package BandStage
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_Loader
 *
 * Maintient et exécute toutes les actions et tous les filtres du plugin.
 * Suit le pattern « WordPress Plugin Boilerplate ».
 */
class BandStage_Loader {

	/** @var array[] Liste des actions enregistrées. */
	private array $actions = array();

	/** @var array[] Liste des filtres enregistrés. */
	private array $filters = array();

	/**
	 * Enregistre une action WordPress.
	 *
	 * @param string   $hook          Nom du hook.
	 * @param object   $component     Objet portant la méthode callback.
	 * @param string   $callback      Nom de la méthode.
	 * @param int      $priority      Priorité (défaut 10).
	 * @param int      $accepted_args Nombre d'arguments (défaut 1).
	 */
	public function add_action(
		string $hook,
		object $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->actions[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	/**
	 * Enregistre un filtre WordPress.
	 *
	 * @param string $hook          Nom du hook.
	 * @param object $component     Objet portant la méthode callback.
	 * @param string $callback      Nom de la méthode.
	 * @param int    $priority      Priorité (défaut 10).
	 * @param int    $accepted_args Nombre d'arguments (défaut 1).
	 */
	public function add_filter(
		string $hook,
		object $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->filters[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	/**
	 * Exécute l'ensemble des hooks enregistrés.
	 */
	public function run(): void {
		foreach ( $this->filters as $f ) {
			add_filter(
				$f['hook'],
				array( $f['component'], $f['callback'] ),
				$f['priority'],
				$f['accepted_args']
			);
		}

		foreach ( $this->actions as $a ) {
			add_action(
				$a['hook'],
				array( $a['component'], $a['callback'] ),
				$a['priority'],
				$a['accepted_args']
			);
		}
	}
}
