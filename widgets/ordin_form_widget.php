<?php
/**
 * Add new widget.
 */
class Ordin_Form_Widget extends WP_Widget {

    function __construct() {

        parent::__construct(
            'ordin_form_widget',
            'Ordin Form',
            array( 'description' => __('This is a widget, help find users profile', 'ordin'), 'classname' => 'ordin_form_widget', )
        );

        // if widget is active add style & script
        if ( is_active_widget( false, false, $this->id_base ) || is_customize_preview() ) {
            add_action('wp_footer', array( $this, 'add_ordin_form_widget_scripts' ));
            add_action('wp_head', array( $this, 'add_ordin_form_widget_style' ) );
        }
    }

    /**
     * Output widget on Front end
     *
     * @param array $args
     * @param array $instance from save options
     */
    function widget( $args, $instance ) {
        global $settings;

        $title = apply_filters( 'ordin_form_widget_title', $instance['title'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        ?>
        <form id="ordin_form" action="" method="post">
            <ul>
                <li>
                    <label for="ordin_name"><?php _e( 'Фамилия Имя (латынью)' ); ?></label>
                    <input class="" id="ordin_name" name="ordin_name" type="text" value="">
                </li>
                <li>
                    <label for="ordin_year"><?php _e( 'Номер дела' ); ?></label>
                    <input class="" id="ordin_profile" name="ordin_year" type="text" value="">
                </li>
            </ul>
            <p>
                <input id="ordin_submit" type="submit" value="<?php _e( 'Искать' ); ?>">
            </p>
            <p class="ordin_output">
                
            </p>
        </form>
        <?php

        // $query = new WP_Query( array(
        //     'post_type' => 'ordin',
        //     'post_status' => 'publish',
        //     'orderby' => array( 'modified' => 'DESC', 'title' => 'DESC' ),
        //     'fields' => 'ids',
        //     'posts_per_page' => 7
        // ) );

        // echo "<ul>";
        // while ( $query->have_posts() ) {
        //     $query->the_post();

        //     $title = get_the_title();
        //     $data = get_the_date();
        //     $url = get_the_excerpt();

        //     if ( parse_url( $url, PHP_URL_HOST) == '' ) {
        //         $url = get_site_url() . $url;
        //     }
            
        //     $item = $title . "<span>&nbsp;&nbsp;&mdash;&nbsp;&nbsp;" . $data . "</span>";
        // ?>
        <!-- <li><a href="<?php echo $url ?>" target="blank" title="<?php echo $title; ?>"><?php echo $item ?></a></li> -->

        <?php
        // }
        // echo "</ul>";

        echo $args['after_widget'];
    }

    /**
     * Output widget on Back end
     *
     * @param array $instance from save options
     */
    function form( $instance ) {

        $title = @ $instance['title'] ? $instance['title'] : __('Проверьте свое досье', 'advanced-widget');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    /**
     * save options to db
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance new options
     * @param array $old_instance old options
     *
     * @return array options to save
     */
    function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }

    function add_ordin_form_widget_scripts() {
        //filter so that you can turn off styles
        if( ! apply_filters( 'add_ordin_form_widget_scripts', true, $this->id_base ) )
            return;
        ?>
        <script>
            jQuery(document).ready(function( $ ) {

            });
        </script>
        <?php
    }

    function add_ordin_form_widget_style() {
        //filter so that you can turn off styles
        if( ! apply_filters( 'add_ordin_form_widget_style', true, $this->id_base ) )
            return;
        ?>
        <style type="text/css">
            .ordin_output {
                max-height: 300px;
                overflow-y: auto;
            }
        </style>
        <?php
    }

}


//register widgets
function register_ordin_form_widgets() {
    register_widget( 'Ordin_form_Widget' );
}
add_action( 'widgets_init', 'register_ordin_form_widgets' );