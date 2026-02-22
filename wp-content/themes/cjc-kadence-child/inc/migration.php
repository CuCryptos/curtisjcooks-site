<?php
/**
 * Divi Shortcode Cleanup — WP-CLI Command
 *
 * Strips leftover Divi/ET shortcodes from post content after migrating
 * away from the Divi theme.  Run once, verify, then remove this file.
 *
 * Usage:
 *   wp cjc-migrate strip-divi --dry-run   (preview changes)
 *   wp cjc-migrate strip-divi             (apply changes)
 *
 * @package CJC_Kadence_Child
 */

defined('ABSPATH') || exit;

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * CJC Migration helpers for the Kadence child theme.
 */
class CJC_Migrate_CLI {

    /**
     * Strip Divi shortcodes from all published posts.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Preview changes without saving.
     *
     * [--post-type=<type>]
     * : Post type to process. Default: post.
     *
     * [--limit=<number>]
     * : Maximum number of posts to process. Default: all.
     *
     * ## EXAMPLES
     *
     *   wp cjc-migrate strip-divi --dry-run
     *   wp cjc-migrate strip-divi --post-type=page
     *   wp cjc-migrate strip-divi --limit=50
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Named arguments.
     */
    public function strip_divi($args, $assoc_args) {
        $dry_run   = isset($assoc_args['dry-run']);
        $post_type = $assoc_args['post-type'] ?? 'post';
        $limit     = isset($assoc_args['limit']) ? (int) $assoc_args['limit'] : -1;

        // Divi shortcode pattern — matches [et_pb_*]...[/et_pb_*] and self-closing [et_pb_* /]
        $pattern = '/\[\/?(et_pb_[a-z0-9_]+)[^\]]*\]/i';

        $query_args = [
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ];

        $post_ids = get_posts($query_args);

        if (empty($post_ids)) {
            WP_CLI::warning("No published {$post_type} posts found.");
            return;
        }

        $total   = count($post_ids);
        $updated = 0;
        $skipped = 0;

        WP_CLI::log(sprintf(
            '%s %d %s(s) for Divi shortcodes…',
            $dry_run ? 'Scanning' : 'Processing',
            $total,
            $post_type
        ));

        $progress = \WP_CLI\Utils\make_progress_bar('Stripping Divi shortcodes', $total);

        foreach ($post_ids as $post_id) {
            $content = get_post_field('post_content', $post_id, 'raw');

            if (!preg_match($pattern, $content)) {
                $skipped++;
                $progress->tick();
                continue;
            }

            // Count matches for reporting
            preg_match_all($pattern, $content, $matches);
            $match_count = count($matches[0]);

            // Strip shortcodes
            $clean = preg_replace($pattern, '', $content);

            // Collapse excessive blank lines (3+ newlines → 2)
            $clean = preg_replace('/\n{3,}/', "\n\n", $clean);
            $clean = trim($clean);

            if ($dry_run) {
                WP_CLI::log(sprintf(
                    '  [DRY RUN] Post %d (%s): would remove %d shortcode tag(s)',
                    $post_id,
                    get_the_title($post_id),
                    $match_count
                ));
            } else {
                wp_update_post([
                    'ID'           => $post_id,
                    'post_content' => $clean,
                ]);
                WP_CLI::log(sprintf(
                    '  Updated Post %d (%s): removed %d shortcode tag(s)',
                    $post_id,
                    get_the_title($post_id),
                    $match_count
                ));
            }

            $updated++;
            $progress->tick();
        }

        $progress->finish();

        WP_CLI::success(sprintf(
            'Done. %d post(s) %s, %d skipped (no Divi shortcodes).',
            $updated,
            $dry_run ? 'would be updated' : 'updated',
            $skipped
        ));
    }
}

WP_CLI::add_command('cjc-migrate', 'CJC_Migrate_CLI');
