<?php

/* Creates Custom Awards Post Type */
//add_action('init','add_awards_custom_post_type');
/*function add_awards_custom_post_type() {
	$labels = array(
			'name'               => _x( 'Awards', 'post type general name', 'cfar-functions' ),
			'singular_name'      => _x( 'Award', 'post type singular name', 'cfar-functions' ),
			'menu_name'          => _x( 'Awards', 'admin menu', 'cfar-functions' ),
			'name_admin_bar'     => _x( 'Award', 'add new on admin bar', 'cfar-functions' ),
			'add_new'            => _x( 'Add New', 'award', 'cfar-functions' ),
			'add_new_item'       => __( 'Add New Award', 'cfar-functions' ),
			'new_item'           => __( 'New Award', 'cfar-functions' ),
			'edit_item'          => __( 'Edit Award', 'cfar-functions' ),
			'view_item'          => __( 'View Award', 'cfar-functions' ),
			'all_items'          => __( 'All Awards', 'cfar-functions' ),
			'search_items'       => __( 'Search Awards', 'cfar-functions' ),
			'parent_item_colon'  => __( 'Parent Awards:', 'cfar-functions' ),
			'not_found'          => __( 'No awards found.', 'cfar-functions' ),
			'not_found_in_trash' => __( 'No awards found in Trash.', 'cfar-functions' )
		);
	$args = array(
			'labels' 	     => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'can_export'	     => true,
			'rewrite'            => array( 'slug' => 'award' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'	     => '',
			'supports'           => array( 'title', 'editor', /*'author', 'thumbnail', 'excerpt', 'comments' )
		);
	//register_post_type( 'awards', $args );
}*/

/**
* Removing the Types Taxonomy in favor of Shared Cores Functionality
*/

add_action( 'init', 'cfar_unregister_wpas_types_taxonomy', 9999 );
function cfar_unregister_wpas_types_taxonomy()
{
    global $wp_taxonomies;
    $taxonomy = 'type';
    if ( taxonomy_exists($taxonomy) )
        unset( $wp_taxonomies[$taxonomy] );
}

/**
* Adding Shared Cores Taxonomy for tickets and projects
*/
add_action('init', 'cfar_add_core_tickets_projects_taxonomy', 1);
if ( !function_exists('cfar_add_core_tickets_projects_taxonomy') ) {
	function cfar_add_core_tickets_projects_taxonomy() {
			/******* Ticket / Project Core Post Taxonomy *******/
			// Add new taxonomy, make it hierarchical 
			$labels = array(
				'name'              => _x( 'Cores', 'taxonomy general name' ),
				'singular_name'     => _x( 'Core', 'taxonomy singular name' ),
				'search_items'      => __( 'Search Cores' ),
				'all_items'         => __( 'All Cores' ),
				'parent_item'       => __( 'Parent Core' ),
				'parent_item_colon' => __( 'Parent Core:' ),
				'edit_item'         => __( 'Edit Core' ),
				'update_item'       => __( 'Update Core' ),
				'add_new_item'      => __( 'Add New Core' ),
				'new_item_name'     => __( 'New Core Name' ),
				'menu_name'         => __( 'Cores' ),
			);
			
			$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'core' ),
		);
		
		register_taxonomy( 'core', array ('tickets', 'projects'), $args );
		
		register_taxonomy_for_object_type( 'core', 'tickets' );	
		register_taxonomy_for_object_type( 'core', 'projects' );	
	}
}


/** 
* Creates Custom Projects Post Type 
*/
add_action('init','add_projects_custom_post_type');
function add_projects_custom_post_type() {
	$labels = array(
			'name'               => _x( 'Projects', 'post type general name', 'cfar-functions' ),
			'singular_name'      => _x( 'Project', 'post type singular name', 'cfar-functions' ),
			'menu_name'          => _x( 'Projects', 'admin menu', 'cfar-functions' ),
			'name_admin_bar'     => _x( 'Project', 'add new on admin bar', 'cfar-functions' ),
			'add_new'            => _x( 'Add New', 'project', 'cfar-functions' ),
			'add_new_item'       => __( 'Add New Project', 'cfar-functions' ),
			'new_item'           => __( 'New Project', 'cfar-functions' ),
			'edit_item'          => __( 'Edit Project', 'cfar-functions' ),
			'view_item'          => __( 'View Project', 'cfar-functions' ),
			'all_items'          => __( 'All Projects', 'cfar-functions' ),
			'search_items'       => __( 'Search Projects', 'cfar-functions' ),
			'parent_item_colon'  => __( 'Parent Projects:', 'cfar-functions' ),
			'not_found'          => __( 'No projects found.', 'cfar-functions' ),
			'not_found_in_trash' => __( 'No projects found in Trash.', 'cfar-functions' )
		);
	$args = array(
			'labels' 	     => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'can_export'	     => true,
			'rewrite'            => array( 'slug' => 'project' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'	     => '',
			'supports'           => array( 'title', 'editor', /*'author', 'thumbnail', 'excerpt', 'comments'*/ )
		);
	register_post_type( 'projects', $args );
}

/**
* Creates Custom Projects Sponsor Taxonomy 
*/ 
add_action( 'init', 'create_sponsor_taxonomy' );

if ( !function_exists('create_sponsor_taxonomy') ) {

	function create_sponsor_taxonomy() {
		/******* Project Sponsor Post Taxonomy *******/
		// Add new taxonomy, make it hierarchical 
		$labels = array(
			'name'              => _x( 'Sponsors', 'taxonomy general name' ),
			'singular_name'     => _x( 'Sponsor', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Sponsors' ),
			'all_items'         => __( 'All Sponsors' ),
			'parent_item'       => __( 'Parent Sponsor' ),
			'parent_item_colon' => __( 'Parent Sponsor:' ),
			'edit_item'         => __( 'Edit Sponsor' ),
			'update_item'       => __( 'Update Sponsor' ),
			'add_new_item'      => __( 'Add New Sponsor' ),
			'new_item_name'     => __( 'New Sponsor Name' ),
			'menu_name'         => __( 'Sponsors' ),
		);
		
		$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'sponsor' ),
	);
	
	register_taxonomy( 'sponsor', array ('projects'), $args );
	
	register_taxonomy_for_object_type( 'sponsor', 'projects' );
	
	}
		
}

/**
* Creates Custom Project Activity Code Taxonomy 
*/ 
add_action( 'init', 'create_activity_code_taxonomy' );

if ( !function_exists('create_activity_code_taxonomy') ) {

	function create_activity_code_taxonomy() {
		/******* Project Activity Code Post Taxonomy *******/
		// Add new taxonomy, make it hierarchical 
		$labels = array(
			'name'              => _x( 'Activity Codes', 'taxonomy general name' ),
			'singular_name'     => _x( 'Activity Code', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Activity Codes' ),
			'all_items'         => __( 'All Activity Codes' ),
			'parent_item'       => __( 'Parent Activity Code' ),
			'parent_item_colon' => __( 'Parent Activity Code:' ),
			'edit_item'         => __( 'Edit Activity Code' ),
			'update_item'       => __( 'Update Activity Code' ),
			'add_new_item'      => __( 'Add New Activity Code' ),
			'new_item_name'     => __( 'New Activity Code Name' ),
			'menu_name'         => __( 'Activity Codes' ),
		);
		
		$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'activity_code' ),
	);
	
	register_taxonomy( 'activity_code', array ('projects'), $args );
	
	register_taxonomy_for_object_type( 'activity_code', 'projects' );
	
	}
		
}

/* Creates Menu icon*/
function cfar_cpt_menu_icon_styles(){
?>

	<style>
	#adminmenu .menu-icon-awards div.wp-menu-image:before {
	  content: "\f118";
	}
	#adminmenu .menu-icon-projects div.wp-menu-image:before {
	  content: "\f322";
	}
	</style>

<?php
}
add_action( 'admin_head', 'cfar_cpt_menu_icon_styles' );

?>