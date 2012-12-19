<?php 
global $wpdb;
$table_name = $wpdb->prefix . "cn_track_post";

if(isset($_POST['to']) and isset($_POST['from'])) {
	$select = "SELECT *,count(*) as counts FROM $table_name WHERE create_date >='".$_POST['from']."' AND create_date<='".$_POST['to']."' group by post_id order by counts desc LIMIT 0,100";
} else {
	$select = "SELECT *,count(*) as counts FROM $table_name WHERE 1 group by post_id order by counts desc LIMIT 0,100";
}
$tabledata = $wpdb->get_results($select);
?>
<div class="wrap">
<h2>Input date range</h2>
<form action="" method="post">
<p><label for="from">From</label>&nbsp;<input type="text" id="from" name="from" />&nbsp;<label for="to">to</label>&nbsp;<input type="text" id="to" name="to" />&nbsp;<input type="submit" class="button-primary" value="<?php _e('Submit') ?>" /></p>
</form>
</div>
<div class="wrap">
<h2>Post views stats <?php if(isset($_POST['to']) and isset($_POST['from'])) { echo ' | '. $_POST['from'].' - '. $_POST['to']; } ?></h2>
<table class="widefat page fixed" cellspacing="0">
	<thead>
	<tr valign="top">
		<th class="manage-column column-title" scope="col" width="50">Serial</th>
		<th class="manage-column column-title" scope="col" width="50">Post ID</th>
		<th class="manage-column column-title" scope="col">Post Title</th>
		<th class="manage-column column-title" scope="col" width="100">Author</th>
		<th class="manage-column column-title" scope="col" width="70">Comment</th>
		<th class="manage-column column-title" scope="col" width="50">Views</th>
	</tr>
	</thead>
	<tbody>
	<?php $i=1; foreach($tabledata as $data) { 
	$posts = get_post($data->post_id); 
	$title = $posts->post_title;
	$user_info = get_userdata($posts->post_author);
	?>
	<tr valign="top">
		<td>
			<?php echo $i ?>
		</td>
		<td>
			<a target="_blank" href="<?php echo get_option('siteurl').'/wp-admin/post.php?post='.$data->post_id.'&action=edit' ?>"><?php echo $data->post_id ?></a>
		</td>
		<td>
			<a target="_blank" href="<?php echo get_permalink( $data->post_id ); ?>"><?php echo $title?$title:'(No Title)' ?></a>
		</td>
		<td>
			<?php echo $user_info->user_login ?>
		</td>
		<td>
			<?php echo $posts->comment_count ?>
		</td>
		<td>
			<?php echo $data->counts ?>
		</td>
	</tr>
	<?php $i++; } ?>
	</tbody>
	<tfoot>
	<tr valign="top">
		<th class="manage-column column-title" scope="col" width="50">Serial</th>
		<th class="manage-column column-title" scope="col" width="50">Post ID</th>
		<th class="manage-column column-title" scope="col">Post Title</th>
		<th class="manage-column column-title" scope="col" width="100">Author</th>
		<th class="manage-column column-title" scope="col" width="70">Comment</th>
		<th class="manage-column column-title" scope="col" width="50">Views</th>
	</tr>
	</tr>
	</tfoot>
</table>
</div>