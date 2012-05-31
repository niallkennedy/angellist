<?php
/**
 * Talk to the AngelList API
 *
 * @since 1.1
 */
class AngelList_API {
	/**
	 * Base API URL used in all requests
	 *
	 * @since 1.1
	 * @var string
	 */
	const BASE_URL = 'http://api.angel.co/1/';

	/**
	 * AngelList sends a 144x144 blank image for objects without their own image
	 * Compare against this value if you would like to remove the default image
	 *
	 * @since 1.1
	 * @var string
	 */
	const DEFAULT_IMAGE = 'http://angel.co/images/icons/startup-nopic.png';

	/**
	 * Special HTTP arguments to customize each request to the AngelList API
	 *
	 * @since 1.1
	 * @var array
	 */
	public static $http_args = array( 'httpversion' => '1.1', 'redirection' => 0, 'timeout' => 3, 'headers' => array( 'Accept' => 'application/json' ) );

	/**
	 * AngelList uses a HTTPS URL for static assets such as images to avoid mixed content issues if the parent page is served over HTTPS
	 * As of May 2012 these images are stored on Amazon S3. If we don't need HTTPS and Amazon's certificate we can construct a new URL based on an assumed CNAME entry for the bucket
	 * Avoids unncessary overhead of HTTPS when we know we are on HTTP (the majority case) & makes the URL a bit more pretty without the vendor hostname
	 *
	 * @since 1.1
	 * @param string $url AngelList static asset URL
	 * @return string cleaned up URL if incoming request was HTTP
	 */
	public static function filter_static_asset_url( $url ) {
		if ( is_ssl() ) {
			// reject including a non-SSL asset on the page if it will generate mixed content warnings
			return esc_url( $url, array( 'https' ) );
		} else if ( strlen( $url ) > 41 && substr_compare( $url, 'https://s3.amazonaws.com/photos.angel.co/', 0, 41 ) === 0 )
			return esc_url( 'http://photos.angel.co/' . substr( $url, 41 ), array( 'http' ) );
		return esc_url( $url, array( 'http', 'https' ) );
	}

	/**
	 * AngelList data for a single company
	 *
	 * @since 1.1
	 * @param int $company_id AngelList company identifer
	 */
	public static function get_company( $company_id ) {
		if ( ! is_int( $company_id ) || $company_id < 1 )
			return;

		$response = wp_remote_get( AngelList_API::BASE_URL . 'startups/' . $company_id, AngelList_API::$http_args );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != '200' )
			return;

		$response_body = wp_remote_retrieve_body( $response );
		if ( empty( $response_body ) )
			return;
		return json_decode( $response_body );
	}

	public static function get_roles_by_company( $company_id ) {
		if ( ! is_int( $company_id ) || $company_id < 1 )
			return;

		$response = wp_remote_get( AngelList_API::BASE_URL . 'startup_roles?startup_id=' . $company_id, AngelList_API::$http_args );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != '200' )
			return;

		$response_body = wp_remote_retrieve_body( $response );
		if ( empty( $response_body ) )
			return;

		$json = json_decode( $response_body );
		if ( ! empty( $json ) && isset( $json->startup_roles ) && ! empty( $json->startup_roles ) )
			return $json->startup_roles;
	}
}
?>