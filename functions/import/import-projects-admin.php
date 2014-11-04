<?php
//echo 'hello world';
?>
<div class="wrap">
    <h2>Import CSV</h2>
    <form class="add:the-list: validate" method="post" enctype="multipart/form-data">
        <!-- Import as draft -->
        <p>
        <input name="_csv_importer_import_as_draft" type="hidden" value="publish" />
        <label><input name="csv_importer_import_as_draft" type="checkbox" <?php if ('draft' == $opt_draft) { echo 'checked="checked"'; } ?> value="draft" /> Import posts as drafts</label>
        </p>

        <!-- Parent category -->
        <p>Core: <?php wp_dropdown_categories(array('show_option_all' => 'Select one ...', 'taxonomy' => 'core', 'hide_empty' => 0, 'hierarchical' => 1, 'show_count' => 0, 'name' => 'csv_importer_core', 'orderby' => 'name', 'selected' => $opt_cat));?><br/>
            <!--<small>This will create new categories inside the category parent you choose.</small>--></p>

        <!-- File input -->
        <p><label for="csv_import">Upload file:</label><br/>
            <input name="csv_import" id="csv_import" type="file" value="" aria-required="true" /></p>
        <p class="submit"><input type="submit" class="button button-primary" name="submit" value="Import" /></p>
    </form>
</div><!-- end wrap -->