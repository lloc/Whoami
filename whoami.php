<?php

/*
Plugin Name: WP-Whoami
Plugin URI: http://lloc.de/
Description: Just another widget to show a photo, a bio and some social media links with nice webfont-icons
Version: 0.2
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
        $args = array(
            'classname'   => 'Whoami_Widget',
            'description' => __( 'Displays a author description widget', 'whoami' ),
        );
        $this->WP_Widget( 'Whoami_Widget', 'Whoami', $args );
        $this->whoami_obj = Whoami_Frontend::instance();
    }

    public function form( $instance ) {
        $instance = wp_parse_args(
            (array) $instance,
            array( 'title' => '' )
        );
        $title = $instance['title'];
        printf(
            '<p><label for="%1$s">%2$s</label> <input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s" /></p><p><label for="author">%5$s</label> %6$s</p>', 
            $this->get_field_id( 'title' ),
            __( 'Title:', 'whoami' ),
            $this->get_field_name( 'title' ),
            attribute_escape( $title ),
            __( 'Author:', 'whoami' ),
            wp_dropdown_users( array( 'name' => 'author', 'echo' => false ) )
        );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title']  = $new_instance['title'];
        $instance['author'] = $new_instance['author'];
        return $instance;
    }

    public function widget( $args, $instance ) {
        global $authordata;
        extract( $args, EXTR_SKIP );
        $author = isset ( $authordata->ID ) ? $authordata->ID : $instance['author'];
        echo $before_widget;
        if ( !empty( $instance['title'] ) )
            echo $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title;
        echo $this->whoami_obj->get( $author );
        echo $after_widget;
    }
 
}
add_action( 'widgets_init', create_function( '', 'return register_widget( "Whoami_Widget" );' ) );

class Whoami_Admin {

    public static function instance() {
        load_plugin_textdomain( 'whoami', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        $obj = new self();
        add_filter( 'user_contactmethods', array( $obj, 'add' ), 10, 1 );
    }

    public function bio_input_name() {
        $blog_id = get_current_blog_id();
        return sprintf( 'bio_%d', $blog_id );
    
    }

    public function networks() {
        $networks = array(
            'facebook'   => array( 'Facebook', 'F' ),
            'googleplus' => array( 'Google+', 'g' ),
            'twitter'    => array( 'Twitter', 't' ),
            'github'     => array( 'GitHub', 'G' ),
            'linkedin'   => array( 'LinkedIn', 'l' ),
            'foursquare' => array( 'Foursquare', 'j' ),
            'wordpress'  => array( 'WordPress', 'w' ),
        );
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
            if ( !isset( $ucmethods[$key] ) )
                $ucmethods[$key] = $value[0];
        }
        $ucmethods[$this->bio_input_name()] = __( 'Bio', 'whoami' );
        return $ucmethods;
	}

}
add_action( 'admin_init', 'Whoami_Admin::instance' );

class Whoami_Frontend extends Whoami_Admin {

    protected $size = 80;

    public static function instance() {
        $obj = new self();
        add_action( 'wp_enqueue_scripts', array( $obj, 'css' ) );
        return $obj;
    }

    public function css() {
        $css = array(
            'whoami-style' => plugins_url( '/css/style.css', __FILE__ ),
        );
        if ( has_filter( 'whoami_frontend_add_css' ) ) {
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
            if ( !empty( $href ) )
                $temp .= sprintf(
                    '<li><a class="%s" href="%s" rel="me">%s</a></li>',
                    $key,
                    $href, 
                    $value[1]
                );
        }
        if ( $temp )
            $temp = '<ul class="socialicons">' . $temp . '</ul>';
        return sprintf(
            '<p>%s%s</p>%s',
            get_avatar( $user_id, $this->size ),
            get_user_meta( $user_id, $this->bio_input_name(), true ),
            $temp
        );
    }

}
