<?php

/**
 * The class used to record login activity
 *
 * @since	1.0.0
 *
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */

class Guardgiant_Login_Activity_Log {

	/**
	 * The database id.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private $id;
	
	/**
	 * The time of the login attempt.
	 *
	 * @since 1.0.0
	 * @var timestamp
	 */
	public $attempt_time;
	
	/**
	 * The IP address of the device making the login attempt.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $ip_address;

	/**
	 * The location (country) of the IP address.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $ip_location;

	/**
	 * The type of the device making the login attempt e.g. iPhone
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $device_type;

	/**
	 * The username that was used in the login attempt
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $username;

	/**
	 * the 'error_code' if the login attempt failed
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $result_code;

	/**
	 * The login error message that was displayed if the login attempt failed.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $result_description;
	
	/**
	 * How many failed attempts to log in have been made by this device
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $attempt_count;

	/**
	 * Was the device trusted?
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $trusted_device;


	/**
	 * Initialize the class and set its properties.
	 * 
	 * @since	1.0.0
	 *
	 * @param	string	$ip_address
	 * @param	string	$username
	 * @param	string	$result_code		The error_code from any error, or 'success'
	 * @param	string	$result_description	The message that was displayed to the user
	 * @param	int		$attempt_count		The number of failed attempts that have been made to login
	 * 
	 */
	public function __construct( $ip_address, $username, $trusted_device, $result_code, $result_description, $attempt_count = 0 ) {

		$this->attempt_time = time();
		$this->ip_address = $ip_address;
		$this->ip_location = null;
		$this->device_type = null;
		$this->username = $username;
		$this->result_code = $result_code;
		$this->result_description = $result_description;
		$this->attempt_count = $attempt_count;
		$this->trusted_device = $trusted_device;

	}

    /**
	* Saves the instance to the database
	*
	* @since	1.0.0
	*
	*/
	public function save()
	{
		global $wpdb;
		$tablename = $wpdb->prefix."guardgiant_login_activity_log";

		// has this been saved to the database before?
		if (empty($this->id)) {

			$newrow = array(
				'attempt_time' => time(),
				'ip_address' => $this->ip_address,
				'ip_location' => $this->ip_location,
				'username' =>  $this->username,
				'result_code' => $this->result_code,
				'result_description' => $this->result_description,
				'attempt_count' => $this->attempt_count,
				'device_type' => $this->device_type,
				'trusted_device' => $this->trusted_device,
				);

			 $wpdb->insert($tablename,$newrow);
			 $this->id = $wpdb->insert_id;

		} else {

			// update the database record
			$update_row = array(
				'id' => $this->id,
				'attempt_time' => $this->attempt_time,
				'ip_address' => $this->ip_address,
				'ip_location' => $this->ip_location,
				'username' => $this->username,
				'result_code' => $this->result_code,
				'result_description' => $this->result_description,
				'attempt_count' =>  $this->attempt_count,
				'device_type' => $this->device_type,
				'trusted_device' => $this->trusted_device,
				 );
 			$wpdb->update($tablename,$update_row,array('id'=>$this->id ) );
		}
	}
	

	/**
	* Delete old Guardgiant_Login_Activity_Log records in the database.
	* This is a housekeeping task
	*
	* @since	1.0.0
	*
	*/
	public static function delete_old_records()
	{	
		global $wpdb;

		$settings = get_option( 'guardgiant-settings' );
		if (isset($settings['delete_login_activity_records_from_db_after_days']))
			$delete_after_days = $settings['delete_login_activity_records_from_db_after_days'];
		else
			$delete_after_days = GUARDGIANT_DEFAULT_ITEMS_PER_PAGE_ON_ACTIVITY_LOG;

		// sanity check before we do it...
		if ( ($delete_after_days >= 1) && ($delete_after_days <= 36500) )
		{
			$time_difference = time() - ($delete_after_days * 86400);	// 86400 = seconds in 1 day
			
			$tablename = $wpdb->prefix."guardgiant_login_activity_log";
			$query = $wpdb->prepare("DELETE FROM {$tablename} WHERE attempt_time < %d " , $time_difference);
			$wpdb->query($query);
		}
	}

	/**
	* Lookup the country for this IP address using a geolocation service
	*
	* @since	1.0.0
	*
	*/
	public function get_ip_location()
	{
		global $wpdb;

		$tablename = $wpdb->prefix."guardgiant_login_activity_log";
		$query = $wpdb->prepare("SELECT ip_location FROM {$tablename} WHERE `ip_address` = %s AND `ip_location` IS NOT NULL LIMIT 1" , $this->ip_address);
		$tablerows = $wpdb->get_results($query);
		
		// have we seen this IP before?
		if ( ($tablerows) && (count($tablerows)==1) ) {

			// lets take the ip location from the previous record
			$this->ip_location = $tablerows[0]->ip_location;
			 
		}

		if ( (!isset($this->ip_location)) || (empty($this->ip_location)) ) {
			
			$args = array(
				'timeout'     => 3
			);

			$url = "http://ip-api.com/json/" . $this->ip_address;
			$request = wp_remote_get($url,$args);

			if( is_wp_error( $request ) ) 
				return; 

			$body = wp_remote_retrieve_body( $request );
			
			if (!is_wp_error($body)) {
				$decode = json_decode($body);

				if (isset($decode->country))
					$this->ip_location = sanitize_text_field($decode->country);
			}
		}
	}


	/**
	* Try to detect the type of device from the HTTP User Agent string
	*
	* @since	1.0.0
	*
	*/
	public function set_device_type_from_user_agent_string()
	{
		$device = "Unknown";

		if( stristr($_SERVER['HTTP_USER_AGENT'],'ipad') ) {
			$device = "Apple iPad";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'iphone') || strstr($_SERVER['HTTP_USER_AGENT'],'iphone') ) {
			$device = "Apple iPhone";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'macintosh') ) {
			$device = "Apple Mac";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'android') ) {
			$device = "Android";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'win') ) {
			$device = "Windows";
		}
		$this->device_type = $device;
	}    

	
    
}