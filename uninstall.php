<?php

/**
 * Remove all post meta
 *
 * @since 1.2
 */
function angellist_uninstall() {
	$meta_key = 'angellist-companies';
	$all_posts = get_posts( array(
		'numberposts' => -1, // everything
		'post_status' => 'any',
		'post_type' => 'post',
		'order_by' => 'none',
		'meta_query' => $meta_key,
		'cache_results' => false,
		'fields' => 'ids'
	) );
	foreach ( $all_posts as $post_id ) {
		delete_post_meta( $post_id, $meta_key );
	}
}

if ( defined( 'WP_UNINSTALL_PLUGIN' ) )
	angellist_uninstall();
?>