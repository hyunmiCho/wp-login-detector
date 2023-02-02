<?php
/*
Plugin Name: criminalip_block
Plugin URI: https://www.criminalip.io
Description: 접속 가능한 임계치를 초과한 경우, IP 를 block 합니다. 
Version: 1.0
Author:  aispera
Author URI: https://www.aispera.com
License: GPLv3
*/

error_reporting( E_ALL );  
ini_set('display_errors', true); 
define('PLUGIN_FILE_URL', __FILE__); 
define( 'CRIMINALIP_BLOCK_VERSION', '1.0' );  
 
$path = preg_replace('/wp-content.*$/', '', __DIR__); 
require_once $path . 'wp-load.php'; 

$class = new MainClass(PLUGIN_FILE_URL);
$class->init();
 
class MainClass { 

    public static function init() {
        register_activation_hook( PLUGIN_FILE_URL, array( 'MainClass', 'install' ) );
    }

    public static function install() {  
        global $wpdb; 
        $charset_collate = $wpdb->get_charset_collate();  
        $wpdb->query(" CREATE OR REPLACE TABLE {$wpdb->prefix}criminalip (
            c_id INT(11) NOT NULL AUTO_INCREMENT,
            c_type CHAR(1) NOT NULL, 
            c_data LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL , 
            c_useyn CHAR(1) NOT NULL DEFAULT 'Y',   
            c_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (c_id)
            ){$charset_collate}; ");   

        // IP 정보 등록  ( 'A' -  Access , 'B' - Block , 'S' - Setting ) 
        //'S' : default setting 
        insert_accessinfo("S",array("limit_sec" => "0","limit_min" => "1","limit_times" => "10" , "block_hour" => "12","api_key"=>"SCvzhUcfca3ZQvCGYFYFHvlpdcWlWPlu4hjkSp4AWmFI6pzXYQVZOBD0pi0a"));   
    }
} 

MainClass::init(); 
 
 
class Config_info
{
    public int $limit_sec;
    public int $limit_min;
    public int $limit_times; 
    public int $block_hour;
    public ?string $api_key;  

    public function __construct(int $limit_sec, int $limit_min , int $limit_times, int $block_hour,  ?string $api_key)
    {
        $this->limit_sec = $limit_sec;
        $this->limit_min = $limit_min;
        $this->limit_times = $limit_times;
        $this->block_hour = $block_hour;
        $this->api_key = $api_key;        
    } 
}


add_action('init','source_criminalip'); 
function source_criminalip()
{    
    global $wpdb;   
    $returnValue = $wpdb->get_row("SELECT c_data FROM {$wpdb->prefix}criminalip WHERE c_type = 'S' AND c_useyn = 'Y'  ORDER BY c_data DESC LIMIT 1");  
    if($returnValue && $returnValue->c_data){  
        $data = json_decode($returnValue->c_data);    
        $config_info = new Config_info($data->limit_sec,$data->limit_min,$data->limit_times,$data->block_hour,$data->api_key);    
 
        // 접속한 아이피가 block 아이피에 존재 &  block 설정된 뒤, 12~24시간이 경과했는지 확인 
        if(check_exists_blockip($config_info)){  
            echo "<div style='background-color: #A9A9A9; color: #FFFFFF;  display: block;   line-height: 300px; height: 300px;  position: relative;  text-align: center; text-decoration: none;  top: 0px;  width: 100%;z-index: 100;'> Your IP Aaddress is blocked by criminalip </div>";
            exit; 
        }else{    
            insert_accessinfo("A",array('ip' => get_userip())); 

            if(check_illegal_pattern_ip($config_info)){ 
                echo "<div style='background-color: #A9A9A9; color: #FFFFFF;  display: block;   line-height: 300px; height: 300px;  position: relative;  text-align: center; text-decoration: none;  top: 0px;  width: 100%;z-index: 100;'> Your IP Aaddress is blocked by criminalip</div>";
                exit;
            } 
        }
    } 
}


function insert_accessinfo($c_type, $arr){

    global $wpdb;     
    $table = $wpdb->prefix.'criminalip';   
    $data = array('c_type'=> $c_type, 'c_data'=> json_encode($arr)); 
    $format = array('%s');   
    $wpdb->insert($table, $data, $format);  
} 


function check_illegal_pattern_ip($config_info){   
    if(isset($config_info)){  
        global $wpdb;  
        //해당 IP 를 criminalip API 에 호출해서 tag 를 확인 
        $ip_tag_info = call_criminal_api($config_info->api_key);   
            
        if(($ip_tag_info != null) && ($ip_tag_info->is_vpn  || $ip_tag_info->is_tor)){

            // VPN  또는  Tor 일때만, 설정정보를 확인해서 블락처리  
            if(strcmp($config_info->limit_sec, "0") !== 0) { 
                $tail = "AND c_date >= DATE_ADD(NOW(), INTERVAL -".$config_info->limit_sec." SECOND ) "; 
            }else if (strcmp ($config_info->limit_min, "0") !== 0) { 
                $tail = "AND c_date >= DATE_ADD(NOW(), INTERVAL -".$config_info->limit_min." MINUTE ) "; 
            } 
            // 접근하는 외부 IP 가 X 초(분) 동안 Y 번 이상의 접근을 할 경우, 
            $query = "SELECT COUNT(c_data) as count FROM {$wpdb->prefix}criminalip WHERE c_type = 'A' AND c_useyn = 'Y' AND  json_value(c_data,'$.ip') ='". get_userip()."' " .$tail; 
            #var_dump($query); exit; 
            $access_count = $wpdb->get_row($query)->count;    
            if($access_count > $config_info->limit_times){  
                    insert_accessinfo("B",array('ip' => get_userip()));  
            } 
        }  
    }
}    

function check_exists_blockip($config_info){   
    $blockyn = false;   
    if(isset($config_info)){   
        global $wpdb;   
        $query = "SELECT * FROM {$wpdb->prefix}criminalip WHERE c_type = 'B' AND  c_useyn = 'Y' AND  json_value(c_data,'$.ip') ='". get_userip()."'";  
        $returnValue = $wpdb->get_results($query);  
        #var_dump($returnValue);  
       
        foreach($returnValue as $row){ 
            if($row->c_useyn == "Y"){ 
                #block time 이 경과했는지 확인 
                $timestamp = strtotime("-".$config_info->block_hour." hours");     
                if($row->c_date >= date("Y-m-d H:i:s", $timestamp)){
                    $blockyn = true; #블락상태
                }else{
                    #기한이 지난 Block 정보 폐기 
                    $query = "UPDATE {$wpdb->prefix}criminalip SET c_useyn = 'N' WHERE c_id = ".$row->c_id;    
                    $wpdb->query( $wpdb->prepare($query)); 
                } 
            }  
        } 
    } 
    return $blockyn;    
}

function call_criminal_api($api_key){    

    $ip = get_userip();
    #$ip = '2.84.116.151'; 
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
	$context  = stream_context_create ($options);
	$result = json_decode(file_get_contents ($host . $path . $params, false, $context));   
    #var_dump($result); 

    if($result->status == 200) 
        return $result->tags;  
    else    
        return null; 
}
 

add_action('admin_menu','source_criminalip_menu_setting'); 
function source_criminalip_menu_setting()
{
    global $wp_last_object_menu; 
    add_menu_page(__('criminalip','source-criminalip'),
    'criminalip','manage_options','source_criminalip_index','source_criminalip_index','dashicons-pinterest',$wp_last_object_menu); 
}

function source_criminalip_index(){    

   require_once plugin_dir_path(PLUGIN_FILE_URL) . 'criminalip_settings.php';

   $admin_setting_form = new Criminalip_Admin_Form(); 
   $admin_setting_form ->register_settings();
   $admin_setting_form ->display_settings_page();  
} 

//클라이언트 IP 
function get_userip() {
    $ipaddress="";
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress="UNKNOWN"; 
    return $ipaddress;
}

// add_shortcode('criminalip_shortcode','criminalip_shortcode'); 
// function criminalip_shortcode($atts)
// {
//     global $wpdb; 
//     $a = shortcode_atts(array('c_data' => '') , $atts); 
//     $c_data = $a['c_data']; 

//     $returnValue = $wpdb->get_row('SELECT * FROM {$wpdb->prefix}criminalip WHERE json_value(c_data,$.block_ip) like '. $c_data. '%'); 
//     return $returnValue; 
// }

// register_activation_hook(PLUGIN_FILE_URL,'source_criminalip_activation'); 
// function source_criminalip_activation(){    
// }
 
// register_deactivation_hook(PLUGIN_FILE_URL,'source_criminalip_deactivation'); 
// function source_criminalip_deactivation(){
//     // 캐시 삭제, 옵션 삭제 

// }
 
// register_uninstall_hook(PLUGIN_FILE_URL,'source_criminalip_uninstall'); 
// function source_criminalip_uninstall(){  
//     // global $wpdb; 
//     // $wpdb->query("DELETE FROM '{$wpdb->prefix}criminalip'");  
// } 