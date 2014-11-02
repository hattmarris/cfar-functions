<?php
/**
* This is the admin page for exporting tickets
*/

$html = '<div class="wrap"><div id="icon-tools" class="icon32"></div>
			<h2>Export as Table 5 Report to CSV or PDF</h2>
		</div>';
$html .= '<form action="" method="post" id="export-pdf">';
$html .= '<h3>Core:</h3>';
$html .= '<p><ul>';

/**
* Dynamically add Cores as radio options - and option for all
*/

$html .= '<li><label><input type="radio" name="core" value="all"> All </label></li>';
$cores = get_terms( 'core', 'hide_empty=0' );
foreach($cores as $core){
	$html .= '<li><label>
	<input type="radio" name="core" value="'.$core->slug.'">'.$core->name
	.'</label></li>';
}
$html .= '</ul></p>';

/**
* Adds radio selection of file format
*/

$html .= '<h3>Format:</h3>';
$html .=
'<p>
	<label>
		<input type="radio" name="type" value="csv"> CSV
	</label>
	<label>
		<input type="radio" name="type" value="pdf"> PDF
	</label>
</p>
<p class="submit">
	<input type="submit" name="submit" value="Export" class="button button-primary">
</p>
</form>';
echo $html;

?>