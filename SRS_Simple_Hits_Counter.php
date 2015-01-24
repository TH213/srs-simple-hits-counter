<?php
/**
Plugin Name: SRS Simple hits Counter
Plugin URI: http://sandyrig.com/srs-simple-hits-counter/
Description: Simple plugin to count and show a total number hits (Unique visitors or page-views) to the site without using any third party code.
Author: Atif N
Version: 0.1.2
Author URI: http://sandyrig.com
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// UPDATE COUNTER
add_action('wp_head','srs_simple_hits_counter');
function srs_simple_hits_counter(){
    if ( !session_id() ) {
        session_start();
    }
    if( !isset( $_SESSION['srs_counter_increased'] ) || ( isset( $_SESSION['srs_counter_increased'] ) && $_SESSION['srs_counter_increased'] != 'yes' ) ) {
        $srs_visitors = intval( get_option('srs_visitors_count') );
        update_option('srs_visitors_count', $srs_visitors+1);
        $_SESSION['srs_counter_increased'] = 'yes';
    }

    $srs_pageViews = intval( get_option('srs_pageViews_count') );
    update_option('srs_pageViews_count', $srs_pageViews+1);
}

// SHORTCODE
add_shortcode('srs_total_pageViews', 'srs_getTotal_pageViews');
function srs_getTotal_pageViews(){
    return intval(get_option('srs_pageViews_count'));
}
add_shortcode('srs_total_visitors', 'srs_getTotal_visitors');
function srs_getTotal_visitors(){
    return intval(get_option('srs_visitors_count'));
}


// WIDGET
add_action( 'widgets_init', 'srs_shc_register_widget' );
function srs_shc_register_widget() {
    register_widget( 'SRS_SHC_Widget' );
}


class SRS_SHC_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'srs_shc_widget', // Base ID
            __( 'SRS Simple Hits Counter', 'text_domain' ), // Name
            array( 'description' => __( 'Add this widget to where you like to display the Total Hits Count for you whole site.', 'text_domain' ), ) // Args
        );
    }


    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }
        if( $instance['type']=='visitors' ){
            $srs_total_visitors =  intval( get_option('srs_visitors_count') );
            echo "<span>" . __( $srs_total_visitors, 'text_domain' ) . "</span>";
        }elseif( $instance['type']=='pageviews' ){
            $srs_total_pageViews =  intval( get_option('srs_pageViews_count') );
            echo "<span>" . __( $srs_total_pageViews, 'text_domain' ) . "</span>";
        }

        echo $args['after_widget'];
    }


    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
        $visitors_count = ! empty( $instance['visitors_count'] ) ? $instance['visitors_count'] : __( '00000', 'text_domain' );
        $pageViews_count = ! empty( $instance['pageViews_count'] ) ? $instance['pageViews_count'] : __( '00000', 'text_domain' );
        $type = ! empty( $instance['type'] ) ? $instance['type'] : __( 'visitors', 'text_domain' );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Counter Type:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" type="radio" value="visitors" <?php echo esc_attr( $type )=='visitors'?'checked':'' ; ?>>Visitors
            <input class="widefat" id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" type="radio" value="pageviews" <?php echo esc_attr( $type )=='pageviews'?'checked':'' ; ?>>Page Views
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'visitors_count' ); ?>"><?php _e( 'Reset Visitor Count to:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'visitors_count' ); ?>" name="<?php echo $this->get_field_name( 'visitors_count' ); ?>" type="text" value="<?php echo esc_attr( $visitors_count ); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'visitors_count_reset_check' ); ?>"><?php _e( 'All instances of this widget share the same Page Views counter value, are you sure want to reset the Page Views counter to value above' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'visitors_count_reset_check' ); ?>" name="<?php echo $this->get_field_name( 'visitors_count_reset_check' ); ?>" type="checkbox" value="yes">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'pageViews_count' ); ?>"><?php _e( 'Reset Page-Views Count to:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'pageViews_count' ); ?>" name="<?php echo $this->get_field_name( 'pageViews_count' ); ?>" type="text" value="<?php echo esc_attr( $pageViews_count ); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'pageViews_count_reset_check' ); ?>"><?php _e( 'All instances of this widget share the same Visitors counter value, are you sure want to reset the Page Views counter to value above' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'pageViews_count_reset_check' ); ?>" name="<?php echo $this->get_field_name( 'pageViews_count_reset_check' ); ?>" type="checkbox" value="yes">
        </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();

        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['type'] = ( ! empty( $new_instance['type'] ) ) ? strip_tags( $new_instance['type'] ) : '';
        $instance['visitors_count_reset_check'] = ( ! empty( $new_instance['visitors_count_reset_check'] ) ) ? strip_tags( $new_instance['visitors_count_reset_check'] ) : '';
        $instance['pageViews_count_reset_check'] = ( ! empty( $new_instance['pageViews_count_reset_check'] ) ) ? strip_tags( $new_instance['pageViews_count_reset_check'] ) : '';
        if( $instance['visitors_count_reset_check'] == 'yes' ) {
            $reset_value = (!empty($new_instance['visitors_count'])) ? strip_tags($new_instance['visitors_count']) : '';
            update_option('srs_visitors_count', intval($reset_value) );
            $instance['visitors_count'] = intval($reset_value);
        }else{
            $instance['visitors_count'] = $old_instance['visitors_count'];
        }
        if( $instance['pageViews_count_reset_check'] != '' ) {
            $reset_value = (!empty($new_instance['pageViews_count'])) ? strip_tags($new_instance['pageViews_count']) : '';
            update_option('srs_pageViews_count', intval($reset_value));
            $instance['pageViews_count'] = intval($reset_value);
        }else{
            $instance['pageViews_count'] = $old_instance['pageViews_count'];
        }

        return $instance;
    }
}