<?php
/**
 * Widget API: WP_Widget_Pages2 class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

/**
 * Core class used to implement a Pages2 widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class WP_Widget_Pages2 extends WP_Widget {

	/**
	 * Sets up a new Pages2 widget instance.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'widget_pages2',
			'description'                 => __( 'A pages2 form for your site.' ),
			'customize_selective_refresh' => true,
			'show_instance_in_rest'       => true,
		);
		parent::__construct( 'pages2', _x( 'Pages2', 'Pages2 widget' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current Pages2 widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Pages2 widget instance.
	 */
	public function widget( $args, $instance ) {
		// $title = ! empty( $instance['title'] ) ? $instance['title'] : '';

		// /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		// $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		// echo $args['before_widget'];
		// if ( $title ) {
		// 	echo $args['before_title'] . $title . $args['after_title'];
		// }

		// // Use active theme pages2 form if it exists.
		// get_search_form();

		// echo $args['after_widget'];

		echo "<p>WP_Widget_Pages2  </p>";
		$strs = get_categories();  

        //echo var_dump($strs); 
            
		 
		foreach ($strs as $key=>$value){
			echo '['.$value->name.']'; 

			// if('Tag' == $value->name){ 
			// 	$tmpsgtr = new WP_Query("cat=$value->cat_ID");
			// 	//echo var_dump($tmpsgtr); 

			// 	foreach ($tmpsgtr->posts as $k => $v){
			// 		echo $v->post_title."<br>"; 
			// 	} 
			// }
  		}				
	}

	/**
	 * Outputs the settings form for the Pages2 widget.
	 *
	 * @since 2.8.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		//$flag = shortcode_atts(array('flagname'=>'Tag'), $atts);

		$strs = get_categories();  

		print_r($strs);
	
		foreach ($strs as $key=>$value){
			//echo '['.$value->cat_ID.']'; 

			if('Tag' == $value->name){ 
				$tmpsgtr = new WP_Query("cat=$value->cat_ID");
				//echo var_dump($tmpsgtr); 

				foreach ($tmpsgtr->posts as $k => $v){
					echo $v->post_title."<br>"; 
				} 
			}
  		}		
	}

 

	/**
	 * Handles updating settings for the current Pages2 widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$new_instance      = wp_parse_args( (array) $new_instance, array( 'title' => '' ) );
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		return $instance;
	}


}
 