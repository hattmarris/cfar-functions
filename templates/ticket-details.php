<?php
/**
 * This is a built-in template file. If you need to customize it, please,
 * DO NOT modify this file directly. Instead, copy it to your theme's directory
 * and then modify the code. If you modify this file directly, your changes
 * will be overwritten during next update of the plugin.
 */
 
/**
 * IMPORTANT: make the $post var global as it is used in this template
 */
global $post, $wpas_notification;

class CFAR_Ticket_Details extends WPAS_Ticket_Details {
	
		/**
	 * Get ticket type
	 *
	 * @return (string) Ticket type
	 */
	public function getCore() {

		$cores  = get_the_terms( $this->id, 'core' );

		if( $cores ) {

			foreach($cores as $c) {

				/**
				 * A ticket can only have one type, that's why we re-define $type at every iteration of the foreach instead of appending values
				 */
				$core = $c->name;

			}

			return $core;

		}

	}
	
	/**
	 * Manage the ticket's details header
	 * 
	 * @return (array) List of columns to display
	 */
	public function manageTicketColumns() {

		$columns = array(
			'status' 	=> array( 'title' => __( 'Status', 'wpas' ), 'callback' => $this->displayStatus() ),
			'id' 		=> array( 'title' => __( 'ID', 'wpas' ), 'callback' => $this->id ),
			'priority' 	=> array( 'title' => __( 'Priority', 'wpas' ), 'callback' => $this->getPriority() ),
			//'type' 		=> array( 'title' => __( 'Type', 'wpas' ), 'callback' => $this->getType() ), replaces type with core
			'core' 		=> array( 'title' => __( 'Core', 'cfar' ), 'callback' => $this->getCore() ),
		);

		if( $this->getTags() ) {
			$columns['tags'] = array( 'title' => __( 'Tags', 'wpas' ), 'callback' => $this->getTags() );
		}

		/**
		 * Return the filtered columns list so that the user can easily customize it
		 */
		return apply_filters( 'wpas_ticket_details_columns', $columns );

	}
}

/* Instanciate the class */
$details = new CFAR_Ticket_Details(); 
?>

<h1><?php echo esc_html( $post->post_title ); ?></h1>

<div class="wpas">

	<?php
	/**
	 * wpas_client_notices hook
	 *
	 * @wpas_notification
	 */
	do_action( 'wpas_client_notices' );

	/**
	 * Make sure a ticket has been requested (useless since version 2)
	 */
	if( !$details->id ):
		$wpas_notification->notification( 'not_found' );

	/**
	 * A ticket is requested and the user can view it. Let's go!
	 */
	elseif( $details->id && wpas_can_view_ticket( $details->id ) ): ?>

		<table id="ticket_details" class="table table-striped">

			<?php
			/**
			 * Display the table header containing the tickets details.
			 * By default, the header will contain ticket status, ID, priority, type and tags (if any).
			 */
			echo $details->constructTableHead();
			?>

		</table>

		<?php
		/**
		 * If the plugin is set to display replies in an ascendant order, we display the original ticket first.
		 */
		if( wpas_get_option('replies_order', 'ASC') == 'ASC' ): ?>

			

			<div class="well">

				<?php
				/**
				 * Get the original ticket template
				 */
				wpas_get_template_part( 'part', 'origin' ); ?>

			</div>

		<?php endif; ?>

			<?php
			/**
			 * If this ticket already has replies we display them here
			 */
			if( $details->getReplies() ):

				/**
				 * Prepare the class for styling rows based on the user level
				 */

				$classes = array(
					'administrator' => __('Administrator', 'wpas'),
					'wpas_agent'	=> __('Agent', 'wpas'),
					get_option( 'default_role' ) 	=> __('Client', 'wpas')
				); ?>

				<h3><?php _e( 'Replies', 'wpas' ); ?></h3>

				<div class="well">
					<table id="tickets_responses" class="table wpas-ticket-responses">

						<thead>
							<tr>
								<td width="20%"><?php _e('User', 'wpas'); ?></td>
								<td width="60%"><?php _e('Description', 'wpas'); ?></td>
								<td width="20%"><?php _e('Posted On', 'wpas'); ?></td>
							</tr>
						</thead>

						<tbody>

							<?php foreach( $details->getReplies() as $row ) {

								/**
								 * Ticket has been submitted by a member
								 */
								if( $row->post_author != 0 ) {

									$user_data 		= get_userdata( $row->post_author );
									$user_id 		= $user_data->data->ID;
									$user_name 		= $user_data->data->display_name;
									$user_avatar 	= get_avatar( $user_data->data->ID, '96', 'mm' );
									$role 			= $classes[$user_data->roles[0]];

								}

								/**
								 * Ticket has been anonymously submitted
								 */
								else {
									$user_name 		= __( 'Anonymous', 'wpas' );
									$user_avatar 	= get_avatar( 0, '96', 'mm' );
									$role 			= '';
								}

								$date 			= human_time_diff( get_the_time('U', $row->ID), current_time('timestamp') );
								$post_type 		= $row->post_type;
								?>

								<tr id="reply-<?php echo $row->ID; ?>" class="wpas_role wpas_<?php echo $user_data->roles[0]; ?> wpas-<?php echo $row->post_status; ?>">

									<?php
									/**
									 * If the reply has been deleted we only need minimal information
									 */
									if( 'trash' == $row->post_status ) { ?>

										<td colspan="3">
											<?php printf( __( 'This reply has been deleted %s ago.', 'wpas' ), $date ); ?>
										</td>
									
									<?php continue; } ?>

									<td class="tbl_col1">
										<div class="ticket_profile">

											<?php
											/**
											 * If Gravatar is enabled
											 */
											if( wpas_get_option('gravatar_on_front', 'yes') == 'yes' )
												echo $user_avatar; ?>

											<div>
												<?php
												/**
												 * Display contact's username
												 */
												?><span class="wpas-profilename"><?php echo $user_name; ?></span> 

												<span class="wpas-profiletype"><?php echo $role; ?></span> 

												<time class="visible-xs wpas-timestamp" datetime="<?php echo str_replace( ' ', 'T', $row->post_date ); ?>Z"><?php printf(__('%s ago', 'wpas'), $date); ?></time>

												<?php
												/**
												 * Display time under the user avatar
												 */
												if( wpas_get_option('date_position', 'right_side') == 'under_avatar' ): ?>
													<time class="wpas-timestamp" datetime="<?php echo str_replace( ' ', 'T', $row->post_date ); ?>Z"><?php printf(__('%s ago', 'wpas'), $date); ?></time>
												<?php endif; ?>

											</div>	
										</div>
									</td>

									<td class="tbl_col2" <?php if( wpas_get_option('date_position', 'right_side') == 'under_avatar' ): ?>colspan="2"<?php endif; ?>>

										<?php
										/**
										 * Show the reply and apply the formatting function on the content
										 */
										echo wpautop( wp_kses_post( $row->post_content ) );

										/**
										 * Check if files are attached and display them if needed
										 */
										if( ( $attachments = get_post_meta( $row->ID, WPAS_PREFIX.'attachments', true ) ) != '' && is_array( $attachments ) ) {

											echo '<div class="attachments"><strong><span aria-hidden="true" class="glyphicon glyphicon-paperclip"></span> ' . __('Attached files', 'wpas') . ':</strong><ul>';

											wpas_get_uploaded_files( $row->ID );

											echo '</ul></div>';
										} ?>
									</td>

									<?php
									/**
									 * Finally display the date on the right
									 */
									if( wpas_get_option('date_position', 'right_side') == 'right_side' ): ?>
										<td class="tbl_col3">
											<time class="wpas-timestamp" datetime="<?php echo str_replace( ' ', 'T', $row->post_date ); ?>Z"><?php printf(__('%s ago', 'wpas'), $date); ?></time>
										</td>
									<?php endif; ?>
								</tr>
							<?php } ?>

						</tbody>
					</table>
				</div>
			
			<?php
			endif; ?>

		<?php
		/**
		 * If the ticket has been closed we inform the user
		 */
		if( $details->getStatus() == 'close' ): ?>

			<div id="wpas_ticket_closed" class="well">
				<h4>&times; <?php _e('This ticket is closed.', 'wpas'); ?></h4>
			</div>

		<?php
		/**
		 * If the user is authorized to view ticket we give him control
		 */
		elseif( 'open' == $details->getStatus() && wpas_can_reply_ticket( $post->ID ) ): ?>

			<h3><?php _e( 'Write a reply', 'wpas' ); ?></h3>

			<div class="well">

				<form id="wpas-new-reply" method="post" action="<?php echo get_permalink( $post->ID ); ?>" enctype="multipart/form-data">					
					<div class="wysiwyg_textarea form-group">
						<textarea class="wpas_textarea visible-xs form-control" id="wpas-reply-mobile" name="user_reply_mobile" rows="6"></textarea>
						<div class="wpas_wysiwyg hidden-xs">

							<?php
							/**
							 * Load the visual editor if enabled
							 */
							if( 'yes' == wpas_get_option( 'frontend_wysiwyg_editor' ) && 'disable' != wpas_get_option( 'plugin_style' ) ) { ?>

								<textarea class="form-control wpas-wysiwyg" id="wpas-reply-wysiwyg" name="user_reply" rows="10"></textarea>

							<?php
							/**
							 * Otherwise just load a textarea
							 */
							} else { ?>
								<label for="reply-textarea" class="sr-only"></label>
								<textarea class="form-control" rows="10" name="user_reply" rows="6" id="wpas-reply-textarea"></textarea>
							<?php } ?>

						</div>
					</div>

					<?php
					/**
					 * Check if the user can attach files and load the uploader if so
					 */
					if( wpas_can_attach_files() ):

						wpas_file_uploader();

					endif; ?>

					<div class="checkbox">
						<label for="close_ticket" data-toggle="tooltip" data-placement="right" title="" data-original-title="No reply is required to close">
							<input type="checkbox" name="close_ticket" id="close_ticket" value="true"> <?php _e( 'Close this ticket', 'wpas' ); ?>
						</label>
					</div>

					<?php wp_nonce_field( 'send_reply', 'client_reply', false, true ); ?>
					<input type="hidden" name="ticket" value="<?php echo $post->ID; ?>" />
					<button type="submit" class="<?php echo wpas_get_option('buttons_class', 'btn btn-primary'); ?>"><?php _e('Reply', 'wpas'); ?></button>
				</form>

			</div>

		<?php
		/**
		 * This case is an agent viewing the ticket from the front-end. All actions are tracked in the back-end only, that's why we prevent agents from replying through the front-end.
		 */
		elseif( 'open' == $details->getStatus() && !wpas_can_reply_ticket( $post->ID ) && current_user_can( 'reply_ticket' ) ):

			$wpas_notification->notification( 'info', sprintf( __( 'To reply to this ticket, please <a href="%s">go to your admin panel</a>.', 'wpas' ), add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ) ) );

		endif; ?>

		<?php
		/**
		 * If the plugin is set to display replies in an ascendant order, we display the original ticket first.
		 */
		if( wpas_get_option('replies_order', 'ASC') == 'DESC' ): ?>

			<div><h3><?php _e( 'Ticket', 'wpas' ); ?></h3></div>

			<?php
			/**
			 * Get the original ticket template
			 */
			$details->getTemplate( 'part', 'origin' );

		endif;

	elseif( is_user_logged_in() && !wpas_can_view_ticket( $details->id ) ):

		$wpas_notification->notification( 'failure', __( 'You are not allowed to view this ticket.', 'wpas' ) );

	/**
	 * A ticket has been requested but the user is not authorized to view it. We ask for a login.
	 */
	else:

		wpas_register_form( apply_filters( 'wpas_need_login', __( 'You need to be logged-in to view this ticket. Please log-in now or create a new account.', 'wpas' ) ) );

	endif; ?>

</div>