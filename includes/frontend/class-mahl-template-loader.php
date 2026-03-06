<?php
/**
 * Frontend template loader.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin templates with theme override support.
 */
class MAHL_Template_Loader {

	/**
	 * Supported archive post types.
	 *
	 * @var array
	 */
	protected $archive_post_types = array(
		'mahl_season',
		'mahl_team',
		'mahl_player',
		'mahl_game',
	);

	/**
	 * Supported single post types.
	 *
	 * @var array
	 */
	protected $single_post_types = array(
		'mahl_season',
		'mahl_team',
		'mahl_player',
		'mahl_game',
	);

	/**
	 * Register frontend hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'template_include', array( $this, 'load_template' ) );
	}

	/**
	 * Load a theme override or plugin template for supported content.
	 *
	 * @param string $template Resolved template path.
	 * @return string
	 */
	public function load_template( $template ) {
		$template_name = $this->get_template_name();

		if ( empty( $template_name ) ) {
			return $template;
		}

		$located_template = $this->locate_template( $template_name );

		if ( ! empty( $located_template ) ) {
			return $located_template;
		}

		return $template;
	}

	/**
	 * Return the template name for the current query.
	 *
	 * @return string
	 */
	protected function get_template_name() {
		if ( is_post_type_archive( $this->archive_post_types ) ) {
			$post_type = get_query_var( 'post_type' );

			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}

			return 'archive-' . sanitize_key( $post_type ) . '.php';
		}

		if ( is_singular( $this->single_post_types ) ) {
			return 'single-' . sanitize_key( get_post_type() ) . '.php';
		}

		return '';
	}

	/**
	 * Locate a template from the theme or plugin defaults.
	 *
	 * @param string $template_name Template filename.
	 * @return string
	 */
	protected function locate_template( $template_name ) {
		$template_path = trailingslashit( $this->get_theme_template_path() );
		$theme_paths   = array(
			$template_path . $template_name,
			$template_name,
		);
		$located       = locate_template( $theme_paths, false, false );

		if ( ! empty( $located ) ) {
			return apply_filters( 'mahl_located_template', $located, $template_name, 'theme' );
		}

		$plugin_template = MAHL_MANAGER_PATH . 'templates/' . $template_name;

		if ( file_exists( $plugin_template ) ) {
			return apply_filters( 'mahl_located_template', $plugin_template, $template_name, 'plugin' );
		}

		return '';
	}

	/**
	 * Return the theme override template path.
	 *
	 * @return string
	 */
	protected function get_theme_template_path() {
		return apply_filters( 'mahl_template_path', 'mahl-manager/' );
	}
}
