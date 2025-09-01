<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<header class="site-header">
    <div class="container">
        <h1 class="site-title"><a href="<?php echo home_url(); ?>">My Website</a></h1>
        <nav class="site-nav">
            <?php wp_nav_menu(array('theme_location' => 'main-menu')); ?>
        </nav>
    </div>
</header>
<body <?php body_class(); ?>>