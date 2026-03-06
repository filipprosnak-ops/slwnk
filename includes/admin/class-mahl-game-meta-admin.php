<?php
/**
 * Game meta admin module.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage game meta fields in the admin area.
 */
class MAHL_Game_Meta_Admin {

	/**
	 * Nonce action for game meta fields.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'mahl_save_game_meta';

	/**
	 * Nonce field name for game meta fields.
	 *
	 * @var string
	 */
	const NONCE_NAME = 'mahl_game_meta_nonce';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_mahl_game', array( $this, 'save_game_meta' ) );
	}

	/**
	 * Register the game details meta box.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'mahl-game-details',
			__( 'Game Details', 'mahl-manager' ),
			array( $this, 'render_meta_box' ),
			'mahl_game',
			'normal',
			'high'
		);
	}

	/**
	 * Render the game details meta box.
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<p>' . esc_html__( 'Enter the competition context and teams first, then add schedule details, score, and event data. Finalized games should have a complete final score before they are used in standings and player statistics.', 'mahl-manager' ) . '</p>';

		foreach ( $this->get_field_groups() as $group ) {
			printf(
				'<h3>%s</h3>',
				esc_html( $group['title'] )
			);

			echo '<table class="form-table" role="presentation"><tbody>';

			foreach ( $group['fields'] as $field ) {
				$this->render_field_row( $post->ID, $field );
			}

			echo '</tbody></table>';
		}
	}

	/**
	 * Save game meta fields.
	 *
	 * @param int $post_id Current post ID.
	 * @return void
	 */
	public function save_game_meta( $post_id ) {
		if ( ! $this->should_save_game_meta( $post_id ) ) {
			return;
		}

		$fields = $this->get_fields();
		$values = array();

		foreach ( $fields as $field ) {
			$values[ $field['meta_key'] ] = $this->sanitize_field_value( $field );
		}

		if ( '1' === $values['_mahl_shootout_played'] ) {
			$values['_mahl_overtime_played'] = '1';
		}

		if ( '1' !== $values['_mahl_overtime_played'] ) {
			$values['_mahl_score_home_overtime'] = '';
			$values['_mahl_score_away_overtime'] = '';
		}

		if ( '1' !== $values['_mahl_shootout_played'] ) {
			$values['_mahl_score_home_shootout'] = '';
			$values['_mahl_score_away_shootout'] = '';
		}

		foreach ( $fields as $field ) {
			$this->persist_field_value( $post_id, $field['meta_key'], $values[ $field['meta_key'] ] );
		}
	}

	/**
	 * Determine whether game meta should be saved.
	 *
	 * @param int $post_id Current post ID.
	 * @return bool
	 */
	protected function should_save_game_meta( $post_id ) {
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

		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Render a single field row.
	 *
	 * @param int   $post_id Current post ID.
	 * @param array $field Field configuration.
	 * @return void
	 */
	protected function render_field_row( $post_id, $field ) {
		$value = get_post_meta( $post_id, $field['meta_key'], true );

		echo '<tr>';
		printf(
			'<th scope="row"><label for="%1$s">%2$s</label></th>',
			esc_attr( $field['meta_key'] ),
			esc_html( $field['label'] )
		);

		echo '<td>';
		$this->render_field_input( $field, $value );

		if ( ! empty( $field['description'] ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html( $field['description'] )
			);
		}

		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Render the input for a field.
	 *
	 * @param array       $field Field configuration.
	 * @param string|int  $value Stored value.
	 * @return void
	 */
	protected function render_field_input( $field, $value ) {
		switch ( $field['type'] ) {
			case 'textarea':
				printf(
					'<textarea class="large-text" rows="4" id="%1$s" name="%1$s">%2$s</textarea>',
					esc_attr( $field['meta_key'] ),
					esc_textarea( $value )
				);
				break;

			case 'select':
				$select_attributes = array(
					'class' => 'regular-text',
					'id'    => $field['meta_key'],
					'name'  => $field['meta_key'],
				);

				if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
					$select_attributes = array_merge( $select_attributes, $field['attributes'] );
				}

				printf(
					'<select %1$s>',
					$this->build_html_attributes( $select_attributes )
				);

				foreach ( $field['options'] as $option_value => $option_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $option_value ),
						selected( $value, $option_value, false ),
						esc_html( $option_label )
					);
				}

				echo '</select>';
				break;

			case 'checkbox':
				$checkbox_attributes = array(
					'type'  => 'checkbox',
					'id'    => $field['meta_key'],
					'name'  => $field['meta_key'],
					'value' => '1',
				);

				if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
					$checkbox_attributes = array_merge( $checkbox_attributes, $field['attributes'] );
				}

				if ( '1' === (string) $value ) {
					$checkbox_attributes['checked'] = 'checked';
				}

				printf(
					'<label for="%1$s"><input %2$s /> %3$s</label>',
					esc_attr( $field['meta_key'] ),
					$this->build_html_attributes( $checkbox_attributes ),
					esc_html( $field['checkbox_label'] )
				);
				break;

			default:
				$attributes = array(
					'type'  => 'key' === $field['type'] ? 'text' : $field['type'],
					'class' => $field['class'],
					'id'    => $field['meta_key'],
					'name'  => $field['meta_key'],
					'value' => $value,
				);

				if ( isset( $field['min'] ) ) {
					$attributes['min'] = $field['min'];
				}

				if ( isset( $field['step'] ) ) {
					$attributes['step'] = $field['step'];
				}

				if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
					$attributes = array_merge( $attributes, $field['attributes'] );
				}

				$this->render_input_tag( $attributes );
				break;
		}
	}

	/**
	 * Render an input tag with attributes.
	 *
	 * @param array $attributes HTML attributes.
	 * @return void
	 */
	protected function render_input_tag( $attributes ) {
		printf(
			'<input %s />',
			$this->build_html_attributes( $attributes )
		);
	}

	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param array $attributes HTML attributes.
	 * @return string
	 */
	protected function build_html_attributes( $attributes ) {
		$parts = array();

		foreach ( $attributes as $attribute => $value ) {
			$parts[] = sprintf(
				'%1$s="%2$s"',
				esc_attr( $attribute ),
				esc_attr( (string) $value )
			);
		}

		return implode( ' ', $parts );
	}

	/**
	 * Return the configured field groups.
	 *
	 * @return array
	 */
	protected function get_field_groups() {
		$fields = $this->get_fields();

		return array(
			array(
				'title'  => __( 'Match Information', 'mahl-manager' ),
				'fields' => array(
					$fields['_mahl_venue'],
					$fields['_mahl_match_date'],
					$fields['_mahl_match_time'],
					$fields['_mahl_status'],
				),
			),
			array(
				'title'  => __( 'Competition Structure', 'mahl-manager' ),
				'fields' => array(
					$fields['_mahl_round_number'],
					$fields['_mahl_group_key'],
				),
			),
			array(
				'title'  => __( 'Final Score', 'mahl-manager' ),
				'fields' => array(
					$fields['_mahl_score_home_final'],
					$fields['_mahl_score_away_final'],
				),
			),
			array(
				'title'  => __( 'Regulation Period Scores', 'mahl-manager' ),
				'fields' => array(
					$fields['_mahl_score_home_period_1'],
					$fields['_mahl_score_away_period_1'],
					$fields['_mahl_score_home_period_2'],
					$fields['_mahl_score_away_period_2'],
					$fields['_mahl_score_home_period_3'],
					$fields['_mahl_score_away_period_3'],
				),
			),
			array(
				'title'  => __( 'Overtime and Shootout', 'mahl-manager' ),
				'fields' => array(
					$fields['_mahl_overtime_played'],
					$fields['_mahl_shootout_played'],
					$fields['_mahl_score_home_overtime'],
					$fields['_mahl_score_away_overtime'],
					$fields['_mahl_score_home_shootout'],
					$fields['_mahl_score_away_shootout'],
				),
			),
			array(
				'title'  => __( 'Notes', 'mahl-manager' ),
				'fields' => array(
					$fields['_mahl_notes'],
				),
			),
		);
	}

	/**
	 * Return all supported game meta fields.
	 *
	 * @return array
	 */
	protected function get_fields() {
		return array(
			'_mahl_venue'                => array(
				'meta_key'    => '_mahl_venue',
				'label'       => __( 'Venue', 'mahl-manager' ),
				'type'        => 'text',
				'class'       => 'regular-text',
				'description' => __( 'Enter the venue or rink for the game.', 'mahl-manager' ),
			),
			'_mahl_match_date'           => array(
				'meta_key'    => '_mahl_match_date',
				'label'       => __( 'Match Date', 'mahl-manager' ),
				'type'        => 'date',
				'class'       => 'regular-text',
				'description' => __( 'Store the game date in calendar format.', 'mahl-manager' ),
			),
			'_mahl_match_time'           => array(
				'meta_key'    => '_mahl_match_time',
				'label'       => __( 'Match Time', 'mahl-manager' ),
				'type'        => 'time',
				'class'       => 'regular-text',
				'step'        => 60,
				'description' => __( 'Store the scheduled puck-drop time.', 'mahl-manager' ),
			),
			'_mahl_status'               => array(
				'meta_key'    => '_mahl_status',
				'label'       => __( 'Status', 'mahl-manager' ),
				'type'        => 'select',
				'options'     => $this->get_status_options(),
				'default'     => 'scheduled',
				'attributes'  => array(
					'data-mahl-game-status' => '1',
				),
				'description' => __( 'Choose the current state of the game record.', 'mahl-manager' ),
			),
			'_mahl_round_number'         => array(
				'meta_key'    => '_mahl_round_number',
				'label'       => __( 'Round Number', 'mahl-manager' ),
				'type'        => 'number',
				'class'       => 'small-text',
				'min'         => 1,
				'step'        => 1,
				'description' => __( 'Store the competition round number for this game.', 'mahl-manager' ),
			),
			'_mahl_group_key'            => array(
				'meta_key'    => '_mahl_group_key',
				'label'       => __( 'Group / Bracket Key', 'mahl-manager' ),
				'type'        => 'key',
				'class'       => 'regular-text',
				'attributes'  => array(
					'placeholder' => __( 'top, bottom, semifinal, final', 'mahl-manager' ),
				),
				'description' => __( 'Optional normalized key for sub-groups or playoff brackets, for example top, bottom, semifinal, or final.', 'mahl-manager' ),
			),
			'_mahl_score_home_final'     => $this->get_score_field(
				'_mahl_score_home_final',
				__( 'Home Final Score', 'mahl-manager' ),
				__( 'Store the final score for the home team.', 'mahl-manager' )
			),
			'_mahl_score_away_final'     => $this->get_score_field(
				'_mahl_score_away_final',
				__( 'Away Final Score', 'mahl-manager' ),
				__( 'Store the final score for the away team.', 'mahl-manager' )
			),
			'_mahl_score_home_period_1'  => $this->get_score_field(
				'_mahl_score_home_period_1',
				__( 'Home Score Period 1', 'mahl-manager' ),
				__( 'Store the home team score for period 1.', 'mahl-manager' )
			),
			'_mahl_score_away_period_1'  => $this->get_score_field(
				'_mahl_score_away_period_1',
				__( 'Away Score Period 1', 'mahl-manager' ),
				__( 'Store the away team score for period 1.', 'mahl-manager' )
			),
			'_mahl_score_home_period_2'  => $this->get_score_field(
				'_mahl_score_home_period_2',
				__( 'Home Score Period 2', 'mahl-manager' ),
				__( 'Store the home team score for period 2.', 'mahl-manager' )
			),
			'_mahl_score_away_period_2'  => $this->get_score_field(
				'_mahl_score_away_period_2',
				__( 'Away Score Period 2', 'mahl-manager' ),
				__( 'Store the away team score for period 2.', 'mahl-manager' )
			),
			'_mahl_score_home_period_3'  => $this->get_score_field(
				'_mahl_score_home_period_3',
				__( 'Home Score Period 3', 'mahl-manager' ),
				__( 'Store the home team score for period 3.', 'mahl-manager' )
			),
			'_mahl_score_away_period_3'  => $this->get_score_field(
				'_mahl_score_away_period_3',
				__( 'Away Score Period 3', 'mahl-manager' ),
				__( 'Store the away team score for period 3.', 'mahl-manager' )
			),
			'_mahl_overtime_played'      => array(
				'meta_key'       => '_mahl_overtime_played',
				'label'          => __( 'Overtime Played', 'mahl-manager' ),
				'type'           => 'checkbox',
				'attributes'     => array(
					'data-mahl-overtime-toggle' => '1',
				),
				'checkbox_label' => __( 'This game included overtime.', 'mahl-manager' ),
				'description'    => __( 'Enable overtime scoring fields for the game.', 'mahl-manager' ),
			),
			'_mahl_shootout_played'      => array(
				'meta_key'       => '_mahl_shootout_played',
				'label'          => __( 'Shootout Played', 'mahl-manager' ),
				'type'           => 'checkbox',
				'attributes'     => array(
					'data-mahl-shootout-toggle' => '1',
				),
				'checkbox_label' => __( 'This game was decided by a shootout.', 'mahl-manager' ),
				'description'    => __( 'Shootout implies overtime and stores its own scoring values.', 'mahl-manager' ),
			),
			'_mahl_score_home_overtime'  => array_merge(
				$this->get_score_field(
					'_mahl_score_home_overtime',
					__( 'Home Overtime Score', 'mahl-manager' ),
					__( 'Store the home team score recorded in overtime.', 'mahl-manager' )
				),
				array(
					'attributes' => array(
						'data-mahl-overtime-field' => '1',
					),
				)
			),
			'_mahl_score_away_overtime'  => array_merge(
				$this->get_score_field(
					'_mahl_score_away_overtime',
					__( 'Away Overtime Score', 'mahl-manager' ),
					__( 'Store the away team score recorded in overtime.', 'mahl-manager' )
				),
				array(
					'attributes' => array(
						'data-mahl-overtime-field' => '1',
					),
				)
			),
			'_mahl_score_home_shootout'  => array_merge(
				$this->get_score_field(
					'_mahl_score_home_shootout',
					__( 'Home Shootout Score', 'mahl-manager' ),
					__( 'Store the home team shootout score.', 'mahl-manager' )
				),
				array(
					'attributes' => array(
						'data-mahl-shootout-field' => '1',
					),
				)
			),
			'_mahl_score_away_shootout'  => array_merge(
				$this->get_score_field(
					'_mahl_score_away_shootout',
					__( 'Away Shootout Score', 'mahl-manager' ),
					__( 'Store the away team shootout score.', 'mahl-manager' )
				),
				array(
					'attributes' => array(
						'data-mahl-shootout-field' => '1',
					),
				)
			),
			'_mahl_notes'                => array(
				'meta_key'    => '_mahl_notes',
				'label'       => __( 'Notes', 'mahl-manager' ),
				'type'        => 'textarea',
				'description' => __( 'Store match notes, remarks, or special circumstances.', 'mahl-manager' ),
			),
		);
	}

	/**
	 * Return a standardized score field configuration.
	 *
	 * @param string $meta_key Meta key.
	 * @param string $label Field label.
	 * @param string $description Field description.
	 * @return array
	 */
	protected function get_score_field( $meta_key, $label, $description ) {
		return array(
			'meta_key'    => $meta_key,
			'label'       => $label,
			'type'        => 'number',
			'class'       => 'small-text',
			'min'         => 0,
			'step'        => 1,
			'description' => $description,
		);
	}

	/**
	 * Return the supported game statuses.
	 *
	 * @return array
	 */
	protected function get_status_options() {
		return array(
			'scheduled'   => __( 'Scheduled', 'mahl-manager' ),
			'in_progress' => __( 'In Progress', 'mahl-manager' ),
			'final'       => __( 'Final', 'mahl-manager' ),
			'postponed'   => __( 'Postponed', 'mahl-manager' ),
			'cancelled'   => __( 'Cancelled', 'mahl-manager' ),
		);
	}

	/**
	 * Sanitize a submitted field value.
	 *
	 * @param array $field Field configuration.
	 * @return string|int
	 */
	protected function sanitize_field_value( $field ) {
		switch ( $field['type'] ) {
			case 'checkbox':
				return isset( $_POST[ $field['meta_key'] ] ) ? '1' : '0';

			case 'textarea':
				if ( ! isset( $_POST[ $field['meta_key'] ] ) ) {
					return '';
				}

				return sanitize_textarea_field( wp_unslash( $_POST[ $field['meta_key'] ] ) );

			case 'select':
				if ( ! isset( $_POST[ $field['meta_key'] ] ) ) {
					return $field['default'];
				}

				$value = sanitize_text_field( wp_unslash( $_POST[ $field['meta_key'] ] ) );

				return isset( $field['options'][ $value ] ) ? $value : $field['default'];

			case 'date':
				if ( ! isset( $_POST[ $field['meta_key'] ] ) ) {
					return '';
				}

				return $this->sanitize_date_value( wp_unslash( $_POST[ $field['meta_key'] ] ) );

			case 'time':
				if ( ! isset( $_POST[ $field['meta_key'] ] ) ) {
					return '';
				}

				return $this->sanitize_time_value( wp_unslash( $_POST[ $field['meta_key'] ] ) );

			case 'key':
				if ( ! isset( $_POST[ $field['meta_key'] ] ) ) {
					return '';
				}

				return $this->sanitize_key_value( wp_unslash( $_POST[ $field['meta_key'] ] ) );

			case 'number':
				if ( ! isset( $_POST[ $field['meta_key'] ] ) ) {
					return '';
				}

				return $this->sanitize_number_value( wp_unslash( $_POST[ $field['meta_key'] ] ) );

			case 'text':
			default:
				if ( ! isset( $_POST[ $field['meta_key'] ] ) ) {
					return '';
				}

				return sanitize_text_field( wp_unslash( $_POST[ $field['meta_key'] ] ) );
		}
	}

	/**
	 * Sanitize a date value.
	 *
	 * @param string $value Raw field value.
	 * @return string
	 */
	protected function sanitize_date_value( $value ) {
		$value = sanitize_text_field( $value );

		if ( '' === $value ) {
			return '';
		}

		$date = DateTime::createFromFormat( 'Y-m-d', $value );

		if ( $date && $date->format( 'Y-m-d' ) === $value ) {
			return $value;
		}

		return '';
	}

	/**
	 * Sanitize a time value.
	 *
	 * @param string $value Raw field value.
	 * @return string
	 */
	protected function sanitize_time_value( $value ) {
		$value = sanitize_text_field( $value );

		if ( '' === $value ) {
			return '';
		}

		$time = DateTime::createFromFormat( 'H:i', $value );

		if ( $time && $time->format( 'H:i' ) === $value ) {
			return $value;
		}

		return '';
	}

	/**
	 * Sanitize a non-negative integer value.
	 *
	 * @param string $value Raw field value.
	 * @return string|int
	 */
	protected function sanitize_number_value( $value ) {
		$value = sanitize_text_field( $value );

		if ( '' === $value ) {
			return '';
		}

		if ( ! preg_match( '/^\d+$/', $value ) ) {
			return '';
		}

		return absint( $value );
	}

	/**
	 * Sanitize a normalized key value.
	 *
	 * @param string $value Raw field value.
	 * @return string
	 */
	protected function sanitize_key_value( $value ) {
		$value = sanitize_key( wp_strip_all_tags( $value ) );

		if ( '' === $value ) {
			return '';
		}

		return $value;
	}

	/**
	 * Persist a field value as post meta.
	 *
	 * @param int        $post_id Current post ID.
	 * @param string     $meta_key Meta key.
	 * @param string|int $value Sanitized value.
	 * @return void
	 */
	protected function persist_field_value( $post_id, $meta_key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		update_post_meta( $post_id, $meta_key, $value );
	}
}
