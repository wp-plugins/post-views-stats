<?php
/*
Plugin Name: Post Views Stats
Plugin URI: http://www.cybernetikz.com
Description: This plugins will track each post view/hit by user. You will be able to see the post view count in the all post page, also you can use the widget to display the most popular post in the sidebar ares.
Version: 1.0
Author: cybernetikz
Author URI: http://www.cybernetikz.com
License: GPL2
*/

$pluginURI = get_option('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__)); 
add_action('wp_head', 'track_post_view');

function cn_tpv_db_install () {
	global $wpdb;
	$table_name = $wpdb->prefix . "cn_track_post";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	
	$sql2 = "CREATE TABLE `$table_name` (
	`id` bigint(20) NOT NULL auto_increment,
	`post_id` int(11) NOT NULL,
	`created_at` varchar(20) NOT NULL,
	`create_date` varchar(20) default NULL,
	PRIMARY KEY  (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql2);
	}
}

register_activation_hook(__FILE__,'cn_tpv_db_install');

function wpgt_add_pages() {
	global $pluginURI;
	add_menu_page('Post Views Stats', 'Post Views Stats', 'manage_options', 'cn_tpv_view_post', 'cn_tpv_view_post_fn',$pluginURI.'/images/stat.png' );
}
add_action('admin_menu', 'wpgt_add_pages');

function cn_tpv_view_post_fn() { 
	ob_start();
	include_once('view.php');
	$out1 = ob_get_contents();
	ob_end_clean();	
	echo $out1;
}

function cn_tpv_most_popular($num) { 
	ob_start();
	include_once('view-most-popular.php');
	$out1 = ob_get_contents();
	ob_end_clean();	
	return $out1;
}

function track_post_view() {
 
	global $post,$wpdb;
	if(is_single())
	{
		$current_user = wp_get_current_user();
		$user_role = $current_user->roles[0];
		if ( $user_role != 'administrator' ) {
			$table_name = $wpdb->prefix . "cn_track_post";
			$insert = "INSERT INTO " . $table_name . "( post_id, created_at, create_date ) VALUES (" . $post->ID . ",'" . time() . "','" . date('Y-m-d')."')";
			$results = $wpdb->query( $insert );
			if($results) $msg = "Updated";
		}
	}	

}

function cn_tpv_jQuery_files() {

echo '
<script>
jQuery(function() {
	jQuery( "#from" ).datepicker({
		//defaultDate: "+1w",
		dateFormat:"yy-mm-dd",
		changeMonth: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			jQuery( "#to" ).datepicker( "option", "minDate", selectedDate );
		}
	});
	jQuery( "#to" ).datepicker({
		//defaultDate: "+1w",
		dateFormat:"yy-mm-dd",
		changeMonth: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			jQuery( "#from" ).datepicker( "option", "maxDate", selectedDate );
		}
	});
});// JavaScript Document    
</script>
';
}


function cn_tpv_my_script() {
	global $pluginURI;
	wp_enqueue_script('jquery-ui-datepicker');
	wp_register_style('jquery-ui-css', $pluginURI . '/css/jquery-ui.css', array(), '1.9.0' );
	wp_enqueue_style( 'jquery-ui-css' );	
}

add_action('admin_init', 'cn_tpv_my_script');
add_action('admin_head', 'cn_tpv_jQuery_files');

function cn_tpv_columns_head($defaults) { 
	$defaults['view_count'] = 'View Count';  
	return $defaults;
}

function cn_tpv_get_view_count($post_ID) {  
		global $wpdb;
		$table_name = $wpdb->prefix . "cn_track_post";
		$select="SELECT *,count(*) as counts FROM $table_name WHERE post_id=$post_ID group by post_id order by counts desc";
		$tabledata = $wpdb->get_row($select);
		return $tabledata->counts?$tabledata->counts:0;
}

function cn_tpv_columns_content($column_name, $post_ID) {  
	if ($column_name == 'view_count') { 
		echo cn_tpv_get_view_count($post_ID);
	}  
}

class Cntpv_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'cntpv_widget', // Base ID
			'Post Views Stats', // Name
			array( 'description' => __( 'Most Popular Post' ) ) // Args
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$number_of_post = $instance['number_of_post'];
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		echo cn_tpv_most_popular($number_of_post);
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number_of_post'] = strip_tags( $new_instance['number_of_post'] );
		return $instance;
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Most Popular Post' );
		}
		if ( isset( $instance[ 'number_of_post' ] ) ) {
			$number_of_post = $instance[ 'number_of_post' ];
		}
		else {
			$number_of_post = 5;
		}
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /><br /><br />
		<label for="<?php echo $this->get_field_id( 'number_of_post' ); ?>"><?php _e( 'Number of post to view:' ); ?></label> <input class="widefat" id="<?php echo $this->get_field_id( 'number_of_post' ); ?>" name="<?php echo $this->get_field_name( 'number_of_post' ); ?>" type="text" value="<?php echo esc_attr( $number_of_post ); ?>" /></p>
		<?php 
	}

} // class Cnss_Widget
add_action( 'widgets_init', create_function( '', 'register_widget( "Cntpv_Widget" );' ) );

add_filter('manage_posts_columns', 'cn_tpv_columns_head');  
add_action('manage_posts_custom_column', 'cn_tpv_columns_content', 10, 2);  
?>