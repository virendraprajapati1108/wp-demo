<?php

/**
 * The main index file for the theme
 * 
 * @package WordPress
 */

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        the_title('<h2>', '</h2>');
        the_content();
    endwhile;
endif;


get_footer();
