<?php
/**
 * The class used to record failed login attempts on a per user basis
 *
 * @since	1.0
 *
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */

class Guardgiant_User_Failed_Logins {

	/**
	 * The database id.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private $id;

	/**
	 * The time this record was created. Used for housekeeping purposes 
	 * 	 
	 * @since 1.0.0
	 * @var timestamp
	 */
	public $created_at_time;

	/**
	 * The username
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $username;

	/**
	 * The WP_User id of this username.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $user_id;

	/**
	 * The ip address of the last login attempt.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $user_ip;

	/**
	 * The number of consecutive failed login attempts for this username.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $attempt_count;

	/**
	 * The time of the last failed login attempt for this username.
	 *
	 * @since 1.0.0
	 * @var timestamp
	 */
	public $last_attempt_time;

	/**
	 * The timestamp for when this username was last locked out
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $locked_out_time;
	
	/**
	 * Initialize the class and set its properties.
	 * 
	 * @since	1.0.0
	 *
	 * @param	string	$ip_address
	 * 
	 */
	public function __construct( $username = NULL) {

		$this->username = $username;
		$this->attempt_count = 0;

	}



	/**
	* Saves this instance to the database
	*
	* @since	1.0.0
	*
	*/
	public function save()
	{
		global $wpdb;
		$tablename = $wpdb->prefix."guardgiant_user_failed_logins";

		// is there an existing record?
		if (empty($this->id)) {

			$newrow = array(
				'created_at_time' => time(),
				'username' => $this->username,
				'user_id' => $this->user_id,
				'user_ip' => $this->user_ip,
				'attempt_count' =>  $this->attempt_count,
				'last_attempt_time' => $this->last_attempt_time,
				'locked_out_time' => $this->locked_out_time
				);

			 $wpdb->insert($tablename,$newrow);
			 $this->id = $wpdb->insert_id;

		} else {

			$update_row = array(
				'id' => $this->id,
				'username' => $this->username,
				'user_id' => $this->user_id,
				'user_ip' => $this->user_ip,
				'attempt_count' =>  $this->attempt_count,
				'last_attempt_time' => $this->last_attempt_time,
				'locked_out_time' => $this->locked_out_time
				 );

 			$wpdb->update($tablename,$update_row,array('id'=>$this->id ) );
		}
	}


	/**
	* Checks if a user should be locked out. 
	*
	* @since	1.0.0
	*
	* @return	bool	True if they should be locked out
	*/
	public function should_user_be_locked_out ()
	{
		$options = get_option( 'guardgiant-settings' );
	
		// Check that the user is not ALREADY locked out
		if (empty($this->locked_out_time)) {

			// Are user lockouts enabled by the site administrator?
			if (isset($options["enable_lockout_of_users_with_multiple_failed_login_attempts"])) {

				// OK, let's check if the user has exceeded the number of failed login attempts that we allow.
				// (The site administrator can set the number of failed login attempts in the plugin settings page)
				if ( (absint($options["num_of_failed_logins_before_mitigation_starts"]) != 0) && ($this->attempt_count >= absint($options["num_of_failed_logins_before_mitigation_starts"] )) ) {

					// Final check - we need to make sure this username isn't in the whitelist. 
					// (The site owner can set a whitelist of users that should never be locked out)
					if (!Guardgiant::is_item_in_whitelist($this->username,$options[ 'whitelist_users' ])) {

						// Yes, user should be locked out
						return TRUE;
					}
				}	
			}
		}
		else {
			// This user has already been locked out but lets check if it's time to let them back in.
			// the locked_out_time is a unix timestamp
			$time_now = time();	// unix timestamp		
			$time_difference = $time_now - $this->locked_out_time;	// in seconds

			if ( ($time_difference/60) < absint($options['mins_to_lockout_account'] )) {

				// Yes, the user is already locked out and they haven't waited enough time yet
				return TRUE;
			}
		}

		// if we made it this far the user should NOT be locked out
		return FALSE;
	}

	


	/**
	* Locks out a user to prevent them from logging in.
	*
	* @since	1.0.0
	*
	*/
	public function lock_out_user()
	{
		$options = get_option( 'guardgiant-settings' );

		// Let's lock out this user. (we put a timestamp in the 'locked_out_time' field)
		if ((empty($this->locked_out_time)) && (absint($options['mins_to_lockout_account']) > 0)) {
			$this->locked_out_time = time();
			$this->save();	
		}
	}

	
	/**
	* Releases a lock out to enable a user to log back in
	*
	* @since	1.0.0
	*
	*/
	public function release_lock_out_for_user()
	{
		$this->locked_out_time = NULL;
		$this->save();
	}



	/**
	* Gets a record of Guardgiant_User_Failed_Logins from the database for the provided username
	* If no record exists, a new one is created 
	*
	* @since	1.0.0
	*
	* @param 	string	$username	The username to find
	*
	* @return	Guardgiant_User_Failed_Logins instance
	*
	*/
    public static function get_record_for_user ($username)
	{
		global $wpdb;

		$tablename = $wpdb->prefix."guardgiant_user_failed_logins";
		$query = $wpdb->prepare("SELECT * FROM {$tablename} WHERE `username` = %s LIMIT 1" , $username);
		$tablerows = $wpdb->get_results($query);
		
		// did we find a record?
		if ( (!$tablerows) || (!count($tablerows)==1) )
			return FALSE;

		$failed_logins_record = new Guardgiant_User_Failed_Logins;

		// populate this instance with data from the database
		$failed_logins_record->id = $tablerows[0]->id;
		$failed_logins_record->username = $tablerows[0]->username;
		$failed_logins_record->user_id = $tablerows[0]->user_id;
		$failed_logins_record->user_ip = $tablerows[0]->user_ip;
		$failed_logins_record->attempt_count = $tablerows[0]->attempt_count;
		$failed_logins_record->last_attempt_time = $tablerows[0]->last_attempt_time;
		$failed_logins_record->locked_out_time = $tablerows[0]->locked_out_time;

		return $failed_logins_record;	
	}

	/**
	* Deletes the record of Guardgiant_User_Failed_Logins in the database for the provided username 
	* usually called after a user makes a successful login
	*
	* @since	1.0.0
	*
	* @param 	string	$username	
	*
	*/
	public static function delete_record_for_user($username)
	{
		global $wpdb;

		//delete the failed logins record 
		$tablename = $wpdb->prefix."guardgiant_user_failed_logins";
		$query = $wpdb->prepare("DELETE FROM  {$tablename} WHERE username IN(%s) " , $username);
		$wpdb->query($query);

	}


	/**
	* Creates an error message to describe a user lockout 
	*
	* @since	1.0.0
	*
	* @param	WP_Error	&$error
	*
	*/
	public function create_user_locked_out_error(&$error)
	{
		$settings = get_option( 'guardgiant-settings' );

		// work out how long this lookout will be
		$time_now = time();
		$time_when_lockout_ends = $this->locked_out_time + (60*absint($settings['mins_to_lockout_account']));
		$mins_until_lockout_ends = ceil(($time_when_lockout_ends - $time_now)/60);

		// create the error message string
		$msg = "<strong>" . __('Error','guardgiant') . ':</strong> ';
		//$msg .= __('You have made too many failed login attempts and this account has been temporarily locked out.','guardgiant');
		$msg .= __('Your account has been temporarily locked out. Too many failed login attempts were made.','guardgiant');

		// we only disclose how long they have to wait if the site administrator has enabled this feature
		if (isset($settings['show_mins_remaining_in_error_msg'])) {
			$msg .= ' ' . __('Please retry in','guardgiant') . ' ' . $mins_until_lockout_ends . ' ' ;
			if ($mins_until_lockout_ends == 1) 
				$msg .= __('minute.','guardgiant');
			else
				$msg .= __('minutes.','guardgiant');
		}
		else
			$msg .= ' ' . __('Please try again later.','guardgiant');
		$error->add( 'locked_out', $msg);
		
	}

	/**
	* Delete old Guardgiant_User_Failed_Logins records in the database 
	* This is a housekeeping task to ensure we dont use up too many resources
	*
	* @since	1.0.0
	*
	*/
	public static function delete_old_records()
	{	
		global $wpdb;

		$time_difference = time() - (GUARDGIANT_DELETE_FAILED_IP_RECORDS_FROM_DB_AFTER_DAYS * GUARDGIANT_SECONDS_IN_1_DAY);	
		
		$tablename = $wpdb->prefix."guardgiant_user_failed_logins";
		$query = $wpdb->prepare("DELETE FROM  {$tablename} WHERE created_at_time < %d " , $time_difference);
		$wpdb->query($query);

	}


}

