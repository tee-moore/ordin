<?php
$true_page = 'ordinoptions.php';
$other_attributes = [];

/**
 * Создаем страницу настроек плагина
 */
add_action('admin_menu', 'add_plugin_page');
function add_plugin_page(){
    global $true_page;
    add_submenu_page( 'edit.php?post_type=ordin', 'Параметры', 'Параметры', 'manage_options', $true_page, 'primer_options_page_output' );
}

function primer_options_page_output(){
    global $true_page;
    ?>
    <div class="wrap">

        <?php
        if (isset($_POST)){
            if( !empty($_POST) && check_admin_referer('name_of_my_action','name_of_nonce_field') ) {
                if(isset($_POST['save_options'])){
                    echo save_options($_POST['ordin_page_url']);
                }
                if(isset($_POST['delete_ordin'])){
                    echo delete_ordin();
                }
                if(isset($_POST['add_ordin'])){
                    echo add_ordin();
                }
                if(isset($_POST['add_profile'])){
                    echo add_profile();
                }
            }
        }
        $options = get_option( 'ordin_options', null );
        $url = $options['url'];
        $other_attributes = [];
        ?>
        <form action="" method="POST">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">URL страницы с приказами, которые нужно импортировать на сайт:</th>
                        <td>
                            <input name="ordin_page_url" size="70" value="<?php echo $url;?>" type="text">
                            <!-- http://cetatenie.just.ro/index.php/ro/ordine/articol-11 -->
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
            wp_nonce_field('name_of_my_action','name_of_nonce_field');
            submit_button( 'Сохранить изменения', 'primary', 'save_options', true, $other_attributes );
            ?>
        </form>

        <form action="" method="POST">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"></th>
                        <td>

                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
            wp_nonce_field('name_of_my_action','name_of_nonce_field');
            submit_button( 'Удалить все приказы', 'delete', 'delete_ordin', false, $other_attributes );
            ?>
        </form>

        <form action="" method="POST">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"></th>
                        <td>

                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
            wp_nonce_field('name_of_my_action','name_of_nonce_field');
            submit_button( 'Импортировать приказы', 'profile', 'add_ordin', false, $other_attributes );
            ?>
        </form>

<!--         <form action="" method="POST">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"></th>
                        <td>

                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
            //wp_nonce_field('name_of_my_action','name_of_nonce_field');
            //submit_button( 'Импортировать досье', 'profile', 'add_profile', false, $other_attributes );
            ?>
        <!-- </form> -->
    </div>
    <?php
}



function save_options( $url ) {
    $options = [];
    if(!empty($url)){
        $options['url'] = $url;
    }

    $updated = update_option( 'ordin_options', $options, false );

    if($updated) {
        return message("Настройки сохранены.", "updated");
    } else {
        return message("Настройки не менялись.", "error");
    }
}



function delete_ordin() {
    global $wpdb;

    $table_name_profile = $wpdb->prefix . 'ordin_profile';
    $wpdb->query("TRUNCATE $table_name_profile;");

    $query = new WP_Query( array(
        'post_type' => 'ordin',
        'post_status' => 'any',
        'fields' => 'ids',
        'posts_per_page' => -1
    ) );

    if ($query->have_posts()){

        while ( $query->have_posts() ) {
            $query->the_post();
            $deleted = wp_delete_post( get_the_ID(), true );
        }

        return message("Все приказы удалены.", "updated");
    } else {
        return message("Приказов в базе нет.", "update-nag");
    }
}



function add_ordin() {
    global $wpdb;
    require( 'vendor/phpQuery/phpQuery.php' );

    $options = get_option( 'ordin_options', null );
    $url = $options['url'];
    $html = file_get_contents( $url );
    $count_ordin = 0;

    phpQuery::newDocument( $html );
    $list = pq( '.item-page ul' )->children( 'li' );

    foreach ( $list as $li ) {
        $li = pq( $li );
        $data_text = $li->find( 'strong' )->text();
        $data_text = explode( ',', $data_text );
        $new_date_text = preg_replace( "/[^0-9.]/", "", $data_text[0] );
        $new_date_text = trim( $new_date_text );
        $date_data = strtotime( $new_date_text );
        $data = date("Y-m-d H:i:s", $date_data );
        $links = $li->find( 'a' );

        foreach ( $links as $link ) {

            $text = pq( $link )->html();
            $text = wp_strip_all_tags( $text, true );
            $oldtext = $text;
            $newtext = preg_match( "/[\d]+/", $text );
            $href = pq( $link )->attr( 'href' );
            $href = "http://cetatenie.just.ro" . $href;

            unset($result);
            unset($query);
            $query = "SELECT * FROM $wpdb->posts WHERE post_title = '$text' AND post_date = '$data' AND post_status = 'publish' AND post_type = 'ordin'";
            $result = $wpdb->get_row( $query, ARRAY_A );

            if (!$result['post_title']){

                if( $newtext != false ) {
                    $post_data = array(
                        'post_title'         => $text,
                        'post_content'       => '',
                        'post_excerpt'       => $href,
                        'post_status'        => 'publish',
                        'post_author'        => 1,
                        'post_type'          => 'ordin',
                        'comment_status'     => 'closed',
                        'post_date'          => $data,
                        'post_date_gmt'      => $data,
                        'post_modified'      => $data,
                        'post_modified_gmt'  => $data
                    );

                    remove_action('save_post_ordin', 'ordin_save');
                    $ordin_id = wp_insert_post( $post_data );
                    $count_ordin++;
                    add_action('save_post_ordin', 'ordin_save');
                    add_profile( $ordin_id, $href );
                    //update_option( 'ordin_last_time_update', current_time('Y-m-d H:i:s'), 'no' );
                    //echo "Base: " . $result['post_title'] . " - " . $result['post_date'] . " - " . "<br>";
                    //echo "Add: $count_ordin) " . $text . " - " . $new_date_text . " - " . $date_data . " - " . $data . "<br><br><br>";
                }
            }
        }
    }

    phpQuery::unloadDocuments();
    return message( "Импортировано приказов: $count_ordin.", "updated" );
}


function add_profile( $ordin_id = 0, $href = 'http://cetatenie.just.ro/images/ordin.221P.din.27.04.2011.pdf'){

    include 'vendor/autoload.php';
    ini_set('max_execution_time', 0);
    $check_pdf = substr( $href, -3);

    if( 'pdf' != $check_pdf ){
        return;
    }

    unset($parser);
    unset($pdf);
    $file_exist = file_get_contents( $href );
    if($file_exist) {

        $parser = new \Smalot\PdfParser\Parser();
        $pdf    = $parser->parseFile( $href );

        $text = $pdf->getText();
        $arr_text = explode( "\n", $text );
        $j = 0;
        foreach ($arr_text as $text) {
            if(preg_match( "~\(.+\/.+\)~", $text )){
                $j++;
                //extract values:
                $parts_text = explode("(", $text);
                $parts_text2 = explode(".", $parts_text[0]);
                //numer
                // preg_match( "~^\d+~", $parts_text[0], $matches);
                $numer = $parts_text2[0];
                $numer = preg_replace("~[^\d]~"," ", $numer);
                //name
                $name = $parts_text2[1];
                $name = preg_replace("~\s{2,}~"," ", $name);
                $name = trim($name);
                $name = explode(" ", $name);
                //firstname
                $firstname = preg_replace("~\s{2,}~"," ", $name[1]);
                //secondname
                $secondname = preg_replace("~\s{2,}~"," ", $name[0]);


                $parts_for_parts_text = explode("/", $parts_text[1]);
                //profile
                $profile = $parts_for_parts_text[0];
                $profile = preg_replace("~\s{2,}~"," ", $parts_for_parts_text[0]);
                //year
                $year = explode( ")", $parts_for_parts_text[1] );
                $year = preg_replace("~\s{2,}~"," ", $year);
                $year = $year[0];
                //echo $text . "<br>";
                //echo $numer . " - ;" . $name . "; - " . $profile . " - " . $year . "<br><br>";
                //echo $numer . "-" . $firstname . "-" . $secondname . "-" . $profile . "-" . $year . "--$j--" .$ordin_id . "<br><br>";
                ordin_insert_profile( $numer, $ordin_id, $firstname, $secondname, $profile, $year);
                unset($numer);
                unset($firstname);
                unset($secondname);
                unset($profile);
                unset($year);
            }
        }
    } else {
        echo "ошибка: '" . $href . "'";
    }
}


function message( $str, $state ) {
    return '<div id="message" class="' . $state . ' notice is-dismissible"><p>' . $str . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Скрыть это уведомление.</span></button></div>';
}


// =======
//  CRON
// =======

// add time
// add_filter( 'cron_schedules', 'cron_add_time' );
// function cron_add_time( $schedules ) {
//     $schedules['3m'] = array(
//         'interval' => 60*3,
//         'display' => __( '3m' )
//     );
//     return $schedules;
// }


add_action('wp', 'ordin_activation');
function ordin_activation() {
    $unixstamp = mktime( 13, 0, 0, 1, 9, 2018 );
    $fix       = HOUR_IN_SECONDS * 2;
    $kyiv_stamp = $unixstamp - $fix;
    if( ! wp_next_scheduled( 'my_daily_event' ) ) {
        wp_schedule_event( $kyiv_stamp, 'hourly', 'my_daily_event');
    }
}

// add func to hook
add_action('my_daily_event', 'do_this_daily');
function do_this_daily() {
    add_ordin();
}

// // remove hook
register_deactivation_hook( __FILE__, 'ordin_deactivation');
function ordin_deactivation() {
    wp_clear_scheduled_hook('my_daily_event');
}


// =======
// PROFILE
// =======
register_activation_hook( __FILE__, 'call_add_profile_table' );
add_action( 'plugins_loaded', 'call_add_profile_table' );


function call_add_profile_table()
{
    global $wpdb;
    $table_name = get_profile_table_name();
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        add_profile_table();
    }
}


function get_profile_table_name() {
    global $wpdb;
    return $table_name = $wpdb->prefix . 'ordin_profile';
}


function add_profile_table() {
    global $wpdb;
    $table_name = get_profile_table_name();
    $charset_collate = "DEFAULT CHARACTER SET " . $wpdb->charset . " COLLATE " . $wpdb->collate .";";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $sql = "CREATE TABLE " . $table_name . " (
        id int(11) unsigned NOT NULL auto_increment,
        numer int(4) unsigned NOT NULL default '0',
        ordin_id int(11) unsigned NOT NULL default '0',
        firstname varchar(20) NOT NULL default '',
        secondname varchar(20) NOT NULL default '',
        profile int(5) unsigned NOT NULL default '0',
        year year(4) NOT NULL,
        PRIMARY KEY  (id),
        KEY profile (profile,year)
    ) " . $charset_collate . ";";

    $str = dbDelta( $sql );
    print_r($str);
}


function ordin_insert_profile( $numer, $ordin_id, $firstname, $secondname, $profile, $year) {
    global $wpdb;
    $numer = esc_sql($numer);
    $ordin_id = esc_sql($ordin_id);
    $firstname = esc_sql($firstname);
    $secondname = esc_sql($secondname);
    $profile = esc_sql($profile);
    $year = esc_sql($year);
    $table_name = get_profile_table_name();
 
    return $wpdb->insert(
        $table_name, array(
            'numer' => $numer,
            'ordin_id' => $ordin_id,
            'firstname' => $firstname,
            'secondname' => $secondname,
            'profile' => $profile,
            'year' => $year
        ), array('%d', '%d', '%s', '%s', '%d', '%s' )
    );
}


add_action('wp_ajax_ordin_find', 'my_action_callback');
add_action('wp_ajax_nopriv_ordin_find', 'my_action_callback');
function my_action_callback() {
    global $wpdb;
    $html = "<p class='ordin-find-title'>Результат поиска: </p>";
    //$wpdb->show_errors();
    $fullname = esc_sql(trim($_POST['fullname']));
    $profile = esc_sql(trim($_POST['profile']));
    $name = explode(" ", $fullname);
    $secondname = $name[0];
    $firstname = $name[1];

    $table_name1 = $wpdb->prefix . 'ordin_profile';
    $table_name2 = $wpdb->prefix . 'posts';

    if(!empty($profile)){
        if(count($name) == 1) {
            $query = "SELECT $table_name1.*, $table_name2.ID, $table_name2.post_title, $table_name2.post_date 
              FROM $table_name1 inner join $table_name2 on $table_name1.ordin_id=$table_name2.ID
              WHERE (secondname LIKE '%{$secondname}%' AND profile = $profile) ORDER BY $table_name2.post_date DESC";
        } else {
            $query = "SELECT $table_name1.*, $table_name2.ID, $table_name2.post_title, $table_name2.post_date 
              FROM $table_name1 inner join $table_name2 on $table_name1.ordin_id=$table_name2.ID
              WHERE (firstname LIKE '%{$firstname}%' AND profile = $profile) OR (secondname LIKE '%{$secondname}%' AND profile = $profile) ORDER BY $table_name2.post_date DESC";
        }
    } else {
        if(count($name) == 1) {
            $query = "SELECT $table_name1.*, $table_name2.ID, $table_name2.post_title, $table_name2.post_date 
              FROM $table_name1 inner join $table_name2 on $table_name1.ordin_id=$table_name2.ID
              WHERE (secondname LIKE '%{$secondname}%') ORDER BY $table_name2.post_date DESC";
        } else {
            $query = "SELECT $table_name1.*, $table_name2.ID, $table_name2.post_title, $table_name2.post_date 
              FROM $table_name1 inner join $table_name2 on $table_name1.ordin_id=$table_name2.ID
              WHERE (secondname LIKE '%{$secondname}%') AND (firstname LIKE '%{$firstname}%') ORDER BY $table_name2.post_date DESC";
        }
    }

    //$query = "SELECT * FROM " . $table_name . " WHERE LOWER(secondname) LIKE '%" . strtolower($secondname) . "%'";
    $results = $wpdb->get_results( $query );

    //$results_data = explode(" ", $result->post_date );

    if($results){
        $html .= "<table><thead><tr><th>Ф.И.О.</th><th>Приказ</th><th>Дата</th></tr>";
        foreach ( $results as $result ) {
            $results_data = strtotime( trim($result->post_date) );
            $results_data = date("d.m.Y", $results_data );
            $html .= "<tr><td>" . ucfirst(strtolower($result->secondname)) . " " . ucfirst(strtolower($result->firstname)) . "</td><td><a href='/orders/?href=" . $result->ID . "'>" . $result->post_title . "</a></td><td>" . $results_data . "</td></tr>";
        }
        $html .= "</thead></table>";
    } else {
         $html = "<p class='ordin-find-list'>Нет результатов. попробуйте изменить запрос</p>";
    }

    echo $html;
    wp_die();
}




function get_my_profile( $profile_id ) {
    // global $wpdb;
    // $table_name = get_profile_table_name();

    // $product = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE `id` = %d LIMIT 1;", $product_id ) );
    // return $product;
}


//813P
//http://cetatenie.just.ro/wp-content/documents/ordine/2012/Ordin%20813P%20din%2005.09.2012.pdf