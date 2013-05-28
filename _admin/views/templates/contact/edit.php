<section class="main__content">
	<article class="main__editor">
		<h1 class="main__editor--heading"><a href="<?php echo DIRECTORY; ?>admin/listing/table/contact" class="back-button icon-arrow-left"></a>contact Edit</h1>
		<form class="main__editor--form" method="post" enctype="multipart/form-data">
			<?php echo $feedback; ?>
			<input type="hidden" name="contact[id]" value="<?php echo $id; ?>" />
			<p><label>name:</label><input type="text" name="contact[name]" class="medium_input" value="<?php echo $name; ?>"></p><p><label>email:</label><input type="text" name="contact[email]" class="medium_input" value="<?php echo $email; ?>"></p><p><label>phone:</label><input type="text" name="contact[phone]" class="medium_input" value="<?php echo $phone; ?>"></p><p><label>postcode:</label><input type="text" name="contact[postcode]" class="medium_input" value="<?php echo $postcode; ?>"></p>
			<p><input type="submit" name="submit" value="Save" /></p>
		</form>
	</article>
</section>
<script>
	var image_count = <?php echo ( !!$image ? '1' : '0' ); ?>;
	var document_count = <?php echo ( !!$uploads_id && !!$upload_name ? '1' : '0' ); ?>
</script>