<?php
    /*
    Plugin Name: Delete All Comments
    Plugin URI: http://www.oviamsolutions.com/plugins/delete-all-comments
    Description: Plugin to delete all comments (Approved, Pending, Spam). Tested upto wordpress 4.6
    Author: Ganesh Chandra
    Version: 1.3
    Author URI: http://www.oviamsolutions.com
    */
?>
<?php
    add_action('admin_menu', 'oviam_dac_admin_actions');

    function oviam_dac_admin_actions(){
		    add_management_page("Delete All Comments", "Delete All Comments", "edit_posts", "oviam_delete_all_comments", "oviam_dac_main");
    } // End of function oviam_dac_admin_actions

    function oviam_log_me($message){
        if (WP_DEBUG === true){
            if (is_array($message) || is_object($message)){
                error_log(print_r($message, true));
            }
            else{
                error_log($message);
            }
        }
    }
    
    function oviam_dac_main() {
        global $wpdb;
        $comments_count = $wpdb->get_var( "SELECT count(comment_id) from $wpdb->comments" );
        $result = $wpdb->get_results("SELECT comment_approved, count(*) as cnt FROM $wpdb->comments group by comment_approved order by comment_approved");

?>
    <div class="wrap">
	    <h2><u>Delete All Comments</u></h2><br/>
        <?php 
        if(isset($_POST['chkdelete']) == 'Y'){
            if(wp_verify_nonce($_POST['ovi@m_safe_c0dr'], 'ovi@m_safe_c0dr')){
                if($wpdb->query("TRUNCATE $wpdb->commentmeta") != FALSE){
                    if($wpdb->query("TRUNCATE $wpdb->comments") != FALSE){
                            $wpdb->query("Update $wpdb->posts set comment_count = 0 where post_author != 0");
                            $wpdb->query("OPTIMIZE TABLE $wpdb->commentmeta");
                            $wpdb->query("OPTIMIZE TABLE $wpdb->comments");
                            echo "<p style='color:green'><strong>All comments have been deleted.</strong></p>";
                    }
                    else{
                            oviam_log_me('Error occured when deleting wpdb comments table');
                            echo "<p style='color:red'><strong>Internal error occured. Please try again later.</strong></p>";
                    }
                }
                else{
                    oviam_log_me('Error occured when deleting wpdb commentmeta table');
                    echo "<p style='color:red'><strong>Internal error occured. Please try again later.</strong></p>";
                }
            } // End of verify_nonce
            else{
                oviam_log_me('Security failure');
                die("Security Validation Failure");
            } // End of Security
        } // End of if comment remove ='Y'
        else{
            $current_site = get_bloginfo( 'name' );
            echo "<table summary='table to display comment status' cellpadding='2' cellspacing='2' border='0'>";
            echo "<tr>" . "<td><strong>" . "Site Name : " . "</strong></td><td>&nbsp;&nbsp;</td>" . "<td><strong>" . $current_site . "</strong></td>" . "</tr>";
            echo "<tr>" . "<td>" . "</td><td>&nbsp;&nbsp;</td>" . "<td>" . "</td>" . "</tr>";
            //echo "<tr>" . "<td><strong>" . "Comment Types" . "</strong></td><td>&nbsp;&nbsp;</td>" . "<td><strong> Number of comments  </strong></td>" . "</tr>";
            echo "<tr>" . "<td>" . "</td><td>&nbsp;&nbsp;</td>" . "<td>" . "</td>" . "</tr>";
            echo "<tr>" . "<td><strong>" . "Total Comments" . "</strong></td><td>&nbsp;&nbsp;</td>" . "<td>" . $comments_count . "</td>" . "</tr>";
            foreach ($result as $comment) {
                switch ($comment->comment_approved){
                    case '0':
                        echo "<tr>" . "<td><em>" . " -> Unapproved Comments" . "</em></td><td>&nbsp;&nbsp;</td>" . "<td>" . $comment->cnt . "</td>" . "</tr>";
                        break;
                    case 'spam':
                        echo "<tr>" . "<td><em>" . " -> Spam Comments" . "</em></td><td>&nbsp;&nbsp;</td>" . "<td>" . $comment->cnt . "</td>" . "</tr>";
                        break;
                    case 'trash':
                        echo "<tr>" . "<td><em>" . " -> Deleted Comments" . "</em></td><td>&nbsp;&nbsp;</td>" . "<td>" . $comment->cnt . "</td>" . "</tr>";
                        break;
                    case 'post-trashed':
                        echo "<tr>" . "<td><em>" . " -> Comments from Deleted posts" . "</em></td><td>&nbsp;&nbsp;</td>" . "<td>" . $comment->cnt . "</td>" . "</tr>";
                        break;
                    case '1':
                        echo "<tr>" . "<td><em>" . " -> Approved Comments" . "</em></td><td>&nbsp;&nbsp;</td>" . "<td>" . $comment->cnt . "</td>" . "</tr>";
                        break;
                }
            }
            echo "</table>";
        ?>
        
        <?php if($comments_count > 0) { ?>
        <p><strong>Note: Please check the box and click Delete All.</strong></p>
            <form name="frmOviamdac" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                <input type="hidden" name="ovi@m_safe_c0dr" value="<?php echo wp_create_nonce('ovi@m_safe_c0dr'); ?>">
                <input type="checkbox" name="chkdelete" value="Y" /> Delete all comments
                <p class="submit">
		            <input type="submit" name="Submit" value="Delete All Comments" />
                </p>
            </form>
        <?php
        } // End of if comments_count > 0
        else{
            echo "<p><strong>All comments have been deleted.</strong></p>" ;
        } // End of else comments_count > 0  ?>
    </div>
<?php
        } // else of if comment remove == 'Y'
    } // End of function oviam_dac_main
?>