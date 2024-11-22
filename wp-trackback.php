<?php
// we do not support trackbacks, they have been removed since CosmicWord 1.1.4 
if ( empty( $wp ) ) {
    require_once __DIR__ . '/wp-load.php';
    wp( array( 'tb' => '1' ) );
}
wp_set_current_user( 0 );
do_action( 'trackback_post', false );
exit;