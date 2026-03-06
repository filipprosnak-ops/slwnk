<?php
/**
 * Relationship admin module.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage entity relationship fields in the admin area.
 */
class MAHL_Relationships_Admin {

	/**
	 * Nonce action for relationship fields.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'mahl_save_relationships';

	/**
	 * Nonce field name for relationship fields.
	 *
	 * @var string
	 */
	const NONCE_NAME = 'mahl_relationships_nonce';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_relationships' ) );
	}

	/**
	 * Register relationship meta boxes.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		foreach ( $this->get_meta_box_configurations() as $post_type => $meta_box ) {
			add_meta_box(
				$meta_box['id'],
				$meta_box['title'],
				array( $this, 'render_meta_box' ),
				$post_type,
				$meta_box['context'],
				$meta_box['priority'],
				array(
					'post_type' => $post_type,
				)
			);
		}
	}

	/**
	 * Render a relationship meta box.
	 *
	 * @param WP_Post $post Current post object.
	 * @param array   $meta_box Meta box arguments.
	 * @return void
	 */
	public function render_meta_box( $post, $meta_box ) {
		$post_type = isset( $meta_box['args']['post_type'] ) ? $meta_box['args']['post_type'] : '';
		$fields    = $this->get_fields_for_post_type( $post_type );

		if ( empty( $fields ) ) {
			return;
		}

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<div class="mahl-relationship-fields">';

		foreach ( $fields as $field ) {
			$this->render_select_field( $post->ID, $field );
		}

		echo '</div>';
	}

	/**
	 * Save relationships for supported post types.
	 *
	 * @param int $post_id Current post ID.
	 * @return void
	 */
	public function save_relationships( $post_id ) {
		if ( ! $this->should_save_relationships( $post_id ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		$fields    = $this->get_fields_for_post_type( $post_type );
		$values    = array();

		foreach ( $fields as $field ) {
			$values[ $field['meta_key'] ] = $this->get_submitted_relationship_value( $field );
		}

		foreach ( $fields as $field ) {
			$value = $values[ $field['meta_key'] ];

			if ( ! $this->is_valid_related_post( $value, $field['related_post_type'] ) ) {
				delete_post_meta( $post_id, $field['meta_key'] );
				continue;
			}

			if ( '_mahl_away_team_id' === $field['meta_key']
				&& ! empty( $values['_mahl_home_team_id'] )
				&& $value === $values['_mahl_home_team_id']
			) {
				delete_post_meta( $post_id, $field['meta_key'] );
				continue;
			}

			update_post_meta( $post_id, $field['meta_key'], $value );
		}
	}

	/**
	 * Determine whether relationships should be saved.
	 *
	 * @param int $post_id Current post ID.
	 * @return bool
	 */
	protected function should_save_relationships( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return false;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return false;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return false;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return ! empty( $this->get_fields_for_post_type( get_post_type( $post_id ) ) );
	}

	/**
	 * Render a select field for a relationship.
	 *
	 * @param int   $post_id Current post ID.
	 * @param array $field Field configuration.
	 * @return void
	 */
	protected function render_select_field( $post_id, $field ) {
		$current_value = absint( get_post_meta( $post_id, $field['meta_key'], true ) );
		$options       = $this->get_related_posts( $field['related_post_type'] );

		echo '<p>';
		printf(
			'<label for="%1$s"><strong>%2$s</strong></label><br />',
			esc_attr( $field['meta_key'] ),
			esc_html( $field['label'] )
		);

		printf(
			'<select class="widefat" id="%1$s" name="%1$s">',
			esc_attr( $field['meta_key'] )
		);

		printf(
			'<option value="">%s</option>',
			esc_html( $field['placeholder'] )
		);

		foreach ( $options as $option ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				absint( $option->ID ),
				selected( $current_value, $option->ID, false ),
				esc_html( get_the_title( $option ) )
			);
		}

		echo '</select>';

		if ( ! empty( $field['description'] ) ) {
			printf(
				'<span class="description">%s</span>',
				esc_html( $field['description'] )
			);
		}

		echo '</p>';
	}

	/**
	 * Return relationship field configurations for a post type.
	 *
	 * @param string $post_type Current post type.
	 * @return array
	 */
	protected function get_fields_for_post_type( $post_type ) {
		$configurations = array(
			'mahl_team'   => array(
				array(
					'meta_key'          => '_mahl_season_id',
					'label'             => __( 'Season', 'mahl-manager' ),
					'placeholder'       => __( 'Select a season', 'mahl-manager' ),
					'description'       => __( 'Choose the season this team belongs to.', 'mahl-manager' ),
					'related_post_type' => 'mahl_season',
				),
			),
			'mahl_player' => array(
				array(
					'meta_key'          => '_mahl_team_id',
					'label'             => __( 'Team', 'mahl-manager' ),
					'placeholder'       => __( 'Select a team', 'mahl-manager' ),
					'description'       => __( 'Choose the team this player belongs to.', 'mahl-manager' ),
					'related_post_type' => 'mahl_team',
				),
			),
			'mahl_game'   => array(
				array(
					'meta_key'          => '_mahl_season_id',
					'label'             => __( 'Season', 'mahl-manager' ),
					'placeholder'       => __( 'Select a season', 'mahl-manager' ),
					'description'       => __( 'Choose the season this game belongs to.', 'mahl-manager' ),
					'related_post_type' => 'mahl_season',
				),
				array(
					'meta_key'          => '_mahl_home_team_id',
					'label'             => __( 'Home Team', 'mahl-manager' ),
					'placeholder'       => __( 'Select a home team', 'mahl-manager' ),
					'description'       => __( 'Choose the home team for this game.', 'mahl-manager' ),
					'related_post_type' => 'mahl_team',
				),
				array(
					'meta_key'          => '_mahl_away_team_id',
					'label'             => __( 'Away Team', 'mahl-manager' ),
					'placeholder'       => __( 'Select an away team', 'mahl-manager' ),
					'description'       => __( 'Choose the away team for this game.', 'mahl-manager' ),
					'related_post_type' => 'mahl_team',
				),
			),
		);

		return isset( $configurations[ $post_type ] ) ? $configurations[ $post_type ] : array();
	}

	/**
	 * Return the meta box configurations.
	 *
	 * @return array
	 */
	protected function get_meta_box_configurations() {
		return array(
			'mahl_team'   => array(
				'id'       => 'mahl-team-relationships',
				'title'    => __( 'Season Relationship', 'mahl-manager' ),
				'context'  => 'side',
				'priority' => 'default',
			),
			'mahl_player' => array(
				'id'       => 'mahl-player-relationships',
				'title'    => __( 'Team Relationship', 'mahl-manager' ),
				'context'  => 'side',
				'priority' => 'default',
			),
			'mahl_game'   => array(
				'id'       => 'mahl-game-relationships',
				'title'    => __( 'Game Relationships', 'mahl-manager' ),
				'context'  => 'normal',
				'priority' => 'default',
			),
		);
	}

	/**
	 * Return related posts for a relationship field.
	 *
	 * @param string $related_post_type Related post type key.
	 * @return array
	 */
	protected function get_related_posts( $related_post_type ) {
		return get_posts(
			array(
				'post_type'      => $related_post_type,
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
	}

	/**
	 * Return the submitted relationship value.
	 *
	 * @param array $field Field configuration.
	 * @return int
	 */
	protected function get_submitted_relationship_value( $field ) {
		if ( ! isset( $_POST[ $field['meta_key'] ] ) ) {
			return 0;
		}

		return absint( wp_unslash( $_POST[ $field['meta_key'] ] ) );
	}

	/**
	 * Check whether a related post is valid for a field.
	 *
	 * @param int    $related_post_id Related post ID.
	 * @param string $expected_post_type Expected related post type.
	 * @return bool
	 */
	protected function is_valid_related_post( $related_post_id, $expected_post_type ) {
		if ( empty( $related_post_id ) ) {
			return false;
		}

		return $expected_post_type === get_post_type( $related_post_id );
	}
}
