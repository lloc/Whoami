<?php

/*
Plugin Name: WP-Whoami
Plugin URI: http://lloc.de/
Description: Just another widget to show a photo, a bio and some social media links with nice webfont-icons
Version: 1.1
Author: Dennis Ploetner
Author URI: http://lloc.de/
*/

/*
Copyright 2012 Dennis Ploetner (email : re@lloc.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

class Whoami_Widget extends WP_Widget {

	protected $whoami_obj;

	public function __construct() {
		$args = [
			'classname'   => 'Whoami_Widget',
			'description' => __( 'Displays a author description widget', 'whoami' ),
		];

		parent::__construct( 'Whoami_Widget', 'Whoami', $args );

		$this->whoami_obj = Whoami_Frontend::init();
	}

	public function form( $instance ) {
		$instance = wp_parse_args(
			( array ) $instance,
			[
				'title'  => '',
				'author' => 0,
			]
		);
		printf(
			'<p><label for="%1$s">%2$s</label> <input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s" /></p><p><label for="author">%5$s</label> %6$s</p>',
			$this->get_field_id( 'title' ),
			__( 'Title:', 'whoami' ),
			$this->get_field_name( 'title' ),
			esc_attr( $instance['title'] ),
			__( 'Author:', 'whoami' ),
			wp_dropdown_users( [ 'name' => 'author', 'echo' => false, 'selected' => $instance['author'] ] )
		);
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']  = ( isset( $new_instance['title'] ) ? $new_instance['title'] : '' );
		$instance['author'] = ( isset( $new_instance['author'] ) ? $new_instance['author'] : '' );

		return $instance;
	}

	public function widget( $args, $instance ) {
		global $authordata;

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo apply_filters( 'widget_title', $instance['title'] );
			echo $args['after_title'];
		}

		$author = ! empty( $authordata->ID ) ? $authordata->ID : $instance['author'];
		echo $this->whoami_obj->get( $author );

		echo $args['after_widget'];
	}

}

add_action( 'widgets_init', function () {
	register_widget( 'Whoami_Widget' );
} );

class Whoami_Admin {

	public static function init() {
		load_plugin_textdomain( 'whoami', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		add_filter( 'user_contactmethods', [ Whoami_Admin::class, 'add' ], 10, 1 );
	}

	public function bio_input_name() {
		return sprintf( 'bio_%d', get_current_blog_id() );
	}

	public function networks() {
		$networks = [
			'facebook'   => [ 'Facebook', 'jv-github' ],
			'googleplus' => [ 'Google+', 'jv-google' ],
			'twitter'    => [ 'Twitter', 'jv-twitter' ],
			'github'     => [ 'GitHub', 'jv-github' ],
			'linkedin'   => [ 'LinkedIn', 'jv-linkedin' ],
			'foursquare' => [ 'Foursquare', 'jv-foursquare' ],
			'wordpress'  => [ 'WordPress', 'jv-wordpress' ],
		];

		if ( has_filter( 'whoami_admin_networks' ) ) {
			$networks = apply_filters(
				'whoami_admin_networks',
				$networks
			);
		}

		return $networks;
	}

	public function add( $ucmethods ) {
		foreach ( $this->networks() as $key => $value ) {
			if ( ! isset( $ucmethods[ $key ] ) ) {
				$ucmethods[ $key ] = $value[0];
			}
		}

		$ucmethods[ $this->bio_input_name() ] = __( 'Bio', 'whoami' );

		return $ucmethods;
	}

}

add_action( 'admin_init', [ Whoami_Admin::class, 'init' ] );

class Whoami_Frontend extends Whoami_Admin {

	protected $size = 80;

	public static function init() {
		add_action( 'wp_enqueue_scripts', [ Whoami_Frontend::class, 'css' ] );

		return new self();
	}

	public function css() {
		$css = [
			'whoami-style' => plugins_url( '/css/style.css', __FILE__ ),
		];

		if ( has_filter( 'whoami_frontend_css' ) ) {
			$css = (array) apply_filters(
				'whoami_frontend_css',
				$css
			);
		}

		foreach ( $css as $handle => $src ) {
			wp_register_style( $handle, $src );
			wp_enqueue_style( $handle );
		}
	}

	public function get( $user_id ) {
		$temp = '';

		foreach ( $this->networks() as $key => $value ) {
			$href = get_user_meta( $user_id, $key, true );
			if ( ! empty( $href ) ) {
				if ( has_filter( 'whoami_frontend_get_li' ) ) {
					$temp .= ( string ) apply_filters(
						'whoami_frontend_get_li',
						$key,
						$value,
						$href
					);
				} else {
					$temp .= sprintf(
						'<li><a class="%s" href="%s" title="%s" rel="me">%s</a></li>',
						$value[1],
						$href,
						sprintf( __( 'My profile at %s', 'whoami' ), $value[0] ),
						$value[0]

					);
				}
			}
		}

		if ( $temp ) {
			if ( has_filter( 'whoami_frontend_get_ul' ) ) {
				$temp = (string) apply_filters(
					'whoami_frontend_get_ul',
					$temp
				);
			} else {
				$temp = sprintf(
					'<ul class="socialicons">%s</ul>',
					$temp
				);
			}
		}

		if ( has_filter( 'whoami_frontend_get_p' ) ) {
			$temp = (string) apply_filters(
				'whoami_frontend_get_p',
				$temp,
				$user_id,
				$this
			);
		} else {
			$temp = sprintf(
				'<p>%s%s</p>%s',
				get_avatar( $user_id, $this->size ),
				get_user_meta( $user_id, $this->bio_input_name(), true ),
				$temp
			);
		}

		return $temp;
	}

}

/**
 * Prints the bio - stored in the current blog - of a specific user
 *
 * @param int $user_id
 */
function the_whoami_bio( $user_id ) {
	echo get_user_meta( $user_id, Whoami_Frontend::init()->bio_input_name(), true );
}
