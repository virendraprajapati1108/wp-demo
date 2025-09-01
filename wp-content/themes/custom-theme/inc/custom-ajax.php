<?php

add_action('wp_ajax_my_custom_ajax_action', 'my_custom_ajax_action');
add_action('wp_ajax_nopriv_my_custom_ajax_action', 'my_custom_ajax_action');

function my_custom_ajax_action()
{

    $posts_per_page = isset($_POST['posts_per_page']) ? $_POST['posts_per_page'] : "3";
    $page_number = isset($_POST['page_number']) ? $_POST['page_number'] : "3";

    $args = [
        "post_type" => "book",
        "posts_per_page" => $posts_per_page,
        "paged" => $page_number
    ];

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()):
        while ($query->have_posts()):
            $query->the_post();

            echo "<div class='inner_conent'>";
            echo "<h2>" . get_the_title() . "</h2>";
            echo "<p>" . get_the_excerpt() . "</p>";
            echo "</div>";

        endwhile;
        wp_reset_postdata();
    endif;

    $html = ob_get_clean();
    $more = ($query->max_num_pages > $page_number);
    wp_send_json_success([
        'html' => $html,
        'more' => $more
    ]);
    wp_die();
}
