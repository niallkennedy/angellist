<?php
/**
 * Search AngelList companies by freeform text
 *
 * @since 1.0
 */

// GET only
if ( array_key_exists( 'REQUEST_METHOD', $_SERVER ) && $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
	header( 'HTTP/1.1 405 Method Not Allowed', true, 405 );
	header( 'Allow: GET', true );
	exit();
}

// WordPress bootstrap. assume a wp-content/plugins/
if ( ! function_exists( 'current_user_can' ) )
	require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );

// override HTML default Content-Type with JSON
header( 'Content-Type: application/json; charset=utf-8', true );

/**
 * Echo a JSON error message, set a HTTP status, and exit
 *
 * @since 1.0
 * @param WP_Error $error error code of HTTP status int. error message echoed in JSON
 */
function angellist_reject_message( WP_Error $error ) {
	status_header( $error->get_error_code() );
	echo json_encode( array( 'error' => $error->get_error_message() ) );
	exit();
}

// allow only logged-on users with the capability to see an edit post screen to access our API proxy
if ( ! current_user_can( 'edit_posts' ) )
	angellist_reject_message( new WP_Error( 403, __( 'Cheatin\' uh?' ) ) );

if ( ! array_key_exists( 'q', $_GET ) )
	angellist_reject_message( new WP_Error( 400, 'Search string needed. Use q query parameter.' ) );

$__search_term = trim( $_GET['q'] );
if ( empty( $__search_term ) )
	angellist_reject_message( new WP_Error( 400, 'No search string provided.' ) );

if ( ! class_exists( 'AngelList_Search' ) )
	require_once( dirname(__FILE__) . '/class-angellist-search.php' );

$__companies = AngelList_Search::startups( $__search_term );
if ( is_wp_error( $__companies ) )
	angellist_reject_message( $__companies );
else
	echo json_encode( $__companies );
?>