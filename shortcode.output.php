<?php
global $wpdb;

echo '<ul id="amenities">';
$sql = 'SELECT * FROM '.$wpdb->prefix.'amenities ORDER BY sort, title ASC';
$result = $wpdb->get_results($sql);

foreach($result as $row) {
    echo '<li class="amenity-item">'.$row->title.'</li>';
}
echo '</ul>';
?>
