<?php
/**
 * Genesis Widget Class.
 *
 * @package genesis-responsive-slider
 */

/**
 * Slideshow Widget Class
 */
class Genesis_Responsive_Slider_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
			$widget_ops = array(
				'classname'   => 'genesis_responsive_slider',
				'description' => __( 'Displays a slideshow inside a widget area', 'genesis-responsive-slider' ),
			);

			$control_ops = array(
				'width'   => 200,
				'height'  => 250,
				'id_base' => 'genesisresponsiveslider-widget',
			);
			parent::__construct( 'genesisresponsiveslider-widget', __( 'Genesis - Responsive Slider', 'genesis-responsive-slider' ), $widget_ops, $control_ops );
	}

	/**
	 * Save settings.
	 *
	 * @param  array $settings Settings.
	 */
	public function save_settings( $settings ) {
		$settings['_multiwidget'] = 0;
			update_option( $this->option_name, $settings );
	}

	/**
	 * Display widget function
	 *
	 * @param  array $args     Arguments.
	 * @param  array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		echo $before_widget;

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		$term_args = array();

		if ( genesis_get_responsive_slider_option( 'post_type' ) !== 'page' ) {

			if ( genesis_get_responsive_slider_option( 'posts_term' ) ) {

				$posts_term = explode( ',', genesis_get_responsive_slider_option( 'posts_term' ) );

				if ( 'category' === $posts_term['0'] ) {
					$posts_term['0'] = 'category_name';
				}

				if ( 'post_tag' === $posts_term['0'] ) {
					$posts_term['0'] = 'tag';
				}

				if ( isset( $posts_term['1'] ) ) {
					$term_args[ $posts_term['0'] ] = $posts_term['1'];
				}
			}

			if ( ! empty( $posts_term['0'] ) ) {

				if ( 'category' === $posts_term['0'] ) {
					$taxonomy = 'category';
				} elseif ( 'post_tag' === $posts_term['0'] ) {
					$taxonomy = 'post_tag';
				} else {
					$taxonomy = $posts_term['0'];
				}
			} else {
				$taxonomy = 'category';
			}

			if ( genesis_get_responsive_slider_option( 'exclude_terms' ) ) {

				$exclude_terms                       = explode( ',', str_replace( ' ', '', genesis_get_responsive_slider_option( 'exclude_terms' ) ) );
				$term_args[ $taxonomy . '__not_in' ] = $exclude_terms;

			}
		}

		if ( genesis_get_responsive_slider_option( 'posts_offset' ) ) {
			$my_offset           = genesis_get_responsive_slider_option( 'posts_offset' );
			$term_args['offset'] = $my_offset;
		}

		if ( genesis_get_responsive_slider_option( 'post_id' ) ) {
			$ids = explode( ',', str_replace( ' ', '', genesis_get_responsive_slider_option( 'post_id' ) ) );
			if ( 'include' === genesis_get_responsive_slider_option( 'include_exclude' ) ) {
				$term_args['post__in'] = $ids;
			} else {
				$term_args['post__not_in'] = $ids;
			}
		}

		$query_args = array_merge(
			$term_args,
			array(
				'post_type'      => genesis_get_responsive_slider_option( 'post_type' ),
				'posts_per_page' => genesis_get_responsive_slider_option( 'posts_num' ),
				'orderby'        => genesis_get_responsive_slider_option( 'orderby' ),
				'order'          => genesis_get_responsive_slider_option( 'order' ),
				'meta_key'       => genesis_get_responsive_slider_option( 'meta_key' ),
			)
		);

		$query_args = apply_filters( 'genesis_responsive_slider_query_args', $query_args );
		add_filter( 'excerpt_more', 'genesis_responsive_slider_excerpt_more' );
		?>

		<div id="genesis-responsive-slider">
			<div class="flexslider">
				<ul class="slides">
					<?php
					$slider_posts = new WP_Query( $query_args );

					if ( $slider_posts->have_posts() ) {
						$show_excerpt  = genesis_get_responsive_slider_option( 'slideshow_excerpt_show' );
						$show_title    = genesis_get_responsive_slider_option( 'slideshow_title_show' );
						$show_type     = genesis_get_responsive_slider_option( 'slideshow_excerpt_content' );
						$show_limit    = genesis_get_responsive_slider_option( 'slideshow_excerpt_content_limit' );
						$more_text     = genesis_get_responsive_slider_option( 'slideshow_more_text' );
						$no_image_link = genesis_get_responsive_slider_option( 'slideshow_no_link' );
					}

					while ( $slider_posts->have_posts() ) :
						$slider_posts->the_post();

						?>

					<li>

						<?php
						if ( 1 === $show_excerpt || 1 === $show_title ) {
							?>
						<div class="slide-excerpt slide-<?php the_ID(); ?>">
							<div class="slide-background"></div><!-- end .slide-background -->
							<div class="slide-excerpt-border ">
							<?php
							if ( 1 === $show_title ) {
								?>
								<h2><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
								<?php
							}
							if ( $show_excerpt ) {
								if ( 'full' !== $show_type ) {
									the_excerpt();
								} elseif ( $show_limit ) {
									the_content_limit( (int) $show_limit, $more_text );
								} else {
									the_content( $more_text );
								}
							}

							?>
							</div><!-- end .slide-excerpt-border  -->
						</div><!-- end .slide-excerpt -->
							<?php
						}
						?>

						<div class="slide-image">
							<?php
							if ( $no_image_link ) {
								?>
							<img src="<?php genesis_image( 'format=url&size=slider' ); ?>" alt="<?php the_title(); ?>" />
								<?php
							} else {
								?>
							<a href="<?php the_permalink(); ?>" rel="bookmark"><img src="<?php genesis_image( 'format=url&size=slider' ); ?>" alt="<?php the_title(); ?>" /></a>
								<?php

							} // $no_image_link
							?>
						</div><!-- end .slide-image -->

					</li>
				<?php endwhile; ?>
				</ul><!-- end ul.slides -->
			</div><!-- end .flexslider -->
		</div><!-- end #genesis-responsive-slider -->

		<?php
		echo $after_widget;
		wp_reset_query();
		remove_filter( 'excerpt_more', 'genesis_responsive_slider_excerpt_more' );

	}

	/**
	 * Widget Options.
	 *
	 * @param  instance $instance Widget Options instance.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title    = $instance['title'];
		?>
	<p><label for="<?php echo esc_html( $this->get_field_id( 'title' ) ); ?>"><?php __e( 'Title:', 'genesis-responsive-slider' ); ?> <input class="widefat" id="<?php echo esc_html( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<?php
			echo '<p>';
			printf( esc_html( __e( 'To configure slider options, please go to the <a href="%s">Slider Settings</a> page.', 'genesis-responsive-slider' ) ), esc_url( menu_page_url( 'genesis_responsive_slider', 0 ) ) );
			echo '</p>';
	}

	/**
	 * Update instance.
	 *
	 * @param  instance $new_instance New instance.
	 * @param  instance $old_instance Old instance.
	 * @return instance Instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$new_instance      = wp_parse_args( (array) $new_instance, array( 'title' => '' ) );
		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );
		return $instance;
	}

}

/**
 * Used to exclude taxonomies and related terms from list of available terms/taxonomies in widget form().
 *
 * @since 0.9
 * @author Nick Croft
 *
 * @param string $taxonomy 'taxonomy' being tested.
 * @return string
 */
function genesis_responsive_slider_exclude_taxonomies( $taxonomy ) {

	$filters = array( '', 'nav_menu' );
	$filters = apply_filters( 'genesis_responsive_slider_exclude_taxonomies', $filters );

	return ( ! in_array( $taxonomy->name, $filters, true ) );

}

/**
 * Used to exclude post types from list of available post_types in widget form().
 *
 * @since 0.9
 * @author Nick Croft
 *
 * @param string $type 'post_type' being tested.
 * @return string
 */
function genesis_responsive_slider_exclude_post_types( $type ) {

	$filters = array( '', 'attachment' );
	$filters = apply_filters( 'genesis_responsive_slider_exclude_post_types', $filters );

	return ( ! in_array( $type, $filters, true ) );

}

/**
 * Returns Slider Option
 *
 * @param string $key key value for option.
 * @return string
 */
function genesis_get_responsive_slider_option( $key ) {
	return genesis_get_option( $key, GENESIS_RESPONSIVE_SLIDER_SETTINGS_FIELD );
}

/**
 * Echos Slider Option
 *
 * @param string $key key value for option.
 */
function genesis_responsive_slider_option( $key ) {

	if ( ! genesis_get_responsive_slider_option( $key ) ) {
		return false;
	}

	echo esc_html( genesis_get_responsive_slider_option( $key ) );
}
