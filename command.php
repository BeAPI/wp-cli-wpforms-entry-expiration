<?php
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

define( 'WP_CLI_WPF_ENT_EXP', dirname( __FILE__ ) );

require_once WP_CLI_WPF_ENT_EXP . '/src/wp-cli-wpforms-entry-expiration.php';
