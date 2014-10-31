<?php
/**
* This is the admin page for exporting tickets
*/

$html = '<div class="wrap"><div id="icon-tools" class="icon32"></div>
			<h2>Export as Table 5 Report to CSV or PDF</h2>
		</div>';
$html .= '<form action="" method="post" id="export-pdf">
<p>
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