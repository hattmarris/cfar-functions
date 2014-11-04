<?php
/**
*  File for User functions
*
*/

/**
*  Create new user roles, first without activation hook - then on activation for future use
*/
function cfar_add_roles_on_plugin_activation() {
	$result = add_role(
	    'principal_investigator',
	    __( 'Principal Investigator' ),
	    array(
	    	'level_0' => true,    
		'read'         => true,  // true allows this capability
		'view_ticket'   => true,
		'reply_ticket' => true, // false can be used to explicitly deny
	    )
	);
	if ( null !== $result ) {
	    echo 'Principal Investigator role created!';
	}
	else {
	    echo 'Oh... the principal_investigator role already exists.';
	}
	
	$result = add_role(
	    'basic_user',
	    __( 'Basic User' ),
	    array(
	    	'level_0' => true,    
		'read'         => true,  // true allows this capability
		'view_ticket'   => true,
		'reply_ticket' => true, // false can be used to explicitly deny
	    )
	);
	if ( null !== $result ) {
	    echo 'Basic User role created!';
	}
	else {
	    echo 'Oh... the Basic User role already exists.';
	}

	$result = add_role(
	    'staff',
	    __( 'Staff' ),
	    array(
	    	'level_0' => true,    
		'close_ticket'         => true,  // true allows this capability
		'delete_published_posts'   => true,
		'edit_published_posts' => true, // false can be used to explicitly deny
		'read' => true,
		'view_ticket' => true,
		'edit_ticket' => true,
		'reply_ticket' => true,
		'assign_ticket' => true,
		'create_ticket' => true,
		'delete_posts' => true,
		'edit_posts' => true,
		'publish_posts' => true,
		'upload_files' => true
	    )
	);
	if ( null !== $result ) {
	    echo 'Staff role created!';
	}
	else {
	    echo 'Oh... the Staff role already exists.';
	}
	
	$result = add_role(
	    'staff',
	    __( 'Staff' ),
	    array(
	    	'level_0' => true,
	    	'level_1' => true,    
	    	'level_2' => true,    	    	
		'close_ticket'         => true,  // true allows this capability
		'delete_published_posts'   => true,
		'edit_published_posts' => true, // false can be used to explicitly deny
		'read' => true,
		'view_ticket' => true,
		'edit_ticket' => true,
		'reply_ticket' => true,
		'assign_ticket' => true,
		'create_ticket' => true,
		'delete_posts' => true,
		'edit_posts' => true,
		'publish_posts' => true,
		'upload_files' => true
	    )
	);
	if ( null !== $result ) {
	    echo 'Staff role created!';
	}
	else {
	    echo 'Oh... the Staff role already exists.';
	}
	
		$result = add_role(
	    'core_administrator',
	    __( 'Core Administrator' ),
	    array(
	    	'level_0' => true,
	    	'level_1' => true,    
	    	'level_2' => true,
		'level_3' => true,    	    	
		'close_ticket'         => true,  // true allows this capability
		'delete_pages'         => true,
		'delete_private_pages'         => true,
		'delete_published_posts'   => true,
		'delete_ticket'         => true,
		'edit_published_posts' => true, // false can be used to explicitly deny
		'edit_pages' => true,
		'edit_private_pages' => true,
		'read' => true,
		'settings_tickets' => true,
		'unfiltered_html' => true,
		'delete_others_pages' => true,
		'delete_private_posts' => true,
		'edit_others_pages' => true,
		'edit_private_posts' => true,
		'manage_categories' => true,
		'manage_tickets' => true,
		'publish_pages' => true,
		'read_private_pages' => true,
		'view_ticket' => true,
		'edit_ticket' => true,
		'reply_ticket' => true,
		'delete_others_posts' => true,
		'delete_published_pages'   => true,
		'edit_others_posts' => true,
		'edit_others_pages' => true,
		'edit_posts' => true,
		'assign_ticket' => true,
		'create_ticket' => true,
		'delete_posts' => true,
		'edit_posts' => true,
		'publish_posts' => true,
		'upload_files' => true,
		'manage_links' => true,
		'moderate_comments' => true,
		'read_private_posts' => true,
		'ticket_taxonomy' => true,		
	    )
	);
	if ( null !== $result ) {
	    echo 'Core Administrator role created!';
	}
	else {
	    echo 'Oh... the Core Administrator role already exists.';
	}
}
function cfar_remove_roles_on_plugin_deactivation() {
	remove_role( 'principal_investigator' );
	remove_role( 'basic_user' );
	remove_role( 'staff' );
	remove_role( 'core_administrator' );
}
register_activation_hook( __FILE__, 'cfar_add_roles_on_plugin_activation' );
register_deactivation_hook(__FILE__, 'cfar_remove_roles_on_plugin_deactivation');

/* OLD METHOD - to change value of wp_user_roles option in wp_options table
//Changes the labels ($display_names) for the roles created by the WP Awesome Support Plugin
$val = get_option( 'wp_user_roles' );
$val['wpas_manager']['name'] = 'WPAS Manager';
update_option( 'wp_user_roles', $val );
$val['wpas_support_manager']['name'] = 'WPAS Support Manager';
update_option( 'wp_user_roles', $val );
$val['wpas_agent']['name'] = 'WPAS Agent';
update_option( 'wp_user_roles', $val );
//Change the rather useless "Subscriber" role to be labeled "Basic User"
$val['subscriber']['name'] = 'Subscriber';
update_option( 'wp_user_roles', $val );
*/
/**
*  Remove User Roles added by WPAS and other plugins
*/
add_action( 'init', 'cfar_remove_wpas_user_roles');
function cfar_remove_wpas_user_roles() {
	remove_role('wpas_agent');
	remove_role('wpas_manager');
	remove_role('wpas_support_manager');
}

//Begin customizing the Profile Page
add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );
function my_show_extra_profile_fields( $user ) { 
		if( !current_user_can( 'administrator' ) )
			return;
	$val 	= get_user_meta( $user->ID, 'cfar_core', true );
	$groups = get_terms( 'core', array('hide_empty' => 0) );
	?>

	<h3>CFAR Profile Information</h3>

	<table class="form-table">
		<tr>
			<th>
				<label for="cfar_core"><?php _e('User\'s Core', 'cfar'); ?></label>
			</th>
			<td>
				<select name="cfar_core" id="cfar_core">
					<option value="" <?php if( $val == '' ) { echo ' selected="selected"'; } ?> disabled="disabled"><?php _e('None', 'cfar'); ?></option>
					<?php
					foreach( $groups as $group => $vars ) {
						?><option value="<?php echo $vars->slug; ?>" <?php if( $val == $vars->slug ) echo ' selected="selected"'; ?>><?php echo $vars->name; ?></option><?php
					}
					?>
				</select>
			</td>
		</tr>		
		
		<tr>
			<th><label>Address</label></th>
			<td>
				<input type="text" name="address_street" id="address_street" value="<?php echo esc_attr( get_the_author_meta( 'address_street', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Street Address</span>
			</td>
		</tr>
		<tr>
		<th><label></label></th>
			<td>
				<input type="text" name="address_line_2" id="address_street" value="<?php echo esc_attr( get_the_author_meta( 'address_line_2', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Address Line 2</span>
			</td>
		</tr>
		<tr>
		<th><label></label></th>
			<td>
				<input type="text" name="address_city" id="address_city" value="<?php echo esc_attr( get_the_author_meta( 'address_city', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">City</span>
			</td>
			<td>
				<input type="text" name="address_state" id="address_state" value="<?php echo esc_attr( get_the_author_meta( 'address_state', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">State / Province / Region</span>
			</td>
		</tr>
		<tr>
		<th><label></label></th>
			<td>
				<input type="text" name="address_zip" id="address_zip" value="<?php echo esc_attr( get_the_author_meta( 'address_zip', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Zip Code</span>
			</td>
			<td>
				<input type="text" name="address_country" id="address_country" value="<?php echo esc_attr( get_the_author_meta( 'address_country', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Country</span>
			</td>
		</tr>
		
		<tr>
			<th><label for="hiv_interest">HIV Interest</label></th>

			<td>
				<input type="text" name="hiv_interest" id="hiv_interest" value="<?php echo esc_attr( get_the_author_meta( 'hiv_interest', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Describe your research interest in HIV/AIDs.</span>
			</td>
		</tr>
		<tr>
			<th><label for="position">Position</label></th>

			<td>
				<input type="text" name="position" id="position" value="<?php echo esc_attr( get_the_author_meta( 'position', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Research Position.</span>
			</td>
		</tr>
		<tr>
			<th><label for="organization">Organization</label></th>

			<td>
				<input type="text" name="organization" id="organization" value="<?php echo esc_attr( get_the_author_meta( 'organization', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Organization.</span>
			</td>
		</tr>
		<tr>
			<th><label for="cb_number">CB Number</label></th>

			<td>
				<input type="text" name="cb_number" id="cb_number" value="<?php echo esc_attr( get_the_author_meta( 'cb_number', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Campus Box Number.</span>
			</td>
		</tr>
		<tr>
			<th><label for="phone">Phone Number</label></th>

			<td>
				<input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'phone', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">User Phone Number.</span>
			</td>
		</tr>
		<tr>
			<th><label for="fax">Fax Number</label></th>

			<td>
				<input type="text" name="fax" id="fax" value="<?php echo esc_attr( get_the_author_meta( 'fax', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">User Fax Number.</span>
			</td>
		</tr>
		<tr>
			<th><label for="previous_nih_pi">Have you been a PI on an NIH grant?</label></th>

			<td>
				<?php $val = esc_attr( get_the_author_meta( 'previous_nih_pi', $user->ID ) ); ?>
				<ul id="previous_nih_pi">
					<li>
						<input name="previous_nih_pi" type="radio" value="1" id="previous_nih_pi_1" <?php if ($val == '1') { echo ' checked="checked"';} ?>>
							<label for="previous_nih_pi_1">Yes, I was the PI on an R01 equivalent grant in HIV/AIDS (R01 equivalents  include R01, R23, R29, R37 and, after 2008, DP2)</label>
					</li>
					<li>
						<input name="previous_nih_pi" type="radio" value="2" id="previous_nih_pi_2" <?php if ($val == '2') { echo ' checked="checked"';} ?>>
							<label for="choice_13_1" id="label_13_1">Yes, I was the PI on an R01 equivalent grant, but never in HIV/AIDS Software Requirements Specification for UNC CFAR Page 6</label>
					</li>
					<li>
						<input name="previous_nih_pi" type="radio" value="3" id="previous_nih_pi_3" <?php if ($val == '3') { echo ' checked="checked"';} ?>>
							<label for="choice_13_2">Yes, but I am an NIH "New Investigators," An NIH definition that  encompasses individuals who have received funding as a PI directly from  NIH, but not yet at the R01 equivalent level.</label>
					</li>
					<li>
						<input name="previous_nih_pi" type="radio" value="4" id="previous_nih_pi_4" <?php if ($val == '4') { echo ' checked="checked"';} ?>>
							<label for="choice_13_3">No, I have not yet received direct funding from NIH* as PI or Co-PI funding  on any NIH grant mechanism - AIDS-research Pipeline</label>
					</li>
				</ul>
	
				<span class="description">Select the option which best describes your experience working with the NIH. </span>
			</td>
		</tr>
		<tr>
			<th><label for="previous_nih_funded">Have you been funded by the NIH?</label></th>

			<td>
				<select name="previous_nih_funded" id="previous_nih_funded">
					<?php $val = esc_attr( get_the_author_meta( 'previous_nih_funded', $user->ID ) ); ?>
					<option value="yes" <?php if ($val == 'yes') { echo ' selected="selected"';} ?> >yes</option>
					<option value="no" <?php if ($val == 'no') { echo ' selected="selected"';} ?> >no</option>
				</select>
				<span class="description">Select yes or no. </span>
			</td>
		</tr>
		<tr> 
		<th><label>Project List</label></th>
			<?php
			$posts = get_posts( array(
				  'connected_type' => 'projects_to_pis',
				  'connected_items' => $user->ID,
				  'suppress_filters' => false,
				  'nopaging' => true
				) );
			echo '<td><ul>';
			foreach($posts as $post){
				$permalink = get_edit_post_link( $post->ID );
				echo '<li><a href="'.$permalink.'">'.$post->post_title.'</a></li>';
			}
			echo '</ul></td>';
			?>
		</tr>

	</table>
<?php }
//Save Options when they are changed and user has permission to edit
add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );
function my_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_user_meta( $user_id, 'cfar_core', $_POST['cfar_core'] );
	update_user_meta( $user_id, 'address_street', $_POST['address_street'] );
	update_user_meta( $user_id, 'address_line_2', $_POST['address_line_2'] );
	update_user_meta( $user_id, 'address_city', $_POST['address_city'] );
	update_user_meta( $user_id, 'address_state', $_POST['address_state'] );
	update_user_meta( $user_id, 'address_zip', $_POST['address_zip'] );
	update_user_meta( $user_id, 'address_country', $_POST['address_country'] );
	update_user_meta( $user_id, 'hiv_interest', $_POST['hiv_interest'] );
	update_user_meta( $user_id, 'position', $_POST['position'] );
	update_user_meta( $user_id, 'organization', $_POST['organization'] );
	update_user_meta( $user_id, 'cb_number', $_POST['cb_number'] );
	update_user_meta( $user_id, 'phone', $_POST['phone'] );
	update_user_meta( $user_id, 'fax', $_POST['fax'] );
	update_user_meta( $user_id, 'previous_nih_pi', $_POST['previous_nih_pi'] );
	update_user_meta( $user_id, 'previous_nih_funded', $_POST['previous_nih_funded'] );
}
//Add Extra User Columns to All Users Screen
function add_extra_user_columns( $defaults ) {
    $defaults['mysite-usercolumn-core'] = __('Core', 'core');
    $defaults['mysite-usercolumn-phone'] = __('Phone', 'phone');
    //$defaults['mysite-usercolumn-address-full'] = __('Full Address', 'address_full');
    return $defaults;
}
function mysite_custom_column_company($value, $column_name, $id) {
   if( $column_name == 'mysite-usercolumn-phone' ) {
        return get_the_author_meta( 'phone', $id );
    } 
    elseif($column_name == 'mysite-usercolumn-core'){
    	    $slug = get_the_author_meta( 'cfar_core', $id );
    	    $core = get_term_by('slug', $slug, 'core');
    	    return $core->name;
    }
}
add_action('manage_users_custom_column', 'mysite_custom_column_company', 15, 3);
add_filter('manage_users_columns' , 'add_extra_user_columns', 15, 1);

/**
 * Remove Custom fields in user profile from WPAS
 *
 * We need to hook those function in a separate function
 * as user_can cannot be loaded too early. We have to call
 * this function on the init hook for it to work.
 */
add_action('init', 'cfar_removeUserCustomFields' );
function cfar_removeUserCustomFields() {
global $wpas;
	if( isset( $_GET['user_id'] ) && current_user_can( 'administrator' ) ) {

		remove_action( 'show_user_profile', array( $wpas, 'UserProfileFields' ) );
		remove_action( 'edit_user_profile', array( $wpas, 'UserProfileFields' ) );

	}

	remove_action( 'profile_personal_options', array( $wpas, 'UserProfileFields' ) );
	remove_action( 'personal_options_update', array( $wpas, 'SaveUserProfileFields' ) );
	remove_action( 'edit_user_profile_update', array( $wpas, 'SaveUserProfileFields' ) );

}
?>