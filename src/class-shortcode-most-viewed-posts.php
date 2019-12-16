<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */
namespace KokoAnalytics;

class Shortcode_Most_Viewed_Posts {

	const SHORTCODE = 'koko_analytics_most_viewed_posts';

	public function init() {
		add_shortcode( self::SHORTCODE, array( $this, 'content' ) );
	}

	public function content( $args ) {
		$default_args = array(
			'number'    => 5,
			'post_type' => 'post',
			'show_date' => false,
			'days'    => 30,
		);
		$args = shortcode_atts( $default_args, $args, self::SHORTCODE );
		$posts = get_most_viewed_posts( $args );

		$html = '<ul>';
		foreach ( $posts as $p ) {
			$post_title   = get_the_title( $p );
			$title        = $post_title !== '' ? $post_title : esc_html__( '(no title)', 'koko-analytics' );
			$aria_current = '';

			if ( get_queried_object_id() === $p->ID ) {
				$aria_current = ' aria-current="page"';
			}

			$html .= '<li>';
			$html .= sprintf( '<a href="%s" %s>%s</a>', get_the_permalink( $p ), $aria_current, $title );

			if ( $args['show_date'] ) {
				$html .= sprintf( PHP_EOL . ' <span class="post-date">%s</span>', get_the_date( '', $p ) );
			}
			$html .= '</li>';
		}
		$html .= '</ul>';
		return $html;
	}
}
