<?php
/**
 * The table used to display recent login activity
 *
 * @since   1.0.0
 * 
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */


// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Guardgiant_Table_Login_Activity_Log extends WP_List_Table
{

    /**
	 * The date format that the user has set in their profile.
	 *
	 * @access   private
	 * @var      string    $date_format    
	 */
    private $date_format;

    /**
	 * The time format that the user has set in their profile.
	 *
	 * @access   private
	 * @var      string    $time_format    
	 */
    private $time_format;

    /**
	 * Whether we display the IP address location in the table
	 *
	 * @access   private
	 * @var      bool    $use_ip_address_geolocation    
	 */
    private $use_ip_address_geolocation;


    /**
	 * Initialize the class and set its properties. 
	 * 
	 * @since	1.0.0
	 * 
	 */
    public function __construct()
    {
        // Set parent defaults.
        parent::__construct(array(
            'singular' => 'log entry', // Singular name of the listed records.
            'plural' => 'log entries', // Plural name of the listed records.
            'ajax' => false, // Does this table support ajax?
            ));

        // Is IP address geolocation enabled? 
        $settings = get_option( 'guardgiant-settings' );
        if (isset($settings['use_ip_address_geolocation']))
            $this->use_ip_address_geolocation = TRUE;
        else
            $this->use_ip_address_geolocation = FALSE;

        // get formats so we can display the date & time in the correct way for the user
        // the user sets this in their profile
        $this->date_format = get_option( 'date_format' );
        $this->time_format = get_option( 'time_format' );     
             
        add_filter( 'default_hidden_columns', 'Guardgiant_Table_Login_Activity_Log::hide_ad_list_columns', 10, 2 );

    }

    public static function hide_ad_list_columns( $hidden, $screen ) {
        
        if( isset( $screen->id ) && 'guardgiant_page_guardgiant-login-activity-log' === $screen->id ){      
            $hidden[] = 'device_type';     
        }   
 
        return $hidden;
    }

    /**
     * Prepare the items that the table will use
     *
     * @since  1.0.0
     * 
     */
    public function prepare_items()
    {
        
        // first, do any actions that the user has requested
        $this->process_bulk_action();

        // we need to remove these arguments to stop the URI expanding each time
        $_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );

       
        // call the standard functions regarding setting up the table
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = $this->get_column_info();

        // get the number of entries to show on each page. This is set in the 'screen options' on the page
        $perPage = $this->get_items_per_page('guardgiant_login_entries_per_page', GUARDGIANT_DEFAULT_ITEMS_PER_PAGE_ON_ACTIVITY_LOG);

         // do any pagination that is required
        $currentPage = $this->get_pagenum();
        $totalItems  = self::record_count();

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        // get the log entries and we are done
        $this->items = $this->table_data( $perPage, $currentPage );
    
    }
     
    

    /**
     * Build the WHERE statements in the sql string
     *
     * @since  1.0.0
     * 
     */
	public static function conditional_sql() {
		
		$where = false;

		//Check Search
		if ( isset( $_GET['s'] ) and ! empty( $_GET['s'] ) ) {
			$search  = sanitize_text_field( $_GET['s'] );
			$where[] = "`ip_address` LIKE '%{$search}%' OR `username` LIKE '%{$search}%'";
		}

		//Check time_period
		if ( isset( $_GET['time_period'] ) and ! empty( $_GET['time_period'] ) ) {
            $time_period_selected = sanitize_title_with_dashes( $_GET["time_period"] );
            $time_to_select = FALSE;
            switch ($time_period_selected) {
                
                case '24hrs': 
                    $time_to_select = time() - GUARDGIANT_SECONDS_IN_1_DAY;
                    break;

                case '7days':
                    $time_to_select = time() - 7*GUARDGIANT_SECONDS_IN_1_DAY;
                    break;

                case '30days':
                    $time_to_select = time() - 30*GUARDGIANT_SECONDS_IN_1_DAY;
                    break;
            }
			if ($time_to_select)
			    $where[]   = '`attempt_time` > ' . $time_to_select;
		}

		//Check result code
		if ( isset( $_GET['result_code'] ) and ! empty( $_GET['result_code'] ) ) {
            $result_code_selected = sanitize_title_with_dashes($_GET['result_code']);
            if ($result_code_selected == 'success')
                $where[] = '`result_code` = ' . "'success'";
            else
                $where[] = '`result_code` <> ' . "'success'";
        }
        
        //Check trusted devices
		if ( isset( $_GET['trusted_device'] ) and ! empty( $_GET['trusted_device'] ) ) {
            $result_code_selected = sanitize_title_with_dashes($_GET['trusted_device']);
            if ($result_code_selected == 'trusted')
                $where[] = '`trusted_device` IS TRUE';
            else
                $where[] = '`trusted_device` IS NOT TRUE';
		}

		return $where;
    }
    

   


    /**
     * Get the data to be displayed in the table from the database
     *
     * @since   1.0.0
     * 
     * @return  Array
     */
    private function table_data($per_page = GUARDGIANT_DEFAULT_ITEMS_PER_PAGE_ON_ACTIVITY_LOG, $page_number = 1)
    {

        global $wpdb;
        $tablename = $wpdb->prefix."guardgiant_login_activity_log";
        $sql = "SELECT * FROM `$tablename`";
       
        //work out the conditional where statements to add to the SQL query string
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
        }
        
        // Do we need to order the results?
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        } else {
            // default to ordering by id
            $sql .= " ORDER BY id DESC";
        }

        // we dont need the whole dataset. we just get the results for the page we are displaying
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
        
        // Get the results from the database
        $results = $wpdb->get_results( $sql, 'ARRAY_A' );
        
        return $results;

    }

     /**
     * Counts how many log entries there are (dependent on the filter/search that the user has set)
     * 
     * @since   1.0.0
     * 
     * @return  array   the number of records found
     *
     */
	public static function record_count() {
		global $wpdb;
		$tablename = $wpdb->prefix."guardgiant_login_activity_log";
		$sql = "SELECT COUNT(*) FROM `$tablename`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		return $wpdb->get_var( $sql );
    }
    

    /**
     * Counts how many log entries there are for each time period. 
     * Used by get_views() function above
     * 
     * @since   1.0.0
     * 
     * @return  array   each time period with the count
     *
     */
    private function count_entries_by_date() {
        global $wpdb;
        $count = array();

        // get all the entries available
        $tablename = $wpdb->prefix."guardgiant_login_activity_log";
        $sql = "SELECT attempt_time FROM  `$tablename`";
        $results = $wpdb->get_results( $sql, 'ARRAY_A' );

        // reset the counts for each time period
        $count['all'] = 0;
        $count['24hrs'] = 0;
        $count['7days'] = 0;
        $count['30days'] = 0;

        // work out what the timestamp would be at each time period
        $time_24hrs_ago = time() - GUARDGIANT_SECONDS_IN_1_DAY;
        $time_7days_ago = time() - (7 * GUARDGIANT_SECONDS_IN_1_DAY);
        $time_30days_ago = time() - (30 * GUARDGIANT_SECONDS_IN_1_DAY);

        // check which time period each log entry falls in to
        foreach ($results as $row) {
            
            if ($row['attempt_time'] > $time_24hrs_ago)
                $count['24hrs']++;
            
            if ($row['attempt_time'] > $time_7days_ago)
                $count['7days']++;

            if ($row['attempt_time'] > $time_30days_ago)
                $count['30days']++;    
               
            $count['all']++; 
        }

        return $count;

    }


    /**
     * Extra controls to be displayed between bulk actions and pagination.
     * We use it to display the 'filter' dropdown box, so users can filter by failed or successful logins
     * 
     * @since   1.0.0
     *
     * @param   string  $which  contains either 'top' or 'bottom' as function is called at either end of table
     * 
     */
	function extra_tablenav( $which ) {
		
		?><div class="alignleft actions"><?php
			
	        if ( 'top' === $which && !is_singular() ) {
		        
	            ob_start();
                
                $this->result_code_dropdown();
                $this->trusted_device_dropdown();
	            
	            $output = ob_get_clean();
	 
	            if ( ! empty( $output ) ) {
	                echo $output;
                    // submit_button( __( 'Filter' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
                    echo '<input type="submit" id="doaction" class="button action" value="Filter">';
	            }
	        }
		?></div><?php
    }
    

    /**
	 * Displays the search box, so users can search by an IP address
	 *
	 * @since 1.0.0
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['time_period'] ) ) {
			echo '<input type="hidden" name="time_period" value="' . esc_attr( $_REQUEST['time_period'] ) . '" />';
        }
        
        if ( ! empty( $_REQUEST['result_code'] ) ) {
			echo '<input type="hidden" name="result_code" value="' . esc_attr( $_REQUEST['result_code'] ) . '" />';
        }

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		
		
		?>
            <p class="search-box">
                <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
                <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_attr_e( 'Username or IP' ); ?>"/>
                    <?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
            </p>
		<?php
    }
    


    /**
     * Outputs the HTML for the 'result_code' dropdown control. 
     * 
     * @since   1.0.0
     *
     *
     */
    public function result_code_dropdown( ) {

        // define the options to display in the dropdown control
		$options = array (
			'0' => __( 'All logins', 'guardgiant' ),
			'success' => __( 'Successful logins', 'guardgiant' ),
			'failed' => __( 'Failed logins', 'guardgiant' ),			
		);

        // check if we have an existing selection
		$result_code = false;
		if ( !empty( $_REQUEST['result_code'] ) ) {
			$result_code = sanitize_title_with_dashes($_REQUEST['result_code']);
		}

        // output the HTML
		?><label class="screen-reader-text" for="result_code"><?php
			_e( 'Filter by result type', 'guardgiant' ); 
		?></label>
		<select id="result_code" name="result_code"><?php
			foreach( $options as $key => $value ) {
				?><option value="<?php echo $key; ?>" <?php selected( $result_code, $key, true );?>><?php 
					echo $value;
				?></option><?php				
			}
		?></select>
        
        <?php
					
    }
    
    public function trusted_device_dropdown() {

        // define the options to display in the dropdown control
		$options = array (
			'0' => __( 'All devices', 'guardgiant' ),
            'trusted' => __( 'Trusted devices', 'guardgiant' ),			
            'unrecognized' => __( 'Unrecognized devices', 'guardgiant' ),
		);

        // check if we have an existing selection
		$trusted_device = false;
		if ( !empty( $_REQUEST['trusted_device'] ) ) {
			$trusted_device = sanitize_title_with_dashes($_REQUEST['trusted_device']);
		}

        // output the HTML
		?><label class="screen-reader-text" for="result_code"><?php
			_e( 'Filter by trusted device', 'guardgiant' ); 
		?></label>
		<select id="trusted_device" name="trusted_device"><?php
			foreach( $options as $key => $value ) {
				?><option value="<?php echo $key; ?>" <?php selected( $trusted_device, $key, true );?>><?php 
					echo $value;
				?></option><?php				
			}
		?></select>
        
        <?php
					
    }



    /**
     * Outputs the views at the top left of the table.  
     * We use this to allow the user to choose specific time periods such as last 24 hours
     * 
     * @since   1.0.0
     * 
     * @return  array   The views with associated URLs
     *
     */
    public function get_views() {

        $views   = array();
        if (! empty($_REQUEST['time_period']))
            $current = sanitize_title_with_dashes($_REQUEST['time_period']);
        else
            $current = 'all';


        // get stats about the number of posts for each time period
        $num_posts = $this->count_entries_by_date();

		//All Actions
		$class        = ( $current == 'all' ? ' class="current"' : '' );
		$all_url      = remove_query_arg( array( 'time_period', 'paged' ) );
		$views['all'] = "<a href='{$all_url }' {$class} >" . __( "All", 'guardgiant' ) . " <span class=\"count\">(" . $num_posts['all'] . ")</span></a>";
		$views_item   = array(
			'24hrs'   => array( "name" => __( "Last 24 hours", 'guardgiant' ), "status_id" => 1 ),
			'7days' => array( "name" => __( "Last 7 days", 'guardgiant' ), "status_id" => 0 ),
			'30days'    => array( "name" => __( "Last 30 days", 'guardgiant' ), "status_id" => 2 )
		);
		foreach ( $views_item as $k => $v ) {
			$custom_url  = add_query_arg( 'time_period', $k, remove_query_arg( array(  'paged' ) ) );
			$class       = ( $current == $k ? ' class="current"' : '' );
			$views[ $k ] = "<a href='{$custom_url}' {$class} >" . $v['name'] . " <span class=\"count\">(" . $num_posts[$k] . ")</span></a>";
		}

		return $views;

    }
    

    

    /**
	 * Method to display the IP address in the table. We add an action to delete the entry in this table column.
	 *
     * @since   1.0
     * 
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_trusted_device( $item ) {

		$delete_nonce = wp_create_nonce( 'gg_delete_log_entry' );
        
        $url = add_query_arg( array( 'action' => 'delete', 'delete_nonce' => $delete_nonce, 'log_entry' => $item['id']) ) ;

		$actions = [
            'delete'    => sprintf('<a href="%s">Delete row</a>',$url)
		];

        if ($item['trusted_device'])
            return esc_html(__('Trusted','guardgiant')) . $this->row_actions( $actions );
        else
            return esc_html( __('Unrecognized','guardgiant')) . $this->row_actions( $actions );

		
    }
    


    /**
     * Format the data to show on each column of the table
     *
     * @since   1.0.0
     * 
     * @param   array   $item        row data
     * @param   string  $column_name the current column name
     *
     * @return  string  the string to display in the table cell
     * 
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            
            case 'attempt_time':
                $attempt_date = wp_date($this->date_format,$item[$column_name]);
                $attempt_time = wp_date($this->time_format,$item[$column_name]);
                return $attempt_date . '<br/>' . $attempt_time;

            case 'id':    
            case 'ip_address':
            case 'ip_location':
            case 'username':
            case 'result_code':
            case 'device_type':
                return esc_html($item[ $column_name ]);

            case 'trusted_device':
                if ($item[$column_name])
                    return esc_html(__('Trusted','guardgiant'));
                else
                    return esc_html( __('Unrecognized','guardgiant'));  

            case 'result_description':
                $truncated_error_message = Guardgiant::get_first_sentence($item[ $column_name ]);
                if (!$truncated_error_message)
                    $truncated_error_message = $item[ $column_name ];
                return esc_html($truncated_error_message);

        }
    }


    /**
     * What to display in the 'bulk actions' dropdown control
     *
     * @since   1.0.0
     *
     * @return  array  id and text to display
     * 
     */
    protected function get_bulk_actions()
    {
        $actions = array(
            'bulk-delete' => __('Delete','guardgiant')
            );

        return $actions;
    }   



	/**
	 * Delete a log entry.
     * 
     * @since   1.0.0
	 *
	 * @param int $id  
	 */
	public static function delete_log_entry( $id ) {
		global $wpdb;
        $tablename = $wpdb->prefix."guardgiant_login_activity_log";

		$wpdb->delete(
			$tablename,
			[ 'id' => $id ],
			[ '%d' ]
		);
	}


    /**
	 * Do any work that the user has requested. e.g. delete log entry
     * This is called before the table is displayed
     * 
     * @since   1.0.0
	 * 
	 */
    protected function process_bulk_action()
    {
    
        //Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
           
            // We should have a nonce.
            if (!isset($_REQUEST['delete_nonce'] ))
                return;
  
            // Verify the nonce
            $nonce = esc_attr( $_REQUEST['delete_nonce'] );
                
			if ( ! wp_verify_nonce( $nonce, 'gg_delete_log_entry' ) ) {
				return;
			}
			else {
				self::delete_log_entry( absint( $_GET['log_entry'] ) );
			}
        }
        
        // If the delete bulk action is triggered
		if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'bulk-delete' )
        || ( isset( $_GET['action2'] ) && $_GET['action2'] == 'bulk-delete' )
        ) {
            
            if (isset($_GET['bulk-delete'])) {
                $delete_ids = esc_sql( $_GET['bulk-delete'] );

                // loop over the array of record IDs and delete them one by one
                foreach ( $delete_ids as $id ) {
                    self::delete_log_entry( absint($id) );
                }
            }

        }
    }

    


    /**
     * Define the columns to use in our table
     *
     * @since   1.0.0
     * 
     * @return  Array   The column ID's and names
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', // Render a checkbox instead of text.
            'attempt_time'    => __('Time','guardgiant'),
            'trusted_device'    => __('Device','guardgiant'),
            'ip_address'       => __('IP Address','guardgiant'),
            'ip_location'   => __('IP Location','guardgiant'),
            'device_type'   => __('Make','guardgiant'),
            'username' => __('Username','guardgiant'),
            'result_code'    => __('Result','guardgiant'),
            'result_description'      => __('Message','guardgiant')
        );

        return $columns;
    }

    /**
     * Define the hidden columns 
     *
     * @since   1.0.0
     * 
     * @return  Array
     */
    public function get_hidden_columns()
    {
        if (!$this->use_ip_address_geolocation)
            return array('id','ip_location');
        else
            return array('id');
    }


    /**
     * Define the html code for outputting a checkbox 
     *
     * @since   1.0.0
     * 
     * @return  string
     */
    protected function column_cb($item)
    {
        return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', esc_html($item['id'])
		);
    }


    /**
     * Define the text to display when there are no entries to display 
     *
     * @since   1.0.0
     * 
     * @return  string
     */
    public function no_items() {
        if ( (isset($_GET['time_period'])) || (isset($_GET['s'])) || (isset($_GET['result_type'])) || (isset($_GET['trusted_device']))   ) 
            _e( 'No log entries found.' );
        else
            _e( 'Login activity will appear here.' );
    }


    /**
     * Define the sortable columns
     *
     * @since   1.0.0
     * 
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('attempt_time' => array('attempt_time', true),
        'ip_address' => array('ip_address', true),
        'ip_location' => array('ip_location',true),
        'trusted_device' => array('trusted_device',true),
        'device_type' => array('device_type',true),
        'username' => array('username', true), 
        'result_code' => array('result_code', true), 
        'result_description' => array('result_description', true), 
        );
    }

}