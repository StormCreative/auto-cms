<section class="main__content">
	<article class="main__editor">
		<h1 class="main__editor--heading"><a href="<?php echo DIRECTORY; ?>admin/listing/table/stuff" class="back-button icon-arrow-left"></a>stuff Edit</h1>
		<form class="main__editor--form" method="post" enctype="multipart/form-data">
			<?php echo $feedback; ?>
			<input type="hidden" name="stuff[id]" value="<?php echo $id; ?>" />
			<p><label>title:</label><input type="text" name="stuff[title]" class="medium_input" value="<?php echo $title; ?>"></p>content <textarea class="js-wysiwyg" name="stuff[content]"><?php echo $content; ?></textarea><div class="js-upload-container" data-type="image"></div>
<?php if ( !!$gallery_items ) : ?>
    <?php foreach ( $gallery_items as $item ) : ?>
        <div id="<?php echo $item['imgname'] ?>" class="image_<?php echo $item[ 'id' ]; ?>">
            <span class="images_holder"><img src="<?php echo DIRECTORY; ?>_admin/assets/uploads/images/<?php echo $item[ 'imgname' ]; ?>" /></span>
            <ol class="hoz btns">
                <input type="hidden" name="multi-image[<?php echo $item[ 'imgname' ]; ?>][id]" value="<?php echo $item[ 'id' ]; ?>" />
                <input type="hidden" name="multi-image[<?php echo $item[ 'imgname' ]; ?>][imgname]" value="<?php echo $item[ 'imgname' ]; ?>" />
                <input type="button" class="del-image js-delete-image delete-btn" data-id="<?php echo $item[ 'id' ]; ?>" data-imagename="<?php echo $item[ 'imgname' ]; ?>"  data-type="<?php echo $item[ 'imgname' ]; ?>" value="Delete" /></li>
            </ol>
        </div>
    <?php endforeach; ?>
<?php endif; ?><p><label>type</label>book: <input type="radio" <?php echo ( $type == "book" ? 'checked="checked"' : "" ); ?> name="stuff[type]" value="book" />film: <input type="radio" <?php echo ( $type == "film" ? 'checked="checked"' : "" ); ?> name="stuff[type]" value="film" />music: <input type="radio" <?php echo ( $type == "music" ? 'checked="checked"' : "" ); ?> name="stuff[type]" value="music" /></p><p><label>tag</label>design: <input type="checkbox" name="stuff[tag][]" value="design" />development: <input type="checkbox" name="stuff[tag][]" value="development" /></p>
			<p><input type="submit" name="submit" value="Save" /></p>
		</form>
	</article>
</section>
<script>
	var image_count = <?php echo ( !!$image ? '1' : '0' ); ?>;
	var document_count = <?php echo ( !!$uploads_id && !!$upload_name ? '1' : '0' ); ?>
</script>