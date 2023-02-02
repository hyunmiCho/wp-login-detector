<?php
/**
 * 
 *
 * This class relates to the Captcha field that is added to the WordPress login page
 *
 * @since      1.0.0
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */
class Guardgiant_Captcha {

    /**
	 * Display the HTML code for the captcha field  
	 *
	 * @since    1.0.0
     * 
	 */
    public static function show_captcha_field() {

        if (Guardgiant_Captcha::has_been_setup_correctly())
        {
            $settings = get_option( 'guardgiant-settings' );
            echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
            echo '<div class="g-recaptcha" style="margin-bottom: .3rem; -webkit-transform: scale(.9); transform: scale(.9); -webkit-transform-origin: 0 0; transform-origin: 0 0;" data-sitekey="' . $settings['recaptcha_site_key'] . '"></div>';
            
        }
    }

    /**
	 * Display the HTML code for the demo captcha  
	 *
	 * @since    1.0.0
     * 
	 */
    public static function show_demo_captcha() {
        echo '<h2>Sample Output</h2>';
        Guardgiant_Captcha::show_captcha_field();
    }


    /**
	 * Validates the captcha response entered by the user is correct  
	 *
	 * @since    1.0.0
     * 
     * @param   string  the response to the captcha
     * 
     * @return  WP_Error|True  returns an error if it fails validation, True if it passes
     * 	 
     */
    public static function validate_captcha_code ($recaptcha_response, $remote_ip_address) {

        $settings = get_option( 'guardgiant-settings' );

        // check the site administrator has set this up correctly
        if (!Guardgiant_Captcha::has_been_setup_correctly())
            return TRUE;

        $secret = $settings['recaptcha_secret_key'];
        $payload = array('secret' => $secret, 'response' => $recaptcha_response, 'remoteip' => $remote_ip_address);
        $result = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array('body' => $payload) );

        // we need to be very careful here. 
        // If something is broken with the captcha, we dont want to lock everyone out of the site
        if (!is_wp_error($result)) {
            if ( (isset($result['body'])) && (!empty($result['body'])) ) {
                $response_from_google = json_decode($result['body']);

                if  (is_object($response_from_google))  {
                    if ($response_from_google->success == 'true') {
                        // great, the attempt is successful
                        return TRUE;
                    }
                    else
                    {
                        
                        // lets check for errors we are interested in
                        
                        if ( isset($response_from_google->{'error-codes'}) && $response_from_google->{'error-codes'} ) {
                            if ( is_array($response_from_google->{'error-codes'})) {

                                if ( (in_array('missing-input-secret', $response_from_google->{'error-codes'})) || 
                                (in_array('invalid-input-secret', $response_from_google->{'error-codes'})) )
                                {
                                    // this must be due to a misconfiguration
                                    // perhaps the site administator entered the wrong site_key etc
                                    // we can't lock the user out for this.
                                    $msg_for_dashboard = __('GuardGiant: Your Google reCaptcha is not working as the secret key is invalid. Please ','guardgiant') .  '<a href="' . admin_url( 'admin.php?page=guardgiant&active_tab=captcha' ) . '">' . __( 'check your settings.','guardgiant' ) . '</a>';
                                    Guardgiant_Admin::add_flash_notice($msg_for_dashboard);
                                    return TRUE;
                                } 

                                if (in_array('bad-response', $response_from_google->{'error-codes'})) {
                                    // The represents a bad response from the google server
                                    // dont lock out the user for this
                                    return TRUE;
                                }

                                if (in_array('missing-input-response', $response_from_google->{'error-codes'})) {
                                    $error = new WP_Error();
                                    $err_msg = '<strong>' . __('Error','guardgiant') . ':</strong> ' . __('You did not complete the Captcha.','guardgiant');
                                    $error->add( 'incorrect_captcha', $err_msg);
                                    return $error;
                                }

                                if (in_array('invalid-input-response', $response_from_google->{'error-codes'})) {
                                    $error = new WP_Error();
                                    $err_msg = '<strong>' . __('Error','guardgiant') . ':</strong> ' . __('The Captcha was not entered correctly.','guardgiant');
                                    $error->add( 'incorrect_captcha', $err_msg);
                                    return $error;
                                }

                                if (in_array('timeout-or-duplicate', $response_from_google->{'error-codes'})) {
                                    $error = new WP_Error();
                                    $err_msg = '<strong>' . __('Error','guardgiant') . ':</strong> ' . __('The Captcha has timed out. Please try again.','guardgiant');
                                    $error->add( 'incorrect_captcha', $err_msg);
                                    return $error;
                                }
                                
                            }
                        }   
                    }
                }
            }
        }
            
        return TRUE;    
    }


    /**
	 * Checks whether the recaptcha settings have been setup correctly
	 *
	 * @since    1.0.0
     * 
     * @return  bool  True if it has been setup correctly
     * 	 
     */
    public static function has_been_setup_correctly()
    {
        $settings = get_option( 'guardgiant-settings' );

        if ( (!isset($settings['recaptcha_site_key'])) || (empty($settings['recaptcha_site_key'])) )
            return FALSE;

        if ( (!isset($settings['recaptcha_secret_key'])) || (empty($settings['recaptcha_secret_key'])) )
        return FALSE;

        return TRUE;    
        
    }

}