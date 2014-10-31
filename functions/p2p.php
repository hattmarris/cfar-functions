<?php
function cfar_p2p_connection_types() {
	p2p_register_connection_type( array(
	    'name' => 'projects_to_pis',
	    'from' => 'projects',
	    'to' => 'user',
	    'fields' => array(
		'role' => array( 
		    'title' => 'Role',
		    'type' => 'select',
		    'values' => array( 'Investigator', 'Collaborator' )
		),
	    ),
	    'to_query_vars' => array( 'role' => 'wpas_support_manager' ),
	    'title' => array(
		    'from' => __( 'Principal Investigator(s)', 'cfar-functions' ),
		    'to' => __( 'Project', 'cfar-functions' )
		  ),
	) );
	p2p_register_connection_type( array(
	    'name' => 'tickets_to_pis',
	    'from' => 'tickets',
	    'to' => 'user',
	    'to_query_vars' => array( 'role' => 'wpas_support_manager' ),
	    'title' => array(
		    'from' => __( 'Principal Investigator', 'cfar-functions' ),
		    'to' => __( 'Ticket', 'cfar-functions' )
		  )
	) );
	p2p_register_connection_type( array(
	    'name' => 'tickets_to_projects',
	    'from' => 'tickets',
	    'to' => 'projects',
	    'title' => array(
		    'from' => __( 'Project', 'cfar-functions' ),
		    'to' => __( 'Tickets', 'cfar-functions' )
		  )
	) );
}
add_action( 'p2p_init', 'cfar_p2p_connection_types' );



?>