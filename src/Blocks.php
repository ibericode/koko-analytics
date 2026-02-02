<?php

namespace KokoAnalytics;

use KokoAnalytics\Shortcodes\Shortcode_Site_Counter;
use WP_Query;

class Blocks
{
    public function hook(): void
    {
        add_action('init', [$this, 'action_init'], 10, 0);
        add_filter('pre_render_block', [$this, 'filter_pre_render_block'], 10, 3);
    }

    public function action_init(): void
    {
        // counter block
        wp_register_script('koko-analytics-counter-block', plugins_url('assets/dist/js/blocks/counter.js', KOKO_ANALYTICS_PLUGIN_FILE), [
            'wp-block-editor',
            'wp-blocks',
            'wp-components',
            'wp-element',
            'wp-i18n'
        ]);
        register_block_type('koko-analytics/counter', [
            'render_callback' => [$this, 'render_counter'],
            'editor_script' => 'koko-analytics-counter-block',
        ]);

        // most viewed pages block
        wp_register_script('koko-analytics-most-viewed-pages-block', plugins_url('assets/dist/js/blocks/most-viewed-pages.js', KOKO_ANALYTICS_PLUGIN_FILE), ['wp-blocks']);
        register_block_type('koko-analytics/most-viewed-pages', [
            'editor_script' => 'koko-analytics-most-viewed-pages-block'
        ]);
    }

    public function render_counter($args): string
    {
        $count = Shortcode_Site_Counter::content($args);
        return '<p>' . sprintf(__('This page has been viewed a total of %s times', 'koko-analytics'), $count) . '</p>';
    }

    public function filter_pre_render_block($prerender, $block, $parent)
    {
        if (($block['attrs']['namespace'] ?? '') !== 'koko-analytics/most-viewed-pages') {
            return $prerender;
        }

        add_filter('query_loop_block_query_vars', [$this, 'filter_query_loop_block_query_vars'], 10, 1);
    }

    public function filter_query_loop_block_query_vars($vars)
    {
        // TODO: Add UI for specifying number of days
        $post_ids = get_most_viewed_post_ids([
            'post_type' => $vars['post_type'],
            'number' => 100, // to support blocks with pagination
            'days' => 30,
        ]);

        // WP_Query checks for post__in argument using ! empty, so we pass a dummy array here in case we didn't find any posts with stats over last N days
        if (count($post_ids) === 0) {
            $post_ids = [0];
        }

        $vars['ignore_sticky_posts'] = true;
        $vars['orderby'] = 'post__in';
        $vars['post__in'] = $post_ids;
        return $vars;
    }
}
