<?php
/**
 * Settings admin module.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage the plugin settings page and option storage.
 */
class MAHL_Settings_Admin {

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'mahl-manager';

	/**
	 * Settings menu slug.
	 *
	 * @var string
	 */
	const SETTINGS_MENU_SLUG = 'mahl-manager-settings';

	/**
	 * Settings group slug.
	 *
	 * @var string
	 */
	const SETTINGS_GROUP = 'mahl_league_settings_group';

	/**
	 * Settings section page slug.
	 *
	 * @var string
	 */
	const SETTINGS_PAGE = 'mahl-manager-settings';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the plugin settings page.
	 *
	 * @return void
	 */
	public function register_menu_page() {
		add_menu_page(
			__( 'MAHL Manager', 'mahl-manager' ),
			__( 'MAHL Manager', 'mahl-manager' ),
			'edit_posts',
			self::PAGE_SLUG,
			array( $this, 'render_overview_page' ),
			'dashicons-shield',
			25
		);

		add_submenu_page(
			self::PAGE_SLUG,
			__( 'League Settings', 'mahl-manager' ),
			__( 'Settings', 'mahl-manager' ),
			'manage_options',
			self::SETTINGS_MENU_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings, sections, and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			MAHL_Settings_Service::get_option_name(),
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => MAHL_Settings_Service::get_defaults(),
				'type'              => 'array',
			)
		);

		foreach ( $this->get_sections() as $section ) {
			add_settings_section(
				$section['id'],
				$section['title'],
				array( $this, 'render_section_description' ),
				self::SETTINGS_PAGE,
				array(
					'description' => $section['description'],
				)
			);
		}

		foreach ( $this->get_fields() as $field ) {
			add_settings_field(
				$field['id'],
				$field['label'],
				array( $this, 'render_field' ),
				self::SETTINGS_PAGE,
				$field['section'],
				array(
					'field' => $field,
				)
			);
		}
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'MAHL Manager Settings', 'mahl-manager' ) . '</h1>';
		echo '<p>' . esc_html__( 'Configure league format, standings rules, and future-ready game rules.', 'mahl-manager' ) . '</p>';

		echo '<form action="options.php" method="post">';
		settings_fields( self::SETTINGS_GROUP );
		do_settings_sections( self::SETTINGS_PAGE );
		submit_button( __( 'Save Settings', 'mahl-manager' ) );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Render the plugin overview page.
	 *
	 * @return void
	 */
	public function render_overview_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'MAHL Manager', 'mahl-manager' ) . '</h1>';
		echo '<p>' . esc_html__( 'Manage league content through the post type screens in this menu. League rules and standings configuration are available on the Settings screen.', 'mahl-manager' ) . '</p>';

		if ( current_user_can( 'manage_options' ) ) {
			printf(
				'<p><a class="button button-primary" href="%s">%s</a></p>',
				esc_url( admin_url( 'admin.php?page=' . self::SETTINGS_MENU_SLUG ) ),
				esc_html__( 'Open Settings', 'mahl-manager' )
			);
		}

		echo '</div>';
	}

	/**
	 * Render a section description.
	 *
	 * @param array $section Section arguments.
	 * @return void
	 */
	public function render_section_description( $section ) {
		if ( empty( $section['description'] ) ) {
			return;
		}

		printf(
			'<p>%s</p>',
			esc_html( $section['description'] )
		);
	}

	/**
	 * Render a settings field.
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_field( $args ) {
		$field    = $args['field'];
		$settings = MAHL_Settings_Service::get_settings();
		$value    = $this->get_field_value( $settings, $field['group'], $field['key'] );
		$name     = sprintf( '%1$s[%2$s][%3$s]', MAHL_Settings_Service::get_option_name(), $field['group'], $field['key'] );
		$id       = sprintf( '%1$s-%2$s', $field['group'], $field['key'] );

		switch ( $field['type'] ) {
			case 'checkbox':
				printf(
					'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( $value, 1, false ),
					esc_html( $field['checkbox_label'] )
				);
				break;

			default:
				$attributes = array(
					'type'  => $field['type'],
					'class' => $field['class'],
					'id'    => $id,
					'name'  => $name,
					'value' => $value,
				);

				if ( isset( $field['min'] ) ) {
					$attributes['min'] = $field['min'];
				}

				if ( isset( $field['step'] ) ) {
					$attributes['step'] = $field['step'];
				}

				$this->render_input_tag( $attributes );
				break;
		}

		if ( ! empty( $field['description'] ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html( $field['description'] )
			);
		}
	}

	/**
	 * Sanitize the settings payload.
	 *
	 * @param array $settings Submitted settings.
	 * @return array
	 */
	public function sanitize_settings( $settings ) {
		$defaults  = MAHL_Settings_Service::get_defaults();
		$sanitized = $defaults;

		if ( ! is_array( $settings ) ) {
			return $sanitized;
		}

		$sanitized['game_format']['period_count']          = $this->sanitize_number_setting( $settings, 'game_format', 'period_count', 1, 10, $defaults['game_format']['period_count'] );
		$sanitized['game_format']['period_length_minutes'] = $this->sanitize_number_setting( $settings, 'game_format', 'period_length_minutes', 1, 60, $defaults['game_format']['period_length_minutes'] );

		$sanitized['standings_rules']['win_points']  = $this->sanitize_number_setting( $settings, 'standings_rules', 'win_points', 0, 10, $defaults['standings_rules']['win_points'] );
		$sanitized['standings_rules']['draw_points'] = $this->sanitize_number_setting( $settings, 'standings_rules', 'draw_points', 0, 10, $defaults['standings_rules']['draw_points'] );
		$sanitized['standings_rules']['loss_points'] = $this->sanitize_number_setting( $settings, 'standings_rules', 'loss_points', 0, 10, $defaults['standings_rules']['loss_points'] );

		$sanitized['game_rules']['allow_draw_regular_season']       = $this->sanitize_checkbox_setting( $settings, 'game_rules', 'allow_draw_regular_season' );
		$sanitized['game_rules']['overtime_allowed_regular_season'] = $this->sanitize_checkbox_setting( $settings, 'game_rules', 'overtime_allowed_regular_season' );
		$sanitized['game_rules']['shootout_allowed_regular_season'] = $this->sanitize_checkbox_setting( $settings, 'game_rules', 'shootout_allowed_regular_season' );
		$sanitized['playoff_rules']['overtime_allowed_playoff']     = $this->sanitize_checkbox_setting( $settings, 'playoff_rules', 'overtime_allowed_playoff' );
		$sanitized['playoff_rules']['shootout_allowed_playoff']     = $this->sanitize_checkbox_setting( $settings, 'playoff_rules', 'shootout_allowed_playoff' );

		if ( ! $sanitized['game_rules']['allow_draw_regular_season'] ) {
			$sanitized['standings_rules']['draw_points'] = 0;
		}

		if ( $sanitized['game_rules']['shootout_allowed_regular_season'] ) {
			$sanitized['game_rules']['overtime_allowed_regular_season'] = 1;
		}

		if ( $sanitized['playoff_rules']['shootout_allowed_playoff'] ) {
			$sanitized['playoff_rules']['overtime_allowed_playoff'] = 1;
		}

		return $sanitized;
	}

	/**
	 * Return the settings sections.
	 *
	 * @return array
	 */
	protected function get_sections() {
		return array(
			array(
				'id'          => 'mahl_game_format',
				'title'       => __( 'Game Format', 'mahl-manager' ),
				'description' => __( 'Configure the default format used for league games.', 'mahl-manager' ),
			),
			array(
				'id'          => 'mahl_standings_rules',
				'title'       => __( 'Standings Rules', 'mahl-manager' ),
				'description' => __( 'Define the points awarded for regular season results.', 'mahl-manager' ),
			),
			array(
				'id'          => 'mahl_regular_season_rules',
				'title'       => __( 'Regular Season Game Rules', 'mahl-manager' ),
				'description' => __( 'Control draws, overtime, and shootout behavior in the regular season.', 'mahl-manager' ),
			),
			array(
				'id'          => 'mahl_playoff_rules',
				'title'       => __( 'Playoff Rules', 'mahl-manager' ),
				'description' => __( 'Prepare future playoff overtime and shootout behavior without enabling playoff logic yet.', 'mahl-manager' ),
			),
		);
	}

	/**
	 * Return the settings fields.
	 *
	 * @return array
	 */
	protected function get_fields() {
		return array(
			array(
				'id'          => 'period_count',
				'section'     => 'mahl_game_format',
				'group'       => 'game_format',
				'key'         => 'period_count',
				'label'       => __( 'Period Count', 'mahl-manager' ),
				'type'        => 'number',
				'class'       => 'small-text',
				'min'         => 1,
				'step'        => 1,
				'description' => __( 'Default number of regulation periods per game.', 'mahl-manager' ),
			),
			array(
				'id'          => 'period_length_minutes',
				'section'     => 'mahl_game_format',
				'group'       => 'game_format',
				'key'         => 'period_length_minutes',
				'label'       => __( 'Period Length (minutes)', 'mahl-manager' ),
				'type'        => 'number',
				'class'       => 'small-text',
				'min'         => 1,
				'step'        => 1,
				'description' => __( 'Default duration of each regulation period in minutes.', 'mahl-manager' ),
			),
			array(
				'id'          => 'win_points',
				'section'     => 'mahl_standings_rules',
				'group'       => 'standings_rules',
				'key'         => 'win_points',
				'label'       => __( 'Win Points', 'mahl-manager' ),
				'type'        => 'number',
				'class'       => 'small-text',
				'min'         => 0,
				'step'        => 1,
				'description' => __( 'Points awarded for a win.', 'mahl-manager' ),
			),
			array(
				'id'          => 'draw_points',
				'section'     => 'mahl_standings_rules',
				'group'       => 'standings_rules',
				'key'         => 'draw_points',
				'label'       => __( 'Draw Points', 'mahl-manager' ),
				'type'        => 'number',
				'class'       => 'small-text',
				'min'         => 0,
				'step'        => 1,
				'description' => __( 'Points awarded for a draw. This is set to zero automatically if draws are disabled.', 'mahl-manager' ),
			),
			array(
				'id'          => 'loss_points',
				'section'     => 'mahl_standings_rules',
				'group'       => 'standings_rules',
				'key'         => 'loss_points',
				'label'       => __( 'Loss Points', 'mahl-manager' ),
				'type'        => 'number',
				'class'       => 'small-text',
				'min'         => 0,
				'step'        => 1,
				'description' => __( 'Points awarded for a loss.', 'mahl-manager' ),
			),
			array(
				'id'             => 'allow_draw_regular_season',
				'section'        => 'mahl_regular_season_rules',
				'group'          => 'game_rules',
				'key'            => 'allow_draw_regular_season',
				'label'          => __( 'Allow Draws', 'mahl-manager' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Allow drawn games in the regular season.', 'mahl-manager' ),
				'description'    => __( 'When disabled, draw points will be stored as zero.', 'mahl-manager' ),
			),
			array(
				'id'             => 'overtime_allowed_regular_season',
				'section'        => 'mahl_regular_season_rules',
				'group'          => 'game_rules',
				'key'            => 'overtime_allowed_regular_season',
				'label'          => __( 'Overtime Allowed', 'mahl-manager' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Allow overtime in the regular season.', 'mahl-manager' ),
				'description'    => __( 'Use this for future league formats that resolve tied games in overtime.', 'mahl-manager' ),
			),
			array(
				'id'             => 'shootout_allowed_regular_season',
				'section'        => 'mahl_regular_season_rules',
				'group'          => 'game_rules',
				'key'            => 'shootout_allowed_regular_season',
				'label'          => __( 'Shootout Allowed', 'mahl-manager' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Allow shootouts in the regular season.', 'mahl-manager' ),
				'description'    => __( 'Enabling shootouts implies overtime in the regular season.', 'mahl-manager' ),
			),
			array(
				'id'             => 'overtime_allowed_playoff',
				'section'        => 'mahl_playoff_rules',
				'group'          => 'playoff_rules',
				'key'            => 'overtime_allowed_playoff',
				'label'          => __( 'Playoff Overtime Allowed', 'mahl-manager' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Allow overtime in playoff games.', 'mahl-manager' ),
				'description'    => __( 'Future playoff logic can read this setting to determine how tied playoff games are resolved.', 'mahl-manager' ),
			),
			array(
				'id'             => 'shootout_allowed_playoff',
				'section'        => 'mahl_playoff_rules',
				'group'          => 'playoff_rules',
				'key'            => 'shootout_allowed_playoff',
				'label'          => __( 'Playoff Shootout Allowed', 'mahl-manager' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Allow shootouts in playoff games.', 'mahl-manager' ),
				'description'    => __( 'Enabling playoff shootouts implies playoff overtime.', 'mahl-manager' ),
			),
		);
	}

	/**
	 * Return a field value from the settings array.
	 *
	 * @param array  $settings Settings array.
	 * @param string $group Settings group key.
	 * @param string $key Settings field key.
	 * @return mixed
	 */
	protected function get_field_value( $settings, $group, $key ) {
		if ( isset( $settings[ $group ][ $key ] ) ) {
			return $settings[ $group ][ $key ];
		}

		return '';
	}

	/**
	 * Sanitize a checkbox setting.
	 *
	 * @param array  $settings Submitted settings.
	 * @param string $group Settings group key.
	 * @param string $key Settings field key.
	 * @return int
	 */
	protected function sanitize_checkbox_setting( $settings, $group, $key ) {
		return isset( $settings[ $group ][ $key ] ) ? 1 : 0;
	}

	/**
	 * Sanitize an integer setting with bounds.
	 *
	 * @param array $settings Submitted settings.
	 * @param string $group Settings group key.
	 * @param string $key Settings field key.
	 * @param int $min Minimum value.
	 * @param int $max Maximum value.
	 * @param int $default Default value.
	 * @return int
	 */
	protected function sanitize_number_setting( $settings, $group, $key, $min, $max, $default ) {
		if ( ! isset( $settings[ $group ][ $key ] ) ) {
			return $default;
		}

		$value = sanitize_text_field( wp_unslash( $settings[ $group ][ $key ] ) );

		if ( ! preg_match( '/^\d+$/', $value ) ) {
			return $default;
		}

		$value = absint( $value );

		if ( $value < $min || $value > $max ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Render an input tag with attributes.
	 *
	 * @param array $attributes HTML attributes.
	 * @return void
	 */
	protected function render_input_tag( $attributes ) {
		$parts = array();

		foreach ( $attributes as $attribute => $value ) {
			$parts[] = sprintf(
				'%1$s="%2$s"',
				esc_attr( $attribute ),
				esc_attr( (string) $value )
			);
		}

		printf(
			'<input %s />',
			implode( ' ', $parts )
		);
	}
}
