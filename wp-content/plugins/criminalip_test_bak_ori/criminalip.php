<?php
 


// add_filter('the_title',function($args){
//     return '['.$args.']';
// });

// add_shortcode('shortCode','test2');

// function test2(){
//     ob_start(); 


//     $instance = array();
//     $instance['title'] = 'core/html';   
    
//     $args = 'before_widget';
//     the_widget( 'WP_Widget_Block', $instance, $args );


//     return ob_get_clean();
// }
  

// function test($atts){  
//     ob_start();  

//     $flag = shortcode_atts(array('flagname'=>'Tag'), $atts);

//     $strs = get_categories(); 
//    //echo var_dump($strs);
  
//     foreach ($strs as $key=>$value){
//         //echo '['.$value->cat_ID.']'; 

//         if($flag['flagname'] == $value->name){ 
//             $tmpsgtr = new WP_Query("cat=$value->cat_ID");
//             //echo var_dump($tmpsgtr); 

//             foreach ($tmpsgtr->posts as $k => $v){
//                 echo $v->post_title."<br>"; 
//             } 
//         }
//    }

//     return ob_get_clean();
// }

// add_filter('the_title',test);

// function  test($args){
//     return '['.$args.']';
// }

// add_filter('the_content',function($args){
//     $s = '<input type="button" value="안녕하세요" onclick="test(this)" />';
//     return $args.'   '.$s.'    '.time();
// }); 

// add_action('wp_footer','dd');

// function   dd(){
//     echo'<p>hi</p>';
// } 

// add_action('wp_head','test1');

// function test1(){
//     $s ='<script> function test(e) { alert(e.value); } </script>'; 
//     echo $s;
// }
 
 
// global $wp_widget_factory; 
// function firstwidget_test(){ 
//      $wp_widget_factory->register( 'WP_Widget_Pages2' );

// } 

//  add_action( 'widgets_init', 'firstwidget_test' );  
// Our custom post type function
 

function create_posttype() {  

    $labels = array(
         'name' => 'Books'
        ,'singular_name' => 'Book'
        ,'add_new' => 'Add new'
        ,'add_new_item' => 'Add New Book'
        ,'new_item' => 'New Book'
        ,'edit_item' => 'Edit Book'
        ,'not_found' => 'No books found.'
        ,'not_found_in_trash' => 'No books found in trash.'
    ); 


    $args = array(
         'public'    => true
        // 'label'     => __( 'test', 'textdomain' ), 
        ,'labels' => $labels
        ,'menu_icon' => 'dashicons-book'
        ,'show_ui' => true 
        ,'show_in_menu' => true
        ,'supports' => array('title','editor','custom-field','page-attributes')
    );

    

    register_post_type('criminalip' , $args); 
}

add_action('init','create_posttype'); 

add_filter( 'manage_edit-post_columns', 'set_columns' ); 
function set_columns() {
	$column = array(
        ' cb'    => '<input type="checkbox">'
        ,'title' => '제목'
        ,'order' => '순서'
        ,'images' => '이미지'
        ,'date' => '날짜' 
    ); 

    return $column; 
}
 
add_action( 'manage_posts_custom_column', 'fill_columns' );
function fill_columns($column) {
	global $post;
	switch($column) {
		case 'thumbnail' :
			if (has_post_thumbnail($post->ID))
				echo get_the_post_thumbnail($post->ID, array(50, 50));
			else
				echo '<em>no thumbnail</em>';
		break;
	}
}

add_action('plugin_action_links', '');
function plugin_action_links( $links ) {
    array_unshift( $links, '<a href="' . admin_url( 'criminalip_settings.php?page=criminalip_settings' ) . '">' . __( 'Settings','criminalip_settings') . '</a>' );

    return $links;
}

?>
