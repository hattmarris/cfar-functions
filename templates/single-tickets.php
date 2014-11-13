<?php
/* Exit if accessed directly */
if( !defined( 'ABSPATH' ) )
	exit;

/* Get theme header */
get_header();

/* Get the post */
if( have_posts() ):

	while( have_posts() ):

		the_post();
		
		/**
		 * wpas_before_main_content hook
		 */
		do_action( 'wpas_before_main_content' );

		/**
		 * Load the ticket content including action hooks
		 */
		 include( 'ticket-details.php' );
		 
		 /**
		 * wpas_after_main_content hook
		 */
		 do_action( 'wpas_after_main_content' );
		 
	endwhile;

endif;

/* Get theme footer */
get_footer();
?>