<?php 
/**
* This file adds metaboxes to taxonomies
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