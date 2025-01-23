<?php

namespace KokoAnalytics;

class Query_Loop_Block
{
    public static function admin_enqueue_scripts($hook_suffix)
    {
        if ($hook_suffix !== 'post-new.php' && $hook_suffix !== 'post.php') {
            return;
        }

        wp_enqueue_script('koko-analytics-query-loop-block', plugins_url('assets/dist/js/query-loop-block.js', KOKO_ANALYTICS_PLUGIN_FILE), [ 'wp-blocks' ]);
    }

    public static function pre_render_block($prerender, $block, $parent)
    {
        if (($block['attrs']['namespace'] ?? '') !== 'koko-analytics/most-viewed-pages') {
            return $prerender;
        }

        add_filter('query_loop_block_query_vars', [self::class, 'query_loop_block_query_vars'], 10, 1);
    }

    public static function query_loop_block_query_vars($vars)
    {
        // TODO: Add UI for specifying number of days
        $post_ids = get_most_viewed_post_ids([
            'post_type' => $vars['post_type'],
            'number' => 100, // to support blocks with pagination
            'days' => 30,
        ]);

        $vars['orderby'] = 'post__in';
        $vars['post__in'] = $post_ids;
        return $vars;
    }
}
