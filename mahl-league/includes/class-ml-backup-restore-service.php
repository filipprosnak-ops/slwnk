<?php
/**
 * Backup/restore service.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ML_Backup_Restore_Service {

	public static function export_data(): array {
		$data = array(
			'version' => 1,
			'generated_at' => gmdate( 'c' ),
			'terms' => self::collect_terms(),
			'posts' => self::collect_posts(),
			'options' => self::collect_options(),
			'cache' => self::collect_cache(),
		);
		return $data;
	}

	public static function restore_data( array $payload ): array {
		$result = array(
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
			'errors'  => 0,
			'messages' => array(),
		);

		if ( empty( $payload['version'] ) || ! in_array( (int) $payload['version'], array( 1 ), true ) ) {
			$result['errors']     = 1;
			$result['messages'][] = __( 'Unsupported backup format.', 'mahl-league' );
			return $result;
		}

		$term_map = self::restore_terms( isset( $payload['terms'] ) && is_array( $payload['terms'] ) ? $payload['terms'] : array() );
		$post_result = self::restore_posts( isset( $payload['posts'] ) && is_array( $payload['posts'] ) ? $payload['posts'] : array(), $term_map );
		$result['created'] += $post_result['created'];
		$result['updated'] += $post_result['updated'];
		$result['skipped'] += $post_result['skipped'];
		$result['errors']  += $post_result['errors'];
		$result['messages'] = array_merge( $result['messages'], $post_result['messages'] );

		self::restore_options( isset( $payload['options'] ) && is_array( $payload['options'] ) ? $payload['options'] : array() );
		self::restore_cache( isset( $payload['cache'] ) && is_array( $payload['cache'] ) ? $payload['cache'] : array() );

		foreach ( $post_result['pairs'] as $pair_key => $pair ) {
			unset( $pair_key );
			ML_Stats_Engine::recompute_pair( (int) $pair['season_id'], (int) $pair['competition_id'] );
		}

		return $result;
	}

	private static function collect_terms(): array {
		$out = array();
		foreach ( array( 'ml_season', 'ml_competition', 'ml_phase' ) as $taxonomy ) {
			$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
			if ( is_wp_error( $terms ) ) {
				continue;
			}
			foreach ( $terms as $term ) {
				$out[] = array(
					'taxonomy' => $taxonomy,
					'name' => $term->name,
					'slug' => $term->slug,
					'description' => $term->description,
				);
			}
		}
		return $out;
	}

	private static function collect_posts(): array {
		$out = array();
		$posts = get_posts(
			array(
				'post_type' => array( 'ml_team', 'ml_player', 'ml_match' ),
				'post_status' => array( 'publish', 'draft', 'future', 'pending' ),
				'posts_per_page' => -1,
			)
		);
		foreach ( $posts as $post ) {
			$meta_all = get_post_meta( $post->ID );
			$meta     = array();
			foreach ( $meta_all as $key => $values ) {
				if ( 0 !== strpos( $key, 'ml_' ) ) {
					continue;
				}
				$meta[ $key ] = maybe_unserialize( $values[0] );
			}
			$out[] = array(
				'post_type' => $post->post_type,
				'post_title' => $post->post_title,
				'post_name' => $post->post_name,
				'post_status' => $post->post_status,
				'external_id' => (string) get_post_meta( $post->ID, 'ml_external_id', true ),
				'terms' => array(
					'ml_season' => wp_get_post_terms( $post->ID, 'ml_season', array( 'fields' => 'slugs' ) ),
					'ml_competition' => wp_get_post_terms( $post->ID, 'ml_competition', array( 'fields' => 'slugs' ) ),
					'ml_phase' => wp_get_post_terms( $post->ID, 'ml_phase', array( 'fields' => 'slugs' ) ),
				),
				'meta' => $meta,
			);
		}
		return $out;
	}

	private static function collect_options(): array {
		return array(
			'ml_pages_map' => get_option( 'ml_pages_map', array() ),
		);
	}

	private static function collect_cache(): array {
		$cache = array();
		$seasons = get_terms( array( 'taxonomy' => 'ml_season', 'hide_empty' => false ) );
		$comps   = get_terms( array( 'taxonomy' => 'ml_competition', 'hide_empty' => false ) );
		if ( is_wp_error( $seasons ) || is_wp_error( $comps ) ) {
			return $cache;
		}
		foreach ( $seasons as $season ) {
			foreach ( $comps as $competition ) {
				$sid = (int) $season->term_id;
				$cid = (int) $competition->term_id;
				$cache[] = array(
					'season_slug' => $season->slug,
					'competition_slug' => $competition->slug,
					'standings' => ML_Cache::get_standings( $sid, $cid ),
					'stats' => ML_Cache::get_stats( $sid, $cid ),
				);
			}
		}
		return $cache;
	}

	private static function restore_terms( array $terms ): array {
		$map = array();
		foreach ( $terms as $term ) {
			$taxonomy = isset( $term['taxonomy'] ) ? sanitize_key( $term['taxonomy'] ) : '';
			if ( ! in_array( $taxonomy, array( 'ml_season', 'ml_competition', 'ml_phase' ), true ) ) {
				continue;
			}
			$name = isset( $term['name'] ) ? sanitize_text_field( $term['name'] ) : '';
			$slug = isset( $term['slug'] ) ? sanitize_title( $term['slug'] ) : '';
			if ( '' === $name || '' === $slug ) {
				continue;
			}
			$existing = get_term_by( 'slug', $slug, $taxonomy );
			if ( ! $existing instanceof WP_Term ) {
				$created = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
				if ( is_wp_error( $created ) ) {
					continue;
				}
				$term_id = (int) $created['term_id'];
			} else {
				$term_id = (int) $existing->term_id;
			}
			$map[ $taxonomy . ':' . $slug ] = $term_id;
		}
		return $map;
	}

	private static function restore_posts( array $posts, array $term_map ): array {
		$result = array( 'created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0, 'messages' => array(), 'pairs' => array() );
		foreach ( $posts as $item ) {
			$post_type = isset( $item['post_type'] ) ? sanitize_key( $item['post_type'] ) : '';
			if ( ! in_array( $post_type, array( 'ml_team', 'ml_player', 'ml_match' ), true ) ) {
				++$result['skipped'];
				continue;
			}

			$post_id = self::find_existing_post( $post_type, $item );
			$data = array(
				'post_type' => $post_type,
				'post_title' => isset( $item['post_title'] ) ? sanitize_text_field( $item['post_title'] ) : '',
				'post_name' => isset( $item['post_name'] ) ? sanitize_title( $item['post_name'] ) : '',
				'post_status' => isset( $item['post_status'] ) ? sanitize_key( $item['post_status'] ) : 'publish',
			);
			if ( $post_id > 0 ) {
				$data['ID'] = $post_id;
				$post_id = wp_update_post( $data, true );
				++$result['updated'];
			} else {
				$post_id = wp_insert_post( $data, true );
				++$result['created'];
			}

			if ( is_wp_error( $post_id ) || $post_id <= 0 ) {
				++$result['errors'];
				$result['messages'][] = __( 'Failed to restore one post.', 'mahl-league' );
				continue;
			}

			if ( isset( $item['meta'] ) && is_array( $item['meta'] ) ) {
				foreach ( $item['meta'] as $meta_key => $meta_value ) {
					$meta_key = sanitize_key( $meta_key );
					if ( 0 !== strpos( $meta_key, 'ml_' ) ) {
						continue;
					}
					update_post_meta( $post_id, $meta_key, self::sanitize_recursive( $meta_value ) );
				}
			}

			if ( isset( $item['terms'] ) && is_array( $item['terms'] ) ) {
				foreach ( array( 'ml_season', 'ml_competition', 'ml_phase' ) as $taxonomy ) {
					if ( empty( $item['terms'][ $taxonomy ] ) || ! is_array( $item['terms'][ $taxonomy ] ) ) {
						continue;
					}
					$ids = array();
					foreach ( $item['terms'][ $taxonomy ] as $slug ) {
						$slug = sanitize_title( (string) $slug );
						$key  = $taxonomy . ':' . $slug;
						if ( isset( $term_map[ $key ] ) ) {
							$ids[] = (int) $term_map[ $key ];
						}
					}
					if ( ! empty( $ids ) ) {
						wp_set_post_terms( $post_id, $ids, $taxonomy, false );
					}
				}
			}

			if ( 'ml_match' === $post_type ) {
				$pair = self::match_pair( $post_id );
				if ( ! empty( $pair ) ) {
					$key = $pair['season_id'] . ':' . $pair['competition_id'];
					$result['pairs'][ $key ] = $pair;
				}
			}
		}
		return $result;
	}

	private static function restore_options( array $options ): void {
		if ( isset( $options['ml_pages_map'] ) && is_array( $options['ml_pages_map'] ) ) {
			update_option( 'ml_pages_map', self::sanitize_recursive( $options['ml_pages_map'] ) );
		}
	}

	private static function restore_cache( array $cache ): void {
		foreach ( $cache as $item ) {
			$season = isset( $item['season_slug'] ) ? get_term_by( 'slug', sanitize_title( $item['season_slug'] ), 'ml_season' ) : false;
			$comp   = isset( $item['competition_slug'] ) ? get_term_by( 'slug', sanitize_title( $item['competition_slug'] ), 'ml_competition' ) : false;
			if ( ! $season instanceof WP_Term || ! $comp instanceof WP_Term ) {
				continue;
			}
			if ( isset( $item['standings'] ) && is_array( $item['standings'] ) ) {
				ML_Cache::set_standings( (int) $season->term_id, (int) $comp->term_id, self::sanitize_recursive( $item['standings'] ) );
			}
			if ( isset( $item['stats'] ) && is_array( $item['stats'] ) ) {
				ML_Cache::set_stats( (int) $season->term_id, (int) $comp->term_id, self::sanitize_recursive( $item['stats'] ) );
			}
		}
	}

	private static function find_existing_post( string $post_type, array $item ): int {
		$external_id = isset( $item['external_id'] ) ? sanitize_text_field( (string) $item['external_id'] ) : '';
		if ( '' !== $external_id ) {
			$found = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_query' => array( array( 'key' => 'ml_external_id', 'value' => $external_id ) ) ) );
			if ( ! empty( $found ) ) {
				return (int) $found[0];
			}
		}

		$slug = isset( $item['post_name'] ) ? sanitize_title( (string) $item['post_name'] ) : '';
		if ( '' !== $slug ) {
			$post = get_page_by_path( $slug, OBJECT, $post_type );
			if ( $post instanceof WP_Post ) {
				return (int) $post->ID;
			}
		}

		$title = isset( $item['post_title'] ) ? sanitize_text_field( (string) $item['post_title'] ) : '';
		if ( '' !== $title ) {
			$found = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1, 's' => $title ) );
			foreach ( $found as $id ) {
				if ( 0 === strcasecmp( $title, (string) get_the_title( $id ) ) ) {
					return (int) $id;
				}
			}
		}

		return 0;
	}

	private static function match_pair( int $post_id ): array {
		$seasons = wp_get_post_terms( $post_id, 'ml_season', array( 'fields' => 'ids' ) );
		$comps = wp_get_post_terms( $post_id, 'ml_competition', array( 'fields' => 'ids' ) );
		if ( is_wp_error( $seasons ) || is_wp_error( $comps ) || empty( $seasons ) || empty( $comps ) ) {
			return array();
		}
		return array( 'season_id' => (int) $seasons[0], 'competition_id' => (int) $comps[0] );
	}

	private static function sanitize_recursive( $value ) {
		if ( is_array( $value ) ) {
			$sanitized = array();
			foreach ( $value as $key => $item ) {
				$sanitized[ sanitize_key( (string) $key ) ] = self::sanitize_recursive( $item );
			}
			return $sanitized;
		}
		if ( is_scalar( $value ) ) {
			return sanitize_text_field( (string) $value );
		}
		return '';
	}
}
