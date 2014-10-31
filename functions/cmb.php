<?php
//CMB File

// This is the taxnomy filter For only displaying Core Specific Metaboxes on the Core's ticket
function cfar_taxonomy_show_on_filter( $display, $meta_box ) {

    if ( 'taxonomy' !== $meta_box['show_on']['key'] )
        return $display;

    if( isset( $_GET['post'] ) ) $post_id = $_GET['post'];
    elseif( isset( $_POST['post_ID'] ) ) $post_id = $_POST['post_ID'];
    if( !isset( $post_id ) )
        return $display;

    foreach( $meta_box['show_on']['value'] as $taxonomy => $slugs ) {
        if( !is_array( $slugs ) )
            $slugs = array( $slugs );

        $display = false;           
        $terms = wp_get_object_terms( $post_id, $taxonomy );
        foreach( $terms as $term )
            if( in_array( $term->slug, $slugs ) )
                $display = true;
    }

    return $display;

}
add_filter( 'cmb_show_on', 'cfar_taxonomy_show_on_filter', 10, 2 );

/**
* Here we add the Service Request Metaboxes, with the show on filter based on which Core they are part of
*/
function cfar_service_request_metaboxes( $meta_boxes ) {
    $prefix = 'cfar_'; // Prefix for all fields
    $cpharm = $prefix . 'cpharm_';
    $biostat = $prefix . 'biostat_';
    $clinical = $prefix . 'clinical_';
    $develop = $prefix . 'developmental_';
    $social = $prefix . 'social_';
    $vim = $prefix . 'vim_'; 
       
    $meta_boxes['clinical_pharmacology_metabox'] = array(    
        'id' => 'clinical_pharmacolocy_fields_metabox',
        'title' => 'Clinical Pharmocology Specific Fields',
        'pages' => array('tickets'), // post type
        'show_on' => array( 
			'key' => 'taxonomy', 
			'value' => array( 
				'type' => 'clinical-pharmacology',  
			) 
		), //Show on from taxonomy filter above
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => 'Services You Require',
                //'desc' => 'field description (optional)',
                'id' => $cpharm . 'services_required',
                'type' => 'multicheck',
                'options' => array(
			'1' => 'Grant proposals, planning, and study design',
			'2' => 'Sample Analysis',
			'3' => 'Bioanalytical methods development',
			'4' => 'Pharmacokinetic/dynamic analyses',
			'5' => 'Data interpretation, presentation/publication support',
			'6' => 'Other',			
		    )
            ),
            array(
		'name' => 'Drugs:',
		//'desc' => 'field description (optional)',
		//'default' => 'standard value (optional)',
		'id' => $cpharm . 'drugs_text',
		'type' => 'text_medium'
	    ),
            array(
		'name' => 'Approximate sample numbers:',
		//'desc' => 'field description (optional)',
		//'default' => 'standard value (optional)',
		'id' => $cpharm . 'sample_numbers_text',
		'type' => 'text_medium'
	    ),
            array(
		'name' => 'Biological matrices to be analyzed:',
		//'desc' => 'field description (optional)',
		//'default' => 'standard value (optional)',
		'id' => $cpharm . 'biological_matrices_text',
		'type' => 'text_medium'
	    ),
            array(
		'name' => 'Analytes of interest and the biological matrices:',
		//'desc' => 'field description (optional)',
		//'default' => 'standard value (optional)',
		'id' => $cpharm . 'analytes_text',
		'type' => 'text'
	    ),
	    array(
		'name' => 'Other:',
		//'desc' => 'field description (optional)',
		//'default' => 'standard value (optional)',
		'id' => $cpharm . 'other_services_text',
		'type' => 'text'
	    ),	    
        ),
    );

    $meta_boxes['biostatistics_metabox'] = array(    
        'id' => 'biostatistics_fields_metabox',
        'title' => 'Biostatistics Specific Fields',
        'pages' => array('tickets'), // post type
        'show_on' => array( 
			'key' => 'taxonomy', 
			'value' => array( 
				'type' => 'biostatistics',  
			) 
		), //Show on from taxonomy filter above
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
	        'name'    => 'Letter of Support',
	        'id'      => $biostat . 'support_letter',
	        'type'    => 'radio',
	        'options' => array(
		    'yes' => __( 'Yes', 'cmb' ),
		    'no'   => __( 'No', 'cmb' ),
	        ),	        
	    ),
	    array(
                'name' => 'Grant Preparation / Study Design',
                'id' => $biostat . 'grant_preparation',
                'type' => 'multicheck',
                'options' => array(
			'1' => 'Sample Size Power Calculation',
			'2' => 'Statistical Analysis Plan',
			'3' => 'Data Management',
			'4' => 'Other',
		    ),
            ),
            array(
		'name' => 'Other:',
		'id' => $biostat . 'grant_preparation_other',
		'type' => 'text'
	    ),            
	    array(
                'name' => 'Analysis / Manuscript Preparation',
                'id' => $biostat . 'manuscript_preparation',
                'type' => 'multicheck',
                'options' => array(
			'1' => 'Statistical Analysis',
			'2' => 'Manuscript Preparation',
			'3' => 'Data Management',
			'4' => 'Other',
		    ),
            ),
            array(
		'name' => 'Other:',
		'id' => $biostat . 'manuscript_preparation_other',
		'type' => 'text'
	    ),
            array(
	        'name'    => 'Design Consultation',
	        'id'      => $biostat . 'design_consultation',
	        'type'    => 'radio',
	        'options' => array(
		    'yes' => __( 'Yes', 'cmb' ),
		    'no'   => __( 'No', 'cmb' ),
	        ),	        
	    ),
        ),
    );
    
    $meta_boxes['clinical_metabox'] = array(    
        'id' => 'clinical_fields_metabox',
        'title' => 'Clinical Specific Fields',
        'pages' => array('tickets'), // post type
        'show_on' => array( 
			'key' => 'taxonomy', 
			'value' => array( 
				'type' => 'clinical',  
			) 
		), //Show on from taxonomy filter above
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
	    array(
		 'name'    => 'Access to UCHCC',
		 //'desc'    => 'Select an option',
		 'id'      => $clinical . 'access_uchcc',
		 'type'    => 'select',
		 'options' => array(
		 	'1' => __( 'Access to stored samples linked or unlinked to data from UCHCC', 'cmb' ),
			'2' => __( 'Assistance with project development', 'cmb' ),
			'3' => __( 'Collaboration with multi-center collaborative cohorts (CNICS and NA- ACCORD', 'cmb' ),
			'4' => __( 'Database coordination with other Cores and Centers', 'cmb' ),
			'5' => __( 'Observational clinical, social and behavioral research data', 'cmb' ),
			'6' => __( 'Preparation of datasets suitable for statistical analysis', 'cmb' ),
		 ),
		 'default' => 'custom',
	    ),
            array(
	        'name'    => 'Study Coordination Project Management',
	        'id'      => $clinical . 'study_coordination',
	        'type'    => 'radio',
	        'options' => array(
		    'recruitment-and-consent' => __( 'Subject identification, recruitment and consent', 'cmb' ),
		    'collection-of-samples'   => __( 'Subject Identification (HIV positive and HIV negative) and collection of samples', 'cmb' ),
	        ),	        
	    ),
	    array(
	        'name' => 'Other Services Requested',
	        'id' => $clinical . 'other_services',
	        'type' => 'textarea'
	    ),	    
        ),
    );
    
    $args = array(
	    'hide_empty'        => false,
	);
    $types = get_terms('type', $args);
    foreach($types as $type) {
    	    $cores[] = array(
			'name' => $type->name,
			'value' => $type->term_id,
		    );
    }
    
    $meta_boxes['developmental_metabox'] = array(    
        'id' => 'developmental_fields_metabox',
        'title' => 'Developmental Specific Fields',
        'pages' => array('tickets'), // post type
        'show_on' => array( 
			'key' => 'taxonomy', 
			'value' => array( 
				'type' => 'developmental',  
			) 
		), //Show on from taxonomy filter above
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
	    array(
	        'name' => 'Requesting Mentor',
	        'id' => $develop . 'requesting_mentor',
	        'type' => 'radio',
		'options' => array(
			'scientific' => __('Scientific', 'cmb'),
			'career' => __('Career', 'cmb'),
		),	        
	    ),
	    array(
	    	'name' => 'Mentor Name',
	    	'id' => $develop . 'mentor_name',
	    	'type' => 'text_medium',
	    ),
	    array(
		'name' => 'Mentor Email',
		'id'   => $develop . 'mentor_email',
		'type' => 'text_email',
            ),
	    array(
                'name' => 'Selected Cores',
                'desc' => 'If you anticipate utilizing one or more of the CFAR Cores in the course of this project, which Core(s) do you plan to use?',
                'id' => $develop . 'selected_cores',
                'type' => 'multicheck',
                'options' => $cores
            ),
	    array(
                'name' => 'Selected Working Groups',
                'desc' => 'If your project is associated with one or more CFAR Working Groups, which Working Group(s) are involved?',
                'id' => $develop . 'working_groups',
                'type' => 'multicheck',
                'options' => array(
                	'1' => 'The Scientific Working Group in Compartments and Latency',
                	'2' => 'The Scientific Working Group in Acute HIV Infection (AHI)',
                	'3' => 'The Scientific Working Group in HIV and the Criminal Justice System',
                	'4' => 'The Exploratory Working Group in Structural Determinants of HIV',
                	'5' => 'The Exploratory Working Group in Russian Injection Drug Users',
                ),
            ),	    
        ),
    );
    
    $meta_boxes['social_behavorial_science_metabox'] = array(    
        'id' => 'social_behavorial_science_fields_metabox',
        'title' => 'Social and Behavorial Science Specific Fields',
        'pages' => array('tickets'), // post type
        'show_on' => array( 
			'key' => 'taxonomy', 
			'value' => array( 
				'type' => 'social-behavorial-science',  
			) 
		), //Show on from taxonomy filter above
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => 'Research Proposals/Planning',
                'id' => $social . 'research_proposals',
                'type' => 'multicheck',
                'options' => array(
                	'1' => 'Grant proposal development',
                	'2' => 'Study design',
                	'3' => 'Sample size/Power calculation',
                	'4' => 'Data management plan',
                	'5' => 'Data analysis plan',
                	'6' => 'Preliminary data analysis',
                	'7' => 'Grant proposal review',
                	'8' => 'Letters of support'
                ),
            ),
            array(
            	'name' => 'Training',
                'id' => $social . 'training',
                'type' => 'multicheck',
                'options' => array(
                	'1' => 'Motivational Interviewing training',
                	'2' => 'Cognitive Interviewing',
                	'3' => 'Quantitative data collection methods training',
                	'4' => 'Qualitative data collection methods training',
                ),
            ),
            array(
            	'name' => 'Intervention Development and Evaluation',
                'id' => $social . 'intervention_development',
                'type' => 'multicheck',
                'options' => array(
                	'1' => 'Theoretical model development',
                	'2' => 'Intervention protocol development',
                	'3' => 'Process evaluation development',
                	'4' => 'Intervention content and materials development',
                	'5' => 'Other'
                ),
            ),
	    array(
	    	'name' => 'Other',
	    	'id' => $social . 'intervention_development_other',
	    	'type' => 'text_medium',
	    ),
            array(
            	'name' => 'Data collection and analysis',
                'id' => $social . 'data_collection_analysis',
                'type' => 'multicheck',
                'options' => array(
                	'1' => 'Qualitative Research',
                	'2' => 'Quantitative Research',
                ),
            ),
            array(
            	'name' => 'Qualitative Research',
                'id' => $social . 'qualitative_research',
                'type' => 'multicheck',
                'options' => array(
                	'1' => 'Focus group/Interview guide development',
                	'2' => 'Data collection',
                	'3' => 'Data coding or analysis',
                	'4' => 'Other',
                ),
            ),
	    array(
	    	'name' => 'Other',
	    	'id' => $social . 'qualitative_research_other',
	    	'type' => 'text_medium',
	    ),
            array(
            	'name' => 'Quantitative Research',
                'id' => $social . 'quantitative_research',
                'type' => 'multicheck',
                'options' => array(
                	'1' => 'Database creation',
                	'2' => 'Data management',
                	'3' => 'Data analysis',
                	'4' => 'Survey development',
                	'5' => 'Other',
                ),
            ),
	    array(
	    	'name' => 'Other',
	    	'id' => $social . 'quantitative_research_other',
	    	'type' => 'text_medium',
	    ),
            array(
            	'name' => 'Survey Development',
                'id' => $social . 'survey_development',
                'type' => 'multicheck',
                'options' => array(
                	'1' => 'Computer-Assisted Survey Programming',
                	'2' => 'Questionnaire Design',
                	'3' => 'Scale Development',
                ),
            ),
	    array(
	    	'name' => 'Manuscript Preparation',
	    	'id' => $social . 'manuscript_preparation',
	    	'type' => 'radio',
                'options' => array(
                	'yes' => 'Yes',
                	'no' => 'No',
                ),	    	
	    ),	    
        ),
    );
    
    $meta_boxes['virology_immunology_microbiology_metabox'] = array(    
        'id' => 'virology_immunology_microbiology_fields_metabox',
        'title' => 'Virology Immunology and Microbiology Specific Fields',
        'pages' => array('tickets'), // post type
        'show_on' => array( 
			'key' => 'taxonomy', 
			'value' => array( 
				'type' => 'virology-immunology-microbiology',  
			) 
		), //Show on from taxonomy filter above
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
	    array(
	        'name' => 'Services Requested',
	        'desc' => 'List the service(s) you would like to request from the VIM core',
	        'id' => $vim . 'services_requested',
	        'type' => 'textarea'
	    ),        	
        ),
    );
    
    return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'cfar_service_request_metaboxes' );

function cfar_projects_metaboxes( $meta_boxes ) {
    $prefix = 'cfar_projects_';
	
    $meta_boxes['project_details_metabox'] = array(    
        'id' => 'project_details_fields_metabox',
        'title' => 'Project Details Fields',
        'pages' => array('projects'), // post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
	    array(
	        'name' => 'Serial Number',
	        'desc' => 'Put the NIH serial number here if applicable. The administering organization code will be appended based on your sponsor selection.',
	        'id' => $prefix . 'serial_number',
	        'type' => 'text_small'
	    ),
	    array(
	        'name' => 'IRB Number',
	        'desc' => 'Enter IRB number here if applicable.',
	        'id' => $prefix . 'irb_number',
	        'type' => 'text_small'
	    ),
	    array(
	        'name' => 'Publications / Presentations',
	        'desc' => 'Enter last names of authors of publications or presentations if applicable.',
	        'id' => $prefix . 'publications_presentations',
	        'type' => 'text_medium'
	    ),
	    array(
	        'name' => '% Core Effort',
	        'desc' => 'Enter % Core Effort here if applicable for reporting purposes.',
	        'id' => $prefix . 'percent_core_effort',
	        'type' => 'text_small'
	    ),
        ),
    );
    
    return $meta_boxes;	
}
add_filter( 'cmb_meta_boxes', 'cfar_projects_metaboxes' );

add_action( 'add_meta_boxes', 'wpas_remove_details_widget');
/**
 * Remove the Ticket Details metabox
 */
function wpas_remove_details_widget() {
	remove_meta_box( 'wpas_ticket_details_mb', 'tickets', 'side' );
}

/**
 * Remove the Author metabox for now, since it is already displayed in Ticket Contact -> Requester field
 */
add_action( 'add_meta_boxes', 'cfar_remove_author_metabox');
function cfar_remove_author_metabox() {
	remove_meta_box( 'authordiv', 'tickets', 'normal' );
}

/**
 * Ticket details widget
 *
 * This widget will show basic information about the ticket
 * such as status, state, type and priority.
 * It will also include all custom taxonomies that have been
 * added through WP_Awesome_Support::addTaxonomy
 */

add_action( 'add_meta_boxes', 'cfar_wpas_add_details_widget' );
/**
 * Register the metabox
 */
function cfar_wpas_add_details_widget() {
	add_meta_box( 'cfar_wpas_ticket_details_mb', __('Ticket Details', 'wpas'), 'cfar_wpas_ticket_details_mb', 'tickets', 'side', 'high' );
}

/**
 * Get all taxonomies and add a dropdown for each
 */
function cfar_wpas_ticket_details_mb() {

	global $post, $wpas;

	$default 	= $wpas->getTaxonomies();
	$count 		= 1;
	?>
	<table class="form-table">
		<tbody>

			<tr valign="top">

				<td>
					<?php
					$count++;
					$currents = get_the_terms( $post->ID, 'status' );

					$curr_status = '';

					if( is_array($currents) ) {
						foreach($currents as $current) {
							$current = $current->slug;
						}
						$curr_status = $current;
					}
					?>
					<label for="status"><strong><?php _e('Status', 'wpas'); ?></strong></label>
					<select name="status" id="status" <?php if( $curr_status == 'open' && !current_user_can('close_ticket') || $curr_status == 'wpas-close' && !current_user_can('create_ticket') ) { echo 'disabled'; } ?> style="width:100%" required>
						<?php
						$statuses = get_terms( 'status', array('hide_empty' => 0) );
						$curr_status = wp_get_object_terms( $post->ID, 'status' );
						if( is_array($curr_status) && !empty($curr_status) ) {
							$curr_status = $curr_status[0]->slug;
						}
						foreach( $statuses as $status ) {
							?><option value="<?php echo $status->slug; ?>" <?php if( !is_array($curr_status) && $curr_status == $status->slug || !$curr_status && $status->slug == 'wpas-open' ) { echo 'selected="selected"'; } ?>><?php echo $status->name; ?></option><?php
						}
						?>
					</select>
				</td>

				<?php
				foreach( $default as $key => $taxonomy ) {

					$tax 		= sanitize_title( $taxonomy['id'] );
					$terms 		= get_terms( $tax, array( 'hide_empty' => 0 ) );
					$current 	= wp_get_object_terms( $post->ID, $tax );
					$single		= '';

					if( is_array( $current ) && !empty( $current ) )
						$single = $current[0]->slug;

					/* Open a new row */
					if( 1 == $count )
						echo '<tr valign="top">';

					?>
					<td>
						<label for="<?php echo $tax; ?>"><strong><?php echo ucwords( $tax ); ?></strong></label>
						<select name="<?php echo $tax; ?>" id="<?php echo $tax; ?>" style="width:100%" <?php if( $taxonomy['required'] ) echo 'required'; ?>><?php

					foreach( $terms as $term ) { ?>

						<option value="<?php echo $term->slug; ?>" <?php if( $single == $term->slug ) { echo 'selected="selected"'; } ?>><?php echo $term->name; ?></option>

					<?php }

					?></select></td><?php

					/* Open a new row */
					if( 2 == $count ) {
						echo '</tr>';
						$count = 0;
					}

					/* Increment the count for row management */
					$count++;

				}

				if( $count == ( 0 || 1 ) ) {
					echo '</tr>';
				}
				?>
				
				<script>					
					jQuery(function($) {
						function showSpecificDateInput() {
							value = $('#priority').val();
							if(value == 'specific-date'){
								$('#specific-date-input-row').css('display', 'inline-block');
							} else {
								$('#specific-date-input-row').css('display', 'none');
							}
						}							
						showSpecificDateInput();							
						$('#priority').change(function () {
							showSpecificDateInput();
						});
					});
				</script>
				
				<tr valign="top" id="specific-date-input-row" style="display:none;">
					<td style="max-width: 95px;">
						<label for="specific-date-input">
							<strong>
								Specific Date:
							</strong>
						</label>
						<input type="date" name="specific-date-input" id="specific-date-input" value="<?php echo get_post_meta($post->ID, 'cfar_specific_date', true);?>">
						</input>
					</td>
				</tr>
		</tbody>
	</table>
<?php } ?>
<?php 
	add_action( 'save_post', 'cfar_save_specific_date', 9, 2 );
	/* Save the meta box's post metadata. */
	function cfar_save_specific_date( $post_id, $post ){

		  /* Verify the nonce before proceeding. 
		  if ( !isset( $_POST['smashing_post_class_nonce'] ) || !wp_verify_nonce( $_POST['smashing_post_class_nonce'], basename( __FILE__ ) ) )
		    return $post_id;*/
		  
		  /* Get the post type object. */
		  $post_type = get_post_type_object( $post->post_type );
		
		  /* Check if the current user has permission to edit the post. */
		  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		    return $post_id;
		
		  /* Get the posted data and sanitize it for use. */
		  $new_meta_value = ( isset( $_POST['specific-date-input'] ) ? sanitize_html_class( $_POST['specific-date-input'] ) : '' );
		  
		  /* Get the meta key. */
		  $meta_key = 'cfar_specific_date';
		
		  /* Get the meta value of the custom field key. */
		  $meta_value = get_post_meta( $post_id, $meta_key, true );
		
		  /* If a new meta value was added and there was no previous value, add it. */
		  if ( $new_meta_value && '' == $meta_value )
		    add_post_meta( $post_id, $meta_key, $new_meta_value, true );
		
		  /* If the new meta value does not match the old value, update it. */
		  elseif ( $new_meta_value && $new_meta_value != $meta_value )
		    update_post_meta( $post->ID, $meta_key, $new_meta_value );
		
		  /* If there is no new meta value but an old value exists, delete it. */
		  elseif ( '' == $new_meta_value && $meta_value )
		    delete_post_meta( $post->ID, $meta_key, $meta_value ); 
	}
?>