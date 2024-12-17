<?php

namespace KokoAnalytics;

class QueryLoopBlock {
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_filter('pre_render_block', [$this, 'pre_render_block'], 10, 3);
    }

    public function admin_enqueue_scripts()
    {
        wp_enqueue_script('koko-analytics-query-loop-block', plugins_url('assets/dist/js/query-loop-block.js', KOKO_ANALYTICS_PLUGIN_FILE));
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
        // TODO: Do our own database query fetching just the ID's
        // TODO: Store ID's in transient?
        // TODO: Add UI for specifying number of days
        // TODO: Performance
        $posts = get_most_viewed_posts([
            'post_type' => $vars['post_type'],
            'number' => 100,
            'days' => 365,
        ]);
        $ids = array_map(function ($post) { return $post->ID; }, $posts);

        $vars['orderby'] = 'post__in';
        $vars['post__in'] = $ids;
        return $vars;
    }
}
