<?php

/*
Template Name: Get Flights
*/

get_header();

$response = wp_remote_get('http://localhost/wp_demo2/wp-json/wp/v2/flights');

if (is_wp_error($response)) {
    return;
}

$flights = json_decode(wp_remote_retrieve_body($response));

foreach ($flights as $flight) {
    echo "<h2>{$flight->title->rendered}</h2>";
    echo "<p>Flight No: {$flight->acf->flight_number}</p>";
    echo "<p>From: {$flight->acf->departure_airport} ({$flight->acf->departure_code})</p>";
    echo "<p>To: {$flight->acf->arrival_airport} ({$flight->acf->arrival_code})</p>";
    echo "<p>Departure: {$flight->acf->departure_date} {$flight->acf->departure_time}</p>";
    echo "<p>Price: {$flight->acf->currency} {$flight->acf->price}</p>";
    echo "<a href='" . site_url('/book-flight/?flight_id=' . $flight->id) . "' class='button'>";
    echo "Book Now</a>";
}
?>



<?php

get_footer();
