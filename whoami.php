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

class Whoami {

    protected $arr = array(
        'facebook'   => 'Facebook Profile',
        'twitter'    => 'Twitter Profile',
        'googleplus' => 'Google+ Profile',
        'wordpress'  => 'WordPress Profile',
        'linkedin'   => 'LinkedIn Profile',
        'rss'        => '',
    );

    public function __construct() {
    	add_filter( 'user_contactmethods', array( $this, 'add' ), 10, 1 );
    }

    function add( $methods ) {
        foreach ( $this->arr as $key => $value ) {
            if ( '' != $value && !isset( $methods[$key] ) )
                $methods[$key] = $value;
        }
        return $methods;
	}

}
if ( is_admin() ) {
    $whoami = new Whoami();
}
else {

}
