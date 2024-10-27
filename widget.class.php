<?php
// Widget setup
class theWidget extends WP_Widget {
    function theWidget() {
        parent::WP_Widget( false, $name = 'Amenities Widget' );
    }

    function widget( $args, $instance ) {
        global $wpdb;
        
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
?>

<?php
        echo $before_widget;
?>

<?php
        if ($title) {
            echo $before_title . $title . $after_title;
        }

        echo '<ul id="amenities">';
        $sql = 'SELECT * FROM '.$wpdb->prefix.'amenities ORDER BY sort, title ASC';
        $result = $wpdb->get_results($sql);

        foreach($result as $row) {
            echo '<li class="amenity-item">'.$row->title.'</li>';
        }
        echo '</ul>';
        
        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        return $new_instance;
    }

    function form( $instance ) {
        $title = esc_attr( $instance['title'] );
?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
            </label>
        </p>
<?php
    }
}
?>