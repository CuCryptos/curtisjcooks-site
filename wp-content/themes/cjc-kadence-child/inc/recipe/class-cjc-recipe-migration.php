<?php
/**
 * CJC Recipe Migration from Tasty Recipes
 *
 * @package CJC_Recipe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles migration from Tasty Recipes plugin.
 */
class CJC_Recipe_Migration {

    /**
     * Initialize migration hooks.
     */
    public static function init() {
        // Add WP-CLI command if available
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'cjc-recipe', array( __CLASS__, 'cli_command' ) );
        }

        // Admin page for migration
        add_action( 'admin_menu', array( __CLASS__, 'add_migration_page' ) );
        add_action( 'admin_post_cjc_recipe_migrate', array( __CLASS__, 'handle_migration_request' ) );

        // AJAX handlers for batch migration
        add_action( 'wp_ajax_cjc_migrate_batch', array( __CLASS__, 'ajax_migrate_batch' ) );
        add_action( 'wp_ajax_cjc_convert_posts_batch', array( __CLASS__, 'ajax_convert_posts_batch' ) );
    }

    /**
     * AJAX handler for batch migration.
     */
    public static function ajax_migrate_batch() {
        check_ajax_referer( 'cjc_migrate_batch', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        $dry_run = ! empty( $_POST['dry_run'] );
        $update_content = ! empty( $_POST['update_content'] );
        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
        $batch_size = 5; // Process 5 recipes at a time

        // Get batch of Tasty Recipes
        $tasty_recipes = get_posts( array(
            'post_type'      => 'tasty_recipe',
            'post_status'    => 'any',
            'posts_per_page' => $batch_size,
            'offset'         => $offset,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ) );

        $results = array(
            'success' => array(),
            'failed'  => array(),
            'skipped' => array(),
        );

        foreach ( $tasty_recipes as $tasty_post ) {
            try {
                $result = self::migrate_single( $tasty_post->ID, $dry_run, $update_content );

                if ( $result['status'] === 'success' ) {
                    $results['success'][] = $result;
                } elseif ( $result['status'] === 'skipped' ) {
                    $results['skipped'][] = $result;
                } else {
                    $results['failed'][] = $result;
                }
            } catch ( Exception $e ) {
                $results['failed'][] = array(
                    'tasty_id' => $tasty_post->ID,
                    'status'   => 'failed',
                    'message'  => $e->getMessage(),
                );
            }
        }

        $total = self::count_tasty_recipes();
        $processed = $offset + count( $tasty_recipes );

        wp_send_json_success( array(
            'results'   => $results,
            'processed' => $processed,
            'total'     => $total,
            'done'      => $processed >= $total,
        ) );
    }

    /**
     * Add migration page to admin menu.
     */
    public static function add_migration_page() {
        add_submenu_page(
            'edit.php?post_type=' . CJC_Recipe_Post_Type::$post_type,
            __( 'Migrate from Tasty Recipes', 'suspended-flavor-child' ),
            __( 'Migration', 'suspended-flavor-child' ),
            'manage_options',
            'cjc-recipe-migration',
            array( __CLASS__, 'render_migration_page' )
        );
    }

    /**
     * Render the migration admin page.
     */
    public static function render_migration_page() {
        $tasty_count = self::count_tasty_recipes();
        $migrated_count = self::count_migrated_recipes();

        // Check for migration results
        $results = get_transient( 'cjc_migration_results' );
        if ( $results ) {
            delete_transient( 'cjc_migration_results' );
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Migrate from Tasty Recipes', 'suspended-flavor-child' ); ?></h1>

            <?php if ( $results ) : ?>
            <div class="notice notice-info">
                <h3><?php echo $results['dry_run'] ? 'Dry Run Results' : 'Migration Results'; ?></h3>
                <p><strong>Successful:</strong> <?php echo count( $results['success'] ); ?></p>
                <p><strong>Skipped:</strong> <?php echo count( $results['skipped'] ); ?></p>
                <p><strong>Failed:</strong> <?php echo count( $results['failed'] ); ?></p>
                <?php if ( ! empty( $results['failed'] ) ) : ?>
                    <p><strong>Failures:</strong></p>
                    <ul>
                    <?php foreach ( $results['failed'] as $fail ) : ?>
                        <li>Tasty ID <?php echo $fail['tasty_id']; ?>: <?php echo esc_html( $fail['message'] ); ?></li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <h2><?php esc_html_e( 'Migration Status', 'suspended-flavor-child' ); ?></h2>
                <p>
                    <strong><?php esc_html_e( 'Tasty Recipes found:', 'suspended-flavor-child' ); ?></strong>
                    <?php echo intval( $tasty_count ); ?>
                </p>
                <p>
                    <strong><?php esc_html_e( 'Already migrated:', 'suspended-flavor-child' ); ?></strong>
                    <?php echo intval( $migrated_count ); ?>
                </p>
                <p>
                    <strong><?php esc_html_e( 'Remaining:', 'suspended-flavor-child' ); ?></strong>
                    <?php echo max( 0, $tasty_count - $migrated_count ); ?>
                </p>
            </div>

            <?php if ( $tasty_count > 0 ) : ?>
            <div class="card">
                <h2><?php esc_html_e( 'Run Migration', 'suspended-flavor-child' ); ?></h2>
                <p><?php esc_html_e( 'This will copy all Tasty Recipes to CJC Recipes and update post content to use the new blocks.', 'suspended-flavor-child' ); ?></p>

                <p>
                    <label>
                        <input type="checkbox" id="cjc-dry-run" checked />
                        <?php esc_html_e( 'Dry run (preview changes without saving)', 'suspended-flavor-child' ); ?>
                    </label>
                </p>

                <p>
                    <label>
                        <input type="checkbox" id="cjc-update-content" checked />
                        <?php esc_html_e( 'Update post content to replace Tasty Recipe blocks', 'suspended-flavor-child' ); ?>
                    </label>
                </p>

                <p>
                    <button type="button" id="cjc-start-migration" class="button button-primary">
                        <?php esc_html_e( 'Run Migration', 'suspended-flavor-child' ); ?>
                    </button>
                </p>

                <div id="cjc-migration-progress" style="display: none; margin-top: 20px;">
                    <div style="background: #e0e0e0; border-radius: 4px; height: 24px; overflow: hidden;">
                        <div id="cjc-progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s;"></div>
                    </div>
                    <p id="cjc-progress-text">Starting...</p>
                </div>

                <div id="cjc-migration-log" style="display: none; margin-top: 20px; max-height: 300px; overflow-y: auto; background: #f5f5f5; padding: 10px; font-family: monospace; font-size: 12px;"></div>
            </div>

            <script>
            jQuery(document).ready(function($) {
                var offset = 0;
                var successCount = 0;
                var failedCount = 0;
                var skippedCount = 0;

                function log(message, type) {
                    var color = type === 'error' ? '#dc3232' : (type === 'success' ? '#46b450' : '#666');
                    $('#cjc-migration-log').show().append('<div style="color: ' + color + '">' + message + '</div>');
                    $('#cjc-migration-log').scrollTop($('#cjc-migration-log')[0].scrollHeight);
                }

                function runBatch() {
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'cjc_migrate_batch',
                            nonce: '<?php echo wp_create_nonce( 'cjc_migrate_batch' ); ?>',
                            dry_run: $('#cjc-dry-run').is(':checked') ? 1 : 0,
                            update_content: $('#cjc-update-content').is(':checked') ? 1 : 0,
                            offset: offset
                        },
                        success: function(response) {
                            if (!response.success) {
                                log('Error: ' + response.data, 'error');
                                $('#cjc-start-migration').prop('disabled', false).text('Run Migration');
                                return;
                            }

                            var data = response.data;

                            // Update counts
                            successCount += data.results.success.length;
                            failedCount += data.results.failed.length;
                            skippedCount += data.results.skipped.length;

                            // Log results
                            data.results.success.forEach(function(r) {
                                log('✓ ' + (r.title || 'Recipe ' + r.tasty_id), 'success');
                            });
                            data.results.skipped.forEach(function(r) {
                                log('⊘ Skipped: ' + r.message + ' (ID: ' + r.tasty_id + ')', 'info');
                            });
                            data.results.failed.forEach(function(r) {
                                log('✗ Failed: ' + r.message + ' (ID: ' + r.tasty_id + ')', 'error');
                            });

                            // Update progress
                            var percent = Math.round((data.processed / data.total) * 100);
                            $('#cjc-progress-bar').css('width', percent + '%');
                            $('#cjc-progress-text').text(data.processed + ' / ' + data.total + ' recipes processed');

                            if (data.done) {
                                var isDryRun = $('#cjc-dry-run').is(':checked');
                                log('', 'info');
                                log('=== ' + (isDryRun ? 'DRY RUN ' : '') + 'COMPLETE ===', 'success');
                                log('Successful: ' + successCount, 'success');
                                log('Skipped: ' + skippedCount, 'info');
                                log('Failed: ' + failedCount, failedCount > 0 ? 'error' : 'info');
                                $('#cjc-start-migration').prop('disabled', false).text('Run Migration');

                                if (!isDryRun) {
                                    location.reload();
                                }
                            } else {
                                offset = data.processed;
                                runBatch();
                            }
                        },
                        error: function(xhr, status, error) {
                            log('AJAX Error: ' + error, 'error');
                            $('#cjc-start-migration').prop('disabled', false).text('Run Migration');
                        }
                    });
                }

                $('#cjc-start-migration').on('click', function() {
                    offset = 0;
                    successCount = 0;
                    failedCount = 0;
                    skippedCount = 0;

                    $(this).prop('disabled', true).text('Migrating...');
                    $('#cjc-migration-progress').show();
                    $('#cjc-migration-log').empty().show();
                    $('#cjc-progress-bar').css('width', '0%');

                    var isDryRun = $('#cjc-dry-run').is(':checked');
                    log('Starting ' + (isDryRun ? 'DRY RUN' : 'MIGRATION') + '...', 'info');
                    log('', 'info');

                    runBatch();
                });
            });
            </script>
            <?php endif; ?>

            <div class="card">
                <h2><?php esc_html_e( 'Convert Regular Posts to Recipes', 'suspended-flavor-child' ); ?></h2>
                <p><?php esc_html_e( 'Scan posts in recipe categories and convert them to CJC Recipes by parsing the content.', 'suspended-flavor-child' ); ?></p>

                <?php
                $recipe_categories = self::get_recipe_category_slugs();
                $convertible_count = self::count_convertible_posts();
                ?>

                <p>
                    <strong><?php esc_html_e( 'Recipe categories:', 'suspended-flavor-child' ); ?></strong>
                    <?php echo esc_html( implode( ', ', $recipe_categories ) ); ?>
                </p>
                <p>
                    <strong><?php esc_html_e( 'Posts to convert:', 'suspended-flavor-child' ); ?></strong>
                    <?php echo intval( $convertible_count ); ?>
                </p>

                <?php if ( $convertible_count > 0 ) : ?>
                <p>
                    <label>
                        <input type="checkbox" id="cjc-convert-dry-run" checked />
                        <?php esc_html_e( 'Dry run (preview without saving)', 'suspended-flavor-child' ); ?>
                    </label>
                </p>

                <p>
                    <button type="button" id="cjc-start-convert" class="button button-primary">
                        <?php esc_html_e( 'Convert Posts', 'suspended-flavor-child' ); ?>
                    </button>
                </p>

                <div id="cjc-convert-progress" style="display: none; margin-top: 20px;">
                    <div style="background: #e0e0e0; border-radius: 4px; height: 24px; overflow: hidden;">
                        <div id="cjc-convert-bar" style="background: #46b450; height: 100%; width: 0%; transition: width 0.3s;"></div>
                    </div>
                    <p id="cjc-convert-text">Starting...</p>
                </div>

                <div id="cjc-convert-log" style="display: none; margin-top: 20px; max-height: 300px; overflow-y: auto; background: #f5f5f5; padding: 10px; font-family: monospace; font-size: 12px;"></div>

                <script>
                jQuery(document).ready(function($) {
                    var convertOffset = 0;
                    var convertSuccess = 0;
                    var convertFailed = 0;
                    var convertSkipped = 0;

                    function convertLog(message, type) {
                        var color = type === 'error' ? '#dc3232' : (type === 'success' ? '#46b450' : '#666');
                        $('#cjc-convert-log').show().append('<div style="color: ' + color + '">' + message + '</div>');
                        $('#cjc-convert-log').scrollTop($('#cjc-convert-log')[0].scrollHeight);
                    }

                    function runConvertBatch() {
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                action: 'cjc_convert_posts_batch',
                                nonce: '<?php echo wp_create_nonce( 'cjc_convert_posts' ); ?>',
                                dry_run: $('#cjc-convert-dry-run').is(':checked') ? 1 : 0,
                                offset: convertOffset
                            },
                            success: function(response) {
                                if (!response.success) {
                                    convertLog('Error: ' + response.data, 'error');
                                    $('#cjc-start-convert').prop('disabled', false).text('Convert Posts');
                                    return;
                                }

                                var data = response.data;

                                convertSuccess += data.results.success.length;
                                convertFailed += data.results.failed.length;
                                convertSkipped += data.results.skipped.length;

                                data.results.success.forEach(function(r) {
                                    convertLog('✓ ' + r.title + ' (Post ID: ' + r.post_id + ')', 'success');
                                });
                                data.results.skipped.forEach(function(r) {
                                    convertLog('⊘ Skipped: ' + r.title + ' - ' + r.message, 'info');
                                });
                                data.results.failed.forEach(function(r) {
                                    convertLog('✗ Failed: ' + r.title + ' - ' + r.message, 'error');
                                });

                                var percent = Math.round((data.processed / data.total) * 100);
                                $('#cjc-convert-bar').css('width', percent + '%');
                                $('#cjc-convert-text').text(data.processed + ' / ' + data.total + ' posts processed');

                                if (data.done) {
                                    var isDryRun = $('#cjc-convert-dry-run').is(':checked');
                                    convertLog('', 'info');
                                    convertLog('=== ' + (isDryRun ? 'DRY RUN ' : '') + 'COMPLETE ===', 'success');
                                    convertLog('Converted: ' + convertSuccess, 'success');
                                    convertLog('Skipped: ' + convertSkipped, 'info');
                                    convertLog('Failed: ' + convertFailed, convertFailed > 0 ? 'error' : 'info');
                                    $('#cjc-start-convert').prop('disabled', false).text('Convert Posts');

                                    if (!isDryRun) {
                                        location.reload();
                                    }
                                } else {
                                    convertOffset = data.processed;
                                    runConvertBatch();
                                }
                            },
                            error: function(xhr, status, error) {
                                convertLog('AJAX Error: ' + error, 'error');
                                $('#cjc-start-convert').prop('disabled', false).text('Convert Posts');
                            }
                        });
                    }

                    $('#cjc-start-convert').on('click', function() {
                        convertOffset = 0;
                        convertSuccess = 0;
                        convertFailed = 0;
                        convertSkipped = 0;

                        $(this).prop('disabled', true).text('Converting...');
                        $('#cjc-convert-progress').show();
                        $('#cjc-convert-log').empty().show();
                        $('#cjc-convert-bar').css('width', '0%');

                        var isDryRun = $('#cjc-convert-dry-run').is(':checked');
                        convertLog('Starting ' + (isDryRun ? 'DRY RUN' : 'CONVERSION') + '...', 'info');
                        convertLog('Scanning posts for recipe content...', 'info');
                        convertLog('', 'info');

                        runConvertBatch();
                    });
                });
                </script>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2><?php esc_html_e( 'WP-CLI Commands', 'suspended-flavor-child' ); ?></h2>
                <p><?php esc_html_e( 'You can also run migration via WP-CLI:', 'suspended-flavor-child' ); ?></p>
                <pre>
# Preview migration (dry run)
wp cjc-recipe migrate --dry-run

# Run full migration
wp cjc-recipe migrate

# Migrate specific recipe by Tasty Recipe ID
wp cjc-recipe migrate --recipe-id=123

# Verify migration
wp cjc-recipe verify
                </pre>
            </div>
        </div>
        <?php
    }

    /**
     * Handle migration form submission.
     */
    public static function handle_migration_request() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized', 'suspended-flavor-child' ) );
        }

        check_admin_referer( 'cjc_recipe_migrate', 'cjc_migrate_nonce' );

        $dry_run = ! empty( $_POST['dry_run'] );
        $update_content = ! empty( $_POST['update_content'] );

        $results = self::migrate_all( $dry_run, $update_content );

        // Store results in transient for display
        set_transient( 'cjc_migration_results', $results, 300 );

        wp_redirect( add_query_arg( 'migrated', '1', admin_url( 'edit.php?post_type=' . CJC_Recipe_Post_Type::$post_type . '&page=cjc-recipe-migration' ) ) );
        exit;
    }

    /**
     * WP-CLI command handler.
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     */
    public static function cli_command( $args, $assoc_args ) {
        $subcommand = isset( $args[0] ) ? $args[0] : 'help';

        switch ( $subcommand ) {
            case 'migrate':
                $dry_run = isset( $assoc_args['dry-run'] );
                $recipe_id = isset( $assoc_args['recipe-id'] ) ? absint( $assoc_args['recipe-id'] ) : 0;
                $update_content = ! isset( $assoc_args['skip-content'] );

                if ( $recipe_id ) {
                    $result = self::migrate_single( $recipe_id, $dry_run, $update_content );
                    self::cli_output_result( $result, $dry_run );
                } else {
                    $results = self::migrate_all( $dry_run, $update_content );
                    self::cli_output_results( $results, $dry_run );
                }
                break;

            case 'verify':
                self::verify_migration();
                break;

            case 'rollback':
                $confirm = isset( $assoc_args['yes'] );
                if ( ! $confirm ) {
                    WP_CLI::confirm( 'This will delete all CJC Recipes. Are you sure?' );
                }
                self::rollback();
                break;

            default:
                WP_CLI::line( 'Usage: wp cjc-recipe <command>' );
                WP_CLI::line( '' );
                WP_CLI::line( 'Commands:' );
                WP_CLI::line( '  migrate [--dry-run] [--recipe-id=<id>] [--skip-content]' );
                WP_CLI::line( '  verify' );
                WP_CLI::line( '  rollback [--yes]' );
        }
    }

    /**
     * Count Tasty Recipes.
     *
     * @return int Number of Tasty Recipes.
     */
    public static function count_tasty_recipes() {
        $count = wp_count_posts( 'tasty_recipe' );
        return isset( $count->publish ) ? $count->publish : 0;
    }

    /**
     * Count already migrated recipes.
     *
     * @return int Number of migrated recipes.
     */
    public static function count_migrated_recipes() {
        global $wpdb;
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
                CJC_Recipe_Meta::$prefix . 'tasty_recipe_id'
            )
        );
    }

    /**
     * Migrate all Tasty Recipes.
     *
     * @param bool $dry_run        Whether to do a dry run.
     * @param bool $update_content Whether to update post content.
     * @return array Migration results.
     */
    public static function migrate_all( $dry_run = false, $update_content = true ) {
        $results = array(
            'success'   => array(),
            'failed'    => array(),
            'skipped'   => array(),
            'dry_run'   => $dry_run,
        );

        $tasty_recipes = get_posts( array(
            'post_type'      => 'tasty_recipe',
            'post_status'    => 'any',
            'posts_per_page' => -1,
        ) );

        foreach ( $tasty_recipes as $tasty_post ) {
            $result = self::migrate_single( $tasty_post->ID, $dry_run, $update_content );

            if ( $result['status'] === 'success' ) {
                $results['success'][] = $result;
            } elseif ( $result['status'] === 'skipped' ) {
                $results['skipped'][] = $result;
            } else {
                $results['failed'][] = $result;
            }
        }

        return $results;
    }

    /**
     * Migrate a single Tasty Recipe.
     *
     * @param int  $tasty_id       Tasty Recipe post ID.
     * @param bool $dry_run        Whether to do a dry run.
     * @param bool $update_content Whether to update post content.
     * @return array Migration result.
     */
    public static function migrate_single( $tasty_id, $dry_run = false, $update_content = true ) {
        $tasty_post = get_post( $tasty_id );

        if ( ! $tasty_post || $tasty_post->post_type !== 'tasty_recipe' ) {
            return array(
                'status'  => 'failed',
                'message' => 'Not a valid Tasty Recipe',
                'tasty_id' => $tasty_id,
            );
        }

        // Check if already migrated
        $existing = self::get_migrated_recipe( $tasty_id );
        if ( $existing ) {
            return array(
                'status'  => 'skipped',
                'message' => 'Already migrated',
                'tasty_id' => $tasty_id,
                'cjc_id'  => $existing,
            );
        }

        // Get Tasty Recipe data
        $data = self::get_tasty_recipe_data( $tasty_id );

        if ( $dry_run ) {
            return array(
                'status'   => 'success',
                'message'  => 'Would migrate',
                'tasty_id' => $tasty_id,
                'title'    => $data['title'],
                'data'     => $data,
            );
        }

        // Create new CJC Recipe
        $cjc_id = wp_insert_post( array(
            'post_type'   => CJC_Recipe_Post_Type::$post_type,
            'post_status' => 'publish',
            'post_title'  => $data['title'],
            'post_author' => $tasty_post->post_author,
        ) );

        if ( is_wp_error( $cjc_id ) ) {
            return array(
                'status'  => 'failed',
                'message' => $cjc_id->get_error_message(),
                'tasty_id' => $tasty_id,
            );
        }

        // Copy featured image
        $thumbnail_id = get_post_thumbnail_id( $tasty_id );
        if ( $thumbnail_id ) {
            set_post_thumbnail( $cjc_id, $thumbnail_id );
        }

        // Save all meta fields
        self::save_migrated_meta( $cjc_id, $data );

        // Store reference to original Tasty Recipe
        update_post_meta( $cjc_id, CJC_Recipe_Meta::$prefix . 'tasty_recipe_id', $tasty_id );

        // Update parent post content if requested
        $parent_ids = get_post_meta( $tasty_id, '_tasty_recipe_parents', false );
        if ( $update_content && ! empty( $parent_ids ) ) {
            foreach ( $parent_ids as $parent_id ) {
                self::update_parent_content( $parent_id, $tasty_id, $cjc_id );
                // Also update parent reference in CJC Recipe
                CJC_Recipe_Meta::set_meta( $cjc_id, 'parent_post_id', $parent_id );
            }
        }

        return array(
            'status'   => 'success',
            'message'  => 'Migrated successfully',
            'tasty_id' => $tasty_id,
            'cjc_id'   => $cjc_id,
            'title'    => $data['title'],
        );
    }

    /**
     * Get Tasty Recipe data in our format.
     *
     * @param int $tasty_id Tasty Recipe ID.
     * @return array Recipe data.
     */
    private static function get_tasty_recipe_data( $tasty_id ) {
        $post = get_post( $tasty_id );

        $data = array(
            'title'        => $post->post_title,
            'description'  => get_post_meta( $tasty_id, 'description', true ),
            'author_name'  => get_post_meta( $tasty_id, 'author_name', true ),
            'prep_time'    => get_post_meta( $tasty_id, 'prep_time', true ),
            'cook_time'    => get_post_meta( $tasty_id, 'cook_time', true ),
            'total_time'   => get_post_meta( $tasty_id, 'total_time', true ),
            'yield'        => get_post_meta( $tasty_id, 'yield', true ),
            'category'     => get_post_meta( $tasty_id, 'category', true ),
            'cuisine'      => get_post_meta( $tasty_id, 'cuisine', true ),
            'method'       => get_post_meta( $tasty_id, 'method', true ),
            'diet'         => get_post_meta( $tasty_id, 'diet', true ),
            'keywords'     => get_post_meta( $tasty_id, 'keywords', true ),
            'notes'        => get_post_meta( $tasty_id, 'notes', true ),
            'video_url'    => get_post_meta( $tasty_id, 'video_url', true ),
            'additional_time_label' => get_post_meta( $tasty_id, 'additional_time_label', true ),
            'additional_time_value' => get_post_meta( $tasty_id, 'additional_time_value', true ),
        );

        // Parse yield to get number
        $data['yield_number'] = self::extract_yield_number( $data['yield'] );

        // Parse ingredients from HTML
        $ingredients_html = get_post_meta( $tasty_id, 'ingredients', true );
        $data['ingredients'] = self::parse_ingredients( $ingredients_html );

        // Parse instructions from HTML
        $instructions_html = get_post_meta( $tasty_id, 'instructions', true );
        $data['instructions'] = self::parse_instructions( $instructions_html );

        // Nutrition fields
        $nutrition_fields = array(
            'serving_size', 'calories', 'sugar', 'sodium', 'fat',
            'saturated_fat', 'unsaturated_fat', 'trans_fat',
            'carbohydrates', 'fiber', 'protein', 'cholesterol'
        );
        foreach ( $nutrition_fields as $field ) {
            $data[ $field ] = get_post_meta( $tasty_id, $field, true );
        }

        // Rating fields
        $data['average_rating'] = get_post_meta( $tasty_id, 'average_rating', true );
        $data['total_reviews'] = get_post_meta( $tasty_id, 'total_reviews', true );

        return $data;
    }

    /**
     * Extract numeric yield from yield string.
     *
     * @param string $yield Yield string (e.g., "4 servings").
     * @return int Numeric yield.
     */
    private static function extract_yield_number( $yield ) {
        if ( preg_match( '/(\d+)/', $yield, $matches ) ) {
            return intval( $matches[1] );
        }
        return 0;
    }

    /**
     * Parse ingredients HTML into structured array.
     *
     * @param string $html Ingredients HTML.
     * @return array Structured ingredients.
     */
    private static function parse_ingredients( $html ) {
        if ( empty( $html ) ) {
            return array();
        }

        $groups = array();
        $current_group = array(
            'title' => '',
            'items' => array(),
        );

        // Load HTML
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="UTF-8">' . $html );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        // Find all relevant elements
        $nodes = $xpath->query( '//h4 | //li | //p' );

        foreach ( $nodes as $node ) {
            $text = trim( $node->textContent );
            if ( empty( $text ) ) {
                continue;
            }

            if ( $node->nodeName === 'h4' ) {
                // Start new group
                if ( ! empty( $current_group['items'] ) ) {
                    $groups[] = $current_group;
                }
                $current_group = array(
                    'title' => $text,
                    'items' => array(),
                );
            } else {
                // Parse ingredient line
                $ingredient = self::parse_ingredient_line( $text );
                if ( $ingredient ) {
                    $current_group['items'][] = $ingredient;
                }
            }
        }

        // Add final group
        if ( ! empty( $current_group['items'] ) ) {
            $groups[] = $current_group;
        }

        return $groups;
    }

    /**
     * Parse a single ingredient line.
     *
     * @param string $line Ingredient line.
     * @return array Parsed ingredient.
     */
    private static function parse_ingredient_line( $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            return null;
        }

        $ingredient = array(
            'amount' => '',
            'unit'   => '',
            'name'   => $line,
            'notes'  => '',
        );

        // Common units
        $units = array(
            'cup', 'cups', 'tablespoon', 'tablespoons', 'tbsp', 'teaspoon', 'teaspoons', 'tsp',
            'pound', 'pounds', 'lb', 'lbs', 'ounce', 'ounces', 'oz',
            'gram', 'grams', 'g', 'kilogram', 'kilograms', 'kg',
            'milliliter', 'milliliters', 'ml', 'liter', 'liters', 'l',
            'pinch', 'dash', 'handful', 'bunch', 'clove', 'cloves',
            'slice', 'slices', 'piece', 'pieces', 'can', 'cans', 'package', 'packages',
            'small', 'medium', 'large'
        );
        $units_pattern = implode( '|', $units );

        // Try to match: amount unit name, notes
        // Pattern: (number/fraction) (unit) (name), (notes)
        $pattern = '/^([\d\/\.\s]+)?\s*(' . $units_pattern . ')?\s*(.+?)(?:,\s*(.+))?$/i';

        if ( preg_match( $pattern, $line, $matches ) ) {
            $ingredient['amount'] = trim( $matches[1] ?? '' );
            $ingredient['unit'] = trim( $matches[2] ?? '' );
            $ingredient['name'] = trim( $matches[3] ?? $line );
            $ingredient['notes'] = trim( $matches[4] ?? '' );
        }

        return $ingredient;
    }

    /**
     * Parse instructions HTML into structured array.
     *
     * @param string $html Instructions HTML.
     * @return array Structured instructions.
     */
    private static function parse_instructions( $html ) {
        if ( empty( $html ) ) {
            return array();
        }

        $groups = array();
        $current_group = array(
            'title' => '',
            'steps' => array(),
        );

        // Load HTML
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="UTF-8">' . $html );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        // Find all relevant elements
        $nodes = $xpath->query( '//h4 | //li | //p' );

        foreach ( $nodes as $node ) {
            // Get inner HTML
            $inner_html = '';
            foreach ( $node->childNodes as $child ) {
                $inner_html .= $dom->saveHTML( $child );
            }
            $inner_html = trim( $inner_html );

            if ( empty( $inner_html ) ) {
                continue;
            }

            if ( $node->nodeName === 'h4' ) {
                // Start new group
                if ( ! empty( $current_group['steps'] ) ) {
                    $groups[] = $current_group;
                }
                $current_group = array(
                    'title' => $node->textContent,
                    'steps' => array(),
                );
            } else {
                $current_group['steps'][] = array(
                    'text' => $inner_html,
                );
            }
        }

        // Add final group
        if ( ! empty( $current_group['steps'] ) ) {
            $groups[] = $current_group;
        }

        return $groups;
    }

    /**
     * Save migrated meta to CJC Recipe.
     *
     * @param int   $cjc_id CJC Recipe ID.
     * @param array $data   Recipe data.
     */
    private static function save_migrated_meta( $cjc_id, $data ) {
        // Simple text fields
        $text_fields = array(
            'description', 'author_name', 'prep_time', 'cook_time', 'total_time',
            'yield', 'category', 'cuisine', 'method', 'diet', 'keywords',
            'notes', 'video_url', 'additional_time_label', 'additional_time_value'
        );
        foreach ( $text_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                CJC_Recipe_Meta::set_meta( $cjc_id, $field, $data[ $field ] );
            }
        }

        // Numeric fields
        CJC_Recipe_Meta::set_meta( $cjc_id, 'yield_number', intval( $data['yield_number'] ?? 0 ) );
        CJC_Recipe_Meta::set_meta( $cjc_id, 'average_rating', floatval( $data['average_rating'] ?? 0 ) );
        CJC_Recipe_Meta::set_meta( $cjc_id, 'total_reviews', intval( $data['total_reviews'] ?? 0 ) );

        // Structured fields (JSON)
        CJC_Recipe_Meta::set_meta( $cjc_id, 'ingredients', $data['ingredients'] ?? array() );
        CJC_Recipe_Meta::set_meta( $cjc_id, 'instructions', $data['instructions'] ?? array() );

        // Nutrition fields
        $nutrition_fields = array(
            'serving_size', 'calories', 'sugar', 'sodium', 'fat',
            'saturated_fat', 'unsaturated_fat', 'trans_fat',
            'carbohydrates', 'fiber', 'protein', 'cholesterol'
        );
        foreach ( $nutrition_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                CJC_Recipe_Meta::set_meta( $cjc_id, $field, $data[ $field ] );
            }
        }
    }

    /**
     * Get already migrated CJC Recipe ID from Tasty Recipe ID.
     *
     * @param int $tasty_id Tasty Recipe ID.
     * @return int|false CJC Recipe ID or false.
     */
    private static function get_migrated_recipe( $tasty_id ) {
        global $wpdb;

        $cjc_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d",
            CJC_Recipe_Meta::$prefix . 'tasty_recipe_id',
            $tasty_id
        ) );

        return $cjc_id ? intval( $cjc_id ) : false;
    }

    /**
     * Update parent post content to use CJC Recipe block.
     *
     * @param int $parent_id Parent post ID.
     * @param int $tasty_id  Tasty Recipe ID.
     * @param int $cjc_id    CJC Recipe ID.
     */
    private static function update_parent_content( $parent_id, $tasty_id, $cjc_id ) {
        $post = get_post( $parent_id );
        if ( ! $post ) {
            return;
        }

        $content = $post->post_content;
        $updated = false;

        // Replace Tasty Recipe block
        // <!-- wp:wp-tasty/tasty-recipe {"id":"123"} /-->
        $patterns = array(
            '/<!-- wp:wp-tasty\/tasty-recipe \{[^}]*"id"\s*:\s*"?' . $tasty_id . '"?[^}]*\} \/-->/i',
            '/<!-- wp:wp-tasty\/tasty-recipe \{[^}]*"id"\s*:\s*"?' . $tasty_id . '"?[^}]*\}[\s\S]*?<!-- \/wp:wp-tasty\/tasty-recipe -->/i',
        );

        $replacement = '<!-- wp:cjc/recipe {"recipeId":' . $cjc_id . '} /-->';

        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $content ) ) {
                $content = preg_replace( $pattern, $replacement, $content );
                $updated = true;
                break;
            }
        }

        // Also check for shortcode
        $shortcode_pattern = '/\[tasty-recipe\s+id=["\']?' . $tasty_id . '["\']?\s*\]/i';
        if ( preg_match( $shortcode_pattern, $content ) ) {
            $content = preg_replace( $shortcode_pattern, '[cjc_recipe id="' . $cjc_id . '"]', $content );
            $updated = true;
        }

        if ( $updated ) {
            wp_update_post( array(
                'ID'           => $parent_id,
                'post_content' => $content,
            ) );
        }
    }

    /**
     * Verify migration integrity.
     */
    public static function verify_migration() {
        $issues = array();

        // Get all CJC Recipes with Tasty Recipe reference
        $cjc_recipes = get_posts( array(
            'post_type'      => CJC_Recipe_Post_Type::$post_type,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => CJC_Recipe_Meta::$prefix . 'tasty_recipe_id',
                    'compare' => 'EXISTS',
                ),
            ),
        ) );

        foreach ( $cjc_recipes as $cjc_post ) {
            $tasty_id = get_post_meta( $cjc_post->ID, CJC_Recipe_Meta::$prefix . 'tasty_recipe_id', true );
            $tasty_post = get_post( $tasty_id );

            // Check if original still exists
            if ( ! $tasty_post ) {
                $issues[] = sprintf(
                    'CJC Recipe %d references non-existent Tasty Recipe %d',
                    $cjc_post->ID,
                    $tasty_id
                );
                continue;
            }

            // Compare titles
            if ( $cjc_post->post_title !== $tasty_post->post_title ) {
                $issues[] = sprintf(
                    'Title mismatch: CJC %d (%s) vs Tasty %d (%s)',
                    $cjc_post->ID,
                    $cjc_post->post_title,
                    $tasty_id,
                    $tasty_post->post_title
                );
            }
        }

        // Check for unmigrated Tasty Recipes
        $unmigrated = self::count_tasty_recipes() - count( $cjc_recipes );
        if ( $unmigrated > 0 ) {
            $issues[] = sprintf( '%d Tasty Recipes have not been migrated', $unmigrated );
        }

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            if ( empty( $issues ) ) {
                WP_CLI::success( 'Migration verified. No issues found.' );
            } else {
                WP_CLI::warning( 'Migration issues found:' );
                foreach ( $issues as $issue ) {
                    WP_CLI::line( '  - ' . $issue );
                }
            }
        }

        return $issues;
    }

    /**
     * Rollback migration (delete all CJC Recipes).
     */
    public static function rollback() {
        $cjc_recipes = get_posts( array(
            'post_type'      => CJC_Recipe_Post_Type::$post_type,
            'post_status'    => 'any',
            'posts_per_page' => -1,
        ) );

        $count = 0;
        foreach ( $cjc_recipes as $post ) {
            wp_delete_post( $post->ID, true );
            $count++;
        }

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::success( sprintf( 'Deleted %d CJC Recipes.', $count ) );
        }

        return $count;
    }

    /**
     * Output single migration result for CLI.
     *
     * @param array $result  Migration result.
     * @param bool  $dry_run Whether this was a dry run.
     */
    private static function cli_output_result( $result, $dry_run ) {
        $prefix = $dry_run ? '[DRY RUN] ' : '';

        if ( $result['status'] === 'success' ) {
            WP_CLI::success( $prefix . $result['message'] . ': ' . $result['title'] );
        } elseif ( $result['status'] === 'skipped' ) {
            WP_CLI::warning( $prefix . $result['message'] . ' (Tasty ID: ' . $result['tasty_id'] . ')' );
        } else {
            WP_CLI::error( $prefix . $result['message'] . ' (Tasty ID: ' . $result['tasty_id'] . ')' );
        }
    }

    /**
     * Output migration results for CLI.
     *
     * @param array $results Migration results.
     * @param bool  $dry_run Whether this was a dry run.
     */
    private static function cli_output_results( $results, $dry_run ) {
        $prefix = $dry_run ? '[DRY RUN] ' : '';

        WP_CLI::line( '' );
        WP_CLI::line( $prefix . 'Migration Results:' );
        WP_CLI::line( '  Successful: ' . count( $results['success'] ) );
        WP_CLI::line( '  Skipped:    ' . count( $results['skipped'] ) );
        WP_CLI::line( '  Failed:     ' . count( $results['failed'] ) );
        WP_CLI::line( '' );

        if ( ! empty( $results['failed'] ) ) {
            WP_CLI::warning( 'Failed migrations:' );
            foreach ( $results['failed'] as $result ) {
                WP_CLI::line( '  - Tasty ID ' . $result['tasty_id'] . ': ' . $result['message'] );
            }
        }

        if ( $dry_run ) {
            WP_CLI::line( '' );
            WP_CLI::line( 'Run without --dry-run to perform actual migration.' );
        } else {
            WP_CLI::success( 'Migration complete.' );
        }
    }

    /**
     * Get recipe category slugs.
     *
     * @return array Category slugs.
     */
    public static function get_recipe_category_slugs() {
        return array(
            'recipes',
            'hawaiian-breakfast',
            'pupus-snacks',
            'island-drinks',
            'tropical-treats',
            'island-comfort',
            'poke-seafood',
        );
    }

    /**
     * Count posts that can be converted to recipes.
     *
     * @return int Number of convertible posts.
     */
    public static function count_convertible_posts() {
        $category_slugs = self::get_recipe_category_slugs();

        $posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'category_name'  => implode( ',', $category_slugs ),
        ) );

        // Filter out posts that already have a CJC Recipe
        $count = 0;
        foreach ( $posts as $post_id ) {
            if ( ! self::post_has_cjc_recipe( $post_id ) ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if a post already has a CJC Recipe associated.
     *
     * @param int $post_id Post ID.
     * @return bool True if post has CJC Recipe.
     */
    private static function post_has_cjc_recipe( $post_id ) {
        global $wpdb;

        // Check if any CJC Recipe references this post
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = %s AND meta_value = %d LIMIT 1",
            CJC_Recipe_Meta::$prefix . 'parent_post_id',
            $post_id
        ) );

        if ( $exists ) {
            return true;
        }

        // Also check if post content already has a CJC recipe block
        $post = get_post( $post_id );
        if ( $post && strpos( $post->post_content, 'wp:cjc/recipe' ) !== false ) {
            return true;
        }

        return false;
    }

    /**
     * AJAX handler for converting posts batch.
     */
    public static function ajax_convert_posts_batch() {
        check_ajax_referer( 'cjc_convert_posts', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        $dry_run = ! empty( $_POST['dry_run'] );
        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
        $batch_size = 3; // Process 3 posts at a time (content parsing is heavier)

        $category_slugs = self::get_recipe_category_slugs();

        // Get batch of posts
        $posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $batch_size,
            'offset'         => $offset,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'category_name'  => implode( ',', $category_slugs ),
        ) );

        $results = array(
            'success' => array(),
            'failed'  => array(),
            'skipped' => array(),
        );

        foreach ( $posts as $post ) {
            try {
                $result = self::convert_post_to_recipe( $post->ID, $dry_run );

                if ( $result['status'] === 'success' ) {
                    $results['success'][] = $result;
                } elseif ( $result['status'] === 'skipped' ) {
                    $results['skipped'][] = $result;
                } else {
                    $results['failed'][] = $result;
                }
            } catch ( Exception $e ) {
                $results['failed'][] = array(
                    'post_id' => $post->ID,
                    'title'   => $post->post_title,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                );
            }
        }

        // Get total count
        $all_posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'category_name'  => implode( ',', $category_slugs ),
        ) );
        $total = count( $all_posts );
        $processed = $offset + count( $posts );

        wp_send_json_success( array(
            'results'   => $results,
            'processed' => $processed,
            'total'     => $total,
            'done'      => $processed >= $total,
        ) );
    }

    /**
     * Convert a regular post to a CJC Recipe.
     *
     * @param int  $post_id Post ID.
     * @param bool $dry_run Whether to do a dry run.
     * @return array Conversion result.
     */
    public static function convert_post_to_recipe( $post_id, $dry_run = false ) {
        $post = get_post( $post_id );

        if ( ! $post || $post->post_type !== 'post' ) {
            return array(
                'status'  => 'failed',
                'message' => 'Not a valid post',
                'post_id' => $post_id,
                'title'   => '',
            );
        }

        // Check if already has a recipe
        if ( self::post_has_cjc_recipe( $post_id ) ) {
            return array(
                'status'  => 'skipped',
                'message' => 'Already has CJC Recipe',
                'post_id' => $post_id,
                'title'   => $post->post_title,
            );
        }

        // Parse content for recipe data
        $data = self::parse_post_content_for_recipe( $post );

        // Check if we found recipe content
        if ( empty( $data['ingredients'] ) && empty( $data['instructions'] ) ) {
            return array(
                'status'  => 'skipped',
                'message' => 'No recipe content found',
                'post_id' => $post_id,
                'title'   => $post->post_title,
            );
        }

        if ( $dry_run ) {
            return array(
                'status'  => 'success',
                'message' => 'Would convert',
                'post_id' => $post_id,
                'title'   => $post->post_title,
                'data'    => $data,
            );
        }

        // Create new CJC Recipe
        $cjc_id = wp_insert_post( array(
            'post_type'   => CJC_Recipe_Post_Type::$post_type,
            'post_status' => 'publish',
            'post_title'  => $post->post_title,
            'post_author' => $post->post_author,
        ) );

        if ( is_wp_error( $cjc_id ) ) {
            return array(
                'status'  => 'failed',
                'message' => $cjc_id->get_error_message(),
                'post_id' => $post_id,
                'title'   => $post->post_title,
            );
        }

        // Copy featured image
        $thumbnail_id = get_post_thumbnail_id( $post_id );
        if ( $thumbnail_id ) {
            set_post_thumbnail( $cjc_id, $thumbnail_id );
        }

        // Save all meta fields
        self::save_migrated_meta( $cjc_id, $data );

        // Store reference to parent post
        CJC_Recipe_Meta::set_meta( $cjc_id, 'parent_post_id', $post_id );

        // Add CJC Recipe block to post content
        $block = '<!-- wp:cjc/recipe {"recipeId":' . $cjc_id . '} /-->';

        // Add block at the end of the content
        $new_content = $post->post_content . "\n\n" . $block;

        wp_update_post( array(
            'ID'           => $post_id,
            'post_content' => $new_content,
        ) );

        return array(
            'status'  => 'success',
            'message' => 'Converted successfully',
            'post_id' => $post_id,
            'cjc_id'  => $cjc_id,
            'title'   => $post->post_title,
        );
    }

    /**
     * Parse post content for recipe data.
     *
     * @param WP_Post $post Post object.
     * @return array Parsed recipe data.
     */
    private static function parse_post_content_for_recipe( $post ) {
        $content = $post->post_content;

        $data = array(
            'title'        => $post->post_title,
            'description'  => '',
            'author_name'  => '',
            'prep_time'    => '',
            'cook_time'    => '',
            'total_time'   => '',
            'yield'        => '',
            'yield_number' => 0,
            'category'     => '',
            'cuisine'      => '',
            'ingredients'  => array(),
            'instructions' => array(),
            'notes'        => '',
        );

        // Extract times from content
        // Pattern: Prep Time: X minutes | Cook Time: Y minutes | Servings: Z
        if ( preg_match( '/Prep\s*Time:\s*([^|<\n]+)/i', $content, $matches ) ) {
            $data['prep_time'] = trim( strip_tags( $matches[1] ) );
        }
        if ( preg_match( '/Cook\s*Time:\s*([^|<\n]+)/i', $content, $matches ) ) {
            $data['cook_time'] = trim( strip_tags( $matches[1] ) );
        }
        if ( preg_match( '/Total\s*Time:\s*([^|<\n]+)/i', $content, $matches ) ) {
            $data['total_time'] = trim( strip_tags( $matches[1] ) );
        }
        // Handle various serving formats: "4 servings", "1 cocktail", "2-4 people"
        if ( preg_match( '/Servings?:\s*([^|<\n]+)/i', $content, $matches ) ) {
            $yield_text = trim( strip_tags( $matches[1] ) );
            $data['yield'] = $yield_text;
            // Try to extract number
            if ( preg_match( '/(\d+)/', $yield_text, $num_match ) ) {
                $data['yield_number'] = intval( $num_match[1] );
            }
        }
        // Also try "Serves: X" format
        if ( empty( $data['yield'] ) && preg_match( '/Serves:\s*([^|<\n]+)/i', $content, $matches ) ) {
            $yield_text = trim( strip_tags( $matches[1] ) );
            $data['yield'] = $yield_text;
            if ( preg_match( '/(\d+)/', $yield_text, $num_match ) ) {
                $data['yield_number'] = intval( $num_match[1] );
            }
        }

        // Get category from post categories
        $categories = get_the_category( $post->ID );
        if ( ! empty( $categories ) ) {
            $data['category'] = $categories[0]->name;
        }

        // Parse ingredients
        $data['ingredients'] = self::parse_ingredients_from_content( $content );

        // Parse instructions
        $data['instructions'] = self::parse_instructions_from_content( $content );

        return $data;
    }

    /**
     * Parse ingredients from post content.
     *
     * @param string $content Post content.
     * @return array Structured ingredients.
     */
    private static function parse_ingredients_from_content( $content ) {
        $groups = array();

        // Try multiple patterns to find ingredients section
        // Pattern 1: Heading with "Ingredients" (may have extra text like "Ingredients (Frozen Version)")
        // Pattern 2: Bold/strong text with "Ingredients"
        $patterns = array(
            '/<h[2-4][^>]*>[^<]*Ingredients[^<]*<\/h[2-4]>(.*?)(?=<h[2-4]|$)/is',
            '/<p[^>]*><strong>[^<]*Ingredients[^<]*<\/strong><\/p>(.*?)(?=<p[^>]*><strong>|<h[2-4]|$)/is',
            '/<strong>[^<]*Ingredients[^<]*<\/strong>(.*?)(?=<strong>|<h[2-4]|$)/is',
        );

        $ingredients_html = '';
        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $content, $section_match ) ) {
                $ingredients_html = $section_match[1];
                break;
            }
        }

        if ( empty( $ingredients_html ) ) {
            return $groups;
        }

        // Load HTML
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $ingredients_html . '</div>' );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        $current_group = array(
            'title' => '',
            'items' => array(),
        );

        // Find group headers and list items OR paragraphs (for non-list format)
        $nodes = $xpath->query( '//h4 | //h5 | //li | //p' );

        foreach ( $nodes as $node ) {
            $text = trim( $node->textContent );
            if ( empty( $text ) ) {
                continue;
            }

            // Skip if this looks like the start of Instructions
            if ( preg_match( '/^Instructions/i', $text ) ) {
                break;
            }

            // Check if this is a group header
            $is_header = false;
            if ( $node->nodeName === 'h4' || $node->nodeName === 'h5' ) {
                $is_header = true;
            } elseif ( $node->nodeName === 'p' ) {
                // Check for "For the X:" pattern or bold group headers
                if ( preg_match( '/^For\s+(the\s+)?(.+?):\s*$/i', $text, $matches ) ) {
                    $is_header = true;
                    $text = trim( $matches[2] );
                }
                // Check if whole paragraph is bold (subheading)
                $strong = $xpath->query( './/strong', $node );
                if ( $strong->length > 0 && trim( $strong->item(0)->textContent ) === $text ) {
                    // This is a bold paragraph, could be a sub-header
                    if ( strlen( $text ) < 50 && ! preg_match( '/^\d/', $text ) ) {
                        $is_header = true;
                    }
                }
            }

            if ( $is_header ) {
                // Save current group if it has items
                if ( ! empty( $current_group['items'] ) ) {
                    $groups[] = $current_group;
                }
                $current_group = array(
                    'title' => $text,
                    'items' => array(),
                );
            } else {
                // Parse as ingredient - could be <li> or <p>
                $ingredient = self::parse_ingredient_line( $text );
                if ( $ingredient && ! empty( $ingredient['name'] ) ) {
                    $current_group['items'][] = $ingredient;
                }
            }
        }

        // Add final group
        if ( ! empty( $current_group['items'] ) ) {
            $groups[] = $current_group;
        }

        return $groups;
    }

    /**
     * Parse instructions from post content.
     *
     * @param string $content Post content.
     * @return array Structured instructions.
     */
    private static function parse_instructions_from_content( $content ) {
        $groups = array();

        // Try multiple patterns to find instructions section
        $patterns = array(
            '/<h[2-4][^>]*>[^<]*Instructions[^<]*<\/h[2-4]>(.*?)(?=<h[2-4]|$)/is',
            '/<p[^>]*><strong>[^<]*Instructions[^<]*<\/strong><\/p>(.*?)(?=<p[^>]*><strong>|<h[2-4]|$)/is',
            '/<strong>[^<]*Instructions[^<]*<\/strong>(.*?)(?=<strong>|<h[2-4]|$)/is',
        );

        $instructions_html = '';
        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $content, $section_match ) ) {
                $instructions_html = $section_match[1];
                break;
            }
        }

        if ( empty( $instructions_html ) ) {
            return $groups;
        }

        // Load HTML
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $instructions_html . '</div>' );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        $current_group = array(
            'title' => '',
            'steps' => array(),
        );

        // Find group headers, list items, and paragraphs
        $nodes = $xpath->query( '//h4 | //h5 | //li | //p' );

        foreach ( $nodes as $node ) {
            // Get inner HTML
            $inner_html = '';
            foreach ( $node->childNodes as $child ) {
                $inner_html .= $dom->saveHTML( $child );
            }
            $inner_html = trim( $inner_html );
            $text = trim( $node->textContent );

            if ( empty( $text ) ) {
                continue;
            }

            // Check if this is a group header
            $is_header = false;
            if ( $node->nodeName === 'h4' || $node->nodeName === 'h5' ) {
                $is_header = true;
            } elseif ( $node->nodeName === 'p' ) {
                // Check for "For the X:" pattern
                if ( preg_match( '/^For\s+(the\s+)?(.+?):\s*$/i', $text, $matches ) ) {
                    $is_header = true;
                    $text = trim( $matches[2] );
                }
            }

            if ( $is_header ) {
                // Save current group if it has steps
                if ( ! empty( $current_group['steps'] ) ) {
                    $groups[] = $current_group;
                }
                $current_group = array(
                    'title' => $text,
                    'steps' => array(),
                );
            } else {
                // Add as instruction step - could be <li> or <p>
                // Skip very short text that's likely not a real step
                if ( strlen( $text ) > 10 ) {
                    $current_group['steps'][] = array(
                        'text' => $inner_html,
                    );
                }
            }
        }

        // Add final group
        if ( ! empty( $current_group['steps'] ) ) {
            $groups[] = $current_group;
        }

        return $groups;
    }
}
