<?php 
/**
* This file adds metaboxes to taxonomies
*/

/**
* Meta information about Cores - Core letter
*/

// Add term page
function cfar_taxonomy_add_core_letter_meta_field() {
	// this will add the Core letter field to the add new term page
	?>
	<div class="form-field">
		<label for="term_meta[cfar_core_letter]"><?php _e( 'Core Letter', 'cfar' ); ?></label>
		<input type="text_small" name="term_meta[cfar_core_letter]" id="term_meta[cfar_core_letter]" value="">
		<p class="description"><?php _e( 'Enter Core Letter here','cfar' ); ?></p>
	</div>
<?php
}
add_action( 'core_add_form_fields', 'cfar_taxonomy_add_core_letter_meta_field', 10, 2 );

// Edit term page
function cfar_taxonomy_edit_core_letter_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[cfar_core_letter]"><?php _e( 'Core Letter', 'cfar' ); ?></label></th>
		<td>
			<input type="text_small" name="term_meta[cfar_core_letter]" id="term_meta[cfar_core_letter]" value="<?php echo esc_attr( $term_meta['cfar_core_letter'] ) ? esc_attr( $term_meta['cfar_core_letter'] ) : ''; ?>">
			<p class="description"><?php _e( 'Enter Core Letter here','cfar' ); ?></p>
		</td>
	</tr>
<?php
}
add_action( 'core_edit_form_fields', 'cfar_taxonomy_edit_core_letter_field', 10, 2 );

// Save core letter taxonomy field callback function.
function cfar_save_taxonomy_core_letter_meta( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$spon_keys = array_keys( $_POST['term_meta'] );
		foreach ( $spon_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
}  
add_action( 'edited_core', 'cfar_save_taxonomy_core_letter_meta', 10, 2 );  
add_action( 'create_core', 'cfar_save_taxonomy_core_letter_meta', 10, 2 );

/**
* Add Core letter column to manage core taxonomy screen
*/

//Add Extra User Columns to All Users Screen
function cfar_add_extra_core_letter_column( $columns ) {
    $columns['cfar-edit-column-core-letter'] = __('Letter', 'cfar_core_letter');
    unset($columns['description']);
    return $columns;
}
function cfar_custom_column_core_letter($value, $column_name, $id) {	
   if( $column_name == 'cfar-edit-column-core-letter' ) {
   	$term_meta = get_option( "taxonomy_$id" );
        return $term_meta['cfar_core_letter'];
   }
}
add_action('manage_core_custom_column', 'cfar_custom_column_core_letter', 15, 3);
add_filter('manage_edit-core_columns' , 'cfar_add_extra_core_letter_column', 15, 1);


/**
* Meta information about NIH sponsor codes
*/

// Add term page
function cfar_taxonomy_add_new_meta_field() {
	// this will add the Administering Organization Code field to the add new term page
	?>
	<div class="form-field">
		<label for="term_meta[cfar_project_administering_organization_code]"><?php _e( 'Administering Organization Code', 'cfar' ); ?></label>
		<input type="text_small" name="term_meta[cfar_project_administering_organization_code]" id="term_meta[cfar_project_administering_organization_code]" value="">
		<p class="description"><?php _e( 'Enter Organization Code (<a href="http://ods.od.nih.gov/Research/CARDS_lists.aspx#ic">NIH list</a>) here','cfar' ); ?></p>
	</div>
<?php
}
add_action( 'sponsor_add_form_fields', 'cfar_taxonomy_add_new_meta_field', 10, 2 );

// Edit term page
function cfar_taxonomy_edit_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[cfar_project_administering_organization_code]"><?php _e( 'Administering Organization Code', 'cfar' ); ?></label></th>
		<td>
			<input type="text_small" name="term_meta[cfar_project_administering_organization_code]" id="term_meta[cfar_project_administering_organization_code]" value="<?php echo esc_attr( $term_meta['cfar_project_administering_organization_code'] ) ? esc_attr( $term_meta['cfar_project_administering_organization_code'] ) : ''; ?>">
			<p class="description"><?php _e( 'Enter Organization Code (<a href="http://ods.od.nih.gov/Research/CARDS_lists.aspx#ic">NIH list</a>) here','cfar' ); ?></p>
		</td>
	</tr>
<?php
}
add_action( 'sponsor_edit_form_fields', 'cfar_taxonomy_edit_meta_field', 10, 2 );

// Save extra taxonomy fields callback function.
function cfar_save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$spon_keys = array_keys( $_POST['term_meta'] );
		foreach ( $spon_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
}  
add_action( 'edited_sponsor', 'cfar_save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_sponsor', 'cfar_save_taxonomy_custom_meta', 10, 2 );

?>