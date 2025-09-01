<?php

/*

Template Name: Ajax Learning

*/

get_header();
?>


<?php

$args = [
    "post_type" => "book",
    "posts_per_page" => "3"
];

$query = new WP_Query($args);

echo "<div class='main_content'>";
if ($query->have_posts()):
    while ($query->have_posts()):
        $query->the_post();

        echo "<div class='inner_conent'>";
        echo "<h2>" . get_the_title() . "</h2>";
        echo "<p>" . get_the_excerpt() . "</p>";
        echo "</div>";

    endwhile;
endif;
echo "</div>";

?>


<button class="button button1" id="my-ajax-button" posts_per_page="3" data-page="2">View More!</button>


<?php
get_footer();
