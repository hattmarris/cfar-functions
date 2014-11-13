<?php
remove_shortcode( 'tickets', 'wpas_list_tickets' );
add_shortcode( 'tickets', 'cfar_list_tickets' );
/**
 * List all the user's tickets
 */
function cfar_list_tickets( $content, $args = array() ) {

	/* Get the post object and the current user data */
	global $post, $current_user, $wpas_notification;

	ob_start();

	/* Define the default settings for the request */
	$def = array(
		'tickets' 			=> -1,
		'search_form' 		=> true,
		'pre_table_links' 	=> true
	);

	/* We make sure $args is an array to avoid errors */
	if( !is_array( $args ) )
		$args = array();

	/* Merge default settings with custom ones */
	$args 		  = array_merge($def, $args);

	/* Get the ticket list page ID */
	$tickets_page = wpas_get_option('ticket_list');

	/* Get the ticket submission page ID */
	$submit 	  = wpas_get_option('ticket_submit');

	/* Retrieve the privacy settings */
	$show 		  = wpas_get_option('ticket_can_read', 'author');

	/* We can deny tickets listing in particular cases */
	$deny 		  = false;
	?>

	<div class="wpas">
		<div class="wpas_clearfix">
			<div class="half">
				<div class="btn-group">
					<?php
					if( $args['pre_table_links'] ):

						if( $show != 'author' ): ?>

							<a href="<?php echo get_permalink($tickets_page); ?>"><?php _e('All Tickets', 'wpas'); ?></a>

							<?php if( is_user_logged_in() ):

								global $current_user; ?>
								<a href="<?php echo add_query_arg( 'user', $current_user->data->ID, get_permalink($tickets_page) ); ?>"><?php _e('My Tickets', 'wpas'); ?></a>

							<?php endif;

						endif;

					endif;
					?>
				</div>
			</div>
			<div class="half">

				<?php if( $args['search_form'] && ( is_user_logged_in() || 'public' == wpas_get_option( 'ticket_can_read', 'author' ) ) ): ?>

					<form id="wpas-searchform" class="pull-right" method="post" action="<?php echo get_permalink( $tickets_page ); ?>">
						<div class="form-group">
							<label for="wpas-searchinput" class="sr-only"><?php _e( 'Search Tickets', 'wpas' ); ?></label>
							<input type="search" class="form-control" name="srch" id="wpas-searchinput" placeholder="<?php _e( 'Search Tickets', 'wpas' ); ?>">
						</div>
					</form>

				<?php endif; ?>
			</div>
		</div>
			
		<?php
		$req = array(
			'orderby'			=> 'post_date',
			'order'				=> 'DESC',
			'post_type'			=> 'tickets',
			'post_status'		=> 'publish',
			'posts_per_page'	=> $args['tickets'],
		);

		/* If a user ID is provided we add it to the request */
		if( isset($_GET['user']) && is_numeric($_GET['user']) ):

			/* If the current logged in user is the one whose tickets are requested we procees */
			if( $current_user->data->ID == $_GET['user'] || current_user_can( 'edit_ticket' ) ) {
				$req['author'] = $_GET['user'];
			}

			/* If not we don't show the list */
			else {
				$deny = true;
			}

		else:
			switch( $show ):

				case 'author':
					if( is_user_logged_in() )
						$req['author'] = $current_user->data->ID;
				break;

				case 'public':

				break;

				case 'closed':
					$req['tax_query'] 	= array(
						array(
							'taxonomy'  => 'status',
							'field' 	=> 'slug',
							'terms' 	=> 'wpas-close'
						)
					);
				break;

				default:
					$req = array();
				break;

			endswitch;
		endif;

		if( isset($_POST['srch']) ) {
			$req['s'] = $_POST['srch'];
		}

		if( isset($_GET['tag']) ) {
			$req['tag'] = $_GET['tag'];
		}

		$tickets = get_posts( $req );

		if( ( $show == 'public' || $show == 'closed' ) && !$deny || is_user_logged_in() && !$deny ):
			if( !empty($tickets) ):
			?>
				<table id="wpas_ticketlist" class="table table-hover">
					<thead>
						<tr>
							<th><?php _e('Status', 'wpas'); ?></th>
							<th><?php _e('Title', 'wpas'); ?></th>
							<th><?php _e('Date', 'wpas'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach( $tickets as $ticket ):

							$status  = get_the_terms( $ticket->ID, 'status' );

							if( $status ) {

								foreach($status as $s) {

									$status = $s->slug;
									$lib 	= explode('-', $status);

								}

							}

							$states  = get_the_terms( $ticket->ID, 'state' );
							$tags 	 = array();
							$status_color = wpas_get_option('status_'.$lib[1].'_color');

							if( $status == 'wpas-open' && !empty( $states ) ) {

								foreach( $states as $sid => $state ) {
									$opts   = get_option('taxonomy_'.$state->term_id);

									/* Tag style */
									$style = 'style="';
									if( isset($opts['color']) ) {
										$style .= 'background-color:'.$opts['color'].';';
									}
									if( isset($opts['font_color']) ) {
										$style .= ' color:'.$opts['font_color'].';';
									}
									$style .= '"';

									$var = '<span';
									if( $style != ' style=""' ) { $var .= ' '.$style; }

									$var .= ' class="label label-default">'.$state->name.'</span>';
									$tags[] = $var;
								}

							} elseif( $status == 'wpas-open' && empty($states) || $status == 'wpas-close' ) {

								$label = array(
									'wpas-open'  => __( 'Open', 'wpas' ),
									'wpas-close' => __( 'Closed', 'wpas' )
								);

								$tags = array();
								$tags[] = '<span class="label label-default" style="background-color:' . $status_color . ';">' . $label[$status] . '</span>';
 
							}

							$tags = implode(' ', $tags);
							
							?>
						<tr>
							<td><?php echo $tags; ?></td>
							<td><a href="<?php echo get_permalink($ticket->ID); ?>"><?php echo esc_html( get_the_title( $ticket->ID ) ) ; ?></a></td>
							<td><time datetime="<?php echo str_replace( ' ', 'T', $ticket->post_date ); ?>Z"><?php printf(__('%s ago', 'wpas'), human_time_diff( get_the_time( 'U', $ticket->ID ), current_time( 'timestamp' ) )); ?></time></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else:

				$wpas_notification->notification( 'failure', __('There are no tickets yet.', 'wpas') );
			
			endif;

		/**
		 * If the user isn't logged-in we load the login form
		 */
		else:
			wpas_register_form( apply_filters( 'wpas_need_login', __( 'Please login or register to see the tickets.', 'wpas' ) ) );
		endif;

		if( $post->ID != $tickets_page && $show == 'public' ): ?><a href="<?php echo get_permalink($tickets_page); ?>" class="btn"><?php _e('View all tickets', 'wpas'); ?></a><?php endif;
		if( $post->ID != $submit && is_user_logged_in() ): ?><a href="<?php echo home_url(); ?>/submit-service-request" class="<?php echo wpas_get_option('buttons_class', 'btn btn-primary'); ?>"><?php _e('Submit Service Request', 'wpas'); ?></a><?php endif; ?>
	</div>

	<?php
	$sc = ob_get_contents();
	ob_end_clean();
	return $sc;
}