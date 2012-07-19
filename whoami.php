<?php

/*
Plugin Name: Whoami
Plugin URI: http://lloc.de/wp-whoami
Description: First prototype of my own author description widget
Version: 0.1
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
            'description' => 'Displays a author description widget'
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
        printf( '<p><label for="%1$s">Title:</label> <input class="widefat" id="%1$s" name="%2$s" type="text" value="%3$s" /></p>', $this->get_field_id('title'), $this->get_field_name('title'), attribute_escape( $title ) );
        echo '<p><label for="author">Author:</label>';
        wp_dropdown_users( array( 'name' => 'author' ) );
        echo '</p>';
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

    protected $arr = array(
        'facebook'      => 'Facebook',
        'googleplus'    => 'Google+',
        'twitter'       => 'Twitter',
        'github'        => 'GitHub',
        'linkedin'      => 'LinkedIn',
        'wordpress'     => 'WordPress',
        'stackoverflow' => 'Stackoverflow',
    );

    public static function instance() {
        add_filter( 'user_contactmethods', array( new self(), 'add' ), 10, 1 );
    }

    public function bio_input_name() {
        $blog_id = get_current_blog_id();
        return sprintf( 'bio_%d', $blog_id );
    
    }

    public function add( $ucmethods ) {
        foreach ( $this->arr as $key => $value ) {
            if ( !isset( $ucmethods[$key] ) )
                $ucmethods[$key] = $value;
        }
        $ucmethods[$this->bio_input_name()] = __( 'Bio' );
        return $ucmethods;
	}

}
add_action( 'admin_init', 'Whoami_Admin::instance' );

class Whoami_Frontend extends Whoami_Admin {

    protected $size = 80;

    public static function instance() {
        $obj = new self();
        add_action( 'wp_enqueue_scripts', array( $obj, 'add' ) );
        return $obj;
    }

    public function add() {
        wp_register_style( 'whoami-style', plugins_url( 'style.css', __FILE__ ) );
        wp_enqueue_style( 'whoami-style' );
    }

    public function get( $user_id ) {
        $temp = '';
        foreach ( array_keys( $this->arr ) as $key ) {
            $value = get_user_meta( $user_id, $key, true );
            if ( !empty( $value ) )
                $temp .= sprintf(
                    '<li><a class="%s" href="%s" rel="nofollow"></a></li>',
                    $key,
                    $value
                );
        }
        if ( $temp )
            $temp = '<ul class="socialicons bw">' . $temp . '</ul>';
        return sprintf(
            '<p>%s%s</p>%s',
            get_avatar( $user_id, $this->size ),
            get_user_meta( $user_id, $this->bio_input_name(), true ),
            $temp
        );
    }

}
