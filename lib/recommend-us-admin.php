<?php
/*
 * The admin stuffs are gathered here
 */

class RecommendUsAdmin {

    var $parent;
    var $db;

    function __construct($parent) {
        $this->parent = $parent;
        $this->db = $this->parent->db;
        add_action('admin_menu', array(&$this, 'init_admin_menu'));
        add_action( 'admin_enqueue_scripts', array(&$this, 'load_admin_scripts_styles'), 100);
        add_filter('plugin_action_links_recommend-us/recommend-us.php', array(&$this, 'add_plugin_settings_link'));
    }

    function init_admin_menu() {
        global $wpdb;
        
        $pendingReviewsCount = $this->db->pending_reviews_count();
        $pendingReviewsText = '';
        $menuTitle = '';
        
        if ($pendingReviewsCount != 0) {
            $pendingReviewsText = ' (' . $pendingReviewsCount . ')';
        }
        
        $required_role = $this->parent->options->get_option('ms_authority_label');
        
        add_menu_page(
            'Recommend Us Settings',
            'Recommend Us' . $pendingReviewsText,
            $required_role,
            'msrp_settings_main',
            array(&$this, 'render_settings_main_page'),
            MSBD_RCMND_PLGN_URL.'images/msbd_favicon_16.png',
            '25.11'
        );
        
        add_submenu_page(
            'msrp_settings_main', // ID of menu with which to register this submenu
            'Recommend Us - Instructions', //text to display in browser when selected
            'Instructions', // the text for this item
            $required_role, // which type of users can see this menu
            'msrp_settings_main', // unique ID (the slug) for this menu item
            array(&$this, 'render_settings_main_page') // callback function
        );
        
        add_submenu_page(
            'msrp_settings_main',
            'Recommend Us - Pending Recommendations',
            'Pending Recommendations' . $pendingReviewsText,
            $required_role,
            'fp_admin_pending_recommendation_page',
            array(&$this, 'render_pending_recommendations_page')
        );
        add_submenu_page(
            'msrp_settings_main',
            'Recommend Us - Approved Recommendations',
            'Approved Recommendations',
            $required_role,
            'fp_admin_approved_recommendation_page',
            array(&$this, 'render_approved_recommendations_page')
        );
        add_submenu_page(
            'msrp_settings_main',
            'Recommend Us - Options',
            'Options',
            $required_role,
            'fp_admin_options_page',
            array(&$this, 'render_options_page')
        );
        add_submenu_page(
            'msrp_settings_main',
            'Recommend Us - Add/Edit',
            'Add Recommendation',
            $required_role,
            'msrp_admin_add_edit',
            array(&$this, 'render_add_edit_page')
        );
    }

    function load_admin_scripts_styles() {
        wp_register_script('recommend-us', MSBD_RCMND_PLGN_URL . 'js/recommend-us.js', array('jquery'));
        wp_enqueue_script('recommend-us');
        
        wp_register_script('recommend-us-dashboard', MSBD_RCMND_PLGN_URL . 'views/view-helper/js/msbd-dashboard-script.js', array('jquery'));
        wp_enqueue_script('recommend-us-dashboard');
        
        wp_register_style('recommend-us-admin', MSBD_RCMND_PLGN_URL . 'css/recommend-us-admin.css');
        wp_enqueue_style('recommend-us-admin');
    }




    function wrap_admin_page($page = null) {
        
        $page_header = '';
        switch($page) {
            case 'main':
                $page_header = 'Instructions';
                break;
            
            case 'pending':
                $page_header = 'Pending Recommendations';
                break;
                
            case 'approved':
                $page_header = 'Approved Recommendations';
                break;
                
            case 'options':
                $page_header = 'Plugin Options';
                break;
                
            case 'add/edit':
                $page_header = 'Add/Edit Recommendation';
                break;
        }
        
        echo '<div class="msbd-admin-page wrap"><h2><img src="' . MSBD_RCMND_PLGN_URL . 'images/msbd_favicon_32.png" /> ' . $page_header . '</h2></div>';
        
        MSBDRecommendUsAdminHelper::render_tabs();
        MSBDRecommendUsAdminHelper::render_container_open('content-container');
        
        if ($page == 'main') {
            MSBDRecommendUsAdminHelper::render_postbox_open('Instructions');
            echo $this->render_settings_main_page(TRUE);
            MSBDRecommendUsAdminHelper::render_postbox_close();
        }
        
        if ($page == 'pending') {
            MSBDRecommendUsAdminHelper::render_postbox_open('Pending Recommendations');
            echo $this->render_pending_recommendations_page(TRUE);
            MSBDRecommendUsAdminHelper::render_postbox_close();
        }
        
        if ($page == 'approved') {
            MSBDRecommendUsAdminHelper::render_postbox_open('Approved Recommendations');
            echo $this->render_approved_recommendations_page(TRUE);
            MSBDRecommendUsAdminHelper::render_postbox_close();
        }
        if ($page == 'options') {
            MSBDRecommendUsAdminHelper::render_postbox_open('Options');
            echo $this->render_options_page(TRUE);
            MSBDRecommendUsAdminHelper::render_postbox_close();
        }
        
        if ($page == 'add/edit') {
            MSBDRecommendUsAdminHelper::render_postbox_open('Add/Edit');
            echo $this->render_add_edit_page(TRUE);
            MSBDRecommendUsAdminHelper::render_postbox_close();
        }
        
        MSBDRecommendUsAdminHelper::render_container_close();
        MSBDRecommendUsAdminHelper::render_container_open('sidebar-container');
        
        MSBDRecommendUsAdminHelper::render_sidebar();
        MSBDRecommendUsAdminHelper::render_container_close();
        echo '<div class="clear"></div>';
    }


    function add_plugin_settings_link($links) {
        $settings_link = '<a href="admin.php?page=msrp_settings_main">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    function render_settings_main_page($wrapped = false) {
        if (!$wrapped) {
            $this->wrap_admin_page('main');
            return;
        }
        
        $output = '
            <div class="msrp_admin_sidebar">                
                <div class="msrp_admin_sidebar_text">
                    <p>This plugin is based around shortcodes. We think that this is the best way to go, as then YOU control where recommendations, forms are shown - pages, posts, widgets... wherever! For batter usage make sure you read the detailed descriptions of how these work below, in <span style="font-weight: 600;">Shortcode Usage</span>!</p>
                </div>
                
                <div class="msrp_admin_sidebar_text">
                    <div class="msrp_admin_sidebar_title">[MSBD_RECOMMENDS_SHOW]</div>             
                    <p><i>[MSBD_RECOMMENDS_SHOW]</i> is the main shortcode for this plugin. By default (if no parameters are given), it will show the first five approved recommendations with paginations.</p>
                    
                    <p>Using parameter <strong>num</strong> the following works could be done!</p>
                    <ul class="msrp_admin_sidebar_list">
                        <li class="msrp_admin_sidebar_list_item"><i>[MSBD_RECOMMENDS_SHOW num="1"]</i>, <i>[MSBD_RECOMMENDS_SHOW num="5"]</i> using numbers into the num parameter will show the number of recommendation to every recommendation page.</li>
                        <li class="msrp_admin_sidebar_list_item"><i>[MSBD_RECOMMENDS_SHOW num="all"]</i> using <strong>all</strong> into the num parameter will show all recommendations without pagination.</li>   
                    </ul>
                </div>
                
                <div class="msrp_admin_sidebar_text">
                    <div class="msrp_admin_sidebar_title">[MSBD_RECOMMENDS_FORM]</div>             
                    <p><i>[MSBD_RECOMMENDS_FORM]</i> shortcode helps you to add recommendation submission form to any page or in an widget! Currently it does not get any parameter!</p>
                </div>
                
                <div class="msrp_admin_sidebar_text">
                    <p>Thank you for using Recommend Us by <a href="http://shahalom.microsolutionsbd.com/">Shah Alom</a> and <a href="http://microsolutionsbd.com">Micro Solutions Bangladesh</a>!</p>
                </div>
            </div>';
        
        echo $output;
    }
    
    

    function render_rr_show_content() {
        $output = '<div class="msrp_shortcode_container">
                <div class="msrp_shortcode_name">[MSBD_RECOMMENDS_SHOW]</div>
                <div class="msrp_shortcode_description">
                    This is the main shortcode for this plugin. By default (if no options are given), it will show the first three global reviews which have been approved. Note that this shortcode on its own will NOT display an average/overall score nor any rich snippet markup. See the "snippet" shortcode for that. Here is the shortcode with all possible options, along with their defaults: [MSBD_RECOMMENDS_SHOW category="none" num="3"]. We will now show some examples of using these options.
                </div>
                <div class="msrp_shortcode_option_container">
                    <div class="msrp_shortcode_option_name">[MSBD_RECOMMENDS_SHOW num="8"]</div>
                    <div class="msrp_shortcode_option_text">
                        This will show the first eight approved global reviews. Any integer greater than or equal to one may be used, and note that (given enough room) reviews are displayed in blocks of three.
                    </div>
                </div>
                <div class="msrp_shortcode_option_container">
                    <div class="msrp_shortcode_option_name">[MSBD_RECOMMENDS_SHOW num="all"]</div>
                    <div class="msrp_shortcode_option_text">
                        This will show EVERY approved global review which has been posted to your site. This is the only non-integer value which works as the value for the "num" option.
                    </div>
                </div>';
            
            $output .= '</div>';
            
        echo $output;
    }

    function render_rr_form_content() {
        $output = '<div class="msrp_shortcode_container">
                <div class="msrp_shortcode_name">[MSBD_RECOMMENDS_FORM]</div>
                <div class="msrp_shortcode_description">
                    This shortcode will insert the form which your users fill out to submit their reviews to you. Note that javascript must be enabled (on both your site and on the user\'s computer) in order for this to work. There is one option, shown here with its default: [MSBD_RECOMMENDS_FORM category="none"]. You do NOT need to specify a category of "page" if you want to use per-page reviews. By default, ALL reviews that users submit will record the page or post from which they were submitted.
                </div>
            </div>';
        echo $output;
    }

    function render_pending_recommendations_page($wrapped = null) {
        if (!$wrapped) {
            $this->wrap_admin_page('pending');
            return;
        }
        require_once('recommend-us-admin-tables.php');
        $msbd_rcmnd_admin_table = new MSBD_RCMND_Table();
        $msbd_rcmnd_admin_table->prepare_items('pending');
        echo '<form id="form" method="POST">';
        $msbd_rcmnd_admin_table->display();
        echo '</form>';
    }

    function render_approved_recommendations_page($wrapped) {
        if (!$wrapped) {
            $this->wrap_admin_page('approved');
            return;
        }
        require_once('recommend-us-admin-tables.php');
        $msbd_rcmnd_admin_table = new MSBD_RCMND_Table();
        $msbd_rcmnd_admin_table->prepare_items('approved');
        echo '<form id="form" method="POST">';
        $msbd_rcmnd_admin_table->display();
        echo '</form>';
    }

    function render_options_page($wrapped) {
        $options = $this->parent->options->get_option();
        if (!$wrapped) {
            $this->wrap_admin_page('options');
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        ?>
        <form id="rr-admin-options-form" action="" method="post">
            <input type="hidden" name="update" value="msr-update-options">

            
            <input type="checkbox" name="ms_give_credit" value="checked" id="msbd_credit_permission" <?php echo $options['ms_give_credit'] ?> />
            <label for="msbd_credit_permission">Give Credit to Micro Solutions Bangladesh - this option will add a small credit line and a link to Micro Solutions Bangladesh's website to the bottom of your recommendation page</label>
            <br />

            <input type="checkbox" name="show_date" value="checked" id="msbd_show_date" <?php echo $options['show_date'] ?> />
            <label for="msbd_show_date">Display the date that the recomendation was submitted after the recomendation.</label>
            <br />

            <input type="checkbox" name="add_msrp_styles" value="checked" id="msbd_add_msrp_styles" <?php echo $options['add_msrp_styles'] ?> />
            <label for="msbd_add_msrp_styles">Add plugin style for recommendation form, pages, and pagination. Unselect it if you want to add your own styles for recommendation form, pages, and pagination in your theme styles!</label>
            <br />

            <input type="checkbox" name="add_glyphicons" value="checked" id="msbd_add_glyphicons" <?php echo $options['add_glyphicons'] ?> />
            <label for="msbd_add_glyphicons">Add Glyphicons from Bootstrap library. Unselect it if you have added bootstrap library in your theme!</label>
            <br />

            <input type="text" name="ms_allowed_html_tags" id="msbd_allowed_html_tags" value="<?php echo $options['ms_allowed_html_tags'] ?>" placeholder="Allowed html tags in csv" />
            <label for="msbd_allowed_html_tags">Write the allowed html tags in csv format that can be submitted on recommendation! By default following tags will allowed strong,bold,i,u,br</label>
            <br />
            
            <select name="reviews_order" value="<?php echo $options['reviews_order'] ?>" id="reviews_order">
                <?php
                if ($options['reviews_order']==="ASC"){ ?><option value="ASC" selected="selected">Oldest First</option><?php }else {?><option value="ASC" >Oldest First</option><?php }
                if ($options['reviews_order']==="DESC"){ ?><option value="DESC" selected="selected">Newest First</option><?php }else {?><option value="DESC" >Newest First</option><?php }
                if ($options['reviews_order']==="random"){ ?><option value="random" selected="selected">Randomize</option><?php }else {?><option value="random" >Randomize</option><?php }
                ?>
            </select>
            <label for="reviews_order"> Recommendation Display Order</label>
            <br />
            <select name="ms_authority_label" id="ms_authority_label">
                <?php
                if ($options['ms_authority_label']==="manage_options"){ ?><option value="manage_options" selected="selected">Admin</option><?php }else {?><option value="manage_options" >Admin</option><?php }
                if ($options['ms_authority_label']==="moderate_comments"){ ?><option value="moderate_comments" selected="selected">Editor</option><?php }else {?><option value="moderate_comments" >Editor</option><?php }
                if ($options['ms_authority_label']==="edit_published_posts"){ ?><option value="edit_published_posts" selected="selected">author</option><?php }else {?><option value="edit_published_posts" >Author</option><?php }
                if ($options['ms_authority_label']==="edit_posts"){ ?><option value="edit_posts" selected="selected">Contributor</option><?php }else {?><option value="edit_posts" >Contributor</option><?php }
                if ($options['ms_authority_label']==="read"){ ?><option value="read" selected="selected">Subscriber</option><?php }else {?><option value="read" >Subscriber</option><?php }
                ?>
            </select>
            <label for="ms_authority_label"> Authority level required to Manage Recommendations</label>
            <br />
            <br />
            <input type="submit" class="button" value="Save Options">
        </form>
        <?php

    }

    function render_add_edit_page($wrapped) {
        $options = $this->parent->options->get_option();
        if (!$wrapped) {
            $this->wrap_admin_page('add/edit');
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        $view = new RRAdminAddEdit($this->parent);
    }

    function get_option($opt_name = '') {
        $options = get_option($this->parent->fp_admin_options);

        // maybe return the whole options array?
        if ($opt_name == '') {
            return $options;
        }

        // are the options already set at all?
        if ($options == FALSE) {
            return $options;
        }

        // the options are set, let's see if the specific one exists
        if (! isset($options[$opt_name])) {
            return FALSE;
        }

        // the options are set, that specific option exists. return it
        return $options[$opt_name];
    }

    function update_option($opt_name, $opt_val = '') {
        // allow a function override where we just use a key/val array
        if (is_array($opt_name) && $opt_val == '') {
            foreach ($opt_name as $real_opt_name => $real_opt_value) {
                $this->update_option($real_opt_name, $real_opt_value);
            }
        } else {
            $current_options = $this->get_option();

            // make sure we at least start with blank options
            if ($current_options == FALSE) {
                $current_options = array();
            }

            $new_option = array($opt_name => $opt_val);
            update_option($this->parent->fp_admin_options, array_merge($current_options, $new_option));
        }
    }

}
