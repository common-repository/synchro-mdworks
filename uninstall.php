<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
die;
}

$option_name = 'wporg_option';

delete_option( 'mdworks_hosted_stmt' );
delete_option( 'mdworks_hosted_slt_db' );
delete_option( 'mdworks_hosted_mapping' );
delete_option( 'mdworks_hosted_login' );
delete_option( 'mdworks_hosted_password' );
