<?php
/**
 * Plugin Name:     All_posts_relational_post
 * Plugin URI:
 * Description:     Output the related post list based on the taxonomy.
 * Author:          marushu
 * Author URI:      https://private.hibou-web.com
 * Text Domain:     all_posts_relational_post
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         All_posts_relational_post
 */

/**
 * Get relational post based on the taxonomy.
 *
 * @return mixed
 */
function all_posts_relational_post( $post, $taxonomy, $num, $content = true,  $orderby = 'rand' ) {

	$post_type = get_post_type( $post );
	$post_type_obj = get_post_type_object( $post_type );
	$post_type_name = $post_type_obj->label;
	$terms = wp_get_post_terms( get_the_ID(), $taxonomy );
	$term_slugs = array();
	foreach ( $terms as $term ) {

		$slugs = $term->slug;
		$term_slugs[] = urldecode_deep( $slugs );

	}

	$args = array(
		'post_type'       => $post_type,
		'exclude'         => get_the_ID(),
		'posts_per_page'  => intval( $num ),
		'tax_query' => array(
			array(
				'taxonomy'  => $taxonomy,
				'field'     => 'slug',
				'terms'     => $term_slugs,
				'operator'  => 'IN',
			)

		),
		'orderby'        => $orderby,
	);
	$relational_posts = get_posts( $args );

	$html = '';

	if ( ! empty( $relational_posts ) ) {

		$html .= '<div class="relational_post_outer">';
		$html .= '<div class="relational_post">';
		$html .= '<h3>関連する' . esc_attr( $post_type_name ) . ' : </h3>';

		foreach ( $relational_posts as $relational_post ) {
			$post_id    = $relational_post->ID;
			$post_title = get_the_title( $post_id );
			$post_url     = get_permalink( $post_id );
			$post_content = wp_strip_all_tags( strip_shortcodes( $relational_post->post_content ) );
			$word_count = intval( mb_strlen( $post_content ) );

			if ( $word_count > 100 ) {
				$more = '<a class="to_relational" href="' . esc_url( $post_url ) . '" title="' . $post_title . 'へ">……続きを読む</a>';
				$post_content = wp_trim_words( $post_content, 100, $more );
			} else {
				$more = '<a class="to_relational" href="' . esc_url( $post_url ) . '" title="' . $post_title . 'へ">記事を読む → </a>';
				$post_content .= $more;
			}

			if ( has_post_thumbnail( $post_id ) ) {

				$size           = 'thumb_293_192';
				$default_attr   = array(
					'class' => "attachment-$size",
					'alt'   => trim( esc_html( $post_title ) ),
					'title' => trim( esc_html( $post_title ) ),
				);
				$post_thumbnail = get_the_post_thumbnail( $post_id, $size, $default_attr );

			} else {

				$post_thumbnail = '';

			}

			$post_term_array = array();
			$post_terms = wp_get_post_terms( get_the_ID(), $taxonomy );
			if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
				foreach ( $post_terms as $post_term ) {
					$post_term_array[] = $post_term->name;
				}
			}


			$html .= '<div class="relational_detail">';
			$html .= '<div class="relational_thumb">';
			$html .= '<a href="' . esc_url( $post_url ) . '">';
			$html .= $post_thumbnail;
			$html .= '</a>';
			$html .= '</div><!-- / .relational_thumb -->';
			$html .= '<div class="relational_text">';
			$html .= '<h4>' . $post_title . '</h4>';
			$html .= $content ? '<p>' . $post_content . '</p>' : '';

			$html .= implode( ', ', $post_term_array );

			$html .= '</div><!-- / .relational_text -->';
			$html .= '</div><!-- / .relational_detail -->';

		}

		$html .= '</div><!-- / .relational_post -->';
		$html .= '</div><!-- / .relational_post_outer -->';

	}

	echo $html;

}
