<?php
/* Plugin Name: Stripe Connect
Plugin URI: http://en.gg
Description: Stripe Connect for Engage Payments
Version: 1.0
Author: Engage
Author URI: http://en.gg/
License: GPLv2 or later
*/

global $stripe_connect_db_version;
$stripe_db_version = "1.0";

define ('NOKEY', "Please enter you App ID and Secret Key in the settings page.");
define ('APPID', get_option( 'stripe_appid' ));
define ('SECRET', get_option( 'stripe_secret' ));

//===========================================
//! Enqueue and Register Scripts and Styles
//===========================================

function stripe_connect_init() {
   	wp_register_style( 'stripe-button', plugins_url('css/stripe.css', __FILE__) );
   	
   	wp_enqueue_script('jquery');
    wp_enqueue_style( 'stripe-button' ); 
}
add_action( 'wp_enqueue_scripts', 'stripe_connect_init' );


//===================
//! Require classes
//===================
require(plugin_dir_path( __FILE__ ) . 'admin/panel.php');
require(plugin_dir_path( __FILE__ ) . 'shortcodes.php');
require(plugin_dir_path( __FILE__ ) . 'assets/OAuth2Client.php');
require(plugin_dir_path( __FILE__ ) . 'assets/StripeOAuth.class.php');

//=======================================================================
//! When we activate the plugin create your tables to store all tokkens
//=======================================================================

function stripe_connect_activation() {
  
   global $wpdb;
   global $stripe_connect_version;

   $table_name = $wpdb->prefix . "stripe_connect";
      
   $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          access_token text NOT NULL,
          stripe_user_id text NOT NULL,
          stripe_publishable_key text NOT NULL,
          UNIQUE KEY id (id)
          );";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );
 
   add_option( "stripe_connect_db_version", $stripe_connect_db_version );
}
register_activation_hook(__FILE__, 'stripe_connect_activation');


//===========================================================================
//! When we deactivate the plugin clear tables - if delete tables is chosen
//===========================================================================

function stripe_connect_deactivation() {
  global $wpdb;
  $table = $wpdb->prefix."stripe_connect";

	  delete_option('stripe_appid');
	  delete_option('stripe_secret');
	  delete_option('stripe_connect_db_version');

	$wpdb->query("DROP TABLE IF EXISTS $table");
}
register_deactivation_hook(__FILE__, 'stripe_connect_deactivation');
?>