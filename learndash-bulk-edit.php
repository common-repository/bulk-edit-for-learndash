<?php
/**
 * Plugin Name:  Bulk Edit for Learndash
 * Plugin URI: https://wptrat.com/learndash-bulk-edit /
 * Description:  Bulk edit learnDash courses prices.
 * Author: Luis Rock
 * Author URI: https://wptrat.com/
 * Version: 1.2.0
 * Text Domain: learndash-bulk-edit
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   Bulk Edit for Learndash
 */


if ( ! defined( 'ABSPATH' ) ) exit;
		
// Requeiring plugin files
require_once('admin/trbe-settings.php');
require_once('includes/functions.php');

add_action( 'init', 'trbe_load_textdomain' );
function trbe_load_textdomain() {
  load_plugin_textdomain( 'learndash-bulk-edit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

//Admin CSS
function trbe_enqueue_admin_script( $hook ) {
    global $trbe_settings_page;
    if( $hook != $trbe_settings_page ) {
        return;
    }
    wp_enqueue_style('trbe_admin_style', plugins_url('assets/css/trbe-admin.css',__FILE__ ));
    wp_enqueue_script('trbe_admin_js', plugins_url('assets/js/trbe-admin.js',__FILE__ ), ['jquery'],'1.0.0',true);
    wp_localize_script( 'trbe_admin_js', 'trbe_js_object',
        array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            '_wpnonce' => wp_create_nonce('trbe_nonce')
            )
    );
}
add_action( 'admin_enqueue_scripts', 'trbe_enqueue_admin_script' );


//add hooks and actions
add_action( "wp_ajax_trbe_ld_bulk_edit", 'trbe_ld_bulk_edit' );
add_action( "wp_ajax_trbe_ld_course_options", 'trbe_ld_course_options' );