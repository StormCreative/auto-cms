    </div>
    <?php include "_admin/assets/includes/delete-popup.php" ;?>
    <script>var site_path = "<?php echo DIRECTORY; ?>_admin/"; </script>
    <?php if ( !!$script ): ?>
        <script data-main="<?php echo DIRECTORY; ?>_admin/assets/scripts/app/<?php echo $script; ?>" src="<?php echo DIRECTORY; ?>assets/scripts/require.min.js"></script>
    <?php endif; ?>
</body>
</html>
