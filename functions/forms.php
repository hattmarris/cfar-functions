<?php
/**
* Actions and Functions after Gravity Forms submission
*
* Form id for the Gravity Forms MUST be == 2, if necessary when going to new environment reset auto increment for forms table in database
*/
//Pulls PI list for form two pi options
add_filter('gform_pre_render', 'populate_cfar_users');
//Note: when changing drop down values, we also need to use the gform_admin_pre_render so that the right values are displayed when editing the entry.
add_filter('gform_admin_pre_render', 'populate_cfar_users');
//Note: this will allow for the labels to be used during the submission process in case values are enabled
add_filter('gform_pre_submission_filter', 'populate_cfar_users');
function populate_cfar_users($form) {
	//only populating drop down for form id 2
	if($form["id"] != 2)
	      return $form;
	
	//Reading users, then users with pi role (principal_investigator) in ticketing system      
	$users = get_users();
	$pis = get_users('role=principal_investigator');

	//Creating drop down item arrays.
	$choices = array();
	$pi_choices = array();
	
	
	//Adding Initial value
	$choices[] = array('text' => 'Select requester name', 'value' => ' ');	
	$pi_choices[] = array('text' => 'Select a PI', 'value' => ' ');
	
	//Adding names to the choices array
	foreach($users as $user){
		$choices[] = array('text' => $user->display_name, 'value' => $user->ID);
	}
	foreach($pis as $pi){
		$pi_choices[] = array('text' => $pi->display_name, 'value' => $pi->ID);
	}
	
	foreach($form["fields"] as &$field) {
        	if($field['type'] == 'select' && $field['cssClass'] == 'populate-pi'){           
        		$field["choices"] = $pi_choices;
        	} 
        	elseif($field['type'] == 'select' && $field['cssClass'] == 'populate-users'){           
        		$field["choices"] = $choices;
        	} 
        }

	return $form;
}

// Dropdown B - Award Name
add_filter("gform_pre_render", "dropdown_project_name");
add_filter("gform_admin_pre_render", "dropdown_project_name");
add_filter('gform_pre_submission_filter', 'dropdown_project_name');
function dropdown_project_name($form){
        if($form["id"] != 2)
           return $form;
        $items = array();
        $items[] = array( "text" => __('Select project...','theme'), "value" => 'default' );
        foreach($form["fields"] as &$field){
            if($field['type'] == 'select' && $field['cssClass'] == 'project-name'){
                $field["choices"] = $items;
            }
        }
        return $form; 
}

//Make ajax call when a new pi is selected 
add_filter("gform_pre_render", "add_project_filter_javascript");
add_filter("gform_admin_pre_render", "add_project_filter_javascript");
function add_project_filter_javascript($form) { ?>
	<script type="text/javascript">
	function projectFilter() {
		var piClass = '.populate-pi select',
		    projectClass  = '.project-name select';
		
		jQuery(piClass).change(function(){
			var piSelect = jQuery(this),
			    pi = piSelect.val(),
			    projectSelect = piSelect.parents('form').find(projectClass);
			    
			    console.log(projectSelect);
		
			if(pi != "default") {
		
			    jQuery.ajax({
				type: 'POST',
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				data: { projectPi : pi, action: 'get_project_name' },
				success: function(data){
				    projectSelect.empty();
				    var options = jQuery.parseJSON(data);
				    for(i=0; i<options.length; i++){
					projectSelect.append('<option value="'+options[i].value+'">'+options[i].text+'</option>');
					console.log(options[i].text);
				    }
				    projectSelect.removeAttr('disabled');
				}
			    });
		
			}
		
		});
	}
	</script>
	<script type="text/javascript">
	jQuery(document).ready(function () { 
	//projectFilter();
	});
		jQuery(document).bind('gform_post_render', function(event, form_id){
		
			if(form_id == 2) {
		
			console.log('worked!');
			projectFilter();
		
			}
		
		});
</script>
<?php return $form;
}

//After Submission Connect PI to the Service Request Ticket
add_action("gform_after_submission_2", "connect_pi_after_submission", 10, 2);
function connect_pi_after_submission($entry, $form) {
	
	//getting ticket post
	$ticket = get_post($entry["post_id"]);
	//getting pi and award from form submission
	$pi = $entry["1"];
	$project = $entry["2"];
	
	// Create connection
	p2p_type( 'tickets_to_pis' )->connect( $ticket, $pi, array(
	    'date' => current_time('mysql')
	) );
	
	p2p_type( 'tickets_to_projects' )->connect( $ticket, $project, array(
	    'date' => current_time('mysql')
	) );
}

/**
* Add Taxonomy / Core selection to the ticket
*/
add_action("gform_after_submission_2", "cfar_add_core_taxonomy", 10, 2);
function cfar_add_core_taxonomy($entry, $form) {
	$post_id = $entry["post_id"];
	$selected_core = $entry["79"];
	$taxonomy = 'core';
	$cores = get_terms($taxonomy, 'hide_empty=0');
	foreach($cores as $core) {
		if($selected_core == $core->slug){
			wp_set_object_terms( $post_id, $core->slug, $taxonomy );
		}
	}
}

/**
* Assign Ticket to appropriate user / contact
*/
add_action("gform_after_submission_2", "cfar_assign_the_ticket", 10, 2);
function cfar_assign_the_ticket($entry, $form) {
	$post_id = $entry["post_id"];
	$selected_core = $entry["79"];
	$meta_key = 'wpas_ticket_assignee';
	$prev_value = get_post_meta($post_id, $meta_key, true);
	$field = 'email';
	//Routing based on Core's main contact	- currently settings for testing
		switch ($selected_core) {
		    case "biostatistics":
			$value = 'matt@andisites.com';
			break;
		    case "clinical":
			$value = 'matt@andisites.com';
			break;
		    case "clinical-pharmacology":
			$value = 'matt@andisites.com';
			break;
		    case "developmental":
			$value = 'matt@andisites.com';
			break;	
		    case "social-behavorial-science":
			$value = 'matt@andisites.com';
			break;
		    case "virology-immunology-microbiology":
			$value = 'matt@andisites.com';
			break;			
		}   
	$user = get_user_by( $field, $value );	
	$meta_value = $user->ID;
	
	update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
}

/**
* Put Post ID number into the post title for the ticket
*/
add_action("gform_after_submission_2", "cfar_add_post_id_title", 10, 2);
function cfar_add_post_id_title($entry, $form) {
	$post_id = $entry["post_id"];
	$date = new DateTime();
	$timestamp = $date->format('m/d/Y') . "\n";
	$title = 'Service Request #' . $post_id . ' ' . $timestamp;
	//? $slug = 'service-request' . $post_id;
	// Preparing to update Title
	  $my_post = array(
	      'ID'           => $post_id,
	      'post_title' => $title,
	      //'slug' => $slug
	  );
	
	// Update the post into the database
	  wp_update_post( $my_post );
}

/**
* Create a Project if they don't have a grant
*/
add_action("gform_after_submission_2", "cfar_add_project_post", 10, 2);
function cfar_add_project_post($entry, $form){
	
	//New Project Title
	$project_title = $entry["57"];
	
	if($project_title != '') {
		//Create Post Array and then insert project into db
		$post = array(
				'post_title' => $project_title,
				'post_type' => 'projects',
				'post_status' => 'publish'
			);
		$project = wp_insert_post( $post );
		
		//getting ticket post and link ticket to new project
		$ticket = get_post($entry["post_id"]);
		p2p_type( 'tickets_to_projects' )->connect( $ticket, $project, array(
		    'date' => current_time('mysql')
		) );
		
		$pi = $entry["1"];
		$p2p_id = p2p_type( 'projects_to_pis' )->connect( $project, $pi, array(
		    'date' => current_time('mysql')
		) );
		
		p2p_update_meta($p2p_id, 'role', 'Investigator');
		
		//add the core taxonomy to the project
		$selected_core = $entry["79"];
		$taxonomy = 'core';
		wp_set_object_terms( $project, $selected_core, $taxonomy );
	}
}

/**
* Below we are transferring the clinical pharmacology checkbox field to the post meta field
*/
add_action("gform_after_submission_2", "cfar_map_cpharm_fields", 10, 2);
function cfar_map_cpharm_fields($entry, $form) {
	
	//getting ticket post
	$ticket = get_post($entry["post_id"]);
	//saving services required checkbox
	$post_id = $ticket->ID;
	$metakey = "cfar_cpharm_services_required";	
	//getting entries
	$entries = array( 
		$entry["22.1"],
		$entry["22.2"],
		$entry["22.3"],
		$entry["22.4"],
		$entry["22.5"],
		$entry["22.6"],
		);
	cfar_save_checkbox_field($entries, $post_id, $metakey);
}

/**
* Below we are transferring the biostatistics checkbox fields to the post meta
*/
add_action("gform_after_submission_2", "cfar_map_biostat_fields", 10, 2);
function cfar_map_biostat_fields($entry, $form) {
	
	//saving services required checkbox
	$post_id = $entry["post_id"];
	$metakey = "cfar_biostat_grant_preparation";	
	//getting preparation entries
	$entries = array( 
			$entry["13.1"],
			$entry["13.2"],
			$entry["13.3"],
			$entry["13.4"],
		);
	cfar_save_checkbox_field($entries, $post_id, $metakey);

	$metakey = "cfar_biostat_manuscript_preparation";	
	//getting manuscript entries
	$entries = array( 
			$entry["15.1"],
			$entry["15.2"],
			$entry["15.3"],
			$entry["15.4"],
		);
	cfar_save_checkbox_field($entries, $post_id, $metakey);
}

/**
* Below we are transferring the developmental checkbox fields to the post meta
*/
add_action("gform_after_submission_2", "cfar_map_develop_fields", 10, 2);
function cfar_map_develop_fields($entry, $form) {	
	
	$post_id = $entry["post_id"];
	$metakey = "cfar_developmental_selected_cores";
	//getting cores entries
	$entries = array( 
			$entry["27.1"],
			$entry["27.2"],
			$entry["27.3"],
			$entry["27.4"],
			$entry["27.5"],
			$entry["27.6"],
		);
	
	cfar_save_checkbox_field($entries, $post_id, $metakey);
	
	$metakey = "cfar_developmental_working_groups";
	//getting working groups entries
	$entries = array( 
			$entry["28.1"],
			$entry["28.2"],
			$entry["28.3"],
			$entry["28.4"],
			$entry["28.5"],
		);
	
	cfar_save_checkbox_field($entries, $post_id, $metakey);
}

/**
* Below we are transferring the Social & Behavioral checkbox fields to the post meta
*/
add_action("gform_after_submission_2", "cfar_map_social_fields", 10, 2);
function cfar_map_social_fields($entry, $form) {
	
	$post_id = $entry["post_id"];
	$metakey = "cfar_social_research_proposals";
	//getting research proposal entries
	$entries = array( 
			$entry["31.1"],
			$entry["31.2"],
			$entry["31.3"],
			$entry["31.4"],
			$entry["31.5"],
			$entry["31.6"],
			$entry["31.7"],
			$entry["31.8"],
		);
	
	cfar_save_checkbox_field($entries, $post_id, $metakey);
	
	$metakey = "cfar_social_training";
	//getting training entries
	$entries = array( 
			$entry["35.1"],
			$entry["35.2"],
			$entry["35.3"],
			$entry["35.4"],
		);
	
	cfar_save_checkbox_field($entries, $post_id, $metakey);
	
	$metakey = "cfar_social_intervention_development";
	//getting intervention development entries
	$entries = array( 
			$entry["69.1"],
			$entry["69.2"],
			$entry["69.3"],
			$entry["69.4"],
			$entry["69.5"],
		);
	
	cfar_save_checkbox_field($entries, $post_id, $metakey);
	
	$metakey = "cfar_social_data_collection_analysis";
	//getting data collection entries
	$entries = array( 
			$entry["71.1"],
			$entry["71.2"],
		);
	
	cfar_save_checkbox_field($entries, $post_id, $metakey);

	$metakey = "cfar_social_qualitative_research";
	//getting qualitative research entries
	$entries = array( 
			$entry["29.1"],
			$entry["29.2"],
			$entry["29.3"],
			$entry["29.4"],			
		);
	
	cfar_save_checkbox_field($entries, $post_id, $metakey);	
	
	$metakey = "cfar_social_quantitative_research";
	//getting qualitative research entries
	$entries = array( 
			$entry["72.1"],
			$entry["72.2"],
			$entry["72.3"],
			$entry["72.4"],
			$entry["72.5"],		
		);
	
	cfar_save_checkbox_field($entries, $post_id, $metakey);
	
	$metakey = "cfar_social_survey_development";
	//getting qualitative research entries
	$entries = array( 
			$entry["38.1"],	
			$entry["38.2"],	
			$entry["38.3"],	
		);
	
	cfar_save_checkbox_field($entries, $post_id, $metakey);
}

//Function for saving checkbox fields from Gravity
function cfar_save_checkbox_field($entries, $post_id, $metakey) {
	if (!array_filter($entries)) {
		$alert = 'No '.$metakey.' info saved';
		//var_dump($alert);
	} else {
		if (!empty($entries)) {	
			//adding entries to serialized array if they exist
			$checks = array();
			foreach ($entries as $check) {
			    if (!empty($check)) {
				$checks[] = $check;
			    }
			}
			//serialize & save
			$checks = serialize($checks);
			//$post = $ticket->ID;
			//$metakey = "cfar_cpharm_services_required";
			
			global $wpdb;
			
			$wpdb->query( $wpdb->prepare( 
				"
					INSERT INTO $wpdb->postmeta
					( post_id, meta_key, meta_value )
					VALUES ( %d, %s, %s )
				", 
				$post_id, 
				$metakey, 
				$checks
			) );
		}
	}
}


/**
* If specific date is timeline selected, add to ticket
*/
add_action("gform_after_submission_2", "cfar_add_specific_date", 10, 2);
function cfar_add_specific_date($entry, $form) {
	
	//getting ticket post
	$ticket = get_post($entry["post_id"]);	
	
	//get date from form
	$specific_date = $entry["9"];

	if (!empty($specific_date)) {
		add_post_meta($ticket->ID, 'cfar_specific_date', $specific_date, true);
	}
}

/**
* If requester is selected / different from logged in user
*/
add_action("gform_after_submission_2", "cfar_add_requester_name", 10, 2);
function cfar_add_requester_name($entry, $form) {
	
	//getting ticket post
	$id = $entry["post_id"];
	$requester = $entry["4"];
	
	//check if requester field has value
	if($requester != '') {
		// Update post args
		$ticket = array(
		'ID'           => $id,
		'post_author'  => $requester
		);
		// Update the post into the database
		wp_update_post( $ticket );
	}
}


?>