<?php
/*
Plugin Name: Anti-Brute Force,Login Fraud Dectector
Plugin URI: https://www.criminalip.io
Description: wp-login-detector 
Version: 1.0
Author:  AI Spera
Author URI: https://www.criminalip.io
License: GPLv3
*/

error_reporting( E_ALL );  
ini_set('display_errors', true); 
define('_FILE_', __FILE__); 
define( 'login_detector_version', '1.0' );   
define('_TBL_', 'login_detector'); 

$path = preg_replace('/wp-content.*$/', '', __DIR__);  
require_once $path . 'wp-load.php';    
$class = new DetectorClass(_FILE_);
$class->init(); 

$is_tor = 0;
$is_vpn = 0; 
$is_proxy= 0; 
$is_hosting = 0; 
$is_mobile = 0;  
$is_darkweb= 0; 
$is_scanner = 0; 
$is_snort= 0; 
$time_limit = 0; 
$api_key =''; 

class DetectorClass {  
    public static function init() {
        register_activation_hook( _FILE_, array( 'DetectorClass', 'install' ) );
    }

    public static function install() {  
        global $wpdb; 
        $charset_collate = $wpdb->get_charset_collate();  
        $wpdb->query(" CREATE OR REPLACE TABLE "._TBL_." (
            c_id INT(11) NOT NULL AUTO_INCREMENT,
            c_type CHAR(1) NOT NULL, 
            c_data LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL , 
            c_useyn CHAR(1) NOT NULL DEFAULT 'Y',   
            c_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (c_id)
            ){$charset_collate}; ");   

        

        // 초기 설정 정보 등록  ( 'A' -  Access , 'B' - Block , 'S' - Setting )  
        insert_accessinfo_login_detector("S",array( "is_tor" => "1",
                                                    "is_vpn" => "1",
                                                    "is_proxy" => "1" , 
                                                    "is_hosting" => "1", 
                                                    "time_limit" => "5",
                                                    "api_key"=>"SCvzhUcfca3ZQvCGYFYFHvlpdcWlWPlu4hjkSp4AWmFI6pzXYQVZOBD0pi0a"));   
    }
} 
DetectorClass::init(); 

class Config_info_login_detector
{
    public bool $_is_tor;
    public bool $_is_vpn;
    public bool $_is_proxy; 
    public bool $_is_hosting; 
    public int $_time_limit;    
    public ?string $_api_key;  

    public function __construct(int $_is_tor, int $_is_vpn , int $_is_proxy, int $_is_hosting, int $_time_limit, ?string $_api_key)
    { 
        global $is_tor;
        global $is_vpn ;
        global $is_proxy; 
        global $is_hosting ; 
        global $time_limit ;
        global $api_key ; 

        $is_tor = $_is_tor;
        $is_vpn = $_is_vpn;
        $is_proxy = $_is_proxy;
        $is_hosting = $_is_hosting; 
        $time_limit = $_time_limit;
        $api_key = $_api_key;    
         
    } 
}

add_action('init','source_login_detector'); 
function source_login_detector()
{      
    global $wpdb;   
    $returnValue = $wpdb->get_row("SELECT c_data FROM {$wpdb->prefix}"._TBL_." WHERE c_type = 'S' AND c_useyn = 'Y'  ORDER BY c_data DESC LIMIT 1");  
    if($returnValue && $returnValue->c_data){  
        $data = json_decode($returnValue->c_data);    
        #var_dump($data); exit; 
        new Config_info_login_detector($data->is_tor,$data->is_vpn,$data->is_proxy,$data->is_hosting,$data->time_limit,$data->api_key);    
 
        // 접속한 아이피가 block 상태라면, 설정된 제한 시간이 경과했는지 확인 
        if(check_exists_blockip_login_detector()){  
            echo "<div style='background-color: #A9A9A9; color: #FFFFFF;  display: block;   line-height: 300px; height: 300px;  position: relative;  text-align: center; text-decoration: none;  top: 0px;  width: 100%;z-index: 100;'> Your IP Aaddress is blocked by criminalip </div>";
            exit; 
        }else{     
            if(check_illegal_pattern_ip_login_detector()){ 
                echo "<div style='background-color: #A9A9A9; color: #FFFFFF;  display: block;   line-height: 300px; height: 300px;  position: relative;  text-align: center; text-decoration: none;  top: 0px;  width: 100%;z-index: 100;'> Your IP Aaddress is blocked by criminalip</div>";
                exit;
            }
            else{
                #insert_accessinfo_login_detector("A",array('ip' => get_userip()));  
            }
        }
    } 
}

function check_exists_blockip_login_detector(){    

    global $time_limit ; 

    $blockyn = false;   
    if(isset($api_key)){   
        global $wpdb;   
        $query = "SELECT * FROM {$wpdb->prefix}"._TBL_." WHERE c_type = 'B' AND  c_useyn = 'Y' AND  json_value(c_data,'$.ip') ='". get_userip()."'";  
        $returnValue = $wpdb->get_results($query);   // c_data " {"ip":"10.3.1.1","cn":"us","reason":"tor"}
        #var_dump($returnValue);  
       
        foreach($returnValue as $row){ 
            if($row->c_useyn == "Y"){ 
                #block time 이 경과했는지 확인 
                $timestamp = strtotime("-".$time_limit." MINUTE");     
                if($row->c_date >= date("Y-m-d H:i:s", $timestamp)){
                    $blockyn = true; #블락상태
                }else{
                    #기한이 지난 Block 정보 폐기 
                    $query = "UPDATE {$wpdb->prefix}"._TBL_." SET c_useyn = 'N' WHERE c_id = ".$row->c_id;    
                    $wpdb->query( $wpdb->prepare($query)); 
                } 
            }  
        } 
    } 
    return $blockyn;    
} 

function check_illegal_pattern_ip_login_detector(){   
 
    global $is_tor;
    global $is_vpn ;
    global $is_proxy; 
    global $is_hosting ; 
    global $time_limit ;
    global $api_key ;  
    $returnvalue = false; 

    #var_dump($api_key); 
 
    if(isset($api_key)){
        
        //해당 IP 를 criminalip API 에 호출해서 tag 를 확인 
        $criminalip_info = call_criminal_api_login_detector($api_key);     
        #var_dump($criminalip_info); 
        if($criminalip_info != null){ // CIP에서 리턴이 정상이라면,  
            
            $ip = $criminalip_info->ip; 
            $ip_is_tor =  $criminalip_info->tags->is_tor;   
            $ip_is_vpn  =  $criminalip_info->tags->is_vpn;
            $ip_is_proxy =  $criminalip_info->tags->is_proxy;
            $ip_is_hosting  =  $criminalip_info->tags->is_hosting; 
            $ip_cn_info = $criminalip_info->whois->data[0]->org_country_code;   
            
            $reason_tor = false; 
            $reason_vpn = false; 
            $reason_proxy = false; 
            $reason_hosting  = false;  

            if($is_tor && $ip_is_tor) $reason_tor = true; 
            if($is_vpn && $ip_is_vpn) $reason_vpn = true; 
            if($is_proxy && $ip_is_proxy) $reason_proxy = true; 
            if($is_hosting && $ip_is_hosting) $reason_hosting = true; 
  

            if( $reason_tor || $reason_vpn || $reason_proxy || $reason_hosting){   
 
                $data = array("ip" => $ip ,  
                "ip_is_tor" => $ip_is_tor,
                "ip_is_vpn" => $ip_is_vpn,
                "ip_is_proxy" => $ip_is_proxy , 
                "ip_is_hosting" => $ip_is_hosting, 
                "ip_cn_info" => $ip_cn_info ,
                "reason" => array("reason_is_tor" => $reason_tor,
                                  "reason_is_vpn" => $reason_vpn,
                                  "reason_is_proxy" => $reason_proxy,
                                  "reason_is_hosting" => $reason_hosting)); 
                #var_dump($data);  
                insert_accessinfo_login_detector("B",$data);  
                $returnvalue = true;
            } 
        }  
    }
    return $returnvalue; 
}    
 

function call_criminal_api_login_detector($api_key){    

    #$ip = get_userip();
    $ip = '104.42.192.23'; 
    $params = '?ip=' . $ip . '&full=true';  
    $headers = "x-api-key: ".$api_key."\r\n"; 
    $options = array (
        'http' => array (
            'header' => $headers,
            'method' => 'GET'
        )
    ); 

    $host = 'https://api.criminalip.io';
    $path = '/core/v1/getipdata';  
    try {  
            $context  = stream_context_create ($options);
            $result = json_decode(file_get_contents ($host . $path . $params, false, $context));   
            #var_dump($result); 

            if($result->status == 200){  
                return $result;  
            }    
            else    
                return null;             
        }
    catch ( Exception $e ){
        return null;  
    } 
}
 
add_action( 'admin_menu', 'login_detector_menu_setting', 1 );
function login_detector_menu_setting()
{ 
    add_menu_page( 
        'wp-login-detector',  
        'Anti-Brute Force,Login Fraud Dectector', 
        'edit_posts', 
        'menu_slug', 
        'plugin_options_page', 
        'dashicons-media-spreadsheet' 
    );
}

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'login_detector_action_links');
function login_detector_action_links( $links ) {
    array_unshift($links, '<a href="' . admin_url( 'admin.php?page=menu_slug' ) . '">' . __( 'Settings','index') . '</a>');

    return $links;
}
   
add_action('admin_init','plugin_register_settings' );    
function plugin_register_settings() { 
  
    global $is_tor;
    global $is_vpn ;
    global $is_proxy; 
    global $is_hosting ; 
    global $time_limit ;
    global $api_key ;    


    #var_dump($is_hosting); exit;  

    // Here we are going to register our setting.
    register_setting('options_group','option_name', 'save');

    // Here we are going to select data from the settings
 
    // Section 
    add_settings_section(
        'criminalip_test_trusted_devices_settings_section',				// ID used to identify this section and with which to register options
        '',									// Title to be displayed on the administration page
        'trusted_devices_settings_section_callback' ,	// Callback used to render the description of the section
        'general_setting_page'								// Page on which to add this section of options
    );

    add_settings_section(
        'criminalip_test_block_ip_settings_section',					// ID used to identify this section and with which to register options
        '',									// Title to be displayed on the administration page
        'block_ip_settings_section_callback',	// Callback used to render the description of the section
        'general_setting_page'								// Page on which to add this section of options
    );
  
    add_settings_field( // API KEY  hmcho 
        'api_key',		// ID used to identify the field
        __('Criminalip API KEY','criminalip_test'),				// The label to the left of the option interface element
        'settings_field_input_text_callback',		// The name of the function responsible for rendering the option interface
        'general_setting_page',									// The page on which this option will be displayed
        'criminalip_test_trusted_devices_settings_section',						// The name of the section to which this field belongs
        array(
            'label_for' => 'api_key',
            'default'   => $api_key,
            
        )															// The array of arguments to pass to the callback
        );   

    add_settings_field(
        'is_vpn',		// ID used to identify the field
        __('접근제한대상','criminalip_test'),				// The label to the left of the option interface element
        'settings_field_input_checkbox_and_2numbers_callback',		// The name of the function responsible for rendering the option interface
        'general_setting_page',									// The page on which this option will be displayed
        'criminalip_test_block_ip_settings_section',						// The name of the section to which this field belongs
        array(
            'label_for_1' => 'is_vpn',
            'label_for_2' => 'is_tor',
            'label_for_3' => 'is_proxy', 
            'label_for_4' => 'is_hosting', 
            'text1' => 'vpn ',
            'text2' => 'tor',
            'text3' => 'proxy',
            'text4' => 'hosting',
            'default1' => $is_tor,
            'default2' => $is_vpn,
            'default3' => $is_proxy,
            'default4' => $is_hosting,
        )							 
    );  

 
    add_settings_field(
        'time_limit',		// ID used to identify the field
        __('','criminalip_test'),				// The label to the left of the option interface element
        'settings_field_input_number_callback',		// The name of the function responsible for rendering the option interface
        'general_setting_page',									// The page on which this option will be displayed
        'criminalip_test_block_ip_settings_section',						// The name of the section to which this field belongs
        array(
            'label_for' => 'time_limit', 
            'after_text' => __( '분 후 해제', 'criminalip_test' ),
            'default' => $time_limit, 
        )															// The array of arguments to pass to the callback
    ); 
} 

// save 
function save( $input ) {  

    global $wpdb;  
 
    $is_tor = 0 ;
    $is_vpn = 0 ;
    $is_proxy = 0 ; 
    $is_hosting = 0 ; 
    $time_limit = 0 ;
    $api_key = '';   
   

    if ( isset( $input ) ) {    

        foreach ( $input as $key => $value ) {  
            
            if($key == 'is_tor') {$is_tor = $value; continue; }
            if($key == 'is_vpn') {$is_vpn = $value;  continue;}
            if($key == 'is_proxy') {$is_proxy = $value;   continue;} 
            if($key == 'is_hosting') {$is_hosting = $value; continue; }
            if($key == 'time_limit') {$time_limit = $value;  continue; }
            if($key == 'api_key'){$api_key = $value;   continue; }
        }  
         

        // 기존 설정값 데이터 disable로 update 
        $update_row = array('c_useyn' => 'N');
        $wpdb->update($wpdb->prefix._TBL_,$update_row,array('c_type'=>'S','c_useyn'=>'Y'));

        // 신규 설정값 데이터 insert
        insert_accessinfo_login_detector("S",array( "is_tor" => $is_tor,
                                                    "is_vpn" => $is_vpn,
                                                    "is_proxy" => $is_proxy , 
                                                    "is_hosting" => $is_hosting, 
                                                    "time_limit" => $time_limit,
                                                    "api_key"=> $api_key));     

    } 
} 


function insert_accessinfo_login_detector($c_type, $arr){ 
    global $wpdb;       
    #var_dump($arr); exit; 
    $data = array('c_type'=> $c_type, 'c_data'=> json_encode($arr)); 
    $format = array('%s');   
    $wpdb->insert($wpdb->prefix._TBL_, $data, $format);  

}  

/* ------------------------------------------------------------------------ *
* Section Callbacks
* ------------------------------------------------------------------------ */

function trusted_devices_settings_section_callback() { 
    echo "<p>" . __("이 플러그인은 최상의 사용자 경험을 유지하면서 WordPress 사이트를 공격자로부터 보호하는 최신 보안 플러그인입니다.",'criminalip_test') . "</p>" ;
    echo "<p>";
    //echo '<h2>' . __('CriminalIP API KEY','criminalip_test') . '</h2>';
    echo __("CriminalIP 발급받은 API KEY를 입력하세요 (https://www.criminalip.io) ").'<br>';
    //return; 
}


function block_ip_settings_section_callback() {
    
    // echo '<h2>' . __('Limit Access Attempts On User IP','criminalip_test') . '</h2>';
    // echo '<p>' . __('Please set a limited number of times for illegal access','criminalip_test') . '</p>';	

    return;
}


function settings_section_callback() {
    echo '<h2>' . __('Settings','criminalip_test') . '</h2>';
    echo '<p>' . __('Whitelisting is a security feature that provides full access to certain users. Criminalip_test offers a User Whitelist for trusted usernames that should never be locked out. The IP Address Whitelist allows you to create a list of trusted IP addresses (e.g. an office IP) which will never be blocked.','criminalip_test') . '</p>';	
    //return;

}
 
function captcha_section_callback() {
    echo '<h2>' . __('Google reCaptcha v2','criminalip_test') . '</h2>';
    echo '<p>' . __('Google reCaptcha (version 2) provides the most robust way of differentiating between genuine users and automated processes (i.e. brute force scripts used by hackers). ','criminalip_test') . '</p>';	
    echo '<p>' . __('Need help with this page? ','criminalip_test') . '<a href="https://www.criminalip_test.com/keys-for-google-recaptcha/">Click here for step-by-step instructions.</a>';

    //return; 
}

function reverse_proxy_section_callback() {
    echo '<h2>' . __('Reverse Proxy','criminalip_test') . '</h2>';
    echo '<p>';
    echo __('Load balancers and CDNs (e.g. Cloudflare) are known as reverse proxies. ','criminalip_test');
    echo __('Due to the nature of these services, all visits to your website are logged with the IP address of the proxy rather than the visitor’s actual IP address. ','criminalip_test');  
    echo __("To remedy this, the visitor's IP address is provided in a 'header field' which Criminalip_test can pick up and use. ",'criminalip_test');  
    echo '</p><p>' . __('Criminalip_test can detect the correct settings for you, however if you prefer you can manually set these details in this section. ','criminalip_test');  
    echo '</p>';
    //return;
}


function general_settings_section_callback() {
    echo '<h2>' . __('General Settings','criminalip_test') . '</h2>';
    //return; 
}

function email_notifications_section_callback() {
    echo '<h2>' . __('Email Notifications','criminalip_test') . '</h2>';
    
    //return; 
}
 
/* ------------------------------------------------------------------------ *
* Field Callbacks
* ------------------------------------------------------------------------ */

 function settings_field_radio_buttons_callback( $args ) {

    $field_id = isset($args['label_for']) ? $args['label_for'] : null;
    $field_description1 = isset($args['description1']) ? $args['description1'] : null;
    $field_description2 = isset($args['description2']) ? $args['description2'] : null;
    $further_text = isset($args['further_text']) ? $args['further_text'] : null;
    $value1 = isset($args['value1']) ? $args['value1'] : null;
    $value2 = isset($args['value2']) ? $args['value2'] : null;

    $options = get_option( 'option_name' );
    $option = 0;

    if ( ! empty( $options[ $field_id ] ) ) 
        $option = $options[ $field_id ];
     
    ?>
    <p>
        <label >
            
            <input type="radio" name="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>" value="<?php echo esc_html($value1);?>" <?php checked(1, $option, true); ?> > 
            <?php if (!empty($field_description1)) echo  esc_html($field_description1) ?>
        </label>
    </p>
    <p>
        <label >
            
            <input type="radio" name="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>" value="<?php echo esc_html($value2);?>" <?php checked(2, $option, true); ?> > 
            <?php if (!empty($field_description2)) echo  esc_html($field_description2) ?>
        </label>
    </p>
    <?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
    <?php


}

function settings_field_single_checkbox_callback( $args ) {

    $field_id = isset($args['label_for']) ? $args['label_for'] : null;
    $field_description = isset($args['description']) ? $args['description'] : null;
    $further_text = isset($args['further_text']) ? $args['further_text'] : null;
    $options = get_option( 'option_name' );
    $option = 0;

    if ( ! empty( $options[ $field_id ] ) ) 
        $option = $options[ $field_id ];
    

    ?>
        <label for="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>">
            <input type="checkbox" name="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'option_name[' . $field_id . ']'; ?>" <?php checked( $option, true, 1 ); ?> value="1" /><?php if (!empty($field_description)) echo esc_html($field_description) ?>
        </label>	
        <?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
        
    <?php
}


function settings_field_input_text_callback( $args ) {

    $field_id = isset($args['label_for']) ? $args['label_for'] : null;
    $field_default = isset($args['default']) ? $args['default'] : null;
    $before_text = isset($args['before_text']) ? $args['before_text'] : null;
    $after_text = isset($args['after_text']) ? $args['after_text'] : null;
    $further_text = isset($args['further_text']) ? $args['further_text'] : null;

    $options = get_option( 'option_name' );
    $option = $field_default;

    if ( ! empty( $options[ $field_id ] ) ) 
        $option = $options[ $field_id ];
    

    ?>	<span class="description"><?php if (!empty($before_text)) echo esc_html($before_text) . '<br/>'; ?> </span>	
        <input type="text" name="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>" value="<?php echo  $option; ?>" class="regular-text" />
        <span class="description"><?php if (!empty($after_text)) echo esc_html($after_text); ?> </span>
        <?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>

    <?php

}

function settings_field_input_textarea_callback( $args ) {

    $field_id = isset($args['label_for']) ? $args['label_for'] : null;
    $field_default = isset($args['default']) ? $args['default'] : null;
    $rows = isset($args['rows']) ? $args['rows'] : null;
    $before_text = isset($args['before_text']) ? $args['before_text'] : null;
    $after_text = isset($args['after_text']) ? $args['after_text'] : null;
    $further_text = isset($args['further_text']) ? $args['further_text'] : null;

    $options = get_option( 'option_name' );
    $option = $field_default;

    if ( ! empty( $options[ $field_id ] ) ) 
        $option = $options[ $field_id ];
    

    if (empty($rows))
        $rows = 4;

    ?>		
    <?php if (!empty($before_text)) echo esc_html($before_text) . '<br/>'; ?>
    <textarea type="text" rows="<?php echo esc_html($rows); ?>" cols="50" name="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>"  class="large-text code" /><?php echo esc_attr( $option ); ?></textarea>
    <span class="description"><?php if (!empty($after_text)) echo '<br/>' . esc_html($after_text); ?> </span>
    <?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
    <?php

}

function settings_field_input_number_callback( $args ) {

    $field_id = isset($args['label_for']) ? $args['label_for'] : null;
    $field_default = isset($args['default']) ? $args['default'] : null; 
    $after_text = isset($args['after_text']) ? $args['after_text'] : null; 

    $options = get_option( 'option_name' );
    $option = $field_default;

    if ( ! empty( $options[ $field_id ] ) ) 
        $option = $options[ $field_id ];
    

    ?> 
        <input type="number" step="1" min="1" name="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'option_name[' . esc_html($field_id) . ']'; ?>" value="<?php echo esc_attr( $option ); ?>" class="small-text" />
        <span class="description"><?php if (!empty($after_text)) echo esc_html($after_text); ?> </span> 
    <?php

}


function settings_field_input_2numbers_callback( $args ) {

    $field_id1 = isset($args['label_for_1']) ? $args['label_for_1'] : null;
    $field_id2 = isset($args['label_for_2']) ? $args['label_for_2'] : null;
    $field_default1 = isset($args['default1']) ? $args['default1'] : null;
    $field_default2 = isset($args['default2']) ? $args['default2'] : null;
    $before_text = isset($args['before_text']) ? $args['before_text'] : null;
    $middle_text = isset($args['middle_text']) ? $args['middle_text'] : null;
    $after_text = isset($args['after_text']) ? $args['after_text'] : null;
    $further_text = isset($args['further_text']) ? $args['further_text'] : null;

    $options = get_option( 'option_name' );
    $option1 = $field_default1;
    $option2 = $field_default2;

    if ( ! empty( $options[ $field_id1 ] ) ) 
        $option1 = $options[ $field_id1 ];
    

    if ( ! empty( $options[ $field_id2 ] ) ) 
        $option2 = $options[ $field_id2 ];
    

    ?>
        <span class="description"><?php if (!empty($before_text)) echo esc_html($before_text); ?> </span>
        <input type="number" step="1" min="1" name="<?php echo 'option_name[' . esc_html($field_id1) . ']'; ?>" id="<?php echo 'option_name[' . esc_html($field_id1) . ']'; ?>" value="<?php echo esc_attr( $option1 ); ?>" class="small-text" />
        <span class="description"><?php if (!empty($middle_text)) echo esc_html($middle_text); ?> </span>
        <input type="number" step="1" min="1" name="<?php echo 'option_name[' . esc_html($field_id2) . ']'; ?>" id="<?php echo 'option_name[' . esc_html($field_id2) . ']'; ?>" value="<?php echo esc_attr( $option2 ); ?>" class="small-text" />

        <span class="description"><?php if (!empty($after_text)) echo esc_html($after_text); ?> </span>
        <?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
    <?php

}

function settings_field_input_checkbox_and_number_callback( $args ) {
    $field_id1 = isset($args['label_for_1']) ? $args['label_for_1'] : null;
    $field_id2 = isset($args['label_for_2']) ? $args['label_for_2'] : null;
    
    $field_default1 = isset($args['default1']) ? $args['default1'] : null;
    $field_default2 = isset($args['default2']) ? $args['default2'] : null;
    
    $before_text = isset($args['before_text']) ? $args['before_text'] : null;
    $middle_text = isset($args['middle_text']) ? $args['middle_text'] : null;
    $after_text = isset($args['after_text']) ? $args['after_text'] : null;
    $further_text = isset($args['further_text']) ? $args['further_text'] : null;

    $options = get_option( 'option_name' );
    $option1 = $field_default1;
    $option2 = $field_default2;
    
    if ( ! empty( $options[ $field_id1 ] ) ) 
        $option1 = $options[ $field_id1 ];
    

    if ( ! empty( $options[ $field_id2 ] ) ) 
        $option2 = $options[ $field_id2 ];
    

    ?>
        <label for="<?php echo 'option_name[' . esc_html($field_id1) . ']'; ?>" >
            <input type="checkbox" name="<?php echo 'option_name[' . esc_html($field_id1) . ']'; ?>" id="<?php echo 'option_name[' . esc_html($field_id1) . ']'; ?>" <?php checked( $option1, true, 1 ); ?> value="1" /><?php if (!empty($before_text)) echo esc_html($before_text); ?>
            <?php if (!empty($middle_text)) echo esc_html($middle_text); ?>
        </label>
        <label for="<?php echo 'option_name[' . esc_html($field_id2) . ']'; ?>" >
            <input type="number" step="1" min="1" name="<?php echo 'option_name[' . esc_html($field_id2) . ']'; ?>" id="<?php echo 'option_name[' . esc_html($field_id2) . ']'; ?>" value="<?php echo esc_attr( $option2 ); ?>" class="small-text" />
            <?php if (!empty($after_text)) echo esc_html($after_text); ?> 
        </label>
        <?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>

    <?php
}
 

function settings_field_input_checkbox_and_2numbers_callback( $args ) {

    $field_id1 = isset($args['label_for_1']) ? $args['label_for_1'] : null;
    $field_id2 = isset($args['label_for_2']) ? $args['label_for_2'] : null;
    $field_id3 = isset($args['label_for_3']) ? $args['label_for_3'] : null;
    $field_id4 = isset($args['label_for_4']) ? $args['label_for_4'] : null;


    $field_default1 = isset($args['default1']) ? $args['default1'] : null;
    $field_default2 = isset($args['default2']) ? $args['default2'] : null;
    $field_default3 = isset($args['default3']) ? $args['default3'] : null;
    $field_default4 = isset($args['default4']) ? $args['default4'] : null;
    
    $text1 = isset($args['text1']) ? $args['text1'] : null;;
    $text2 = isset($args['text2']) ? $args['text2'] : null;;
    $text3 = isset($args['text3']) ? $args['text3'] : null;;
    $text4 = isset($args['text4']) ? $args['text4'] : null;;
     
    $options = get_option( 'option_name' );
    $option1 = $field_default1;
    $option2 = $field_default2;
    $option3 = $field_default3;
    $option4 = $field_default4;

    if ( ! empty( $options[ $field_id1 ] ) ) 
        $option1 = $options[ $field_id1];
    

    if ( ! empty( $options[ $field_id2 ] ) ) 
        $option2 = $options[ $field_id2];
    

    if ( ! empty( $options[ $field_id3 ] ) ) 
        $option3 = $options[ $field_id3];

    if ( ! empty( $options[ $field_id4 ] ) ) 
        $option4 = $options[ $field_id4];   

    ?>	
        <!-- vpn-->	
        <p>  
            <label for="<?php echo 'option_name[' . esc_html($field_id1) . ']'; ?>">
                <input type="checkbox" name="<?php echo 'option_name[' . esc_html($field_id1) . ']'; ?>" 
                id="<?php echo 'option_name[' . $field_id1 . ']'; ?>"
                <?php checked( $option1, true, 1 ); ?> value="1" /><?php if (!empty($text1)) echo esc_html($text1); ?>
            </label>
        </p><p>
            <label for="<?php echo 'option_name[' . esc_html($field_id2) . ']'; ?>">
                <input type="checkbox" name="<?php echo 'option_name[' . esc_html($field_id2) . ']'; ?>" 
                id="<?php echo 'option_name[' . $field_id2 . ']'; ?>" 
                <?php checked( $option2, true, 1 ); ?> value="1" /><?php if (!empty($text2)) echo esc_html($text2); ?>
            </label>
        </p><p>   
            <label for="<?php echo 'option_name[' . esc_html($field_id3) . ']'; ?>">
                <input type="checkbox" name="<?php echo 'option_name[' . esc_html($field_id3) . ']'; ?>" 
                id="<?php echo 'option_name[' . $field_id3 . ']'; ?>" 
                <?php checked( $option3, true, 1 ); ?> value="1" /><?php if (!empty($text3)) echo esc_html($text3); ?>
            </label>
        </p><p>   
            <label for="<?php echo 'option_name[' . esc_html($field_id4) . ']'; ?>">
                <input type="checkbox" name="<?php echo 'option_name[' . esc_html($field_id4) . ']'; ?>"
                id="<?php echo 'option_name[' . $field_id4 . ']'; ?>" 
                <?php checked( $option4, true, 1 ); ?> value="1" /><?php if (!empty($text4)) echo esc_html($text4); ?>
            </label>        
        </p>
    <?php 
}



function show_login_activity_log_page() {  
}
 
function plugin_action_links( $links ) {
    array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=criminalip_test' ) . '">' . __( 'Settings','criminalip_test') . '</a>' );
    
    return $links;
}  

function display_flash_notices() {
    $notices = get_option( "criminalip_test_flash_notices", array() );
        
    // Iterate through our notices to be displayed and print them.
    foreach ( $notices as $notice ) {
            printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                $notice['type'],
                $notice['dismissible'],
                $notice['notice']
            );
        
    }
    
    // Now we reset our options to prevent notices being displayed forever.
    if( ! empty( $notices ) ) {
        delete_option( "criminalip_test_flash_notices", array() );
    }
} 
   
?>

<?php function plugin_options_page()
{
?>  

<div id="wrap">	
	<div class="wrap">  
	<?php
		$default_tab = 'api_key';
		if (isset($_GET['active_tab']))
			$active_tab = sanitize_text_field($_GET['active_tab']);
		else
			$active_tab = $default_tab; 
		settings_errors();
    ?>
	
	<h2 class="nav-tab-wrapper"> 
    	<a href="?page=menu_slug&active_tab=api_key" class="nav-tab <?php echo $active_tab == 'api_key' ? 'nav-tab-active' : ''; ?>">설정</a>
		<a href="?page=menu_slug&active_tab=stats" class="nav-tab <?php echo $active_tab == 'stats' ? 'nav-tab-active' : ''; ?>">차단된 IP 결과</a>   
    </h2> 
		<?php 
		if( $active_tab == 'api_key' ) { ?> 
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'options_group' );
			do_settings_sections( 'general_setting_page' );
			submit_button();
		} 

		if( $active_tab == 'stats' ) { 
 
            $action = isset($_GET["action"]) ? trim($_GET["action"]) : "" ;
            if($action=="detect-delete"){
                $c_id = isset($_GET["post_id"]) ? intval($_GET["post_id"]) : ""; 
                global $wpdb; 
                $where = array('c_id' => intval(sanitize_text_field($c_id))); 
                $wpdb->delete( $wpdb->prefix._TBL_, $where); 
            } 

            $action = isset($_POST["action"]) ? trim($_POST["action"]) : "" ;
            if($action=="bulk-delete"){
 
                if(!empty($_POST['bulk-delete'])){
                    $delete_ids = esc_sql( $_POST['bulk-delete'] ); 

                    if( !empty($delete_ids) ){
                        foreach ( $delete_ids as $id ) { 
                         
                            global $wpdb; 
                            $where = array('c_id' => intval(sanitize_text_field($id)));  
                            $wpdb->delete( $wpdb->prefix._TBL_, $where); 
                        }
                    }
                 
                }
               
            }   

            ob_start();     

            include_once plugin_dir_path( __FILE__ ).'admin/partials/wp-list-table.php';
            
            $template = ob_get_contents();

            ob_end_clean(); 

            echo $template;   
		}        
		?>
	</form>
	</div>
</div> 
<?php
}     