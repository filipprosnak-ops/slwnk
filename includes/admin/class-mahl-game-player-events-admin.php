<?php
/**
 * Game player events admin module.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage player appearances and stat events for games.
 */
class MAHL_Game_Player_Events_Admin {

	/**
	 * Nonce action for player event fields.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'mahl_save_game_player_events';

	/**
	 * Nonce field name for player event fields.
	 *
	 * @var string
	 */
	const NONCE_NAME = 'mahl_game_player_events_nonce';

	/**
	 * Player appearances meta key.
	 *
	 * @var string
	 */
	const APPEARANCES_META_KEY = '_mahl_game_player_appearances';

	/**
	 * Game events meta key.
	 *
	 * @var string
	 */
	const EVENTS_META_KEY = '_mahl_game_events';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_mahl_game', array( $this, 'save_game_player_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register the player appearances and events meta box.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'mahl-game-player-events',
			__( 'Player Appearances and Events', 'mahl-manager' ),
			array( $this, 'render_meta_box' ),
			'mahl_game',
			'normal',
			'default'
		);
	}

	/**
	 * Enqueue admin assets for game editing.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$screen = get_current_screen();

		if ( ! $screen || 'mahl_game' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			'mahl-manager-admin',
			MAHL_MANAGER_URL . 'assets/js/admin.js',
			array(),
			MAHL_MANAGER_VERSION,
			true
		);
	}

	/**
	 * Render the player event meta box.
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		$team_options   = $this->get_game_team_options( $post->ID );
		$player_options = $this->get_player_options_by_team( array_keys( $team_options ) );
		$appearances    = $this->get_stored_appearances( $post->ID );
		$events         = $this->get_stored_events( $post->ID );

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<p>' . esc_html__( 'Use player appearances to track who played in the game and event rows to record goals, assists, and penalties. Save the game after assigning home and away teams to populate the team player lists.', 'mahl-manager' ) . '</p>';

		$this->render_appearances_section( $appearances, $team_options, $player_options );
		$this->render_events_section( $events, $team_options, $player_options );
	}

	/**
	 * Save player appearances and event data.
	 *
	 * @param int $post_id Current post ID.
	 * @return void
	 */
	public function save_game_player_data( $post_id ) {
		if ( ! $this->should_save_game_player_data( $post_id ) ) {
			return;
		}

		$game_team_ids = $this->get_game_team_ids( $post_id );
		$appearances   = $this->sanitize_appearances( $post_id, $game_team_ids );
		$events        = $this->sanitize_events( $post_id, $game_team_ids );

		$this->persist_meta_value( $post_id, self::APPEARANCES_META_KEY, $appearances );
		$this->persist_meta_value( $post_id, self::EVENTS_META_KEY, $events );
	}

	/**
	 * Determine whether player event data should be saved.
	 *
	 * @param int $post_id Current post ID.
	 * @return bool
	 */
	protected function should_save_game_player_data( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
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

		return 'mahl_game' === get_post_type( $post_id );
	}

	/**
	 * Render the player appearances section.
	 *
	 * @param array $appearances Stored appearances.
	 * @param array $team_options Game team options.
	 * @param array $player_options Player options grouped by team.
	 * @return void
	 */
	protected function render_appearances_section( $appearances, $team_options, $player_options ) {
		if ( empty( $appearances ) ) {
			$appearances = array(
				array(
					'team_id'   => 0,
					'player_id' => 0,
				),
			);
		}

		echo '<h3>' . esc_html__( 'Player Appearances', 'mahl-manager' ) . '</h3>';
		echo '<div class="mahl-repeater" data-mahl-repeater="appearances">';
		echo '<table class="widefat striped"><thead><tr>';
		echo '<th>' . esc_html__( 'Team', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Player', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Actions', 'mahl-manager' ) . '</th>';
		echo '</tr></thead><tbody data-mahl-rows>';

		foreach ( $appearances as $index => $appearance ) {
			echo $this->get_appearance_row_markup( $index, $appearance, $team_options, $player_options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</tbody></table>';
		printf(
			'<p><button type="button" class="button" data-mahl-add-row="%1$s">%2$s</button></p>',
			esc_attr( 'appearances' ),
			esc_html__( 'Add Player Appearance', 'mahl-manager' )
		);
		printf(
			'<template data-mahl-template="%1$s">%2$s</template>',
			esc_attr( 'appearances' ),
			$this->get_appearance_row_markup( '__INDEX__', array(), $team_options, $player_options ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		echo '</div>';
	}

	/**
	 * Render the game events section.
	 *
	 * @param array $events Stored events.
	 * @param array $team_options Game team options.
	 * @param array $player_options Player options grouped by team.
	 * @return void
	 */
	protected function render_events_section( $events, $team_options, $player_options ) {
		if ( empty( $events ) ) {
			$events = array(
				array(
					'event_type'        => 'goal',
					'team_id'           => 0,
					'period_number'     => 1,
					'event_time'        => '',
					'player_id'         => 0,
					'assist_1_player_id'=> 0,
					'assist_2_player_id'=> 0,
					'penalty_minutes'   => 0,
					'event_label'       => '',
				),
			);
		}

		echo '<h3>' . esc_html__( 'Game Events', 'mahl-manager' ) . '</h3>';
		echo '<p class="description">' . esc_html__( 'For goals, select the scoring player and up to two assists. For penalties, select the penalized player and enter the penalty minutes and reason. Event time is optional and should be entered as MM:SS.', 'mahl-manager' ) . '</p>';
		echo '<div class="mahl-repeater" data-mahl-repeater="events">';
		echo '<table class="widefat striped"><thead><tr>';
		echo '<th>' . esc_html__( 'Type', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Team', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Period', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Time', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Player', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Assist 1', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Assist 2', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Penalty Minutes', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Reason / Label', 'mahl-manager' ) . '</th>';
		echo '<th>' . esc_html__( 'Actions', 'mahl-manager' ) . '</th>';
		echo '</tr></thead><tbody data-mahl-rows>';

		foreach ( $events as $index => $event ) {
			echo $this->get_event_row_markup( $index, $event, $team_options, $player_options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</tbody></table>';
		printf(
			'<p><button type="button" class="button" data-mahl-add-row="%1$s">%2$s</button></p>',
			esc_attr( 'events' ),
			esc_html__( 'Add Game Event', 'mahl-manager' )
		);
		printf(
			'<template data-mahl-template="%1$s">%2$s</template>',
			esc_attr( 'events' ),
			$this->get_event_row_markup( '__INDEX__', array(), $team_options, $player_options ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		echo '</div>';
	}

	/**
	 * Return the markup for an appearance row.
	 *
	 * @param int|string $index Row index.
	 * @param array      $appearance Appearance data.
	 * @param array      $team_options Game team options.
	 * @param array      $player_options Player options grouped by team.
	 * @return string
	 */
	protected function get_appearance_row_markup( $index, $appearance, $team_options, $player_options ) {
		$team_id   = isset( $appearance['team_id'] ) ? absint( $appearance['team_id'] ) : 0;
		$player_id = isset( $appearance['player_id'] ) ? absint( $appearance['player_id'] ) : 0;
		$index     = (string) $index;

		ob_start();
		?>
		<tr data-mahl-row="appearance" data-row-index="<?php echo esc_attr( $index ); ?>">
			<td>
				<select class="widefat" data-mahl-team-select name="mahl_player_appearances[<?php echo esc_attr( $index ); ?>][team_id]">
					<option value=""><?php esc_html_e( 'Select team', 'mahl-manager' ); ?></option>
					<?php foreach ( $team_options as $option_team_id => $team_label ) : ?>
						<option value="<?php echo esc_attr( $option_team_id ); ?>" <?php selected( $team_id, $option_team_id ); ?>><?php echo esc_html( $team_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<select class="widefat" data-mahl-player-select="appearance" name="mahl_player_appearances[<?php echo esc_attr( $index ); ?>][player_id]">
					<option value=""><?php esc_html_e( 'Select player', 'mahl-manager' ); ?></option>
					<?php echo $this->get_player_options_markup( $player_options, $player_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</select>
			</td>
			<td>
				<button type="button" class="button-link-delete" data-mahl-remove-row="appearance"><?php esc_html_e( 'Remove', 'mahl-manager' ); ?></button>
			</td>
		</tr>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Return the markup for an event row.
	 *
	 * @param int|string $index Row index.
	 * @param array      $event Event data.
	 * @param array      $team_options Game team options.
	 * @param array      $player_options Player options grouped by team.
	 * @return string
	 */
	protected function get_event_row_markup( $index, $event, $team_options, $player_options ) {
		$index = (string) $index;
		$defaults = array(
			'event_type'        => 'goal',
			'team_id'           => 0,
			'period_number'     => 1,
			'event_time'        => '',
			'player_id'         => 0,
			'assist_1_player_id'=> 0,
			'assist_2_player_id'=> 0,
			'penalty_minutes'   => 0,
			'event_label'       => '',
		);
		$event = wp_parse_args( $event, $defaults );

		ob_start();
		?>
		<tr data-mahl-row="event" data-row-index="<?php echo esc_attr( $index ); ?>">
			<td>
				<select class="widefat" data-mahl-event-type-select name="mahl_game_events[<?php echo esc_attr( $index ); ?>][event_type]">
					<option value="goal" <?php selected( $event['event_type'], 'goal' ); ?>><?php esc_html_e( 'Goal', 'mahl-manager' ); ?></option>
					<option value="penalty" <?php selected( $event['event_type'], 'penalty' ); ?>><?php esc_html_e( 'Penalty', 'mahl-manager' ); ?></option>
				</select>
			</td>
			<td>
				<select class="widefat" data-mahl-team-select name="mahl_game_events[<?php echo esc_attr( $index ); ?>][team_id]">
					<option value=""><?php esc_html_e( 'Select team', 'mahl-manager' ); ?></option>
					<?php foreach ( $team_options as $option_team_id => $team_label ) : ?>
						<option value="<?php echo esc_attr( $option_team_id ); ?>" <?php selected( absint( $event['team_id'] ), $option_team_id ); ?>><?php echo esc_html( $team_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input class="small-text" type="number" min="1" step="1" name="mahl_game_events[<?php echo esc_attr( $index ); ?>][period_number]" value="<?php echo esc_attr( $event['period_number'] ); ?>" />
			</td>
			<td>
				<input class="small-text" type="text" inputmode="numeric" placeholder="<?php echo esc_attr__( '08:32', 'mahl-manager' ); ?>" name="mahl_game_events[<?php echo esc_attr( $index ); ?>][event_time]" value="<?php echo esc_attr( $event['event_time'] ); ?>" />
			</td>
			<td>
				<select class="widefat" data-mahl-player-select="player" name="mahl_game_events[<?php echo esc_attr( $index ); ?>][player_id]">
					<option value=""><?php esc_html_e( 'Select player', 'mahl-manager' ); ?></option>
					<?php echo $this->get_player_options_markup( $player_options, absint( $event['player_id'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</select>
			</td>
			<td>
				<select class="widefat" data-mahl-player-select="assist-1" name="mahl_game_events[<?php echo esc_attr( $index ); ?>][assist_1_player_id]">
					<option value=""><?php esc_html_e( 'Select player', 'mahl-manager' ); ?></option>
					<?php echo $this->get_player_options_markup( $player_options, absint( $event['assist_1_player_id'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</select>
			</td>
			<td>
				<select class="widefat" data-mahl-player-select="assist-2" name="mahl_game_events[<?php echo esc_attr( $index ); ?>][assist_2_player_id]">
					<option value=""><?php esc_html_e( 'Select player', 'mahl-manager' ); ?></option>
					<?php echo $this->get_player_options_markup( $player_options, absint( $event['assist_2_player_id'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</select>
			</td>
			<td>
				<input class="small-text" data-mahl-penalty-minutes type="number" min="0" step="1" name="mahl_game_events[<?php echo esc_attr( $index ); ?>][penalty_minutes]" value="<?php echo esc_attr( $event['penalty_minutes'] ); ?>" />
			</td>
			<td>
				<input class="regular-text" type="text" name="mahl_game_events[<?php echo esc_attr( $index ); ?>][event_label]" value="<?php echo esc_attr( $event['event_label'] ); ?>" />
			</td>
			<td>
				<button type="button" class="button-link-delete" data-mahl-remove-row="event"><?php esc_html_e( 'Remove', 'mahl-manager' ); ?></button>
			</td>
		</tr>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Return player select options grouped by team.
	 *
	 * @param array $player_options Player options grouped by team.
	 * @param int   $selected_player_id Selected player ID.
	 * @return string
	 */
	protected function get_player_options_markup( $player_options, $selected_player_id ) {
		ob_start();

		foreach ( $player_options as $group ) {
			printf(
				'<optgroup label="%s">',
				esc_attr( $group['label'] )
			);

			foreach ( $group['players'] as $player_id => $player_name ) {
				printf(
					'<option value="%1$d" data-mahl-team-id="%2$d" %3$s>%4$s</option>',
					absint( $player_id ),
					absint( $group['team_id'] ),
					selected( $selected_player_id, $player_id, false ),
					esc_html( $player_name )
				);
			}

			echo '</optgroup>';
		}

		return (string) ob_get_clean();
	}

	/**
	 * Return stored appearance rows prepared for the editor.
	 *
	 * @param int $post_id Current game post ID.
	 * @return array
	 */
	protected function get_stored_appearances( $post_id ) {
		$appearances = get_post_meta( $post_id, self::APPEARANCES_META_KEY, true );

		return is_array( $appearances ) ? $appearances : array();
	}

	/**
	 * Return stored event rows prepared for the editor.
	 *
	 * @param int $post_id Current game post ID.
	 * @return array
	 */
	protected function get_stored_events( $post_id ) {
		$stored_events = get_post_meta( $post_id, self::EVENTS_META_KEY, true );
		$editor_events = array();

		if ( ! is_array( $stored_events ) ) {
			return $editor_events;
		}

		foreach ( $stored_events as $event ) {
			if ( ! is_array( $event ) ) {
				continue;
			}

			$editor_event = array(
				'event_type'        => isset( $event['event_type'] ) ? sanitize_key( $event['event_type'] ) : 'goal',
				'team_id'           => isset( $event['team_id'] ) ? absint( $event['team_id'] ) : 0,
				'period_number'     => isset( $event['period_number'] ) ? absint( $event['period_number'] ) : 1,
				'event_time'        => isset( $event['event_time'] ) ? sanitize_text_field( $event['event_time'] ) : '',
				'player_id'         => 0,
				'assist_1_player_id'=> 0,
				'assist_2_player_id'=> 0,
				'penalty_minutes'   => isset( $event['penalty_minutes'] ) ? absint( $event['penalty_minutes'] ) : 0,
				'event_label'       => isset( $event['label'] ) ? sanitize_text_field( $event['label'] ) : ( isset( $event['notes'] ) ? sanitize_text_field( $event['notes'] ) : '' ),
			);

			if ( ! empty( $event['participants'] ) && is_array( $event['participants'] ) ) {
				foreach ( $event['participants'] as $participant ) {
					if ( empty( $participant['player_id'] ) || empty( $participant['role'] ) ) {
						continue;
					}

					$player_id = absint( $participant['player_id'] );
					$role      = sanitize_key( $participant['role'] );

					if ( 'scorer' === $role || 'penalized_player' === $role ) {
						$editor_event['player_id'] = $player_id;
					}

					if ( 'assist' === $role && empty( $editor_event['assist_1_player_id'] ) ) {
						$editor_event['assist_1_player_id'] = $player_id;
					} elseif ( 'assist' === $role && empty( $editor_event['assist_2_player_id'] ) ) {
						$editor_event['assist_2_player_id'] = $player_id;
					}
				}
			}

			$editor_events[] = $editor_event;
		}

		return $editor_events;
	}

	/**
	 * Return the valid team options for the current game.
	 *
	 * @param int $post_id Current game post ID.
	 * @return array
	 */
	protected function get_game_team_options( $post_id ) {
		$options = array();
		$labels  = array(
			'_mahl_home_team_id' => __( 'Home', 'mahl-manager' ),
			'_mahl_away_team_id' => __( 'Away', 'mahl-manager' ),
		);

		foreach ( $labels as $meta_key => $label ) {
			$team_id = absint( get_post_meta( $post_id, $meta_key, true ) );

			if ( empty( $team_id ) || 'mahl_team' !== get_post_type( $team_id ) ) {
				continue;
			}

			$options[ $team_id ] = sprintf(
				/* translators: 1: team side label, 2: team name. */
				__( '%1$s: %2$s', 'mahl-manager' ),
				$label,
				get_the_title( $team_id )
			);
		}

		return $options;
	}

	/**
	 * Return player options grouped by team.
	 *
	 * @param array $team_ids Valid game team IDs.
	 * @return array
	 */
	protected function get_player_options_by_team( $team_ids ) {
		$team_ids = array_filter( array_map( 'absint', $team_ids ) );

		if ( empty( $team_ids ) ) {
			return array();
		}

		$players = get_posts(
			array(
				'post_type'      => 'mahl_player',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_mahl_team_id',
						'value'   => $team_ids,
						'compare' => 'IN',
						'type'    => 'NUMERIC',
					),
				), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		$grouped_players = array();

		foreach ( $team_ids as $team_id ) {
			$grouped_players[ $team_id ] = array(
				'team_id' => $team_id,
				'label'   => get_the_title( $team_id ),
				'players' => array(),
			);
		}

		foreach ( $players as $player ) {
			$team_id = absint( get_post_meta( $player->ID, '_mahl_team_id', true ) );

			if ( isset( $grouped_players[ $team_id ] ) ) {
				$grouped_players[ $team_id ]['players'][ $player->ID ] = $player->post_title;
			}
		}

		return $grouped_players;
	}

	/**
	 * Return the valid game team IDs.
	 *
	 * @param int $post_id Current game post ID.
	 * @return array
	 */
	protected function get_game_team_ids( $post_id ) {
		return array_values(
			array_unique(
				array_filter(
					array(
						absint( get_post_meta( $post_id, '_mahl_home_team_id', true ) ),
						absint( get_post_meta( $post_id, '_mahl_away_team_id', true ) ),
					)
				)
			)
		);
	}

	/**
	 * Sanitize submitted player appearances.
	 *
	 * @param int   $post_id Current game post ID.
	 * @param array $game_team_ids Valid game team IDs.
	 * @return array
	 */
	protected function sanitize_appearances( $post_id, $game_team_ids ) {
		if ( ! isset( $_POST['mahl_player_appearances'] ) || ! is_array( $_POST['mahl_player_appearances'] ) ) {
			return array();
		}

		$submitted_appearances = wp_unslash( $_POST['mahl_player_appearances'] );
		$sanitized_appearances = array();
		$seen_players          = array();

		foreach ( $submitted_appearances as $appearance ) {
			if ( ! is_array( $appearance ) ) {
				continue;
			}

			$team_id   = isset( $appearance['team_id'] ) ? absint( $appearance['team_id'] ) : 0;
			$player_id = isset( $appearance['player_id'] ) ? absint( $appearance['player_id'] ) : 0;

			if ( ! $this->is_valid_game_team( $team_id, $game_team_ids ) ) {
				continue;
			}

			if ( ! $this->is_valid_player_for_team( $player_id, $team_id ) ) {
				continue;
			}

			if ( isset( $seen_players[ $player_id ] ) ) {
				continue;
			}

			$seen_players[ $player_id ] = true;
			$sanitized_appearances[]    = array(
				'team_id'   => $team_id,
				'player_id' => $player_id,
			);
		}

		return $sanitized_appearances;
	}

	/**
	 * Sanitize submitted game events.
	 *
	 * @param int   $post_id Current game post ID.
	 * @param array $game_team_ids Valid game team IDs.
	 * @return array
	 */
	protected function sanitize_events( $post_id, $game_team_ids ) {
		if ( ! isset( $_POST['mahl_game_events'] ) || ! is_array( $_POST['mahl_game_events'] ) ) {
			return array();
		}

		$submitted_events = wp_unslash( $_POST['mahl_game_events'] );
		$sanitized_events = array();

		foreach ( $submitted_events as $index => $event ) {
			if ( ! is_array( $event ) ) {
				continue;
			}

			$event_type      = isset( $event['event_type'] ) ? sanitize_key( $event['event_type'] ) : '';
			$team_id         = isset( $event['team_id'] ) ? absint( $event['team_id'] ) : 0;
			$period_number   = isset( $event['period_number'] ) ? max( 1, absint( $event['period_number'] ) ) : 1;
			$penalty_minutes = isset( $event['penalty_minutes'] ) ? absint( $event['penalty_minutes'] ) : 0;
			$event_time      = isset( $event['event_time'] ) ? $this->sanitize_event_time( $event['event_time'] ) : '';
			$event_label     = isset( $event['event_label'] ) ? sanitize_text_field( $event['event_label'] ) : '';

			if ( ! in_array( $event_type, array( 'goal', 'penalty' ), true ) ) {
				continue;
			}

			if ( ! $this->is_valid_game_team( $team_id, $game_team_ids ) ) {
				continue;
			}

			$player_id         = $this->sanitize_player_reference( $event, 'player_id', $team_id );
			$assist_1_player_id = $this->sanitize_player_reference( $event, 'assist_1_player_id', $team_id );
			$assist_2_player_id = $this->sanitize_player_reference( $event, 'assist_2_player_id', $team_id );

			$normalized_event = array(
				'event_type'      => $event_type,
				'team_id'         => $team_id,
				'period_number'   => $period_number,
				'event_order'     => $index + 1,
				'event_time'      => $event_time,
				'participants'    => array(),
				'penalty_minutes' => 0,
				'label'           => $event_label,
			);

			if ( 'goal' === $event_type ) {
				if ( empty( $player_id ) ) {
					continue;
				}

				$normalized_event['participants'][] = array(
					'role'      => 'scorer',
					'player_id' => $player_id,
				);

				foreach ( array( $assist_1_player_id, $assist_2_player_id ) as $assist_player_id ) {
					if ( empty( $assist_player_id ) || $this->participant_exists( $normalized_event['participants'], $assist_player_id ) ) {
						continue;
					}

					$normalized_event['participants'][] = array(
						'role'      => 'assist',
						'player_id' => $assist_player_id,
					);
				}
			}

			if ( 'penalty' === $event_type ) {
				if ( empty( $player_id ) || $penalty_minutes <= 0 ) {
					continue;
				}

				$normalized_event['participants'][] = array(
					'role'      => 'penalized_player',
					'player_id' => $player_id,
				);
				$normalized_event['penalty_minutes'] = $penalty_minutes;
			}

			if ( empty( $normalized_event['participants'] ) ) {
				continue;
			}

			$sanitized_events[] = $normalized_event;
		}

		return $sanitized_events;
	}

	/**
	 * Sanitize a player reference from submitted event data.
	 *
	 * @param array  $event Submitted event row.
	 * @param string $field_key Field key.
	 * @param int    $team_id Team post ID.
	 * @return int
	 */
	protected function sanitize_player_reference( $event, $field_key, $team_id ) {
		$player_id = isset( $event[ $field_key ] ) ? absint( $event[ $field_key ] ) : 0;

		return $this->is_valid_player_for_team( $player_id, $team_id ) ? $player_id : 0;
	}

	/**
	 * Sanitize an optional event time value.
	 *
	 * @param string $value Raw event time.
	 * @return string
	 */
	protected function sanitize_event_time( $value ) {
		$value = sanitize_text_field( $value );

		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '/^\d{1,2}:\d{2}$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Determine whether a participant already exists in an event.
	 *
	 * @param array $participants Event participants.
	 * @param int   $player_id Player post ID.
	 * @return bool
	 */
	protected function participant_exists( $participants, $player_id ) {
		foreach ( $participants as $participant ) {
			if ( isset( $participant['player_id'] ) && absint( $participant['player_id'] ) === absint( $player_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether a team is valid for the game.
	 *
	 * @param int   $team_id Team post ID.
	 * @param array $game_team_ids Valid game team IDs.
	 * @return bool
	 */
	protected function is_valid_game_team( $team_id, $game_team_ids ) {
		return ! empty( $team_id ) && in_array( $team_id, $game_team_ids, true );
	}

	/**
	 * Determine whether a player belongs to a team.
	 *
	 * @param int $player_id Player post ID.
	 * @param int $team_id Team post ID.
	 * @return bool
	 */
	protected function is_valid_player_for_team( $player_id, $team_id ) {
		if ( empty( $player_id ) || empty( $team_id ) ) {
			return false;
		}

		if ( 'mahl_player' !== get_post_type( $player_id ) ) {
			return false;
		}

		return absint( get_post_meta( $player_id, '_mahl_team_id', true ) ) === $team_id;
	}

	/**
	 * Persist a structured post meta value.
	 *
	 * @param int    $post_id Current post ID.
	 * @param string $meta_key Meta key.
	 * @param array  $value Structured value.
	 * @return void
	 */
	protected function persist_meta_value( $post_id, $meta_key, $value ) {
		if ( empty( $value ) ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		update_post_meta( $post_id, $meta_key, $value );
	}
}
