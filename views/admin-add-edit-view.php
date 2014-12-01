<?php
class RRAdminAddEdit {

    var $core;

    var $recommend_id;
    var $date_time;
    var $recommend_by_name;
    var $recommend_by_email;
    var $recommendation_title;
    var $rating_number;
    var $recommendation_text;
    var $recommendation_status;
    var $recommend_by_ip;


    function __construct($core) {
        $this->core = $core;
        if (isset($_GET['recommend_id'])) {
            $this->recommend_id = $_GET['recommend_id'];
        }
        $this->date_time = $rDateTime = date('Y-m-d H:i:s');
        $this->recommend_by_ip = $_SERVER['REMOTE_ADDR'];
        $this->check_add_update();
        $this->display_form();
    }

    function check_add_update() {
        $output = '';
        if (isset($_POST['msbd_save_rcmnd'])) {
            if ($_POST['msbd_save_rcmnd'] == 'admin-save-rcmnd') {
                $this->date_time     = $this->core->msbd_sanitization($_POST['date_time']);
                $this->recommend_by_name     = $this->core->msbd_sanitization($_POST['recommend_by_name']);
                $this->recommend_by_email    = $this->core->msbd_sanitization($_POST['recommend_by_email'], 'email');
                $this->recommendation_title    = $this->core->msbd_sanitization($_POST['recommendation_title']);
                $this->rating_number   = $this->core->msbd_sanitization($_POST['rating_number']);
                $this->recommendation_text     = $this->core->msbd_sanitization($_POST['recommendation_text'], 'textarea');
                $this->recommendation_status   = 1;
                
                $newdata = array(
                        'date_time'       => $this->date_time,
                        'recommend_by_name'   => $this->recommend_by_name,
                        'recommend_by_email'  => $this->recommend_by_email,
                        'recommendation_title'    => $this->recommendation_title,
                        'rating_number'   => intval($this->rating_number),
                        'recommendation_text'     => $this->recommendation_text,
                        'recommendation_status'   => $this->recommendation_status,
                        'recommend_by_ip'     => $this->recommend_by_ip,
                );
                //dump($newdata, 'NEW DATA');
                $validData = true;
                if ($this->recommend_by_name  == '') {
                    $output .= 'You must include your name.';
                    $validData = false;
                } else if ($this->recommendation_title == '') {
                    $output .= 'You must include a title for your recommendation.';
                    $validData = false;
                } else if ($this->recommendation_text== '') {
                    $output .= 'You must write some text in your recommendation.';
                    $validData = false;
                } else if ($this->rating_number == 0) {
                    $output .= 'Please give a rating between 1 and 5 stars.';
                    $validData = false;
                } else if ($this->recommend_by_email != '') {
                    $firstAtPos = strpos($this->recommend_by_email,'@');
                    $periodPos  = strpos($this->recommend_by_email,'.');
                    $lastAtPos  = strrpos($this->recommend_by_email,'@');
                    if (($firstAtPos === false) || ($firstAtPos != $lastAtPos) || ($periodPos === false)) {
                        $output .= 'You must provide a valid email address.';
                        $validData = false;
                    }
                }
                if ($validData) {
                    if ((strlen($this->recommend_by_name) > 100)) {
                        $output .= 'The name you entered was too long, and has been shortened.<br />';
                    }
                    if ((strlen($this->recommendation_title) > 150)) {
                        $output .= 'The recommendation title you entered was too long, and has been shortened.<br />';
                    }
                    if ((strlen($this->recommend_by_email) > 100)) {
                        $output .= 'The email you entered was too long, and has been shortened.<br />';
                    }
                    $this->core->db->save($newdata, $this->recommend_id);
                    $output .= 'The recommendation has been saved.<br />';
                }
            }
        }
        echo $output;
    }

    function display_form($recommendation = NULL) {
        if ($this->recommend_id && $recommendation == NULL) {
            $recommendation =(array) $this->core->db->get($this->recommend_id, TRUE);
            //dump($recommendation, 'REVIEW');
            $this->display_form($recommendation);
            return;
        }
        if (is_null($recommendation)) {
            $recommendation = array(
                'date_time'       => NULL,
                'recommend_by_name'   => NULL,
                'recommend_by_email'  => NULL,
                'recommendation_title'    => NULL,
                'rating_number'   => NULL,
                'recommendation_text'     => NULL,
                'recommendation_status'   => NULL,
                'recommend_by_ip'     => NULL,
            );
        }
        foreach ($recommendation as $key=>$value) {
            $recommendation[$key] = $this->core->nice_output($value);
        }
        ?>
<form method="post" action="">
    <input type="hidden" name="msbd_save_rcmnd" value="admin-save-rcmnd" />
    <input type="hidden" name="date_time" value="<?php echo $recommendation['date_time']; ?>" />
    <table class="form_table">
        <tr class="msr_form_row">
            <td class="msr_form_heading msrp_required">Name</td>
            <td class="rr_form_input"><input class="rr_small_input" type="text" name="recommend_by_name" value="<?php echo $recommendation['recommend_by_name']; ?>" /></td>
        </tr>
        <tr class="msr_form_row">
            <td class="msr_form_heading">Email</td>
            <td class="rr_form_input"><input class="rr_small_input" type="text" name="recommend_by_email" value="<?php echo $recommendation['recommend_by_email']; ?>" /></td>
        </tr>
        <tr class="msr_form_row">
            <td class="msr_form_heading msrp_required">Recommendation Title</td>
            <td class="rr_form_input"><input class="rr_small_input" type="text" name="recommendation_title" value="<?php echo $recommendation['recommendation_title']; ?>" /></td>
        </tr>
        <tr class="msr_form_row">
            <td class="msr_form_heading msrp_required">Rating</td>
            <td class="rr_form_input"><input type="number" name="rating_number" value="<?php echo $recommendation['rating_number']; ?>" min="1" max="5"/></td>
        </tr>
        <tr class="msr_form_row">
            <td class="msr_form_heading msrp_required">Your Recommendation</td>
            <td class="rr_form_input"><textarea class="msrp_large_input" name="recommendation_text" rows="10"><?php echo stripslashes($recommendation['recommendation_text']); ?></textarea></td>
        </tr>
        <tr class="msr_form_row">
            <td></td>
            <td class="rr_form_input"><input name="submitButton" type="submit" value="Submit" /></td>
        </tr>
    </table>
</form>
        <?php
    }
}
