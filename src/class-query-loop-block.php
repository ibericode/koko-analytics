<?php

namespace KokoAnalytics;

class QueryLoopBlock
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_filter('pre_render_block', [$this, 'pre_render_block'], 10, 3);
    }

    public function admin_enqueue_scripts($hook_suffix)
    {
        if ($hook_suffix !== 'post-new.php' && $hook_suffix !== 'post.php') {
            return;
        }

        wp_enqueue_script('koko-analytics-query-loop-block', plugins_url('assets/dist/js/query-loop-block.js', KOKO_ANALYTICS_PLUGIN_FILE), [ 'wp-blocks' ]);
    }

    public function pre_render_block($prerender, array $block, $parent)
    {
        if (($block['attrs']['namespace'] ?? '') !== 'koko-analytics/most-viewed-pages') {
            return $prerender;
        }

        add_filter('query_loop_block_query_vars', [$this, 'query_loop_block_query_vars'], 10, 1);
    }

    public function query_loop_block_query_vars($vars)
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
