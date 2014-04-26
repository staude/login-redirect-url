<?php
/*
Plugin Name: Login Redirect Url
Plugin URI: http://www.staude.net/wordpress/plugins/LoginRedirectUrl
Description: Redirect a user after login to a specified URL or Page
Author: Frank Staude
Version: 0.1
Author URI: http://www.staude.net/
Compatibility: WordPress 3.9
Text Domain: login-redirect-url
Domain Path: languages
 * 

Copyright 2014  Frank Staude  (email : frank@staude.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists( 'login_redirect_url' ) ) {

    include_once dirname( __FILE__ ) . '/class-login-redirect-url.php';

    /**
     * Delete plugindate on uninstall
     * 
     * Delete the usermeta and options data from the plugin on uninstalling
     * the plugin.
     */
    function login_redirect_url_uninstall() {
        global $wpdb;
        $wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key = 'login_redirect_url';" );
        $wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key = 'login_redirect_page';" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE meta_key = 'login_redirect_url_hosts';" );
    }

    register_uninstall_hook( __FILE__,  'login_redirect_url_uninstall' );

    $login_redirect_url = new login_redirect_url();

}
