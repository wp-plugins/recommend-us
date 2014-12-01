<?php
/*
Plugin Name: Recommend Us
Plugin URI: http://microsolutionsbd.com/
Description: Recommend Us empowers you to easily capture client recommendations for your business, website and display them on your Wordpress page or post.
Version: 1.0.0
Author: Shah Alom
Author URI: http://shahalom.microsolutionsbd.com/
Text Domain: recommend-us
License: GPL2
*/


define('MSBD_RCMND_PLGN_ROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('MSBD_RCMND_PLGN_URL', trailingslashit(plugins_url(basename(dirname(__FILE__)))));

class RecommendUs {

    var $sqltable = 'msbd_recommends';
    var $fp_admin_options = 'msr_admin_options';

    var $admin;
    var $db;
    
    var $form_counter = 1;
    var $form_submitted = 0;

    /**
     * @var MSBDOptions
     */
    var $options;

    /**
     * The variable that stores all current options
     */
    var $msr_options;

    function __construct() {
        
        global $wpdb;
        $this->sqltable = $wpdb->prefix . $this->sqltable;
        
        $this->options_name = 'msr_options';
        
        $this->options= new MSBDOptions($this);
        $this->db = new RecommendUsDB($this);
        $this->admin = new RecommendUsAdmin($this);

        add_action('plugins_loaded', array(&$this, 'on_load'));
        add_action('init', array(&$this, 'init'));
        add_action('wp_enqueue_scripts', array(&$this, 'load_scripts_styles'), 100);

        add_shortcode('MSBD_RECOMMENDS_FORM', array(&$this, 'shortcode_reviews_form'));
        add_shortcode('MSBD_RECOMMENDS_SHOW', array(&$this, 'shortcode_reviews_show'));
        
        add_filter('widget_text', 'do_shortcode'); // Adding option to execute shortcodes from widget
    }

    function init() {
        $this->options->update_options();
        $this->msr_options = $this->options->get_option();
    }


    function load_scripts_styles() {        
        wp_register_script('recommend-us', MSBD_RCMND_PLGN_URL . 'js/recommend-us.js', array('jquery'));
        wp_enqueue_script('recommend-us');
        
        $add_glyphicons = $this->msr_options['add_glyphicons'];
        if($add_glyphicons) {
            wp_register_style('recommend-us-glyphicons', MSBD_RCMND_PLGN_URL . 'css/glyphicons.css');
            wp_enqueue_style('recommend-us-glyphicons');
        }
        
        $add_msrp_styles = $this->msr_options['add_msrp_styles'];
        if($add_msrp_styles) {
            wp_register_style('recommend-us', MSBD_RCMND_PLGN_URL . 'css/recommend-us.css');
            wp_enqueue_style('recommend-us');
        }
    }
    

    function star_rating_input() {
        $output = '
            <span class="label_star glyphicon glyphicon-star-empty" id="label_star_1"></span>
            <span class="label_star glyphicon glyphicon-star-empty" id="label_star_2"></span>
            <span class="label_star glyphicon glyphicon-star-empty" id="label_star_3"></span>
            <span class="label_star glyphicon glyphicon-star-empty" id="label_star_4"></span>
            <span class="label_star glyphicon glyphicon-star-empty" id="label_star_5"></span>
        ';
        return __($output, 'recommend-us');
    }



    function shortcode_reviews_form($atts) {
        global $wpdb;
        
        $output = '';
        $rName  = '';
        $rEmail = '';
        $rTitle = '';
        $rText  = '';
        $displayForm = true;
        
        if (isset($_POST['submitted']) && $this->form_submitted==0) {
            
            $this->form_submitted++; //Flag that the submitted data process once
            
            if ($_POST['submitted'] == 'Y') {
                $rDateTime = date('Y-m-d H:i:s');
                $rName     = $this->msbd_sanitization($_POST['rName']);
                $rEmail    = $this->msbd_sanitization($_POST['rEmail']);
                $rTitle    = $this->msbd_sanitization($_POST['rTitle']);
                $rRating   = $this->msbd_sanitization($_POST['rRating']);
                $rText     = $this->msbd_sanitization($_POST['rText'], 'textarea');
                $rStatus   = 0; // Need to approve from admin
                $rIP       = $_SERVER['REMOTE_ADDR'];

                $newdata = array(
                        'date_time'       => $rDateTime,
                        'recommend_by_name'   => $rName,
                        'recommend_by_email'  => $rEmail,
                        'recommendation_title'    => $rTitle,
                        'rating_number'   => intval($rRating),
                        'recommendation_text'     => $rText,
                        'recommendation_status'   => $rStatus,
                        'recommend_by_ip'     => $rIP
                );
                $validData = true;
                if ($rName == '') {
                    $output .= 'You must include your name.';
                    $validData = false;
                } else if ($rTitle == '') {
                    $output .= 'You must include a title for your review.';
                    $validData = false;
                } else if ($rText == '') {
                    $output .= 'You must write some text in your review.';
                    $validData = false;
                } else if ($rRating == 0) {
                    $output .= 'Please give a rating between 1 and 5 stars.';
                    $validData = false;
                } else if ($rEmail != '') {
                    $firstAtPos = strpos($rEmail,'@');
                    $periodPos  = strpos($rEmail,'.');
                    $lastAtPos  = strrpos($rEmail,'@');
                    if (($firstAtPos === false) || ($firstAtPos != $lastAtPos) || ($periodPos === false)) {
                        $output .= 'You must provide a valid email address.';
                        $validData = false;
                    }
                }
                if ($validData) {
                    if ((strlen($rName) > 100)) {
                        $output .= 'The name you entered was too long, and has been shortened.<br />';
                    }
                    if ((strlen($rTitle) > 150)) {
                        $output .= 'The review title you entered was too long, and has been shortened.<br />';
                    }
                    if ((strlen($rEmail) > 100)) {
                        $output .= 'The email you entered was too long, and has been shortened.<br />';
                    }
                    $wpdb->insert($this->sqltable, $newdata);
                    $output .= 'Thank you '.$this->nice_output($rName).'! Your recommendation has been recorded and submitted for approval!<br />';
                    $displayForm = false;
                }
            }
        }
        
        if ($displayForm) {
        
            ob_start();
            ?>
            <form action="" class="ms_rcmnd_form" id="ms_rcmnd_form_<?= $this->form_counter; ?>" method="post">
                <input type="hidden" name="submitted" value="Y" />
                <input type="hidden" name="rRating" id="rRating" value="0" />    
                <div class="form_row">
                    <div class="grid_6 alpha"><input type="text" class="text" placeholder="Name *" name="rName"></div>    
                    <div class="grid_6 omega"><input type="text" class="text" placeholder="Email Address *" name="rEmail"></div>
                </div>    
                <div class="form_row">
                    <input type="text" class="text" placeholder="Recommendation Title *" name="rTitle" />
                </div>    
                <div class="form_row tcenter">
                    <?php echo $this->star_rating_input(); ?>
                </div>    
                <div class="form_row">
                    <textarea rows="8" class="textarea" placeholder="Write Your Recommendation *" name="rText"></textarea>
                </div>    
                <div class="form_row">
                    <input type="submit" value="Submit" name="send" class="btn-black" />
                </div>
                <span></span>
            </form>                                    
            <?php           
            $output = ob_get_clean();
            
            $this->form_counter++;
        }
        
        return __($output, 'recommend-us');
    }
    
    
    
    function shortcode_reviews_show($atts) {
        
        global $wpdb;
        
        $output = '';
        extract(shortcode_atts(
            array(
                'num' => '5',
            )
        , $atts));        
        
        $this->db->where('recommendation_status', 1);        
        
        if (intval($num)>0) {
            $num = intval($num);
            if ($num < 1) { $num = 1; }
            $this->db->limit($num);
            
            $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
            $offset = ( $pagenum - 1 ) * $num;
            $this->db->offset($offset);
        }
        
        // Set up the Order BY
        if ($this->msr_options['reviews_order'] === 'random') {
            $this->db->order_by('rand()');
        }
        else {
            $this->db->order_by('date_time', $this->msr_options['reviews_order']);
        }
        
        $entries = $this->db->get();        
        
        ob_start();
    ?>
        <div class="recommendation_group">     
            <?php 
            if( $entries ) {            
                foreach( $entries as $entry ) {
                    echo $this->build_recommendation($entry);
                }
            } else { 
            ?>
                <h3 class="msrp_title">No Recommendation yet</h3>
            <?php 
            }
            
            echo $this->print_credit();
            ?>
        </div>
        <!-- /.recommendation_group -->
    <?php
        $output = ob_get_clean();
        echo __($output, 'recommend-us');
        
        /*Start Pagination */
        if (intval($num)>0) {            
            $total =  $this->db->approved_reviews_count();
        
            $num_of_pages = ceil( $total / $num );
            $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'aag' ),
                'next_text' => __( '&raquo;', 'aag' ),
                'total' => $num_of_pages,
                'current' => $pagenum
            ) );

            if ( $page_links ) {
                $output = '<div class="msrp-paging">' . $page_links . '</div>';            
                echo __($output, 'recommend-us');
            }
        }
    }


    function build_recommendation($recommendation) {
        $rID        = $recommendation->id;
        $rDateTime  = date("jS F, Y", strtotime($recommendation->date_time));  //date("DD m, YY", strtotime($recommendation->date_time));
        $rName      = $this->nice_output($recommendation->recommend_by_name, FALSE);
        $rEmail     = $this->nice_output($recommendation->recommend_by_email, FALSE);
        $rTitle     = $this->nice_output($recommendation->recommendation_title, FALSE);
        $rRatingVal = max(1,intval($recommendation->rating_number));
        $rText      = $this->nice_output($recommendation->recommendation_text);
        $rStatus    = $recommendation->recommendation_status;
        $rIP        = $recommendation->recommend_by_ip;
               
        $goldenStar='';
        $blackStar='';
        
        for ($i=1; $i<=$rRatingVal; $i++) {
            $goldenStar .= '&#9733;'; // orange star
        }
        for ($i=$rRatingVal+1; $i<=5; $i++) {
            $blackStar .= '&#9733;'; // white star
        }

        $newRatings = '<span class="stars">'.$goldenStar.'</span>';
        $newRatings .= '<span class="black_stars">'.$blackStar.'</span>';
        
        $output = '<div class="wrap_recommendation">
            <h2 class="msrp_title">' . $rTitle . '</h2>';
       
        $output .= '<div class="msrp_stars">' . $newRatings . '</div>';
        $output .= '<div class="msrp_recommendation_text">' . stripslashes($rText) . '</div>';
        
        $output .= '<div class="msrp_recommendation_name"> - ' . $rName . '</div>';
        
        $show_date = $this->msr_options['show_date'];
        if($show_date) {
            $output .= '<div class="msrp_recommendation_date">Recommended on: ' . $rDateTime . '</div>';
        }
        
        $output .= '<div class="clearfix"></div>';
            
        $output .= '</div>';
        return __($output, 'recommend-us');
    }
    

    function print_credit() {
        $permission = $this->msr_options['ms_give_credit'];
        $output = "";
        if ($permission) {
            $output = '<div class="credit-line">Supported By: <a href="https://www.microsolutionsbd.com/" rel="nofollow"> Micro Solutions Bangladesh</a>';
            $output .= '</div>' . PHP_EOL;
        }
        return __($output, 'recommend-us');
    }


    function on_load() {
        $plugin_dir = basename(dirname(__FILE__));
        load_plugin_textdomain( 'recommend-us', false, $plugin_dir );
    }




    function msbd_sanitization($data, $field_type='text', $allowedHtmlTag='') {        
        if (is_array($data)) {
            foreach($data as $var=>$val) {
                $output[$var] = $this->msbd_sanitization($val, $field_type, $allowedHtmlTag);
            }
        }
        else {

            switch($field_type) {
                case 'text':
                    $output = sanitize_text_field($data);
                    break;
                
                case 'email':
                    $output = sanitize_email($data);
                    break;
                    
                case 'textarea':                    
                    $allowedHtmlTags = $this->msbd_define_kses_html_tags($this->msr_options['ms_allowed_html_tags']);
                                        
                    $output = wp_kses($data, $allowedHtmlTags);
                    break;
                
                default:
                    $output = addslashes($data);
                    break;
            }
        }
        
        return $output;
    }



    function msbd_define_kses_html_tags($csv_tags) {
        $csvArray = explode(',', $csv_tags);
                    
        $tagsArray=array();
        foreach($csvArray as $i=>$v) {                        
            switch($v) {
                case 'img':
                    $tagsArray[$v] = array(
                                            'src' => array(),
                                            'width' => array(),
                                            'height' => array()
                                        );
                    break;
                
                case 'a':
                    $tagsArray[$v] = array(
                                            'href' => array(),
                                            'title' => array()
                                        );
                    break;
                    
                case 'div':
                case 'span':
                    $tagsArray[$v] = array(
                                            'class' => array()
                                        );
                    break;
                        
                default:
                    $tagsArray[$v] = array();
                    break;
            }
        }
        
        return $tagsArray;
    }


    function nice_output($input, $keep_breaks = TRUE) {        

        return $input;
    }

    function clean_input($input) {
        $handling = $input;

        $handling = sanitize_text_field($handling);
        $handling = stripslashes($handling);

        $output = $handling;
        return $output;
    }

}

// Define the "dump" function, a debug helper.
if (!function_exists('dump')) {
    function dump ($var, $label = 'Dump', $echo = TRUE) {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        $output = '<pre style="background: #FFFEEF; color: #000; border: 1px dotted #000; padding: 10px; margin: 10px 0; text-align: left;">' . $label . ' => ' . $output . '</pre>';
        
        if ($echo == TRUE) {echo $output;}else {return $output;}
    }
}
if (!function_exists('dump_exit')) {
    function dump_exit($var, $label = 'Dump', $echo = TRUE) {
        dump ($var, $label, $echo);
        exit;
    }
}

if (!class_exists('MSBDRecommendUsAdminHelper')) {
    require_once('views/view-helper/admin-view-helper-functions.php');
}

if (!class_exists('MSBDDB')) {
    require_once('lib/msbd-db.php');
}
if (!class_exists('MSBDOptions')) {
    require_once('lib/msbd-options.php');
}
require_once('lib/recommend-us-admin.php');
require_once('lib/recommend-us-db.php');
require_once("views/admin-add-edit-view.php");

global $recommendUs;
$recommendUs = new RecommendUs();
