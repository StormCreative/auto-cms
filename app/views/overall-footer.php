        <?php include "assets/includes/footer.php"; ?>
    </div>
    <script>var site_path = "<?php echo DIRECTORY; ?>"; </script>
    <?php if ( !!$script ): ?>
        <script data-main="<?php echo DIRECTORY; ?>assets/scripts/app/<?php echo $script; ?>" src="<?php echo DIRECTORY; ?>assets/scripts/require.min.js"></script>
    <?php endif; ?>
</body>
</html>
