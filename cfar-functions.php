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


// Process ajax Call from Service Request Form
function get_project_name_fn(){
    $projectPi = $_POST['projectPi'];
    $projects = get_posts( array(
		  'connected_type' => 'projects_to_pis',
		  'connected_items' => $projectPi,
		  'suppress_filters' => false,
		  'nopaging' => true
		) );
    $items = array();
    $items[] = array( "text" => __('Select project...','theme'), "value" => 'default' );
    foreach($projects as $project){
        $items[] = array( "text" => $project->post_title, "value" => $project->ID );
    }
    echo json_encode($items);
    die;
}
add_action('wp_ajax_get_project_name', 'get_project_name_fn');
add_action('wp_ajax_nopriv_get_project_name', 'get_project_name_fn');

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
		    	if($flag) { $flag = false; continue; } //because the first time while loop is entered - the flag is true so it skips code block (does not continue) and runs while loop again
			$num = count($data);
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
	   $log['error'][] = "Email: ".$pi_email." already in use. Check and fix row: " . $row . " of .csv file to upload user.";
	   cfar_print_log_messages($log);
	   return;       	       
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
	       wp_update_user( array ('ID' => $user_id, 'first_name'=> $full_name[0], 'last_name'=> $full_name[1], 'role'=> 'principal_investigator') ) ;
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

add_action('admin_init', 'cfar_export_master_function');
function cfar_export_master_function() {
	global $plugin_page;
	$core = $_POST['core'];
	$start_date = $_POST['start-date'];
	$end_date = $_POST['end-date'];
	if (isset($_POST['submit']) && $plugin_page == 'export-projects' ) {
		if($_POST['type'] == 'csv') {
			$args = array(
				'post_type' => 'tickets',
			);
			$q = new WP_Query( $args );
			if ( $q->have_posts() ) {
				while ( $q->have_posts() ) {
					$q->the_post();
					global $post;
					$user = get_user_by('id',$post->post_author);
					$title = get_the_title();
					$pis = get_users( array(
						'connected_type' => 'tickets_to_pis',
						'connected_items' => $post
					) );
					$i = 1;
					foreach($pis as $pi) {
						$pi_names .= $i++;
						$pi_names .= '. ' . $pi->display_name . ' ';
					}
					$projects = get_posts( array(
						'connected_type' => 'tickets_to_projects',
						'connected_items' => $post
					) );
					$i = 1;
					foreach($projects as $project) {
						$project_titles .= $i++;
						$project_titles .= '. ' . $project->post_title;
					}
					$sponsors = get_the_terms($projects[0]->ID, 'sponsor');
					foreach($sponsors as $sponsor) {
						if($sponsor->parent != 0){
							$subfunder .= $sponsor->name;
						} else {
							$funder .= $sponsor->name;
						}	
					}
					//in process building rows for csv export
					//echo '<li>' . $post->ID . ', ' . $post->post_date . ', ' . $pi_names . ', ' . $pis[0]->phone . ', ' . $pis[0]->user_email . ', ' . $pis[0]->organization . ', ' . $pis[0]->other_org . ', ' . $user->display_name . ', ' . $user->phone . ', ' . $user->user_email . ', ' . $project_titles . ', ' . $funder . ', ' . $subfunder . '</li>';
	
					$results[] = $pi->display_name;//$title; 
				}
			} else {
				echo  'no service request tickets found';
			}
			/* Restore original Post Data */
			wp_reset_postdata();
			foreach ($results as &$value)
			    {
				$value = str_replace("\r\n", "", $value);
				$value = "\"" . $value . "\"";
			    }
			$output .= join(',', $results)."\n";
			$size_in_bytes = strlen($output);
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition:  attachment; filename=export_data.csv; size=$size_in_bytes");
			$labels = 'id ,timestamp,pi_name,pi_phone,pi_email,pi_org,pi_other_org,user_name,user_phone,user_email,project_title,project_funding_source,project_funding_source_addendum,project_grant_title,project_grant_number,project_description ,project_irb_approval ,services,other_service,notes_core_service,notes_award_title,percent_effort'."\n";
			print $labels;
			print $output;
			exit;
		}
		/**
		* Beging PDF export Query block
		*/
		if($_POST['type'] == 'pdf') {
			$html = '<body style="font-family: arial, sans-serif; font-size: 10pt;">';
			/*$html .= '<table><htmlpageheader name="header">';
			$html .= '<h1>Table 5</h1>';
			$html .= '<table>';
			$html .= '<tr><td>APPENDIX F (TABLE 5) 2013-2014:  CORE C</td></tr>';
			$html .= '<tr>';
			$html .= '<th>Sponsor<br><br> <em>Program</em></th><th>Investigator<br>(site)<br><br> <em>Collaborators (site)</em></th><th>Award Supported</th><th>Core Service</th><th>Award Title<br><br><em>Description of Support/Supported Study</em><br><br>[Outcome Measure (IRB#, Grant Submitted/Awarded, Publications, Presentations)]</th><th>% Core Effort</th>';
			$html .= '</tr>';
			$html .= '</table>';
			$html .= '</htmlpageheader>
            <sethtmlpageheader name="header" page="O" value="on" show-this-page="1" />
            <sethtmlpageheader name="header" page="E" value="on" />';*/
			$html .= '<table>';
			$html .= '<tr><td>APPENDIX F (TABLE 5) 2013-2014:  CORE C</td></tr>';
			$html .= '<tr>';
			$html .= '<th>Sponsor<br><br> <em>Program</em></th><th>Investigator<br>(site)<br><br> <em>Collaborators (site)</em></th><th>Award Supported</th><th>Core Service</th><th>Award Title<br><br><em>Description of Support/Supported Study</em><br><br>[Outcome Measure (IRB#, Grant Submitted/Awarded, Publications, Presentations)]</th><th>% Core Effort</th>';
			$html .= '</tr>';
			/**
			*  Get Top level Sponsor Terms Which is How the Table 5 list of projects / grants is divided beyond core divisions - May need to Add Core as shared taxonomy for projects
			*/
			$taxonomy_name = 'sponsor';
			$a = array('parent' => 0, 'hide_empty' => true);
			$top_level_sponsors = get_terms($taxonomy_name, $a);
			foreach($top_level_sponsors as $top_level_sponsor){
				$name = $top_level_sponsor->name;
				$html .= '<tr><td><b>'.$name.'</b></td></tr>';
				$children = get_term_children($top_level_sponsor->term_id, $taxonomy_name);
				// NOT Checking for each unique child... 
				foreach($children as $child) {
					$term = get_term_by( 'id', $child, $taxonomy_name );
					if($term->count != 0) {
						$html .= '<tr><td><b>'.$term->name.'</b></td></tr>';
						/**
						*  Row is added for the top level sponsor above, then the query is run based on projects associated with that sponsor
						*  
						*  $core variable for querying projects only from a certain core, was set by $_POST['core']
						*  (If core is 'all' the query is set as null value)
						*
						*  $date_args are null if both start and end are not set
						*
						*/
						if($core == 'all'){$core = null;}
						if($start_date == '' && $end_date == '') {
							$date_args = null;
						} else {
							$date_args = array(
								array(
									'after'      => $start_date,
									'before'   => $end_date
								),
							);
						}
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
								$irb = get_post_meta($post->ID, 'cfar_projects_irb_number', true);
								$pubs = get_post_meta($post->ID, 'cfar_projects_publications_presentations', true);
								$effort = get_post_meta($post->ID, 'cfar_projects_percent_core_effort', true);
								/**
								*  Okay! Let's get those service requests connected to this project!
								*/
								$ticket_meta_list = '';
								$meta = '';
								$tickets = get_posts( array(
									'connected_type' => 'tickets_to_projects',
									'connected_items' => $post
								) );
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
								}
								//content of project post
								$content = get_the_content();
								$html .= '<tr>';
								$html .= '<td>'.$sponsor_list.'</td><td>'.$investigators. '<br><br><em>'.$coinvestigators.'</em></td><td><u>'.$activity_codes[0].' '.$ao_code.$serial_number.'</u></td><td>'.$ticket_meta_list.'<td>' . $post->post_title . '<br><br>'.$content.'<br><br>['.$irb.']<br><br>['.$pubs.']</td><td>'.$effort.'</td>';
								$html .= '</tr>';
							}
						} else {
							$html .=  '<tr><td>no projects found</td></tr>';
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
			include(CFARF_PATH . "lib/mpdf/mpdf.php");
			
			$mpdf=new mPDF(); 
			$mpdf->AddPage('L');
			$mpdf->SetHTMLHeaderByName('header');
			$mpdf->WriteHTML($html);
			$mpdf->Output();
			exit;
			
			//==============================================================
			//==============================================================
			//==============================================================
		}
	}
}
?>
