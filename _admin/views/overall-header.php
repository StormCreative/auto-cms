<!doctype html>
<!--[if IE 8]><html class="ie8" dir="ltr" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9" dir="ltr" lang="en"><![endif]-->
<!--[if gt IE 9]><!--> <html dir="ltr" lang="en"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
        <meta name="author" content="Storm Creative" />

        <title>[SITE TITLE] - <?php echo $title; ?></title>

        <script src="<?php echo DIRECTORY; ?>assets/scripts/utils/modernizr.min.js"></script>
        <?php foreach ( $stylesheets as $style ): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>" />
        <?php endforeach; ?>

    </head>
    <body>
         <?php if ( !$dont_show_header ) : ?>
            <?php include "_admin/assets/includes/header.php"; flush(); ?>
        <?php endif; ?>
        
        <div class="wrapper">
            <?php if ( !$dont_show_menu ) : ?>
                <?php include "_admin/assets/includes/aside.php" ?>
            <?php endif; ?>
