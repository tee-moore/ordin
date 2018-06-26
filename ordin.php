<?php
/*
Plugin Name: Ordin
Description: Ordin romania
Version: 1.0.0
Author: Timur Panchenko
*/

/*  Copyright 2017  Timur Panchenko  (email: 2teemoore@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


//settings
$settings['add_series_to_post_types'] = array('post');
$settings['display_on_post_types'] = array('post', 'page');
$settings['add_supports_to_post_types'] = 'page';



//add textdomai
add_action('init', 'ordin_locale');
function ordin_locale() {
     load_plugin_textdomain( 'ordin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}


//add scripts
add_action( 'admin_enqueue_scripts', 'ordin_admin_enqueue_scripts' );
function ordin_admin_enqueue_scripts(){
    wp_enqueue_media();
    wp_enqueue_script( 'jquery-ui-script', plugins_url('/js/jquery-ui.min.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script( 'ordin-script', plugins_url('/js/ordin-script-admin.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_style( 'ordin-jquery-style', plugins_url('/css/jquery-ui.min.css', __FILE__), array(), null, 'all' );
    wp_enqueue_style( 'ordin-admin-style', plugins_url('/css/ordin-style-admin.css', __FILE__), array(), null, 'all' );
}

add_action( 'wp_enqueue_scripts', 'ordin_enqueue_scripts' );
function ordin_enqueue_scripts(){
    wp_enqueue_script( 'ordin-script', plugins_url('/js/ordin-script-front.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_style( 'ordin-style', plugins_url('/css/ordin-style-front.css', __FILE__), array(), null, 'all' );
    wp_localize_script('ordin-script', 'myajax', 
        array(
            'url' => admin_url('admin-ajax.php')
        )
    );
}


//add custom type 'ordin'
add_action('init', 'ordin_register_post_types');
function ordin_register_post_types(){
    register_post_type('ordin', array(
        'label'  => null,
        'labels' => array(
            'name'               => 'Приказы',
            'singular_name'      => 'Приказ',
            'add_new'            => 'Добавить Приказ',
            'add_new_item'       => 'Добавление Приказа',
            'edit_item'          => 'Редактирование Приказа',
            'new_item'           => 'Новый Приказ',
            'view_item'          => 'Смотреть Приказ',
            'search_items'       => 'Искать Приказ',
            'not_found'          => 'Не найдено',
            'not_found_in_trash' => 'Не найдено в корзине',
            'parent_item_colon'  => '',
            'menu_name'          => 'Приказы',
        ),
        'description'         => '',
        'public'              => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => true,
        'show_in_rest'        => true,
        'menu_position'       => 9,
        'menu_icon'           => "dashicons-media-text",
        'capability_type'   => 'page',
        //'capabilities'      => 'post',
        //'map_meta_cap'      => null,
        'hierarchical'        => false,
        'supports'            => array('title'),
        'taxonomies'          => array(),
        'has_archive'         => false,
        'rewrite'             => true,
        'query_var'           => true,
    ) );
}

//add custom type 'faq'
add_action('init', 'faq_register_post_types');
function faq_register_post_types(){
    register_post_type('faq', array(
        'label'  => null,
        'labels' => array(
            'name'               => 'Полезное',
            'singular_name'      => 'Полезное',
            'add_new'            => 'Добавить',
            'add_new_item'       => 'Добавление',
            'edit_item'          => 'Редактирование',
            'new_item'           => 'Новая Полезность',
            'view_item'          => 'Смотреть Полезное',
            'search_items'       => 'Искать Полезное',
            'not_found'          => 'Не найдено',
            'not_found_in_trash' => 'Не найдено в корзине',
            'parent_item_colon'  => '',
            'menu_name'          => 'Полезное',
        ),
        'description'         => '',
        'public'              => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => true,
        'show_in_rest'        => true,
        'menu_position'       => 9,
        'menu_icon'           => "dashicons-admin-comments",
        'capability_type'   => 'post',
        //'capabilities'      => 'post',
        //'map_meta_cap'      => null,
        'hierarchical'        => false,
        'supports'            => array('title','editor','author','thumbnail','excerpt'),
        'taxonomies'          => array(),
        'has_archive'         => false,
        'rewrite'             => true,
        'query_var'           => false,
    ) );
}



/*
 * Add a meta box
 */
add_action( 'admin_menu', 'misha_meta_box_add' );

function misha_meta_box_add() {
    add_meta_box('mishadiv', // meta box ID
        'Приказ', // meta box title
        'misha_print_box', // callback function that prints the meta box HTML 
        'ordin', // post type where to add it
        'normal', // priority
        'high' ); // position
}


/*
 * Meta Box HTML
 */
function misha_print_box( $post ) {
    echo misha_image_uploader_field( $post );
}


function misha_image_uploader_field( $post ) {
    $image = ' button">Загрузить приказ';
    $image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
    $display = 'none'; // display state ot the "Remove image" button

    $date = get_the_date();
    $value= get_the_excerpt();

    return '
    <div>
        <div id="titlewrap1">
            <label class="" id="title-url-ordin" for="url_ordin">Введите ссылку на приказ или загрузите файл приказа, нажав кнопку "Загрузить приказ".</label>
        </div>
        <input style="display: block;width:100%;margin-bottom: 5px;" name="url_ordin" size="30" value="'. $value .'" id="url_ordin" spellcheck="true" autocomplete="off" type="text">
        <a href="#" style="margin-bottom: 15px;" class="misha_upload_image_button' . $image . '</a>
        <div id="titlewrap2">
            <label class="" id="data-url-ordin" for="data_ordin">Дата приказа:</label>
        </div>
        <input style="display: block;width:100%;" name="data_ordin" size="30" value="'. $date .'" id="data_ordin" type="text">' . wp_nonce_field( plugin_basename(__FILE__), "ordin_noncename" ).'</div>';
}


/*
 * Save Meta Box data
 */
add_action('save_post_ordin', 'ordin_save');
function ordin_save( $post_id ) {
    if ( ! wp_is_post_revision( $post_id ) ){

        if ( ! wp_verify_nonce( $_POST['ordin_noncename'], plugin_basename(__FILE__) ) )
            return $post_id;

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
            return;

        if ( 'ordin' == $_POST['post_type'] && ! current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        } elseif( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        if ( ! isset( $_POST['url_ordin'] ) )
            return;

        if ( ! isset( $_POST['data_ordin'] ) )
            return;


        if (parse_url($_POST['url_ordin'], PHP_URL_HOST) == parse_url(get_site_url(), PHP_URL_HOST)) {
            $url = parse_url(sanitize_text_field($_POST['url_ordin']), PHP_URL_PATH);
        } else {
            $url = sanitize_text_field($_POST['url_ordin']);
        }

        $date_text = sanitize_text_field( $_POST['data_ordin'] );
        $date_date = strtotime( $date_text );
        $date = date("Y-m-d H:i:s", $date_date );
        $title = sanitize_text_field( $_POST['post_title'] );

        remove_action('save_post_ordin', 'ordin_save');
        $post_data = array(
            'ID'                 => $post_id,
            'post_title'         => $title,
            'post_content'       => '',
            'post_excerpt'       => $url,
            'post_status'        => 'publish',
            'post_author'        => 1,
            'post_type'          => 'ordin',
            'comment_status'     => 'closed',
            'post_date'          => $date,
            'post_date_gmt'      => $date
        );

        wp_update_post( $post_data );
        add_action('save_post_ordin', 'ordin_save');
    }
}


/*
 * Archive ordins shortcode
 */
function footag_func( $atts ){
    global $wpdb;
    $opt = "";
    $geturl = "";
    $firsturl = "";

    if(isset($_GET['href'])){
        $ordin_id = $_GET['href'];
        $table_name2 = $wpdb->prefix . 'posts';
        $query = "SELECT post_excerpt FROM $table_name2 WHERE ID = " . $ordin_id;
        $results = $wpdb->get_results( $query );
        $geturl = $results[0]->post_excerpt;
    }

    echo "<div class='pdf-archive-page'>";

        $query = new WP_Query( array(
            'post_type' => 'ordin',
            'post_status' => 'publish',
            'orderby' => array( 'modified' => 'DESC', 'title' => 'DESC' ),
            'fields' => 'ids',
            'posts_per_page' => -1
        ) );

        echo "<select class='select2'>";
            while ( $query->have_posts() ) {
                $query->the_post();
                $title = get_the_title();
                $data = get_the_date();
                $url = get_the_excerpt();
                if ( $firsturl == "" ) {
                    $firsturl = $url;
                }

                if ( parse_url( $url, PHP_URL_HOST) == '' ) {
                    $url = get_site_url() . $url;
                }

                if ( isset($geturl) and $url == $geturl) {
                    $opt = 'selected="selected"';
                    $firsturl = $url;
                } 

                $option = $title . " - " . $data;

            ?>

            <option value="<?php echo $url; ?>" <?php echo $opt; ?>><?php echo $option; ?></option>

            <?php
                $opt = "";
            }
        echo "</select>";


    echo "<div class='pdf-viewer'>";
    echo '<iframe id="pdfFrame" name="pdfFrame" src="' . $firsturl . '" width="100%"></iframe>';
    echo "</div>";
    echo "<div class='pdf-info'>";
    echo '<a target="blank" class="download" href="' . $firsturl . '">Скачать приказ</a>';
    echo "</div>";
    echo "</div>";

}
add_shortcode('page_ordins', 'footag_func');


//include widget file
include "widgets/ordin_widget.php";
include "widgets/ordin_form_widget.php";

//include optiona page file
include "ordin_option.php";