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
				'core' => 'clinical-pharmacology',  
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
				'core' => 'biostatistics',  
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
				'core' => 'clinical',  
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
    $terms = get_terms('core', $args);
    foreach($terms as $term) {
    	    $cores[] = array(
			'name' => $term->name,
			'value' => $term->slug,
		    );
    }
    
    $meta_boxes['developmental_metabox'] = array(    
        'id' => 'developmental_fields_metabox',
        'title' => 'Developmental Specific Fields',
        'pages' => array('tickets'), // post type
        'show_on' => array( 
			'key' => 'taxonomy', 
			'value' => array( 
				'core' => 'developmental',  
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
				'core' => 'social-behavioral-science',  
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
				'core' => 'virology-immunology-microbiology',  
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
    
    $args = array(
	'hide_empty'        => false,
	);
    $terms = get_terms('core', $args);
    foreach($terms as $term) {
    	    $cores[] = array(
			'name' => $term->name,
			'value' => $term->slug,
		    );
    }
    
    if( isset( $_GET['post'] ) ) $post_id = $_GET['post'];
    elseif( isset( $_POST['post_ID'] ) ) $post_id = $_POST['post_ID'];
    $terms = wp_get_object_terms( $post_id, 'core' );
    $default = $terms[0]->slug;
	
    $meta_boxes['project_details_metabox'] = array(    
        'id' => 'project_details_fields_metabox',
        'title' => 'Project Details Fields',
        'pages' => array('projects'), // post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
	        'name' => 'Core',
	        'desc' => 'CFAR Core',
	        'id' => $prefix . 'core',
	        'type' => 'select',
	        'options' => $cores,
	        'default' => $default
	    ),
	    array(
	        'name' => 'Grant Title',
	        'desc' => 'Put the project grant or award title here if it is different from the project title.',
	        'id' => $prefix . 'grant_title',
	        'type' => 'text'
	    ),
	    array(
	        'name' => 'Project / Grant Number',
	        'desc' => 'A general text field for numbers & codes associated with this project/grant.',
	        'id' => $prefix . 'grant_number',
	        'type' => 'text_medium'
	    ),
	    array(
	        'name' => 'Serial Number',
	        'desc' => 'Put the NIH serial number (# only) here if applicable. The administering organization code will be appended based on your sponsor selection for table 5 reporting.',
	        'id' => $prefix . 'serial_number',
	        'type' => 'text_small'
	    ),
	    array(
	        'name' => 'IRB Approval',
	        'desc' => 'Enter IRB approval status if applicable.',
	        'id' => $prefix . 'irb_approval',
	        'type' => 'select',
	        'options' => array(
	        		'Yes' => 'Yes',
	        		'Pending' => 'Pending',
	        		'N/A' => 'N/A'
	        	),
	        'default' => 'N/A'
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
 * Remove the wpas_ticket_attachments metabox for now, attachments seem distracting 
 */
add_action( 'add_meta_boxes', 'cfar_remove_wpas_ticket_attachments');
function cfar_remove_wpas_ticket_attachments() {
	remove_meta_box( 'wpas_ticket_attachments', 'tickets', 'normal' );
}

/**
 * Remove the customsidebars-mb metabox from plugin on CFAR staging and production environment - priority of 11 to override custom sidebars plugin
 */
add_action( 'add_meta_boxes', 'cfar_remove_customsidebars_mb', 11);
function cfar_remove_customsidebars_mb() {
	remove_meta_box( 'customsidebars-mb', 'tickets', 'side' );
	remove_meta_box( 'customsidebars-mb', 'projects', 'side' );
}

/**
 * Remove the default core taxonomy metabox so only one value can be selected
 */
add_action( 'add_meta_boxes', 'cfar_remove_cores_default_metabox');
function cfar_remove_cores_default_metabox() {
	remove_meta_box( 'corediv', 'tickets', 'side' );
	remove_meta_box( 'corediv', 'projects', 'side' );
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
				
				<td>
					<?php
					$count++;
					$currents = get_the_terms( $post->ID, 'core' );

					$curr_core = '';

					if( is_array($currents) ) {
						foreach($currents as $current) {
							$current = $current->slug;
						}
						$curr_core = $current;
					}
					// Use nonce for verification
					wp_nonce_field( plugin_basename( __FILE__ ), 'core_noncename' );
					?>
					<label for="core"><strong><?php _e('Core', 'cfar'); ?></strong></label>
					<select name="core" id="core" style="width:100%" required>
						<?php
						$cores = get_terms( 'core', array('hide_empty' => 0) );
						$curr_core = wp_get_object_terms( $post->ID, 'core' );
						if( is_array($curr_core) && !empty($curr_core) ) {
							$curr_core = $curr_core[0]->slug;
						}
						foreach( $cores as $core ) {
							?><option value="<?php echo $core->slug; ?>" <?php if( !is_array($curr_core) && $curr_core == $core->slug || !$curr_core && $core->slug == 'biostatistics' ) { echo 'selected="selected"'; } ?>><?php echo $core->name; ?></option><?php
						}
						?>
					</select>
				</td>

				<?php
				foreach( $default as $key => $taxonomy ) {
					if($taxonomy['id'] != 'type') {
						$tax 		= sanitize_title( $taxonomy['id'] );
						$terms 		= get_terms( $tax, array( 'hide_empty' => 0 ) );
						$current 	= wp_get_object_terms( $post->ID, $tax );
						$single		= '';
	
						if( is_array( $current ) && !empty( $current ) )
							$single = $current[0]->slug;
	
						/* Open a new row */
						if( 3 == $count )
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
						if( 4 == $count ) {
							echo '</tr>';
							$count = 0;
						}
	
						/* Increment the count for row management */
						$count++;
					}
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
	}
	
	add_action( 'save_post', 'cfar_save_core_taxonomy', 9, 2 );
	/* Save the meta box's post metadata. */
	function cfar_save_core_taxonomy( $post_id, $post ) {
		    
		  // verify if this is an auto save routine. 
		  // If it is our form has not been submitted, so we dont want to do anything		    
		  if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) 
		      return;
		  
		  /* Get the post type object. */
		  $post_type = get_post_type_object( $post->post_type );
		  
		  if($post_type->name == 'tickets') {
	    	    $core = $_POST['core'];
	    	    if ( !wp_verify_nonce( $_POST['core_noncename'], plugin_basename( __FILE__ ) ) )
		      return;
	    	  }
	    	
	    	  if($post_type->name == 'projects') {
	    	    $core = $_POST['cfar_projects_core'];
	    	  }
		
		  /* Check if the current user has permission to edit the post. */
		  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		    return $post_id;
	    	  
	    	  wp_set_object_terms(  $post_id , $core, 'core' );
	    	  
	    	  
	    	  //Leaving comment for now... issue: meta information is wiped by wp_set_object_terms() when new core is selected. Ultimately may not be a problem. 
	    	  //Issue 2 nonce on projects CMB field - CMB class checks nonce already I believe
	}
/**
*  Checking to see if they've set a service for reporting on ticket, and Changing Post date when ticket is marked closed
*/	
	add_action( 'save_post', 'cfar_update_ticket_date_on_close', 9, 2 );
	function cfar_update_ticket_date_on_close($post_id, $post) {
		
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) 
			return;
		
		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );		
		
		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;
		
		if($post_type->name != 'tickets')
			return;
		
		$currents = get_the_terms( $post_id, 'status' );

		$curr_status = '';

		if( is_array($currents) ) {
			foreach($currents as $current) {
				$current = $current->slug;
			}
			$curr_status = $current;
		}
		
		if($_POST['status']=='wpas-close') {
			if($curr_status == 'wpas-open') {
				//We're going from open to close, so we can check to see if they've categorized it as a service and save a new time on the post_date of this ticket.
				$service = $_POST['tax_input']['service'];
				$service = array_filter($service);
				if(!has_term( 'service', $post_id ) && empty($service)) {
					$url = admin_url()."post.php?post=$post_id&action=edit&message=service-error";
					wp_redirect($url);
					exit;
				}
				$timestamp = time();
				$date = gmdate("Y-m-d H:i:s", $timestamp);
				global $wpdb;
				$wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET post_date = '$date' WHERE id = $post_id", $post_id ));
			}
		}
	}

/**
*  Error message if service is not set, and user is redirected back to edit page on save
*/	
add_action('admin_init', 'cfar_ticket_service_error_message');
function cfar_ticket_service_error_message() {
	global $pagenow;
	if($pagenow == 'post.php' && $_GET['action']['edit']){
		if(isset($_GET['message']) && $_GET['message'] == 'service-error') {
			   $log['error'][] = "You must first set a <a href='".admin_url()."edit-tags.php?taxonomy=service&post_type=tickets'>service</a> for this ticket before setting status to closed.  
			   This information is used when generating table 5 reports on projects with connected tickets, in order to populate the \"Core Service\" column.";
			   cfar_print_log_messages($log);
			   return; 
		}

	}
}
	
/**
*  Adding timestamps to Publish box for tickets
*/
//add_action( 'post_submitbox_misc_actions', 'article_or_box' );
function article_or_box() {
    global $post;
    if (get_post_type($post) == 'tickets') {
    	echo '<div class="misc-pub-section curtime misc-pub-curtime">
	<span id="timestamp">
	Published on: <b>Nov 7, 2014 @ 3:56</b></span>
	<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js"><span aria-hidden="true">Edit</span> <span class="screen-reader-text">Edit date and time</span></a>
	<div id="timestampdiv" class="hide-if-js"><div class="timestamp-wrap"><label for="mm" class="screen-reader-text">Month</label><select id="mm" name="mm">
			<option value="01">01-Jan</option>
			<option value="02">02-Feb</option>
			<option value="03">03-Mar</option>
			<option value="04">04-Apr</option>
			<option value="05">05-May</option>
			<option value="06">06-Jun</option>
			<option value="07">07-Jul</option>
			<option value="08">08-Aug</option>
			<option value="09">09-Sep</option>
			<option value="10">10-Oct</option>
			<option value="11" selected="selected">11-Nov</option>
			<option value="12">12-Dec</option>
</select> <label for="jj" class="screen-reader-text">Day</label><input type="text" id="jj" name="jj" value="07" size="2" maxlength="2" autocomplete="off">, <label for="aa" class="screen-reader-text">Year</label><input type="text" id="aa" name="aa" value="2014" size="4" maxlength="4" autocomplete="off"> @ <label for="hh" class="screen-reader-text">Hour</label><input type="text" id="hh" name="hh" value="03" size="2" maxlength="2" autocomplete="off"> : <label for="mn" class="screen-reader-text">Minute</label><input type="text" id="mn" name="mn" value="56" size="2" maxlength="2" autocomplete="off"></div><input type="hidden" id="ss" name="ss" value="50">

<input type="hidden" id="hidden_mm" name="hidden_mm" value="11">
<input type="hidden" id="cur_mm" name="cur_mm" value="11">
<input type="hidden" id="hidden_jj" name="hidden_jj" value="07">
<input type="hidden" id="cur_jj" name="cur_jj" value="07">
<input type="hidden" id="hidden_aa" name="hidden_aa" value="2014">
<input type="hidden" id="cur_aa" name="cur_aa" value="2014">
<input type="hidden" id="hidden_hh" name="hidden_hh" value="03">
<input type="hidden" id="cur_hh" name="cur_hh" value="05">
<input type="hidden" id="hidden_mn" name="hidden_mn" value="56">
<input type="hidden" id="cur_mn" name="cur_mn" value="34">

<p>
<a href="#edit_timestamp" class="save-timestamp hide-if-no-js button">OK</a>
<a href="#edit_timestamp" class="cancel-timestamp hide-if-no-js button-cancel">Cancel</a>
</p>
</div>
</div>';
        /*echo '<div class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">';
        wp_nonce_field( plugin_basename(__FILE__), 'article_or_box_nonce' );
        $val = get_post_meta( $post->ID, '_article_or_box', true ) ? get_post_meta( $post->ID, '_article_or_box', true ) : 'article';
        echo '<input type="radio" name="article_or_box" id="article_or_box-article" value="article" '.checked($val,'article',false).' /> <label for="article_or_box-article" class="select-it">Article</label><br />';
        echo '<input type="radio" name="article_or_box" id="article_or_box-box" value="box" '.checked($val,'box',false).'/> <label for="article_or_box-box" class="select-it">Box</label>';
        echo '</div>';*/
    }
}
//add_action( 'save_post', 'save_article_or_box' );
function save_article_or_box($post_id) {

    if (!isset($_POST['post_type']) )
        return $post_id;

    if ( !wp_verify_nonce( $_POST['article_or_box_nonce'], plugin_basename(__FILE__) ) )
        return $post_id;

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return $post_id;

    if ( 'post' == $_POST['post_type'] && !current_user_can( 'edit_post', $post_id ) )
        return $post_id;
    
    if (!isset($_POST['article_or_box']))
        return $post_id;
    else {
        $mydata = $_POST['article_or_box'];
        update_post_meta( $post_id, '_article_or_box', $_POST['article_or_box'], get_post_meta( $post_id, '_article_or_box', true ) );
    }

}	
?>