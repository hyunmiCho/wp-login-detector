<?php

/**
 * The class used to record the number of failed logins by an IP address
 *
 * @since	1.0.0
 *
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */

class Guardgiant_IP_Failed_Logins {

	/**
	 * The database id.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private $id;

	/**
	 * The IP address
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $ip_address;
	
	/**
	 * The username that was used in the last login attempt
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $username;
	
	/**
	 * The number of attempted logins that have been made
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $attempt_count;

	/**
	 * The time of the last login attempt
	 *
	 * @since 1.0.0
	 * @var timestamp
	 */
	public $last_attempt_time;

	/**
	 * The time when the IP address was last blocked
	 *
	 * @since 1.0.0
	 * @var timestamp
	 */
	public $blocked_time;

	/**
	 * The time when this record was created
	 * We delete old records as part of housekeeping
	 *
	 * @since 1.0.0
	 * @var timestamp
	 */
	public $created_at_time;
	


	/**
	 * Initialize the class and set its properties.
	 * 
	 * @since	1.0.0
	 *
	 * @param	string	$ip_address
	 * 
	 */
	public function __construct( $ip_address = NULL) {

		$this->ip_address = $ip_address;
		$this->attempt_count = 0;

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
		$tablename = $wpdb->prefix."guardgiant_ip_failed_logins";

		// is this a new record?
		if (empty($this->id)) {

			$newrow = array(
				'ip_address' => $this->ip_address,
				'username' => $this->username,
				'attempt_count' =>  $this->attempt_count,
				'last_attempt_time' => $this->last_attempt_time,
				'blocked_time' => $this->blocked_time,
				'created_at_time' => time(),
				);

			// insert row in to database;	
			$wpdb->insert($tablename,$newrow);
			$this->id = $wpdb->insert_id;

		} else {

			// update existing row
			$update_row = array(
				'id' => $this->id,
				'ip_address' => $this->ip_address,
				'username' => $this->username,
				'attempt_count' =>  $this->attempt_count,
				'last_attempt_time' => $this->last_attempt_time,
				'blocked_time' => $this->blocked_time
				 );

 			$wpdb->update($tablename,$update_row,array('id'=>$this->id ) );
		}
	}

	/**
	* Gets a record of Guardgiant_IP_Failed_Logins from the database for the provided ip address
	* If no record exists, a new one is created 
	*
	* @since	1.0.0

	* @param 	string	ip_address
	*
	* @return	Guardgiant_IP_Failed_Logins | False  if no record found
	*/
    public static function get_record_for_ip_address ($ip_address)
	{
		global $wpdb;
		$tablename = $wpdb->prefix."guardgiant_ip_failed_logins";

		$settings = get_option( 'guardgiant-settings' );

		// get data for this IP address from the database
		$query = $wpdb->prepare("SELECT * FROM {$tablename} WHERE `ip_address` = %s LIMIT 1" , $ip_address);
		$tablerows = $wpdb->get_results($query);

		// did we find a record?
		if ( (!$tablerows) || (!count($tablerows)==1) )
			return FALSE;

		// there is a record for this IP, but we need to check if the record has expired
		// the site administrator can expire records after a certain period of time to prevent
		// ever longer lockouts. 
		$record_created_at = $tablerows[0]->created_at_time;

		// has the administrator enabled expiration of these records?
		if (isset($settings['expire_ip_failed_logins_record'])) {

			// get the expiration time (in hours). This is set by the administrator in the settings page.
			if ( (isset($settings['expire_ip_failed_logins_record_in_hours'])) && (!empty($settings['expire_ip_failed_logins_record_in_hours'])) ) {
				$expire_in_hours = absint($settings['expire_ip_failed_logins_record_in_hours']);
				if ( ($record_created_at + ($expire_in_hours*3600)) < time() ) {
					// this record has expired, so lets delete it
					Guardgiant_IP_Failed_Logins::delete_record_for_ip($ip_address);
					return FALSE;
				}
			}
		}
		
		// there is a valid database record, let's populate the data in to a new instance
		
		// create a new instance
		$failed_logins_record = new Guardgiant_IP_Failed_Logins;
		
		$failed_logins_record->id = $tablerows[0]->id;
		$failed_logins_record->ip_address = $tablerows[0]->ip_address;
		$failed_logins_record->username = $tablerows[0]->username;
		$failed_logins_record->attempt_count = $tablerows[0]->attempt_count;
		$failed_logins_record->last_attempt_time = $tablerows[0]->last_attempt_time;
		$failed_logins_record->blocked_time = $tablerows[0]->blocked_time;
		$failed_logins_record->created_at_time = $tablerows[0]->created_at_time;
		
		return $failed_logins_record;
	}


	/**
	* Deletes the record of Guardgiant_IP_Failed_Logins in the database for the provided ip address
	*
	* @since	1.0.0
	*
	* @param 	string			ip_address
	*
	*/
	public static function delete_record_for_ip($ip_address)
	{
		global $wpdb;

		//delete the failed login record for this IP
		$tablename = $wpdb->prefix."guardgiant_ip_failed_logins";
		$query = $wpdb->prepare("DELETE FROM  {$tablename} WHERE ip_address IN(%s) " , $ip_address);
		$wpdb->query($query);

	}


	/**
	* Delete old Guardgiant_IP_Failed_Logins records in the database 
	* This is a housekeeping task to ensure we dont use up too many resources
	*
	* @since	1.0.0
	*
	*/
	public static function delete_old_records()
	{	
		global $wpdb;

		$time_difference = time() - (GUARDGIANT_DELETE_FAILED_IP_RECORDS_FROM_DB_AFTER_DAYS * GUARDGIANT_SECONDS_IN_1_DAY);	
		
		$tablename = $wpdb->prefix."guardgiant_ip_failed_logins";
		$query = $wpdb->prepare("DELETE FROM  {$tablename} WHERE created_at_time < %d " , $time_difference);
		$wpdb->query($query);

	}


	/**
	* Checks if an IP address should be blocked. 
	*
	* @since	1.0.0
	*
	* @return	bool	Returns true if they should be blocked, false if not
	*
	*/
	public function should_ip_be_blocked()
	{
		$settings = get_option( 'guardgiant-settings' );

		// Check that this IP not already blocked
		if (empty($this->blocked_time)) {
			
			// Is IP address blocking enabled by the site administrator?
			if (isset($settings["enable_blocking_of_ips_with_multiple_failed_login_attempts"])) {

				// OK, let's check if this IP has exceeded the number of failed login attempts that we allow.
				// (The site administrator can set the number of failed login attempts in the wordpress settings)
				if ( (absint($settings["num_of_failed_logins_by_IP_before_mitigation_starts"]) != 0) && ($this->attempt_count >= absint($settings["num_of_failed_logins_by_IP_before_mitigation_starts"] )) )
				{
					if (!Guardgiant::is_item_in_whitelist($this->ip_address,$settings[ 'whitelist_ip_addresses' ]))
						return TRUE;
					
				}	
			}
		}
		else {
			// This IP has already been blocked but lets check if it's time to release it.
			// the blocked_time is a unix timestamp
			$time_now = time();	// unix timestamp		
			$time_difference = $time_now - $this->blocked_time;	// in seconds
			$mins_to_block = $this->get_mins_to_block();

			if ( ($time_difference/60) < $mins_to_block ) {

				// The IP is already blocked but they haven't waited enough time yet to release it
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	* Blocks an IP address to prevent log in.
	*
	* @since	1.0.0
	*
	*/
	public function block_ip()
	{
		$settings = get_option( 'guardgiant-settings' );

		// Block this IP. (we put a timestamp in the 'blocked_time' field)
		if ( (isset($settings['mins_to_block_ip'])) && (!empty($settings['mins_to_block_ip'])) ) {
			if ((empty($this->blocked_time)) && (absint($settings['mins_to_block_ip']) > 0)) {

				$this->blocked_time = time();
				$this->save();	
			}
		}
	}


	/**
	* Releases a blocked IP address to enable log in
	*
	* @since	1.0.0
	*
	*/
	public function release_block_on_ip()
	{
		$this->blocked_time = NULL;
		$this->save();
	}



	/**
	* Should we show the captcha field on the login page?
	* The administrator can set if and when this happens
	*
	* @since	1.0.0
	*
	* @return	bool	True if we should show the captcha field, false otherwise
	*/
	public function should_show_captcha_field() {
		$settings = get_option( 'guardgiant-settings' );

		// Are we are on the login page? Some plugins hide or replace the login page
		if(stripos($_SERVER["SCRIPT_NAME"], strrchr(wp_login_url(), '/')) !== false){
					
			// Is this option enabled by the site administrator?
			if (isset($settings["enable_login_captcha"])) { 

				// Check the site administrator has set this up correctly
				if (!Guardgiant_Captcha::has_been_setup_correctly())
					return FALSE;

				// Check the IP is not in the whitelist 
				if (Guardgiant::is_ip_in_whitelist($this->ip_address,$settings[ 'whitelist_ip_addresses' ]))
					return FALSE;
				
				// Have we reached the 'trigger number' of failed login attempts by this IP?
				if ( (isset($settings['num_of_failed_logins_by_IP_before_captcha_shown'])) && (!empty($settings['num_of_failed_logins_by_IP_before_captcha_shown'])) ) { 
					if ($this->attempt_count >= absint($settings["num_of_failed_logins_by_IP_before_captcha_shown"] ))
						return true;
					else
						return false;
				}
			}
		}
	}


	/**
	* Calculate the number of minutes we should block this IP for
	* The administrator can set a progressive amount of time, so after each failed login attempt
	* the block time increases
	*
	* @since	1.0
	*
	* @return	int	$mins_to_block	
	*/
	public function get_mins_to_block()
	{
		$settings = get_option( 'guardgiant-settings' );

		// get the base amount of time that an IP is blocked for
		$mins_to_block = $settings['mins_to_block_ip'];

		// do we add additional mins after each attempt
		if (isset($settings['block_IP_on_each_subsequent_failed_attempt'])) {

			// yes we do, so let's get the 'additional minutes' we need to add for each attempt
			if (isset($settings['block_IP_on_each_subsequent_failed_attempt_mins'])) 
				$additional_mins =  absint($settings['block_IP_on_each_subsequent_failed_attempt_mins']);
			else
				$additional_mins = 0;
		
			// ok, we need to work out the total block time.
			// start by working out how many further attempts need to have the 'additional minutes' applied
			if (isset($settings["num_of_failed_logins_by_IP_before_mitigation_starts"])) {
				$start_blocking_at_num_attempts = absint($settings["num_of_failed_logins_by_IP_before_mitigation_starts"]);
				$further_attempts_made = $this->attempt_count - $start_blocking_at_num_attempts;

				// now work out the additional minutes
				$total_additional_mins_to_add = 0;
				if ($further_attempts_made > 0) {	
					$total_additional_mins_to_add += $additional_mins * $further_attempts_made;
				}

				$mins_to_block = $mins_to_block + $total_additional_mins_to_add;
			} 
		}
		
		return $mins_to_block;
	}


	/**
	* Create an error message string (for display to the user).
	* This functions adds the error to an existing WP_Error object. 
	*
	* @since	1.0.0
	*
	* @param	WP_Error	&$error
	*/
	public function create_blocked_ip_error(&$error)
	{
		$settings = get_option( 'guardgiant-settings' );

		// IP is locked out. Create error message and return
		$time_now = time();
		$time_when_block_ends = $this->blocked_time + (60*absint($this->get_mins_to_block()));
		$mins_until_block_ends = ceil(($time_when_block_ends - $time_now)/60);
		
		// create the error message string
		$msg = '<strong>' . __('Error','guardgiant') . ':</strong> ';
		$msg .= __('Your IP address is temporarily blocked.','guardgiant');

		if (isset($settings['show_mins_remaining_in_error_msg'])) {
			$msg .= ' ' . __('Please retry in','guardgiant') .' ' . $mins_until_block_ends . ' ';
			if ($mins_until_block_ends == 1)
				$msg .=  __('minute.','guardgiant' );
			else
				$msg .=  __('minutes.','guardgiant' );

		}
		else
			$msg .= ' ' . __('Please retry later.','guardgiant' );

		$error->add('ip_blocked',$msg);
		
	}
}



