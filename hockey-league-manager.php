<?php
/**
 * Plugin Name: Hockey League Manager for Blocksy
 * Description: Správa hokejovej ligy: tímy, hráči, zápasy, štatistiky a automatická tabuľka sezóny.
 * Version: 1.0.0
 * Author: Codex
 */

if (!defined('ABSPATH')) {
    exit;
}

final class HLM_Blocksy_Hockey_Manager
{
    private const PLAYER_STATS_META_KEY = '_hlm_player_stats';

    public function __construct()
    {
        add_action('init', [$this, 'register_content_types']);
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post_hl_player', [$this, 'save_player_meta']);
        add_action('save_post_hl_match', [$this, 'save_match_meta']);

        add_filter('manage_hl_match_posts_columns', [$this, 'match_columns']);
        add_action('manage_hl_match_posts_custom_column', [$this, 'render_match_columns'], 10, 2);

        add_shortcode('hockey_table', [$this, 'render_table_shortcode']);
        add_shortcode('hockey_matches', [$this, 'render_matches_shortcode']);
        add_shortcode('hockey_team_card', [$this, 'render_team_card_shortcode']);
        add_shortcode('hockey_player_card', [$this, 'render_player_card_shortcode']);

        add_filter('the_content', [$this, 'append_single_post_cards']);
    }

    public function register_content_types(): void
    {
        register_taxonomy('hl_season', ['hl_team', 'hl_player', 'hl_match'], [
            'labels' => [
                'name' => __('Sezóny', 'hlm'),
                'singular_name' => __('Sezóna', 'hlm'),
            ],
            'public' => true,
            'hierarchical' => false,
            'show_admin_column' => true,
            'show_in_rest' => true,
        ]);

        register_post_type('hl_team', [
            'labels' => [
                'name' => __('Tímy', 'hlm'),
                'singular_name' => __('Tím', 'hlm'),
            ],
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-groups',
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'team'],
        ]);

        register_post_type('hl_player', [
            'labels' => [
                'name' => __('Hráči', 'hlm'),
                'singular_name' => __('Hráč', 'hlm'),
            ],
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-universal-access',
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'player'],
        ]);

        register_post_type('hl_match', [
            'labels' => [
                'name' => __('Zápasy', 'hlm'),
                'singular_name' => __('Zápas', 'hlm'),
            ],
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-awards',
            'show_in_rest' => true,
            'supports' => ['title', 'editor'],
            'rewrite' => ['slug' => 'match'],
        ]);
    }

    public function register_meta_boxes(): void
    {
        add_meta_box('hlm_player_details', __('Detail hráča', 'hlm'), [$this, 'render_player_meta_box'], 'hl_player', 'normal', 'default');
        add_meta_box('hlm_match_details', __('Detail zápasu', 'hlm'), [$this, 'render_match_meta_box'], 'hl_match', 'normal', 'high');
    }

    public function render_player_meta_box(\WP_Post $post): void
    {
        wp_nonce_field('hlm_player_meta', 'hlm_player_meta_nonce');

        $team_id = (int) get_post_meta($post->ID, '_hlm_team_id', true);
        $number = sanitize_text_field((string) get_post_meta($post->ID, '_hlm_player_number', true));
        $position = sanitize_text_field((string) get_post_meta($post->ID, '_hlm_player_position', true));

        $teams = get_posts([
            'post_type' => 'hl_team',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        echo '<p><label><strong>' . esc_html__('Tím', 'hlm') . '</strong></label><br/>';
        echo '<select name="hlm_team_id">';
        echo '<option value="">' . esc_html__('Vyber tím', 'hlm') . '</option>';
        foreach ($teams as $team) {
            printf(
                '<option value="%1$d" %3$s>%2$s</option>',
                (int) $team->ID,
                esc_html($team->post_title),
                selected($team_id, (int) $team->ID, false)
            );
        }
        echo '</select></p>';

        echo '<p><label><strong>' . esc_html__('Číslo hráča', 'hlm') . '</strong></label><br/>';
        echo '<input type="text" name="hlm_player_number" value="' . esc_attr($number) . '" /></p>';

        echo '<p><label><strong>' . esc_html__('Pozícia', 'hlm') . '</strong></label><br/>';
        echo '<input type="text" name="hlm_player_position" value="' . esc_attr($position) . '" placeholder="C, LW, RW, D, G" /></p>';
    }

    public function render_match_meta_box(\WP_Post $post): void
    {
        wp_nonce_field('hlm_match_meta', 'hlm_match_meta_nonce');

        $home_team = (int) get_post_meta($post->ID, '_hlm_home_team', true);
        $away_team = (int) get_post_meta($post->ID, '_hlm_away_team', true);
        $home_score = get_post_meta($post->ID, '_hlm_home_score', true);
        $away_score = get_post_meta($post->ID, '_hlm_away_score', true);
        $round = sanitize_text_field((string) get_post_meta($post->ID, '_hlm_round', true));
        $match_date = sanitize_text_field((string) get_post_meta($post->ID, '_hlm_match_date', true));
        $is_played = (int) get_post_meta($post->ID, '_hlm_is_played', true);
        $player_stats = (string) get_post_meta($post->ID, self::PLAYER_STATS_META_KEY, true);

        $teams = get_posts([
            'post_type' => 'hl_team',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $render_select = static function (string $name, int $selected_id) use ($teams): void {
            echo '<select name="' . esc_attr($name) . '">';
            echo '<option value="">' . esc_html__('Vyber tím', 'hlm') . '</option>';
            foreach ($teams as $team) {
                printf(
                    '<option value="%1$d" %3$s>%2$s</option>',
                    (int) $team->ID,
                    esc_html($team->post_title),
                    selected($selected_id, (int) $team->ID, false)
                );
            }
            echo '</select>';
        };

        echo '<p><label><strong>' . esc_html__('Domáci tím', 'hlm') . '</strong></label><br/>';
        $render_select('hlm_home_team', $home_team);
        echo '</p>';

        echo '<p><label><strong>' . esc_html__('Hosťujúci tím', 'hlm') . '</strong></label><br/>';
        $render_select('hlm_away_team', $away_team);
        echo '</p>';

        echo '<p><label><strong>' . esc_html__('Kolo', 'hlm') . '</strong></label><br/>';
        echo '<input type="text" name="hlm_round" value="' . esc_attr($round) . '" placeholder="1" /></p>';

        echo '<p><label><strong>' . esc_html__('Dátum zápasu', 'hlm') . '</strong></label><br/>';
        echo '<input type="datetime-local" name="hlm_match_date" value="' . esc_attr($match_date) . '" /></p>';

        echo '<p><label><strong>' . esc_html__('Skóre domáci', 'hlm') . '</strong></label><br/>';
        echo '<input type="number" min="0" name="hlm_home_score" value="' . esc_attr((string) $home_score) . '" /></p>';

        echo '<p><label><strong>' . esc_html__('Skóre hostia', 'hlm') . '</strong></label><br/>';
        echo '<input type="number" min="0" name="hlm_away_score" value="' . esc_attr((string) $away_score) . '" /></p>';

        echo '<p><label>';
        echo '<input type="checkbox" name="hlm_is_played" value="1" ' . checked(1, $is_played, false) . ' /> ';
        echo esc_html__('Zápas odohraný', 'hlm');
        echo '</label></p>';

        echo '<p><label><strong>' . esc_html__('Štatistiky hráčov (1 riadok = player_id;goals;assists;penalty_minutes)', 'hlm') . '</strong></label><br/>';
        echo '<textarea name="hlm_player_stats" rows="8" style="width:100%;">' . esc_textarea($player_stats) . '</textarea></p>';
    }

    public function save_player_meta(int $post_id): void
    {
        if (!$this->can_save_post('hlm_player_meta_nonce', 'hlm_player_meta')) {
            return;
        }

        $team_id = isset($_POST['hlm_team_id']) ? (int) $_POST['hlm_team_id'] : 0;
        $number = isset($_POST['hlm_player_number']) ? sanitize_text_field(wp_unslash($_POST['hlm_player_number'])) : '';
        $position = isset($_POST['hlm_player_position']) ? sanitize_text_field(wp_unslash($_POST['hlm_player_position'])) : '';

        update_post_meta($post_id, '_hlm_team_id', $team_id);
        update_post_meta($post_id, '_hlm_player_number', $number);
        update_post_meta($post_id, '_hlm_player_position', $position);
    }

    public function save_match_meta(int $post_id): void
    {
        if (!$this->can_save_post('hlm_match_meta_nonce', 'hlm_match_meta')) {
            return;
        }

        $home_team = isset($_POST['hlm_home_team']) ? (int) $_POST['hlm_home_team'] : 0;
        $away_team = isset($_POST['hlm_away_team']) ? (int) $_POST['hlm_away_team'] : 0;
        $home_score = isset($_POST['hlm_home_score']) && $_POST['hlm_home_score'] !== '' ? (int) $_POST['hlm_home_score'] : '';
        $away_score = isset($_POST['hlm_away_score']) && $_POST['hlm_away_score'] !== '' ? (int) $_POST['hlm_away_score'] : '';
        $round = isset($_POST['hlm_round']) ? sanitize_text_field(wp_unslash($_POST['hlm_round'])) : '';
        $match_date = isset($_POST['hlm_match_date']) ? sanitize_text_field(wp_unslash($_POST['hlm_match_date'])) : '';
        $is_played = isset($_POST['hlm_is_played']) ? 1 : 0;
        $player_stats = isset($_POST['hlm_player_stats']) ? sanitize_textarea_field(wp_unslash($_POST['hlm_player_stats'])) : '';

        update_post_meta($post_id, '_hlm_home_team', $home_team);
        update_post_meta($post_id, '_hlm_away_team', $away_team);
        update_post_meta($post_id, '_hlm_home_score', $home_score);
        update_post_meta($post_id, '_hlm_away_score', $away_score);
        update_post_meta($post_id, '_hlm_round', $round);
        update_post_meta($post_id, '_hlm_match_date', $match_date);
        update_post_meta($post_id, '_hlm_is_played', $is_played);
        update_post_meta($post_id, self::PLAYER_STATS_META_KEY, $player_stats);

        $this->flush_table_cache();
    }

    public function match_columns(array $columns): array
    {
        $columns['hlm_score'] = __('Skóre', 'hlm');
        $columns['hlm_round'] = __('Kolo', 'hlm');
        return $columns;
    }

    public function render_match_columns(string $column, int $post_id): void
    {
        if ($column === 'hlm_score') {
            $home_score = get_post_meta($post_id, '_hlm_home_score', true);
            $away_score = get_post_meta($post_id, '_hlm_away_score', true);
            if ($home_score === '' || $away_score === '') {
                echo '—';
                return;
            }
            echo esc_html($home_score . ':' . $away_score);
        }

        if ($column === 'hlm_round') {
            echo esc_html((string) get_post_meta($post_id, '_hlm_round', true));
        }
    }

    public function render_table_shortcode(array $atts): string
    {
        $atts = shortcode_atts(['season' => ''], $atts);
        $season_term = $this->resolve_season($atts['season']);
        $season_key = $season_term ? (string) $season_term->term_id : 'all';
        $table = $this->get_table($season_term ? (int) $season_term->term_id : null);

        if ($table === []) {
            return '<p>' . esc_html__('Zatiaľ nie sú dostupné žiadne odohrané zápasy.', 'hlm') . '</p>';
        }

        ob_start();
        ?>
        <table class="hlm-table" data-season="<?php echo esc_attr($season_key); ?>">
            <thead>
            <tr>
                <th>#</th><th><?php esc_html_e('Tím', 'hlm'); ?></th><th>Z</th><th>V</th><th>P</th><th>GF</th><th>GA</th><th>B</th>
            </tr>
            </thead>
            <tbody>
            <?php $rank = 1; foreach ($table as $row): ?>
                <tr>
                    <td><?php echo (int) $rank++; ?></td>
                    <td><a href="<?php echo esc_url(get_permalink((int) $row['team_id'])); ?>"><?php echo esc_html($row['team_name']); ?></a></td>
                    <td><?php echo (int) $row['played']; ?></td>
                    <td><?php echo (int) $row['wins']; ?></td>
                    <td><?php echo (int) $row['losses']; ?></td>
                    <td><?php echo (int) $row['gf']; ?></td>
                    <td><?php echo (int) $row['ga']; ?></td>
                    <td><strong><?php echo (int) $row['points']; ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return (string) ob_get_clean();
    }

    public function render_matches_shortcode(array $atts): string
    {
        $atts = shortcode_atts(['season' => '', 'limit' => 100], $atts);
        $season_term = $this->resolve_season($atts['season']);

        $query = [
            'post_type' => 'hl_match',
            'post_status' => 'publish',
            'posts_per_page' => (int) $atts['limit'],
            'meta_key' => '_hlm_match_date',
            'orderby' => 'meta_value',
            'order' => 'DESC',
        ];

        if ($season_term) {
            $query['tax_query'] = [[
                'taxonomy' => 'hl_season',
                'field' => 'term_id',
                'terms' => [(int) $season_term->term_id],
            ]];
        }

        $matches = get_posts($query);
        if ($matches === []) {
            return '<p>' . esc_html__('Žiadne zápasy.', 'hlm') . '</p>';
        }

        $out = '<ul class="hlm-matches">';
        foreach ($matches as $match) {
            $home_team = (int) get_post_meta($match->ID, '_hlm_home_team', true);
            $away_team = (int) get_post_meta($match->ID, '_hlm_away_team', true);
            $home_score = get_post_meta($match->ID, '_hlm_home_score', true);
            $away_score = get_post_meta($match->ID, '_hlm_away_score', true);
            $round = get_post_meta($match->ID, '_hlm_round', true);

            $out .= '<li>';
            $out .= '<a href="' . esc_url(get_permalink($match->ID)) . '"><strong>' . esc_html(get_the_title($home_team)) . ' vs ' . esc_html(get_the_title($away_team)) . '</strong></a>';
            if ($home_score !== '' && $away_score !== '') {
                $out .= ' — ' . esc_html($home_score . ':' . $away_score);
            }
            if ($round !== '') {
                $out .= ' (' . esc_html__('kolo', 'hlm') . ' ' . esc_html((string) $round) . ')';
            }
            $out .= '</li>';
        }
        $out .= '</ul>';

        return $out;
    }

    public function render_team_card_shortcode(array $atts): string
    {
        $atts = shortcode_atts(['id' => get_the_ID()], $atts);
        $team_id = (int) $atts['id'];

        if (get_post_type($team_id) !== 'hl_team') {
            return '';
        }

        $players = get_posts([
            'post_type' => 'hl_player',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_key' => '_hlm_team_id',
            'meta_value' => $team_id,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $table = $this->get_table();
        $team_row = null;
        foreach ($table as $row) {
            if ((int) $row['team_id'] === $team_id) {
                $team_row = $row;
                break;
            }
        }

        ob_start();
        echo '<div class="hlm-team-card">';
        echo '<h3>' . esc_html(get_the_title($team_id)) . '</h3>';
        if ($team_row) {
            echo '<p>' . esc_html__('Zápasy', 'hlm') . ': ' . (int) $team_row['played'] . ' | ';
            echo esc_html__('Body', 'hlm') . ': ' . (int) $team_row['points'] . '</p>';
        }

        echo '<h4>' . esc_html__('Káder', 'hlm') . '</h4><ul>';
        foreach ($players as $player) {
            $number = (string) get_post_meta($player->ID, '_hlm_player_number', true);
            $position = (string) get_post_meta($player->ID, '_hlm_player_position', true);
            echo '<li><a href="' . esc_url(get_permalink($player->ID)) . '">' . esc_html($player->post_title) . '</a>';
            if ($number !== '') {
                echo ' #' . esc_html($number);
            }
            if ($position !== '') {
                echo ' (' . esc_html($position) . ')';
            }
            echo '</li>';
        }
        echo '</ul></div>';

        return (string) ob_get_clean();
    }

    public function render_player_card_shortcode(array $atts): string
    {
        $atts = shortcode_atts(['id' => get_the_ID()], $atts);
        $player_id = (int) $atts['id'];

        if (get_post_type($player_id) !== 'hl_player') {
            return '';
        }

        $team_id = (int) get_post_meta($player_id, '_hlm_team_id', true);
        $stats = $this->get_player_totals($player_id);

        ob_start();
        echo '<div class="hlm-player-card">';
        echo '<h3>' . esc_html(get_the_title($player_id)) . '</h3>';
        if ($team_id > 0) {
            echo '<p>' . esc_html__('Tím:', 'hlm') . ' <a href="' . esc_url(get_permalink($team_id)) . '">' . esc_html(get_the_title($team_id)) . '</a></p>';
        }
        echo '<p>G: ' . (int) $stats['goals'] . ' | A: ' . (int) $stats['assists'] . ' | PIM: ' . (int) $stats['pim'] . '</p>';
        echo '</div>';

        return (string) ob_get_clean();
    }

    public function append_single_post_cards(string $content): string
    {
        if (!is_singular() || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        if (get_post_type() === 'hl_team') {
            return $content . do_shortcode('[hockey_team_card]');
        }

        if (get_post_type() === 'hl_player') {
            return $content . do_shortcode('[hockey_player_card]');
        }

        if (get_post_type() === 'hl_match') {
            $home_team = (int) get_post_meta(get_the_ID(), '_hlm_home_team', true);
            $away_team = (int) get_post_meta(get_the_ID(), '_hlm_away_team', true);
            $home_score = get_post_meta(get_the_ID(), '_hlm_home_score', true);
            $away_score = get_post_meta(get_the_ID(), '_hlm_away_score', true);

            $extra = '<div class="hlm-match-card"><h3>' . esc_html__('Detail zápasu', 'hlm') . '</h3>';
            $extra .= '<p><a href="' . esc_url(get_permalink($home_team)) . '">' . esc_html(get_the_title($home_team)) . '</a> vs <a href="' . esc_url(get_permalink($away_team)) . '">' . esc_html(get_the_title($away_team)) . '</a>';
            if ($home_score !== '' && $away_score !== '') {
                $extra .= ' — <strong>' . esc_html($home_score . ':' . $away_score) . '</strong>';
            }
            $extra .= '</p></div>';
            return $content . $extra;
        }

        return $content;
    }

    private function can_save_post(string $nonce_key, string $nonce_action): bool
    {
        if (!isset($_POST[$nonce_key])) {
            return false;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_key])), $nonce_action)) {
            return false;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        return current_user_can('edit_post', (int) ($_POST['post_ID'] ?? 0));
    }

    private function resolve_season(string $season): ?\WP_Term
    {
        if ($season === '') {
            return null;
        }

        if (is_numeric($season)) {
            $term = get_term((int) $season, 'hl_season');
            return $term instanceof \WP_Term ? $term : null;
        }

        $term = get_term_by('slug', sanitize_title($season), 'hl_season');
        return $term instanceof \WP_Term ? $term : null;
    }

    private function get_table(?int $season_term_id = null): array
    {
        $cache_key = 'hlm_table_' . ($season_term_id ?: 'all');
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $query = [
            'post_type' => 'hl_match',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => '_hlm_is_played',
                'value' => 1,
                'compare' => '=',
                'type' => 'NUMERIC',
            ]],
        ];

        if ($season_term_id) {
            $query['tax_query'] = [[
                'taxonomy' => 'hl_season',
                'field' => 'term_id',
                'terms' => [$season_term_id],
            ]];
        }

        $matches = get_posts($query);
        $table = [];

        foreach ($matches as $match) {
            $home_team = (int) get_post_meta($match->ID, '_hlm_home_team', true);
            $away_team = (int) get_post_meta($match->ID, '_hlm_away_team', true);
            $home_score = get_post_meta($match->ID, '_hlm_home_score', true);
            $away_score = get_post_meta($match->ID, '_hlm_away_score', true);

            if ($home_team <= 0 || $away_team <= 0 || $home_score === '' || $away_score === '') {
                continue;
            }

            $home_score = (int) $home_score;
            $away_score = (int) $away_score;

            if (!isset($table[$home_team])) {
                $table[$home_team] = $this->empty_row($home_team);
            }
            if (!isset($table[$away_team])) {
                $table[$away_team] = $this->empty_row($away_team);
            }

            $table[$home_team]['played']++;
            $table[$away_team]['played']++;
            $table[$home_team]['gf'] += $home_score;
            $table[$home_team]['ga'] += $away_score;
            $table[$away_team]['gf'] += $away_score;
            $table[$away_team]['ga'] += $home_score;

            if ($home_score > $away_score) {
                $table[$home_team]['wins']++;
                $table[$home_team]['points'] += 3;
                $table[$away_team]['losses']++;
            } elseif ($home_score < $away_score) {
                $table[$away_team]['wins']++;
                $table[$away_team]['points'] += 3;
                $table[$home_team]['losses']++;
            }
        }

        usort($table, static function (array $a, array $b): int {
            if ($a['points'] === $b['points']) {
                $goal_diff_a = $a['gf'] - $a['ga'];
                $goal_diff_b = $b['gf'] - $b['ga'];
                return $goal_diff_b <=> $goal_diff_a;
            }

            return $b['points'] <=> $a['points'];
        });

        set_transient($cache_key, $table, HOUR_IN_SECONDS);
        return $table;
    }

    private function empty_row(int $team_id): array
    {
        return [
            'team_id' => $team_id,
            'team_name' => get_the_title($team_id),
            'played' => 0,
            'wins' => 0,
            'losses' => 0,
            'gf' => 0,
            'ga' => 0,
            'points' => 0,
        ];
    }

    private function flush_table_cache(): void
    {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_hlm_table_%' OR option_name LIKE '_transient_timeout_hlm_table_%'"
        );
    }

    private function get_player_totals(int $player_id): array
    {
        $matches = get_posts([
            'post_type' => 'hl_match',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => '_hlm_is_played',
                'value' => 1,
                'compare' => '=',
                'type' => 'NUMERIC',
            ]],
        ]);

        $totals = ['goals' => 0, 'assists' => 0, 'pim' => 0];
        foreach ($matches as $match) {
            $raw = (string) get_post_meta($match->ID, self::PLAYER_STATS_META_KEY, true);
            if ($raw === '') {
                continue;
            }

            $lines = preg_split('/\r\n|\r|\n/', trim($raw));
            if (!is_array($lines)) {
                continue;
            }

            foreach ($lines as $line) {
                $parts = array_map('trim', explode(';', $line));
                if (count($parts) < 4) {
                    continue;
                }

                if ((int) $parts[0] !== $player_id) {
                    continue;
                }

                $totals['goals'] += (int) $parts[1];
                $totals['assists'] += (int) $parts[2];
                $totals['pim'] += (int) $parts[3];
            }
        }

        return $totals;
    }
}

new HLM_Blocksy_Hockey_Manager();
