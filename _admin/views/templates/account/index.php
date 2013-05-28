<section class="main__content">
    <article class="main__editor">
        <h1 class="main__editor--heading"><a href="<?php echo DIRECTORY; ?>admin/listing/table/news" class="back-button icon-arrow-left"></a>Account</h1>
        <form class="main__editor--form" method="post" enctype="multipart/form-data">
            <?php echo $feedback; ?>
            <?php if ( !$success ) : ?>
                <p><label>Current Password: </label><input type="password" name="access[current_password]" class="medium_input" value="<?php echo $_POST[ 'access' ][ 'current_password' ]; ?>"></p>
                <p><label>New Password: </label><input type="password" name="access[new_password]" class="medium_input" value="<?php echo $_POST[ 'access' ][ 'new_password' ]; ?>"></p>
                <p><label>Confirm Password: </label><input type="password" name="access[confirm_password]" class="medium_input" value="<?php echo $_POST[ 'access' ][ 'confirm_password' ]; ?>"></p>
                <p><input type="submit" name="access[submit]" value="Save" class="save-button" /></p>
            <?php endif; ?>
        </form>
    </article>
</section>
