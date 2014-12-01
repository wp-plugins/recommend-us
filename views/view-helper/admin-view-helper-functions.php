<?php
class MSBDRecommendUsAdminHelper {

    public static function render_header($title, $echo = TRUE) {
        global $file;
        
        $plugin_data = get_plugin_data( $file);
        $output = '';
        $output .= '<h1>' . $plugin_data['Name'] . '</h1>';
        
        if ($echo) {
            echo $output;
        } else {
            return $output;
        }
    }

    public static function render_sidebar() {
        MSBDRecommendUsAdminHelper::render_postbox_open('Micro Solutions Bangladesh');
        MSBDRecommendUsAdminHelper::render_msbd_logos();
        MSBDRecommendUsAdminHelper::render_postbox_close();
    }



    public static function render_msbd_logos() {
        ?>
            <div class="msbd-logo one-fourth">
                <a href="https://www.microsolutionsbd.com/" target="_blank">
                    <img src="<?php echo MSBD_RCMND_PLGN_URL."images/msbd_logo.png"; ?>" />
                </a>
            </div>
            
            <div class="msbd-social-media-links-container three-fourths">
                <div class="msbd-social-media-link msbd-facebook-link">
                    <script>(function(d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) {return;}
                        js = d.createElement(s); js.id = id;
                        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));</script>
                </div>
                <div class="msbd-social-media-link msbd-google-plus-link">
                    <div id="wp-meetup-social">
                        <div class="fb-like" data-href="https://www.facebook.com/microsolutionsbd" data-send="false" data-layout="button_count" data-width="100" data-show-faces="true"></div><br><br>
                        <g:plusone annotation="inline" width="216" href="https://www.google.com/+Microsolutionsbd"></g:plusone><br>
                        <!-- Place this tag where you want the +1 button to render -->
                        <script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>

                    </div>
                </div>
            </div>

        <?php
    }


    public static function render_tabs($echo = TRUE) {
        /*
         * key value pairs of the form:
         * 'admin_page_slug' => 'Tab Label'
         * where admin_page_slug is from
         * the add_menu_page or add_submenu_page
         */
        $tabs = array(
            'msrp_settings_main' => 'Dashboard',
            'fp_admin_pending_recommendation_page' => 'Pending Recommendations',
            'fp_admin_approved_recommendation_page' => 'Approved Recommendations',
            'fp_admin_options_page' => 'Options',
            'msrp_admin_add_edit' => 'Add Recommendation',
        );

        // what page did we request?
        $current_slug = '';
        if (isset($_GET['page'])) {
            $current_slug = $_GET['page'];
        }

        // render all the tabs
        $output = '';
        $output .= '<div class="tabs-container">';
        foreach ($tabs as $slug => $label) {
            $output .= '<div class="tab ' . ($slug == $current_slug ? 'active' : '') . '">';
            $output .= '<a href="' . admin_url('admin.php?page='.$slug) . '">' . $label . '</a>';
            $output .= '</div>';
        }
        $output .= '</div>'; // end .tabs-container

        if ($echo) {
            echo $output;
        } else {
            return $output;
        }
    }

    public static function render_postbox_open($title = '') {
        ?>
        <div class="postbox">
            <div class="handlediv" title="Click to toggle"><br/></div>
            <h3 class="hndle"><span><?php echo $title; ?></span></h3>
            <div class="inside">
        <?php
    }

    public static function render_postbox_close() {
        echo '</div>'; // end .inside
        echo '</div>'; // end .postbox
    }

    public static function render_container_open($extra_class = '', $echo = TRUE) {
        $output = '';
        $output .= '<div class="metabox-holder ' . $extra_class . '">';
        $output .= '  <div class="postbox-container msbd-postbox-container">';
        $output .= '    <div class="meta-box-sortables ui-sortable">';

        if ($echo) {
            echo $output;
        } else {
            return $output;
        }
    }

    public static function render_container_close($echo = TRUE) {
        $output = '';
        $output .= '</div>'; // end .ui-sortable
        $output .= '</div>'; // end .msbd-postbox-container
        $output .= '</div>'; // end .metabox-holder

        if ($echo) {
            echo $output;
        } else {
            return $output;
        }
    }
}
