<?php
if (is_admin()) require_once(ABSPATH . 'wp-includes/pluggable.php');

class stripeConnect {

  public function __construct() {
    if(is_admin()) {
	    add_action('admin_menu', array($this, 'stripe_register_settings'));
	    add_action('admin_init', array($this, 'page_init'));
      add_action('admin_init', array($this, 'stripe_connect_scripts'));
    }
  }
    
  public function stripe_register_settings() {
    add_options_page('Stripe Connect', 'Stripe Connect', 'administrator', 'stripe-connect-settings',array($this, 'stripe_display_settings'));
  }

  public function stripe_display_settings() { ?>
    <div class="wrap">
      <?php screen_icon(); ?>
      <h2>Stripe Connect Settings</h2>
      <p>For information on how to use this plugin see the <a href="#" target="_blank">Online Documentation</a>.</p>
      <form method="post" action="options.php"><?php
        settings_fields('stripe-connect-setting-group'); // This prints out all hidden setting fields
        do_settings_sections('stripe-connect'); ?>
        <?php submit_button(); ?>
      </form>
    </div><?php
    
    global $wpdb;
    $table = $wpdb->prefix."stripe_connect";
    $saved_tokens = $wpdb->get_results("SELECT * FROM $table;"); ?>
    <h3>Saved Access Tokens</h3>
    <table border="1" class="widefat" style="width:900px">
     <thead>
       <th scope="col">Time</th>
       <th scope="col">Access Token</th>
       <th scope="col">Plublishable Key</th>
       <th scope="col">Stripe User Id</th>
       <th scope="col">Delete?</th>
      </thead><?
      
      $i = 0;
      foreach($saved_tokens as $token) {
        if ($i % 2 == 0){ ?>
          <tr><?
        } else { ?>
          <tr class='alternate'><?
        } ?>
        <td><?php echo $token->time; ?></td>
        <td><?php echo $token->access_token; ?></td>
        <td><?php echo $token->stripe_publishable_key; ?></td>
        <td><?php echo $token->stripe_user_id; ?></td>
        <td>
          <form action="?page=stripe-connect-settings&noheader=true" method="post">
            <input type="hidden" name="option_page" value="stripe-connect-setting-group">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo $token->id ?>">
            <input type="submit" class="button-primary" id="stripe-connect-delete" value="Delete">
          </form>
        </td>
        </tr><?php 
        $i++;
      } ?>
    </table><?
  }
  
  public function page_init() {	
	
    register_setting('stripe-connect-setting-group', 'stripe_appid', array($this, 'stripe_process_settings'));
    register_setting('stripe-connect-setting-group', 'stripe_secret', array($this, 'stripe_process_settings'));
    
    //Print the info
    add_settings_section(
      'master-settings',
	    'Settings',
	    array($this, 'print_section_info'),
	    'stripe-connect'
    );	
		
    add_settings_field(
	    'stripe_appid', 
	    'Stripe Client ID', 
	    array($this, 'create_text_field'), 
	    'stripe-connect',
	    'master-settings',
      array( 
        'name' => 'stripe_appid', 
        'value' => get_option('stripe_appid'), 
        'id' => 'stripe_appid')      
    );
    
    add_settings_field(
	    'stripe_secret', 
	    'Stripe Secret Key', 
	    array($this, 'create_text_field'), 
	    'stripe-connect',
	    'master-settings',
      array( 
        'name' => 'stripe_secret', 
        'value' => get_option('stripe_secret'), 
        'id' => 'stripe_secret'
       )     
    );

  }
  
  function stripe_connect_scripts() {
    wp_enqueue_script('stripe-connect-js', plugins_url( '/js/main.js', dirname(__FILE__) ) , array( 'jquery' ) );
    }
  
  public function stripe_process_settings($input) {
  
    $option_name = $input['name']; ;
    $new_value = $input['value'] ;

    if ( get_option( $option_name ) != $new_value ) {
      update_option( $option_name, $new_value );
    } else {
      add_option( $option_name, $new_value );
    }
   
    return $input;
   
  }
  
  public function stripe_delete_data($id) {
    global $wpdb;
    $table = $wpdb->prefix."stripe_connect";
    $deleted = $wpdb->delete($table, array( 'ID' => $id ) );
    return $deleted;
  }
	
  public function print_section_info() {
    print 'Enter your Stripe settings below:<br>';
  }
	
  public function create_text_field($args) { ?>
    <input type="text" size="50" id="<?php echo $args['id'];?>" name="<?php echo $args['name'];?>" value="<?php echo $args['value'];?>" /><?php
  }

}

$stripeConnect = new stripeConnect();

if (!empty($_POST['action'])
  && 'delete' == $_POST['action'] 
  && isset($_POST['id']) ) {    
     if($stripeConnect->stripe_delete_data($_POST['id'])) {
      wp_safe_redirect(add_query_arg('updated','true',wp_get_referer()));
     } else {
      wp_safe_redirect(add_query_arg('updated','false',wp_get_referer()));
     }
}