<?php


/*uninstall section*/
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
}else{
	// Check if options exist and delete them if present
if ( false != get_option( 'magicpipe_options' )) {
  delete_option('magicpipe_options' );
}
}
