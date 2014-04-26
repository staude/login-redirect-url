<?php

/*  Copyright 2014  Frank Staude  (email : frank@staude.net)

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

class login_redirect_url {
    
    /**
     * Constructor
     * 
     * Register all actions and filters
     */
    function __construct() {
        add_action( 'edit_user_profile',            array( 'login_redirect_url', 'user_startpage_option' ) );
        add_action( 'edit_user_profile_update',     array( 'login_redirect_url', 'update_startpage_option' ) );
        add_action( 'plugins_loaded',               array( 'login_redirect_url', 'load_translations' ) );
        add_filter( 'login_redirect',               array( 'login_redirect_url', 'redirect_user' ) , 10, 3 );
        add_filter( 'allowed_redirect_hosts',       array( 'login_redirect_url', 'allowed_hosts' ) );
        add_action( 'admin_menu',                   array( 'login_redirect_url', 'options_menu' ) );  
        add_action( 'admin_init',                   array( 'login_redirect_url', 'register_settings' ) );
        add_filter( 'manage_users_columns',         array( 'login_redirect_url', 'add_user_list_url_head') );
        add_filter( 'manage_users_custom_column',   array( 'login_redirect_url', 'add_user_list_url_column'), 10, 3);        
    }

    /**
     * Add userlist column title
     * 
     * Add the  title to the userlist for the new redirect url column
     * 
     * @param array $defaults
     * @return array
     */
    static public function add_user_list_url_head( $defaults ) {
        $defaults[ 'login-redirect-url' ]  = _x( 'Redirect URL', 'Userlist Columntitle', 'login-redirect-url');
        return $defaults;
    }
    
    /**
     * Add the redirect url to userlist column
     * 
     * @param string $retval
     * @param string $column_name
     * @param integer $user_id
     * @return string
     */
    static public function add_user_list_url_column( $retval, $column_name, $user_id ) {
        if ( $column_name == 'login-redirect-url' ) {
            $url = get_user_meta( $user_id, 'login_redirect_url', true );
            if ( $url != '' ) {
                $retval = $url;
            }
            $page = get_user_meta( $user_id, 'login_redirect_page', true );
            if ( $page != '' ) {
                $retval = get_permalink( $page );
            }
        }
        return $retval;
    }
    
    
    /**
     * load the plugin textdomain
     * 
     * load the plugin textdomain with translations
     */
    static public function load_translations() {
        load_plugin_textdomain( 'login-redirect-url', false, apply_filters ( 'login_redirect_url_translationpath', dirname( plugin_basename( __FILE__ )) . '/languages/' ) ); 
    }

    static public function options_menu() {
        $my_option_page = add_options_page(
                _x( 'Login Redirect URL', 'Backend Pagetitle','login-redirect-url'),  
                _x('Login redirect', 'Backend Menutitle' ,'login-redirect-url'), 
                'manage_options',
                __FILE__, 
                array( 'login_redirect_url', 'options_page' )
        );
        add_action( 'load-' . $my_option_page, array( 'login_redirect_url', 'options_page_add_help_tab') );
    }

    /**
     * Add helptab for plugins optionpage
     */
    static public function options_page_add_help_tab() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'	=> 'login_redirect_url_help',
            'title'	=> _x('Redirect URL', 'Plugin optionspage helptab', 'login-redirect-url' ),
            'content'	=> '<p>' . _x( 'To redirect users to exteral sites, this sites must be allowed in wordpress. ', 'Plugin optionspage helptext', 'login-redirect-url' ) . '</p>',
        ) );
        $screen->set_help_sidebar(
            '<p>' . _x('For more Information about the plugin visit this <a href="http://www.staude.net/wordpress/plugins/LoginRedirectUrl">site</a>.', 'Plugin optionspage helptab sidebar', 'login-redirect-url') . '<p>'
        );
    }
    
    /**
     * Register pluginsettings
     * 
     */
    static public function register_settings() {
        register_setting( 'login_redirect_url_settings', 'login_redirect_url_hosts' ); 
    }    
    
    
    /**
     * Generate the plugin optionspage
     * 
     * Generate the content for the optionspage for this plugin
     */
    static public function options_page() {
        ?>
        <div class="wrap"  id="loginredircturl">
        <h2><?php _e('Settings'); echo ' > '; _e( 'Login redirect URL', 'login-redirect-url' ); ?></h2>
        <p><?php _e( 'Settings to redirect user after login.', 'login-redirect-url' ); ?></p>
        <form method="POST" action="options.php">
        <?php 
        settings_fields( 'login_redirect_url_settings' ); 
        echo '<table class="form-table">';
        ?>
            <p>
                <textarea class="large-text code" name="login_redirect_url_hosts" id="login_redirect_url_hosts"><?php echo get_option( 'login_redirect_url_hosts' ); ?></textarea><br>
                <?php _e( 'Hostlist of allowed hosts, when redirecting to external hosts.', 'login-redirect-url' ); ?>
            </p>
        <br/>
        <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'login-redirect-url' )?>" />
        </form>
        </div>
        <?php   
    }
    
    /**
     * Add hosts to allows hosts array
     * 
     * Add the specified hosts to the wordpress allowd hosts array
     * 
     * @param array $hosts
     * @return array
     */
    static public function allowed_hosts( $hosts ) {
        $myhosts = get_option( 'login_redirect_url_hosts', '' );
        if ( $myhosts != '' ) {
            $myhosts = explode( "\n", $myhosts );
            foreach ( $myhosts as $value ) {
                $value = apply_filters( 'login_redirect_url_hostlist', $value );
                if ( $value != '' ) {
                    $hosts[] = $value;
                }
            }
        }
        return $hosts;
    }

    /**
     * redirect user
     * 
     * Redirect the user after login to URL or Page
     * 
     * @param string $redirect_to
     * @param type $request
     * @param object $user
     * @return string
     */
    static public function redirect_user ($redirect_to, $request, $user ) {
        if ( is_wp_error( $user ) ) { // Wordpress login_redirect action is called without a user?
            return $redirect_to;
        }
        if ( !is_admin() && !is_super_admin() ) {
            $url = get_user_meta($user->ID, 'login_redirect_url', true );
            if ($url != '') {
                return ( $url);
            }
            $page = get_user_meta( $user->ID, 'login_redirect_page', true );
            if ( $page != '' ) {
                return ( get_permalink( $page ) );
            }
        }
        return $redirect_to;
    }

    /**
     * create html for the user options page
     * 
     * creates html to extend the users personal options with a selectbox for 
     * a user customized startpage
     * 
     * @param object $user
     */
    static public function user_startpage_option( $user ) {
        $url = get_user_meta( $user->ID, 'login_redirect_url', true );
        $page = get_user_meta( $user->ID, 'login_redirect_page', true );
        ?>
        <table class="form-table">
             <tr>
                 <th><label for="login_redirect_url"><?php _e( 'User redirect to', 'login-redirect-url' ); ?></label></th>
                 <td>
                    <input class="regular-text" type="text" name="login_redirect_url" id="backend_startpage" value="<?php echo $url; ?>"><br>
                    <span class="description"><?php _e( 'Insert destination URL.', 'login-redirect-url' ); ?></span>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <?php wp_dropdown_pages( array( 'name' => 'login_redirect_page', 'selected' => $page, 'show_option_none' => __( 'none page selected', 'login-redirect-url' ) ) ); ?><br>
                    <span class="description"><?php _e( 'or select a page', 'login-redirect-url' ); ?></span>

                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Update the startpapge of a user
     * 
     * Saves the startpage of a user in the user_meta table with the key 'backend_startpage'
     * 
     * @param integer $user_id
     * @return void
     */
    static public function update_startpage_option( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) )
            return false;
        $starturl = ( $_POST['login_redirect_url'] );
        $startpage = ( $_POST['login_redirect_page'] );
        update_user_meta ( $user_id, 'login_redirect_url', $starturl );
        update_user_meta ( $user_id, 'login_redirect_page', $startpage );
    }
        
}
