<?php
/**
 * Plugin Name: CFAR Functions
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Additional Functions for the UNC Center For Aids Research Service Request System.
 * Version: 1.0
 * Author: AndiSites Inc.
 * Author URI: http://andisites.com
 * License: GPL2
 */
 
 /*  Copyright 2014  Matt Harris  (email : matt@andisites.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined('ABSPATH') or die("No script kiddies please!");
define( 'CFARF_URL', plugin_dir_url( __FILE__ ) );
define( 'CFARF_PATH', plugin_dir_path( __FILE__ ) );

// Initialize the metabox class
add_action( 'init', 'cfar_initialize_cmb_meta_boxes', 9999 );
function cfar_initialize_cmb_meta_boxes() {
    if ( !class_exists( 'cmb_Meta_Box' ) ) {
        require_once( 'lib/metabox/init.php' );
    }
}

//IMPORTANT init.php 'save_post' action for cmb class has been edited to priority 9, 
//since it is removed by the ticket plugin at 10 for preventing looping

/* Load plugin core functions */
require( CFARF_PATH . 'functions/users.php' );
require( CFARF_PATH . 'functions/forms.php' );
require( CFARF_PATH . 'functions/cpt.php' );
require( CFARF_PATH . 'functions/p2p.php' );
require( CFARF_PATH . 'functions/cmb.php' );
require( CFARF_PATH . 'functions/taxonomy-meta.php' );
require( CFARF_PATH . 'functions/tickets-list.php' );

//Change Details button label in ticket edit screen
function changeDetailsLabel( $translation, $text ) {
	global $typenow;

	if( $text == 'Issuer' && $typenow == 'tickets' && isset($_GET['post']) )
	    return __('Requester', 'wpas');
        
    	if( $text == 'Issue' && $typenow == 'tickets' && isset($_GET['post']) )
	    return __('Request', 'wpas');
    
    	if( $text == 'Solve This Issue' && $typenow == 'tickets' && isset($_GET['post']) )
	    return __('Respond To Request', 'wpas');
    	
    	global $pagenow;
	if( $pagenow == 'user-edit.php' ) {
		if ($text == 'Agent Support Group') {
			return __('Core', 'wpas');
		}
		if ($text == 'Agent\'s Group') {
			return __('User\'s Core', 'wpas');
		}
	}    	
	return $translation;
}
add_filter( 'gettext', 'changeDetailsLabel', 10, 2 );


/** 
* Process ajax Call from Service Request Form
*/
function get_project_name_fn(){
    $projectPi = $_POST['projectPi'];
    $core = $_POST['core'];
    $projects = get_posts( array(
		  'connected_type' => 'projects_to_pis',
		  'connected_items' => $projectPi,
		  'suppress_filters' => false,
		  'core' => $core,
		  'nopaging' => true
		) );
    $items = array();
    $items[] = array( "text" => __('Select grant / project...','theme'), "value" => 'default' );
    foreach($projects as $project){
    	$grant = get_post_meta($project->ID, 'cfar_projects_grant_title', true);    
    	if($grant != '') {
    		$items[] = array( "text" => $grant, "value" => $project->ID );
    	} else {
    		$items[] = array( "text" => $project->post_title, "value" => $project->ID );
        }
    }
    echo json_encode($items);
    die;
}
add_action('wp_ajax_get_project_name', 'get_project_name_fn');
add_action('wp_ajax_nopriv_get_project_name', 'get_project_name_fn');


/**
* Removing WPAS admin bar customizations to replace with cfar customization
*
* Complicated because admin_bar_menu is one of the last WP hooks to run...
* it wasn't working to hook remove action to admin_bar_menu or wp_before_admin_bar_render
*/
//add_action('admin_bar_menu', 'removeCustomizeAdminBar', 1000);
//function removeCustomizeAdminBar() {
global $wpas;
remove_action( 'admin_bar_menu', array( $wpas, 'CustomizeAdminBar' ), 999 );
	//$hook_name = 'admin_bar_menu';
	//global $wp_filter;
	//echo '<pre>'; print_r($wp_filter[$hook_name]); echo '</pre>';
	//wp_die();
//}

add_action( 'admin_bar_menu', 'cfar_customize_admin_bar', 999 );
function cfar_customize_admin_bar() {
	global $wpas, $wp_admin_bar, $current_user, $post;

	//$submit 	  = wpas_get_option( 'ticket_submit' );
	$tickets_page = wpas_get_option( 'ticket_list' );

	/* Does the user want to see all states? */
	$show = wpas_get_option( 'only_list_open', 'no' );

	/* Get CPT slug */
	$slug = $wpas->getPostTypeSlug();

	if( 'yes' == $show ) {

		/* Get the open term ID */
		$term = get_term_by( 'slug', 'wpas-open', 'status' );

		/* Open term ID */
		$term_id = $term->term_id;

		/* Prepare URL */
		$url = add_query_arg( array( 'status' => $term_id ), admin_url( 'edit.php?post_type=' . $slug ) );

	} else {
		$url = admin_url( 'edit.php?post_type=' . $slug );
	}
		
	if( !current_user_can( 'edit_ticket' ) ) {

		$wp_admin_bar->add_menu( array(
			'id' 	=> 'new-ticket',
			'title' => __( 'Submit Service Request', 'wpas' ),
			//'href' 	=> __( get_permalink($submit) )
			'href' => __(home_url() . '/submit-service-request')
		) );

		$wp_admin_bar->add_menu( array(
			'id' 	=> 'my-tickets',
			'title' => __( 'My Tickets', 'wpas' ),
			'href' 	=> get_permalink( $tickets_page )
		) );

	}

	if( current_user_can( 'edit_ticket' ) ) {

		$wp_admin_bar->add_menu( array(
			'id' 	=> 'all-tickets',
			'title' => __( 'My Tickets', 'wpas' ),
			'href' 	=> __( $url )
		) );

		if( isset( $_GET['action'] ) && 'edit' == $_GET['action'] && 'tickets' == $post->post_type )

		$wp_admin_bar->add_menu( array(
			'id' 	=> 'wpas-private-note',
			'title' => __( 'Add Note', 'wpas' ),
			'href' 	=> '#TB_inline?height=400&amp;width=700&amp;inlineId=wpas-note-modal',
			'meta'  => array( 'class' => 'wpas-add-note thickbox', 'title' => __( 'Add a private note to this ticket', 'wpas' ) )
		) );

	}

	$wp_admin_bar->add_menu( array(
		'id' 	=> 'about-wpas',
		'parent' => 'wp-logo',
		'title' => __( 'About WP Awesome Support', 'wpas' ),
		'href' 	=> 'http://bit.ly/1hYx4Tw'
	) );

}

/**
* Removing WPAS singleTemplate in order to add cores as column properly in cfar plugin, not theme
*
*/
remove_action( 'template_redirect', array( $wpas, 'singleTemplate' ) );
add_action( 'template_redirect', 'cfar_singleTemplate' );
/**
 * Get the new single ticket template for front-end
 */
function cfar_singleTemplate() {
	global $post;
	if( is_single() && 'tickets' == $post->post_type ) {
			include( CFARF_PATH . 'templates/single-tickets.php' );
			exit();
	}
}

//REMOVING WP COMMENTS

// Removes from admin menu
add_action( 'admin_menu', 'my_remove_admin_menus' );
function my_remove_admin_menus() {
    remove_menu_page( 'edit-comments.php' );
}
// Removes from post and pages
add_action('init', 'remove_comment_support', 100);

function remove_comment_support() {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
}
// Removes from admin bar
function mytheme_admin_bar_render() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}
add_action( 'wp_before_admin_bar_render', 'mytheme_admin_bar_render' );

/**
* This will create a menu item under projects for import
*/
function cfar_add_projects_import_menu(){
	$parent_slug = 'edit.php?post_type=projects';
	$page_title = 'Import Projects';
	$menu_title = 'Import';
	$capability = 'import';
	$menu_slug = 'import-projects';
	$function = 'cfar_import_project_data';
	add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
}
add_action('admin_menu',  'cfar_add_projects_import_menu');

function cfar_import_project_data() {
	require_once( CFARF_PATH . 'functions/import/import-projects-admin.php' );
}
/**
* Import CSV function
*/
add_action('admin_init', 'cfar_import_master_function');
function cfar_import_master_function() {
	global $plugin_page;
	$core_id = $_POST['csv_importer_core'];
	$log = array();
	if (isset($_POST['submit']) && $plugin_page == 'import-projects' ) {
		$core = get_term($core_id, 'core');
		$core = $core->slug;
		if (empty($_FILES['csv_import']['tmp_name'])) {
			$log['error'][] = 'No file uploaded, aborting.';
			cfar_print_log_messages($log);
			return;
		}
		if (!current_user_can('publish_pages') || !current_user_can('publish_posts')) {
			$log['error'][] = 'You don\'t have the permissions to publish posts and pages. Please contact an administrator.';
			cfar_print_log_messages($log);
			return;
		}
		$file = $_FILES['csv_import']['tmp_name'];
		//$csv = array_map('str_getcsv', file($file));
		ini_set('auto_detect_line_endings',TRUE);
		$flag = true;
		$row = 2;
		if (($handle = fopen($file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, "," )) !== FALSE) {
		    	$num = count($data);
			if($num !== 18){
				$log['error'][] = 'Wrong column formatting. There should be exactly 18 columns.';
				cfar_print_log_messages($log);
				return;
			}		    	
		    	//Condition to validate column headings are labeled correctly
		    	if($flag === true) {
		    		$fields = array('timestamp', 'pi_name', 'pi_phone', 'pi_email', 'pi_org', 'pi_other_org', 'project_title', 'project_funding_source', 'project_funding_source_addendum', 'project_grant_title', 'activity_code', 'serial_number', 'project_grant_number', 'project_description ', 'project_irb_approval ', 'irb_number', 'publications_presentations', 'percent_core_effort');
		    		for ($c=0; $c < $num; $c++) {
		    				if($data[$c] != $fields[$c]){
		    				$log['error'][] = 'Wrong column format. \''.$fields[$c].'\' data should be entered in column labeled \''.$data[$c].'\'. Please reformat .csv file to upload.';
		    					cfar_print_log_messages($log);
		    					return;
		    				}
		    		}
		    	}
		    	//End column heading validation skip row 1 and continue with processing other rows.
		    	if($flag) { $flag = false; continue; }  //continue is used within looping structures to skip the rest of the current loop iteration and continue execution at the condition evaluation and then the beginning of the next iteration.
			//echo "<p> $num fields in line $row: <br /></p>\n";
			$timestamp = $data[0]; 
			$pi_name = $data[1];
			$pi_phone = $data[2];
			$pi_email = $data[3];
			$pi_org = $data[4];
			$pi_other_org = $data[5];
			$project_title = $data[6];
			$project_funding_source = $data[7];
			$project_funding_source_addendum = $data[8];
			$project_grant_title = $data[9];
			$activity_code = $data[10];
			$serial_number = $data[11];
			$project_grant_number = $data[12];
			$project_description = $data[13];
			$project_irb_approval = $data[14];
			$project_irb_number = $data[15];
			$publications_presentations = $data[16];
			$core_effort = $data[17];
			
			if($timestamp) {
				$date = gmdate("Y-m-d H:i:s", $timestamp);
			} else {
				$ts = time();
				$date = gmdate("Y-m-d H:i:s", $ts);
			}
			
			cfar_process_csv_create_project_user($core, $date, $row, $pi_name, $pi_phone, $pi_email, $pi_org, $pi_other_org, $project_title, $project_description, $project_funding_source, $project_funding_source_addendum, $project_grant_title, $activity_code, $serial_number, $project_grant_number, $project_irb_approval, $project_irb_number, $publications_presentations, $core_effort);
				   
			/*//Print all the data for inspection
			for ($c=0; $c < $num; $c++) {
			    echo $data[$c] . "<br />\n";
			}*/
			$row++;
		    }
		    fclose($handle);
		}
		ini_set('auto_detect_line_endings',FALSE);
	}
}

function cfar_process_csv_create_project_user($core, $date, $row, $pi_name, $pi_phone, $pi_email, $pi_org, $pi_other_org, $project_title, $project_description, $project_funding_source, $project_funding_source_addendum, $project_grant_title, $activity_code, $serial_number, $project_grant_number, $project_irb_approval, $project_irb_number, $publications_presentations, $core_effort) {
       if ( username_exists( $user_name ) ) {
	   $log['error'][] = "Username: ".$user_name." already in use. Check and fix row: " . $row . " of .csv file to upload user.";
	   cfar_print_log_messages($log);
	   return;
       }
       elseif (email_exists($pi_email)) {
	   //$log['error'][] = "Email: ".$pi_email." already in use. Check and fix row: " . $row . " of .csv file to upload user.";
	   $log['notice'][] = "Email: ".$pi_email." already in use. Project will be connected to this user.";
	   //cfar_print_log_messages($log);
	   $user = get_user_by( 'email', $pi_email );
	   $user_id = $user->ID;
	   $user_name = $user->user_login;
	   //return;       	       
       } elseif ($pi_email == ''){
	   $log['error'][] = "No email entered for ".$pi_name." Check and fix row: " . $row . " of .csv file to upload user and corresponding projects.";
	   cfar_print_log_messages($log);
	   return;  
       } else {
       	       // Take pi_name -> lowercase and concatenate to form a wp user_name
	       $string = strtolower($pi_name);
	       $user_name = str_replace(' ', '', $string);
	       $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
	       $user_id = wp_create_user( $user_name, $random_password, $pi_email );
	       $full_name = explode(" ", $pi_name);
	       wp_update_user( array ('ID' => $user_id, 'display_name' => $pi_name, 'first_name'=> $full_name[0], 'last_name'=> $full_name[1], 'role'=> 'principal_investigator') ) ;
	       update_user_meta( $user_id, 'organization', $pi_org );
	       update_user_meta( $user_id, 'other_org', $pi_other_org );
	       update_user_meta( $user_id, 'phone', $pi_phone );
	       update_user_meta( $user_id, 'cfar_core', $core );
	       $log['notice'][] = 'User '.$user_name.' created with password: '.$random_password.'';
       }
       //If project title isn't already in db, add new project, else display error
       if (!get_page_by_title( $project_title, 'OBJECT', 'projects' )) {
       		       
	       $new_post = array(
	       	   'post_type' => 'projects',    
		   'post_title'    =>   $project_title,
		   'post_content'  =>   $project_description,
		   'post_date'     =>   $date,
		   'post_status'   =>   'publish',
		   'post_author' => get_current_user_id(),
		);
		//SAVE THE POST & set Core
	       $pid = wp_insert_post($new_post);
	       wp_set_object_terms( $pid, $core, 'core');
	       //Ignore special / International characters - umlaut wasn't saving to db
	       $g = iconv("UTF-8", "ISO-8859-1//IGNORE", $project_grant_title);
	       update_post_meta($pid, 'cfar_projects_grant_title', $g);
	       if($project_irb_approval == 'Yes' || $project_irb_approval == 'Pending' ) {
	       	       update_post_meta($pid, 'cfar_projects_irb_approval', $project_irb_approval);
	       } //else N/A is default for field
	       if($project_grant_number){
	       	       update_post_meta($pid, 'cfar_projects_grant_number', $project_grant_number);
	       }
	       //set activity code
	       if($activity_code != ''){
	       	       $activity_code = strtolower($activity_code);
	       	       wp_set_object_terms($pid, $activity_code, 'activity_code', true);
	       }
	       //other meta fields
	       if($serial_number) {
	       	       update_post_meta($pid, 'cfar_projects_serial_number', $serial_number);
	       }
	       if($project_irb_number) {
	       	       update_post_meta($pid, 'cfar_projects_irb_number', $project_irb_number);
	       }
	       if($publications_presentations) {
	       	       update_post_meta($pid, 'cfar_projects_publications_presentations', $publications_presentations);
	       }
	       if($core_effort) {
	       	       update_post_meta($pid, 'cfar_projects_percent_core_effort', $core_effort);
	       }
	       
	       //overly complex ways to get term_ids for setting parent/child relationships because wp_set_object_terms returns term_taxonomy_id not term_id...
	       $term_taxonomy_id = wp_set_object_terms( $pid, $project_funding_source, 'sponsor');
	       $term = get_term_by( 'term_taxonomy_id', $term_taxonomy_id[0], 'sponsor' );
	       $parent = $term->term_id;
	       $addendums = explode(', ', $project_funding_source_addendum);
	       foreach($addendums as $a) {
	       	      $child_term_taxonomy_id = wp_set_object_terms( $pid, $a, 'sponsor', true);
	       	      $child_term = get_term_by( 'term_taxonomy_id', $child_term_taxonomy_id[0], 'sponsor' );
	       	      $child = $child_term->term_id;
	       	      wp_update_term( $child, 'sponsor', array('parent' => $parent) );
	       }
	       
	       $log['notice'][] = 'Project #'.$pid.' '.$project_title.' created for '.$core.' core.';
	       
	       //Connect pi to project *******WRAPPED in if($user_id) statement so project can be created if user error, no id set
	       if($user_id) {
			$p2p_id = p2p_type( 'projects_to_pis' )->connect( $pid, $user_id, array(
			    'date' => current_time('mysql')
			) );
			p2p_update_meta($p2p_id, 'role', 'Investigator');
			$log['notice'][] = 'Project #'.$pid.' connected to pi: '.$user_name;
	       }
       } else {
	   $log['error'][] = "Project with title: ".$project_title." already exists, no new project created. Check and fix row: " . $row . " of .csv file";
	   cfar_print_log_messages($log);
	   return; 
       }
       cfar_print_log_messages($log);
}

function cfar_print_log_messages($log) {
        if (!empty($log)) {

        // messages HTML {{{
?>

    <?php if (!empty($log['error'])): ?>

    <div class="error">

        <?php foreach ($log['error'] as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>

    <?php if (!empty($log['notice'])): ?>

    <div class="updated fade">

        <?php foreach ($log['notice'] as $notice): ?>
            <p><?php echo $notice; ?></p>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>

<?php
        // end messages HTML }}}

            $log = array();
       }
}


/**
* This will create a menu item under projects for export
*/
function cfar_add_projects_export_menu(){
	$parent_slug = 'edit.php?post_type=projects';
	$page_title = 'Export as Table 5 Report to CSV or PDF';
	$menu_title = 'Export';
	$capability = 'export';
	$menu_slug = 'export-projects';
	$function = 'cfar_export_project_data';
	add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
}
// Hook for adding admin menus
add_action('admin_menu',  'cfar_add_projects_export_menu');

function cfar_export_project_data() {
	require_once( CFARF_PATH . 'functions/export/export-projects-admin.php' );
}

add_action('admin_init', 'cfar_export_master_function', 11);
function cfar_export_master_function() {
	global $plugin_page;
	if (isset($_POST['submit']) && $plugin_page == 'export-projects' ) {
		
		//Define Inputs
		$core = $_POST['core'];
		if($core == 'all'){$core = null;}
		$start_date = $_POST['start-date'];
		$end_date = $_POST['end-date'];
		$letter = cfar_get_core_letter($core);
		$date_args = cfar_get_export_date_args($start_date, $end_date);
		
		//CSV or PDF
		if($_POST['type'] == 'csv') {
			cfar_export_table_5_csv($core, $letter, $date_args);
		}
		if($_POST['type'] == 'pdf') {
			cfar_export_table_5_pdf($core, $letter, $date_args);
		}
	}
}

function cfar_get_core_letter($core) {
	$core_object = get_term_by('slug', $core , 'core');
	$core_id = (int) $core_object->term_id;
	$core_meta = get_option( "taxonomy_$core_id" );
	$core_letter = $core_meta['cfar_core_letter'];
return $core_letter;
}

function cfar_get_export_date_args($start_date, $end_date) {
	if($start_date == '' && $end_date == '') {
		$args = null;
	} else {
		$start = $start_date . ' 00:00:00';
		$end = $end_date . ' 23:59:59';
		$args = array(
			array(
				'after'      => $start,
				'before'   => $end,
				'inclusive' => true,
			),
		);
	}
return $args;	
}

/**
* Begin CSV export block
*/
function cfar_export_table_5_csv($core, $letter, $date_args) {
			$timestamp = time();
			$date = gmdate("Y-m-d", $timestamp);
			$name = 'table5_export_' . $core . '_' . $date . '.csv';
				/**
				*  
				*  $core variable for querying projects only from a certain core, was set by $_POST['core']
				*  (If core is 'all' the query is set as null value)
				*
				*  $date_args are null if both start and end are not set
				*
				*/
				$args = array(
					'post_type' => 'projects',
					'order' => 'ASC',
					'core' => $core,
					'date_query' => $date_args
				);
				$q = new WP_Query( $args );
				if ( $q->have_posts() ) {
					while ( $q->have_posts() ) {
						$q->the_post();
						global $post;						
						//Get sponsors of this project
						$sponsors = array();
						$programs = array();
						$terms = get_the_terms( $post->ID, 'sponsor' );
						foreach($terms as $term){
							if($term->parent == 0) {
								$sponsors[] = $term->name;
							}
							//Children only since we already have the top level in a row, joined by comma as sponsor list
							if($term->parent != 0) {
								$programs[] = $term->name;
								$t_id = $term->term_id;
								$term_meta = get_option( "taxonomy_$t_id" );
								$ao_code = $term_meta['cfar_project_administering_organization_code'];
							}
						}
						if($sponsors){$sponsor_list = join( "; ", $sponsors );}
						if($programs){$program_list = join( "; ", $programs );}
						//Get Pi's for this project, first pi in array - pis[0] should be principal
						$pis = get_users( array(
							'connected_type' => 'projects_to_pis',
							'connected_items' => $post,
						) );
						//Other pis are coinvestigators
						$coinvestigators = '';
						$investigators = '';
						foreach($pis as $pi) {
							$coinvestigator_info = '';
							$investigator_info = '';
							$role = p2p_get_meta( $pi->p2p_id, 'role', true );
							if($role == 'Collaborator') {
								$coinvestigator_info = get_userdata($pi->ID);
								$coinvestigators .= $coinvestigator_info->last_name .  ', ' . $coinvestigator_info->first_name . ' (' .$coinvestigator_info->organization. ')';
							} else {
								$investigator_info = get_userdata($pi->ID);
								$investigators .= $investigator_info->last_name .  ', ' . $investigator_info->first_name . ' (' .$investigator_info->organization. ')';									
							}
						}
						//Get activity code, serial name for "award supported" field
						$activity_codes = wp_get_post_terms( $post->ID, 'activity_code', array("fields" => "names") );
						$serial_number = get_post_meta($post->ID, 'cfar_projects_serial_number', true);
						$irb = get_post_meta($post->ID, 'cfar_projects_irb_number', true);
						if($irb){$irb = "[$irb]";}
						$pubs = get_post_meta($post->ID, 'cfar_projects_publications_presentations', true);
						if($pubs){$pubs = "[$pubs]";};
						$effort = get_post_meta($post->ID, 'cfar_projects_percent_core_effort', true);
						/**
						*  Okay! Let's get those service requests connected to this project!
						*/
						$ticket_meta_list = '';
						$meta = '';
						$core_services_list = '';
						$core_services = array();
						$services = array();
						$tickets = get_posts( array(
							'connected_type' => 'tickets_to_projects',
							'date_query' => $date_args,
							'status' => 'wpas-close',
							'connected_items' => $post
						) );
						if(!empty($tickets)){
								foreach($tickets as $ticket){
									$services = get_the_terms($ticket->ID, 'service');
									if (is_array($services)) {
										foreach($services as $service) {
											$service_name = $service->name;
											if (!in_array($service_name, $core_services)) {
												$core_services[] = $service_name;
											}
										}
									}											
								}
						$core_services = array_filter($core_services);		
						}
						if(!empty($core_services)){$core_services_list = join("; ", $core_services);}
						/*
						if(!empty($tickets)) {
							$ticket_meta_list = '';
							$meta = '';
							foreach($tickets as $ticket) {
								$statuses = '';
								$t_cores = '';
								$t_cores = get_the_terms( $ticket->ID, 'core' );
								$t_core = array_pop($t_cores);
								$statuses = get_the_terms($ticket->ID, 'status');
								if(!empty($statuses)) {
									$status = array_pop($statuses);
									if($status->slug == 'wpas-close') {
										if($t_core->slug == 'clinical'){
											$meta[] = get_post_meta($ticket->ID, 'cfar_clinical_access_uchcc', true);
											$meta[] = get_post_meta($ticket->ID, 'cfar_clinical_study_coordination', true);
											$meta[] = get_post_meta($ticket->ID, 'cfar_clinical_other_services', true);
										}
										if($t_core->slug == 'clinical-pharmacology'){
											$meta[] = get_post_meta($ticket->ID, 'cfar_cpharm_services_required', true);
											$meta[] = get_post_meta($ticket->ID, 'cfar_cpharm_drugs_text', true);
											$meta[] = get_post_meta($ticket->ID, 'cfar_cpharm_sample_numbers_text', true);
										}
									}
								}
							}
							if($meta != ''){$ticket_meta_list = join("; ", $meta);}
						}*/
						//content of project post
						$content = get_the_content();
						$results = array();
						$results[] = $sponsor_list;
						$results[] = $program_list;
						$results[] = $investigators;
						$results[] = $coinvestigators;
						$results[] = $activity_codes[0].' '.$ao_code.$serial_number;
						$results[] = $core_services_list;
						$results[] = $post->post_title; 
						$results[] = $content;
						$results[] = $irb.' '.$pubs.' ';
						$results[] = $effort;
									foreach ($results as &$value)
			    {
				$value = str_replace("\r\n", "", $value);
				$value = "\"" . $value . "\"";
			    }
						$output .= join(',', $results)."\n";
					}
				} else {
					$results[] =  'none';
				}
				/* Restore original Post Data */
				wp_reset_postdata();
				//var_dump($results);
				//wp_die();

			//$output .= join(',', $results)."\n";
			
			$size_in_bytes = strlen($output);
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition:  attachment; filename=$name; size=$size_in_bytes");
			$labels = 'Sponsor, Program, Investigator (site), Collaborators (site), Award Supported, Core Service, Award Title, Description, "[Outcome Measure (IRB#; Grant Submitted/Awarded; Publications; Presentations)]", % Core Effort'."\r\n";
			print $labels;
			print $output;
			exit;
}

/**
* Begin PDF export Query block
*/
function cfar_export_table_5_pdf($core, $letter, $date_args) {
			$html = '<body style="font-family: arial, sans-serif; font-size: 10pt;">';
			$html .= '<p>'.strtoupper('CORE ' .$letter).'</p>';
			$html .= '<table id="table-5"><thead>';
			$html .= '<tr><th colspan="6" class="center">APPENDIX F (TABLE 5) 2013-2014:  CORE '.strtoupper($letter).'</th></tr>';
			$html .= '<tr>';
			$html .= '<th>Sponsor<br><br> <em>Program</em></th><th>Investigator<br>(site)<br><br> <em>Collaborators (site)</em></th><th>Award Supported</th><th>Core Service</th><th>Award Title<br><br><em>Description of Support/Supported Study</em><br><br>[Outcome Measure (IRB#, Grant Submitted/Awarded, Publications, Presentations)]</th><th>% Core Effort (Avg)</th>';
			$html .= '</tr></thead>';
			/**
			*  Get Top level Sponsor Terms Which is How the Table 5 list of projects / grants is divided beyond core divisions - May need to Add Core as shared taxonomy for projects
			*/
			$taxonomy_name = 'sponsor';
			$a = array('parent' => 0, 'hide_empty' => true);
			$top_level_sponsors = get_terms($taxonomy_name, $a);
			foreach($top_level_sponsors as $top_level_sponsor){
				$name = '<tr><td colspan="6" class="sponsor"><b>'.$top_level_sponsor->name.'</b></td></tr>';
				$html .= $name;
				$children = get_term_children($top_level_sponsor->term_id, $taxonomy_name);
				// NOT Checking for each unique child... 
				foreach($children as $child) {
					$term = get_term_by( 'id', $child, $taxonomy_name );
					if($term->count != 0) {
						$program = '<tr><td colspan="6" class="sponsor-child"><b>'.$term->name.'</b></td></tr>';
						$html .= $program;
						/**
						*  Row is added for the top level sponsor above, then the query is run based on projects associated with that sponsor
						*  
						*  $core variable for querying projects only from a certain core, was set by $_POST['core']
						*  (If core is 'all' the query is set as null value)
						*
						*  $date_args are null if both start and end are not set
						*
						*/
						$args = array(
							'post_type' => 'projects',
							'order' => 'ASC',
							'sponsor' => $term->name,
							'core' => $core,
							'date_query' => $date_args
						);
						$q = new WP_Query( $args );					
						if ( $q->have_posts() ) {
							while ( $q->have_posts() ) {
								$q->the_post();
								global $post;						
								//Get sponsors of this project
								$sponsors = '';
								$terms = get_the_terms( $post->ID, 'sponsor' );
								foreach($terms as $term){
									//Children only since we already have the top level in a row, joined by comma as sponsor list
									if($term->parent != 0) {
										$sponsors[] = $term->name;
										$t_id = $term->term_id;
										$term_meta = get_option( "taxonomy_$t_id" );
										$ao_code = $term_meta['cfar_project_administering_organization_code'];
									}
								}
								if($sponsors){$sponsor_list = join( ", ", $sponsors );}
								//Get Pi's for this project, first pi in array - pis[0] should be principal
								$pis = get_users( array(
									'connected_type' => 'projects_to_pis',
									'connected_items' => $post,
								) );
								//Other pis are coinvestigators
								$coinvestigators = '';
								$investigators = '';
								foreach($pis as $pi) {
									$coinvestigator_info = '';
									$investigator_info = '';
									$role = p2p_get_meta( $pi->p2p_id, 'role', true );
									if($role == 'Collaborator') {
										$coinvestigator_info = get_userdata($pi->ID);
										$coinvestigators .= $coinvestigator_info->last_name .  ', ' . $coinvestigator_info->first_name . ' (' .$coinvestigator_info->organization. ')<br>';
									} else {
										$investigator_info = get_userdata($pi->ID);
										$investigators .= $investigator_info->last_name .  ', ' . $investigator_info->first_name . ' (' .$investigator_info->organization. ')<br>';									
									}
								}
								//Get activity code, serial name for "award supported" field
								$activity_codes = wp_get_post_terms( $post->ID, 'activity_code', array("fields" => "names") );
								$serial_number = get_post_meta($post->ID, 'cfar_projects_serial_number', true);
								if(!empty($activity_codes) && isset($serial_number)) {
									$project_number = '<u>'.$activity_codes[0].' '.$ao_code.$serial_number.'</u>';
								} else {
									$project_number = '<u>'.get_post_meta($post->ID, 'cfar_projects_grant_number', true).'</u>';										
								}
								$irb = get_post_meta($post->ID, 'cfar_projects_irb_number', true);
								if($irb){$irb = "[$irb]";}
								$pubs = get_post_meta($post->ID, 'cfar_projects_publications_presentations', true);
								if($pubs){$pubs = "[$pubs]";};
								$effort = get_post_meta($post->ID, 'cfar_projects_percent_core_effort', true);
								/**
								*  Okay! Let's get those service requests connected to this project!
								*/
								$ticket_meta_list = '';
								$meta = '';
								$core_services_list = '';
								$core_services = array();
								$services = array();
								$tickets = get_posts( array(
									'connected_type' => 'tickets_to_projects',
									'date_query' => $date_args,
									'status' => 'wpas-close',
									'connected_items' => $post
								) );
								if(!empty($tickets)){
										foreach($tickets as $ticket){
											$services = get_the_terms($ticket->ID, 'service');
											if (is_array($services)) {
												foreach($services as $service) {
													$service_name = $service->name;
													if (!in_array($service_name, $core_services)) {
														$core_services[] = $service_name;
													}
												}
											}											
										}
								$core_services = array_filter($core_services);		
								}
								if(!empty($core_services)){$core_services_list = join(", ", $core_services);}
								/*
								if(!empty($tickets)) {
									$ticket_meta_list = '';
									$meta = '';
									foreach($tickets as $ticket) {
										$statuses = '';
										$t_cores = '';
										$t_cores = get_the_terms( $ticket->ID, 'core' );
										$t_core = array_pop($t_cores);
										$statuses = get_the_terms($ticket->ID, 'status');
										if(!empty($statuses)) {
											$status = array_pop($statuses);
											if($status->slug == 'wpas-close') {
												if($t_core->slug == 'clinical'){
													$meta[] = get_post_meta($ticket->ID, 'cfar_clinical_access_uchcc', true);
													$meta[] = get_post_meta($ticket->ID, 'cfar_clinical_study_coordination', true);
													$meta[] = get_post_meta($ticket->ID, 'cfar_clinical_other_services', true);
												}
												if($t_core->slug == 'clinical-pharmacology'){
													$meta[] = get_post_meta($ticket->ID, 'cfar_cpharm_services_required', true);
													$meta[] = get_post_meta($ticket->ID, 'cfar_cpharm_drugs_text', true);
													$meta[] = get_post_meta($ticket->ID, 'cfar_cpharm_sample_numbers_text', true);
												}
											}
										}
									}
									if($meta != ''){$ticket_meta_list = join("; ", $meta);}
								}*/
								//content of project post
								$grant = get_post_meta($post->ID, 'cfar_projects_grant_title', true);
								if($grant != '') {
									$title = $grant;
								} else {
									$title = $post->post_title;
								}
								$content = get_the_content();
								$html .= '<tr>';
								$html .= '<td>'.$sponsor_list.'</td><td>'.$investigators. '<br><br><em>'.$coinvestigators.'</em></td><td>'.$project_number.'</td><td>'.$core_services_list.'<td>' . $title . '<br><br>'.$content.'<br><br>'.$irb.'<br><br>'.$pubs.'</td><td>'.$effort.'</td>';
								$html .= '</tr>';
							}
						} else {
							//remove top level sponsor name and program from html
							$html = str_replace($name, null, $html);
							$html = str_replace($program, null, $html);
							//$html .=  '<tr><td>no projects found</td></tr>';
						}
						/* Restore original Post Data */
						wp_reset_postdata();
					}
				}
			}
			//==============================================================
			//==============================================================
			//==============================================================
			$html .= '</table><body>';
			$stylesheet = file_get_contents(CFARF_PATH . "css/style.css");
			include(CFARF_PATH . "lib/mpdf/mpdf.php");
			
			$mpdf=new mPDF(); 
			$mpdf->AddPage('L');
			$mpdf->SetHTMLHeaderByName('header');
			$mpdf->WriteHTML($stylesheet,1);
			$mpdf->WriteHTML($html,2);
			$mpdf->Output();
			exit;
			
			//==============================================================
			//==============================================================
			//==============================================================	
}
?>
