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
* This will create a menu item under tickets
*/
function cfar_add_tickets_export_menu(){
	$parent_slug = 'edit.php?post_type=tickets';
	$page_title = 'Export as Table 5 Report to CSV or PDF';
	$menu_title = 'Export';
	$capability = 'export';
	$menu_slug = 'export-tickets';
	$function = 'cfar_export_ticket_data';
	add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
}
// Hook for adding admin menus
add_action('admin_menu',  'cfar_add_tickets_export_menu');

function cfar_export_ticket_data() {
	require_once( CFARF_PATH . 'functions/export/export-tickets-admin.php' );
}

add_action('admin_init', 'cfar_export_master_function');
function cfar_export_master_function() {
	global $plugin_page;
	if (isset($_POST['submit']) && $plugin_page == 'export-tickets' ) {
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
				$html .= '<tr><td>'.$name.'</td></tr>';
				$children = get_term_children($top_level_sponsor->term_id, $taxonomy_name);
				foreach($children as $child) {
					$term = get_term_by( 'id', $child, $taxonomy_name );
					//var_dump($term->count);
					if($term->count != 0) {
						$html .= '<tr><td>'.$term->name.'</td></tr>';
						/**
						*  Row is added for the top level sponsor above, then the wuery is run based on projects associated with that sponsor
						*/				
						$args = array(
							'post_type' => 'projects',
							'order' => 'ASC',
							'sponsor' => $term->name
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
								$tickets = get_posts( array(
									'connected_type' => 'tickets_to_projects',
									'connected_items' => $post
								) );
								$ticket_meta_list = '';
								$meta = '';
								foreach($tickets as $ticket) {
									$statuses = '';
									$cores = '';
									$cores = get_the_terms( $ticket->ID, 'type' );
									if($cores){$core = array_pop($cores);}
									$statuses = get_the_terms($ticket->ID, 'status');
									if($statuses) {
										$status = array_pop($statuses);
										if($status->slug == 'wpas-close') {
											if($core->slug == 'clinical'){
												$meta[] = get_post_meta($ticket->ID, 'cfar_clinical_access_uchcc', true);
												$meta[] = get_post_meta($ticket->ID, 'cfar_clinical_study_coordination', true);
												$meta[] = get_post_meta($ticket->ID, 'cfar_clinical_other_services', true);
											}
										}
									}
								}
								if($meta){$ticket_meta_list = join("; ", $meta);}
								//content of project post
								$content = get_the_content();
								$html .= '<tr>';
								$html .= '<td>'.$sponsor_list.'</td><td>'.$investigators. '<br><br><em>'.$coinvestigators.'</em></td><td><u>'.$activity_codes[0].' '.$ao_code.$serial_number.'</u></td><td>'.$ticket_meta_list.'<td>' . $post->post_title . '<br><br>'.$content.'<br><br>'.$irb.'<br><br>'.$pubs.'</td><td>'.$effort.'</td>';
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
