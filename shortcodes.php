<?php

//========================================================================
//! This shortcode will use OAuth2 to POST CODE and to retrive the TOKEN
//========================================================================

add_shortcode("stripe_token", "stripe_token_function");

function stripe_token_function() {
 if (get_option('stripe_appid') && get_option('stripe_secret')) {
  if (isset($_GET['code'])) { // Redirect w/ code
 
    $oauth  = (new StripeOAuth(APPID,SECRET));
    $token  = $oauth->getAccessToken($_GET['code']);
    $key    = $oauth->getPublishableKey($_GET['code']);
    $userid = $oauth->getUserId($_GET['code']); //This needs to be changed.
    
    global $wpdb;
    $table = $wpdb->prefix."stripe_connect";
    
    $wpdb->insert($table , array(
      'time'                    => current_time('mysql'), 
      'access_token'            => $token, 
      'stripe_publishable_key'  => $key, 
      'stripe_user_id'          => $userid)
    );
    
    $response = '<h4>Thank you for connecting with Stripe. This information has been saved in the database and can be viewed in the Admin Panel.</h4>';
    
    $response .= '<strong>Access token: </strong>' . ($token) . '<br /><strong>Key:</strong> ' . ($key) . '<br />'. '<strong>UserId:</strong> ' . ($userid) . '';
    
    return $response;

  } elseif (isset($_GET['error_description'])) {
   return $_GET['error_description']; 
  } else {
    return 'An error has occured.';
  }
} else {
  return NOKEY;
  }
}

//=====================
//! Shortcode Builder
//=====================

add_shortcode("stripe", "stripe_register_function");

function stripe_register_function($atts) {
	//extract short code attributes
	extract( shortcode_atts( array(
		'style'   => '1',
		'type'    => 'register',
		'text'    => 'Register with Stripe',
		'class'
		), $atts ));
	
	//switch the style variables
    switch ($style) {
        case '1':
            $btn_class  = 'stripe-connect';
            break;
        case '2':
            $btn_class  = 'stripe-connect dark';
            break;
        case '3':
            $btn_class  = 'stripe-connect light-blue';
            break;
        case '4':
            $btn_class  = 'stripe-connect light-blue dark';
            break;   
        }
	
	$oauth  = (new StripeOAuth(APPID,SECRET));
    
    $url    = $oauth->getAuthorizeUri();
    $button = '<a class="'.$btn_class.'" href="'.$url.'&stripe_landing='.$type.'"><span>'.$text.'</span></a>';
    
    if (get_option('stripe_appid') && get_option('stripe_secret')) {
      return $button;
    } else {
      return NOKEY;
    }
}
    
?>  