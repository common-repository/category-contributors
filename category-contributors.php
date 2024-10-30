<?php
/*
Plugin Name: Category Contributors
Plugin URI:
Description: Displays a list of contributors from a category, and on posts will list authors who have contributed to the same category.
Author: Hors Hipsrectors
Author URI:
Tags: adopt-me
Version: 2017.08.13
*/

/**
 * Category Contributors core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://wordpress.org/extend/plugins/category-contributors/
 *
 * @package		Category Contributors
 * @copyright		Copyright ( c ) 2017, Hors Hipsrectors
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 ( or newer )
 *
 * @since		Category Contributors 1.0
 */


class CategoryContributorsWidget extends WP_Widget
{
  function CategoryContributorsWidget() {
	$widget_options = array(  'classname' => 'CategoryContributorsWidget',
							'description' => 'Displays a list of contributors in a category'
					);
	$this->WP_Widget( 'CategoryContributorsWidget', 'Category Contributors', $widget_options );
  }

  function form( $instance ) {
	$instance = wp_parse_args( ( array ) $instance, array( 'title' => '' ) );
	$title = $instance['title'];
	$title = $instance['sort_option'];
  ?>
  <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title' );?>: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
  <?php
  }

  function update( $new_instance, $old_instance ) {
	$instance = $old_instance;
	$instance['title'] = $new_instance['title'];
	$instance['sort_option'] = $new_instance['sort_option'];
	return $instance;
  }

  function widget( $args, $instance ) {
	extract( $args, EXTR_SKIP );

	echo $before_widget;
	$title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
	$sort_option = $instance['sort_option'];

	if ( is_category() ) {
	$query =  'showposts=-1&cat=' . get_query_var( 'cat' );
	} elseif ( is_home() ) {
	$query =  'showposts=-1';
	} elseif ( is_single() ) {
	global $post;
	$category = get_the_category( $post->ID );
	$query =  'showposts=-1&cat=' . $category[0]->term_id;
	}

	if ( isset( $query ) ) {

	$get_category_posts = get_posts( $query );

	if ( ! empty ( $get_category_posts ) )  {

		foreach( $get_category_posts as $check_post ) {
		$cat_authors[ $check_post->post_author ] = $cat_authors[ $check_post->post_author ] + 1;
		}

		if ( $cat_authors ) {
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

		if ( count( $cat_authors ) > 1 ) {

			asort( $cat_authors, SORT_NUMERIC );

			foreach ( $cat_authors as $key=>$value ) {
			$cat_authors_new[ $key ] = $key;
			}

			$sorted_authors = array_reverse( $cat_authors_new, true );

		} else {
			$sorted_authors = array_flip( $cat_authors );
		}

		$sorted_authors = array_slice( $sorted_authors, 0, 10 );

		if( ! empty( $sorted_authors ) ) {

			echo '<ul class="category-contributors">';
			foreach ( $sorted_authors as $author ) {

			$author_details = get_userdata( $author );
			$author_user_description = get_user_meta( $author );

			$author_description = $author_user_description[ 'description' ][0];

			if ( function_exists( 'get_simple_local_avatar' ) )
				$author_avatar = get_simple_local_avatar( $author_list );

			if ( ! empty( $author_details ) )
				echo '<li>';

				if ( ! empty( $author_avatar ) )
				echo '<div class-"author-photo">' . $author_avatar . '</div>';

				echo '<div class-"author-name"><a href="' . get_author_posts_url( $author_details->ID ) . '">' . $author_details->display_name . '</a></strong>';

				if ( ! empty( $author_description ) )
				echo '<br/><div class="author-user-description">' . $author_description . '</div>';

				echo '</li>';

			}
		echo '</ul>';
		}
		}
	}
	}

	echo $after_widget;
  }

}

add_action( 'widgets_init',
  function(){
	register_widget( 'CategoryContributorsWidget' );
  }
 );