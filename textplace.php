<?php
/*
	Plugin Name: Textplace
	Version: 1.0.1
	Plugin URI: http://www.samgerstenzang.com/textplace.html
	Description: Textplace is a plugin to include commonly used text across multiple posts, pages and templates.
	Author: Sam Gerstenzang
	Author URI: http://www.samgerstenzang.com/
*/

/*  
	Copyright 2008  Sam Gerstenzang  (email : sgerstenzang@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

register_activation_hook( __FILE__, 'textplace_install' );

function textplace_install () {
	global $wpdb;
	$table_name = $wpdb->prefix . "textplace";
	
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
  
		$sql = "CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name tinytext NOT NULL,
				text text,
				UNIQUE KEY id (id)
				);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
   }
}


add_action('admin_menu', 'textplace_add_menu');
function textplace_add_menu(){
	add_management_page('Textplace', 'Textplace', '3', 'textplace','textplace');
	// wp_enqueue_script('jquery');
	// if (user_can_richedit())wp_enqueue_script('editor');
	// wp_enqueue_script('thickbox');
	// wp_enqueue_script('media-upload');
}

function textplace(){
	if (isset($_POST['textplace_name'])){
		check_admin_referer('textplace_create');
		create_textplace($_POST['textplace_name']);
		list_textplace("Textplace added.");
	}
	elseif (isset($_POST['textplace_delete'])){
		check_admin_referer('textplace_delete_link');
		delete_textplace($_POST['textplace_delete']);
		list_textplace("Textplace deleted.");
	}
	elseif (isset($_POST['textplace_modify']) && isset($_POST['textplace_text']) ){
		check_admin_referer('textplace_modify');
		modify_textplace($_POST['textplace_modify'],$_POST['textplace_text']);
		list_textplace("Textplace updated.");
	}
	elseif (isset($_POST['textplace_modify'])){
		check_admin_referer('textplace_modify_link');
		show_modify_textplace($_POST['textplace_modify']);
	}
	else {
		list_textplace();
	}
}

function list_textplace(){
 	if(func_num_args() == 1){
		$message = func_get_arg(0);
	}

	global $wpdb;
	$table_name = $wpdb->prefix . "textplace";
	
	?>
	
	<div class="wrap">
	<?php if (isset($message)){ ?><div class="updated fade" id="message" style="background-color: rgb(255, 251, 204);"><p><?php print $message; ?></p></div><?php } ?>
		<h2>Textplace</h2>
	
	<?php
	$sql = "SELECT * FROM $table_name";
	$results = $wpdb->get_results($sql);
	?>

	<table class="widefat">
		<thead>
			<tr>
				<th scope="col" width="5%">Id</th>
				<th scope="col" width="25%">Name</th>
				<th scope="col" width="10%">Modify</th>
				<th scope="col" width="10%">Delete</th>
				<th scope="col" width="20%">For posts/pages:</th>
				<th scope="col" width="20%">For templates:</th>
				
			</tr>
		</thead>
		<tbody>
			<?php foreach($results as $result){ ?>
				<tr>
				<td><?php print $result->id;?></td>
				<td><?php print $result->name;?></td>
				<td><form method='post' action='<?php print $_SERVER["REQUEST_URI"]; ?>'> <?php wp_nonce_field('textplace_modify_link');?><input type='hidden' name='textplace_modify' value='<?php print $result->id; ?>'><input type="submit" class="button" value="Modify" /></form></td>
				<td><form method='post' action='<?php print $_SERVER["REQUEST_URI"]; ?>'><?php wp_nonce_field('textplace_delete_link');?><input type='hidden' name='textplace_delete' value='<?php print $result->id; ?>'><input type="submit" class="button" value="Delete" onclick="if ( confirm('You are about to delete this Textplace \'<?php print $result->name;?>\'') ) { return true;}return false;" /></form></td>
				<td>[textplace id="<?php print $result->id; ?>"]</td>
				<td>&lt;?php&nbsp; grab_textplace("<?php print $result->id; ?>"); ?&gt;</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
 
	<hr />
	
	<form method='post' action='<?php print $_SERVER["REQUEST_URI"]; ?>'><?php wp_nonce_field('textplace_create');?><input type='text' name='textplace_name'><input type='submit' class='button' value='Add Textplace'></form>
	</div>
	<?php
}

function create_textplace($name){
	global $wpdb;
	$table_name = $wpdb->prefix . "textplace";
	
	$sql = "INSERT INTO $table_name (name) VALUES (%s)";
	$results = $wpdb->query($wpdb->prepare($sql,$name));
}

function delete_textplace($id){	
	global $wpdb;
	$table_name = $wpdb->prefix . "textplace";
	
	$sql = "DELETE FROM $table_name where id = %d";
	$results = $wpdb->query($wpdb->prepare($sql,$id));
}

function modify_textplace($id,$text){
	global $wpdb;
	$table_name = $wpdb->prefix . "textplace";
	
	$sql = "UPDATE $table_name set text = %s where id = %d";
	$results = $wpdb->query($wpdb->prepare($sql,$text,$id));
}

function show_modify_textplace($id){
		global $wpdb;
		$table_name = $wpdb->prefix . "textplace";
		
		$sql = "SELECT * FROM $table_name where id = %d";
		$result = $wpdb->get_row($wpdb->prepare($sql,$id));
	
		?>	
		<div class="wrap">
		 <style type='text/css' media='screen'>
				.wrap #tags{display:none;}
			</style>
		<script>
			$j=jQuery.noConflict();
			$j(document).ready(function(){
				$j(".slide").click(function(){
				  $j("#tags").slideToggle("slow");
				  $j(this).toggleClass("active");
				});

			});
		</script>
		<h2>Textplace</h2> (<a href="<?php print $_SERVER["REQUEST_URI"]; ?>">back</a>)
				<h4><?php print $result->name; ?></h4>
	
				<form method='post' action='<?php print $_SERVER["REQUEST_URI"]; ?>'><?php wp_nonce_field('textplace_modify');?><input type='hidden' name='textplace_modify' value='<?php print $result->id; ?>' />
				<textarea cols='60' rows='4' name='textplace_text'><?php print stripslashes(htmlentities($result->text)); ?></textarea>
				 <!-- the_editor($result->text,"textplace_text$result->id","textplace_text$result->id"); -->
				<br /><br />
				<input type='submit' class='button' value='Update'>
				</form><br />
				Helpful hints:
				<ol>
					<li>All HTML is allowed</li>
					<li>You can include other plugins or Textplaces simply by including their shortcode, [textplace id="<?php print $result->id+1; ?>"], for example.
				<!-- <a class="slide">How to insert into a Page/Template</a>
				<div id="tags">
				For pages/posts: [textplace id="<?php print $result->id; ?>"] <br />
				For templates: &lt;?php&nbsp; grab_textplace("<?php print $result->id; ?>"); ?&gt;
				</div> -->
		</div>
<?php	
}

function fetch_textplace($id){
	global $wpdb;
	$table_name = $wpdb->prefix . "textplace";

	$sql = "SELECT * FROM $table_name where id = %d";
	$result = $wpdb->get_row($wpdb->prepare($sql,$id));
	return do_shortcode(stripslashes($result->text));
}

function grab_textplace($id){
	print fetch_textplace($id);
}

function textplace_shortcode($atts) {
	extract(shortcode_atts(array('id' => '-1',), $atts));

	return fetch_textplace($id);
}

add_shortcode('textplace', 'textplace_shortcode');
?>