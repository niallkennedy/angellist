<?php
/**
 * Request remote data from AngelList
 * Generate HTML from the result
 *
 * @since 1.0
 */
class AngelList_Company {

	/**
	 * Identify the unique company, kick off data request
	 *
	 * @since 1.0
	 * @param int $company_id AngelList company identifier
	 */
	public function __construct( $company_id ) {
		$company_id = absint( $company_id );
		if ( $company_id < 1 )
			return;
		$this->id = absint( $company_id );

		// allow a publisher to disable Schema.org markup by attaching to this filter
		$this->schema_org = (bool) apply_filters( 'angellist_schema_org', true, $this->id );

		// allow override of default browsing context
		$this->browsing_context = apply_filters( 'angellist_browsing_context', '_blank', $this->id );
		// limit browsing context to special keywords
		if ( ! in_array( $this->browsing_context, array( '', '_blank', '_self', '_parent', '_top' ), true ) )
			$this->browsing_context = '_blank';

		// check for cached version of company info before we request data from AngelList
		$this->cache_key = $this->generate_cache_key();
		$html = get_transient( $this->cache_key );
		if ( empty( $html ) )
			$this->populate_data();
		else
			$this->html = $html;
	}

	/**
	 * AngelList uses a HTTPS URL for static assets such as images to avoid mixed content issues if the parent page is served over HTTPS
	 * As of May 2012 these images are stored on Amazon S3. If we don't need HTTPS and Amazon's certificate we can construct a new URL based on an assumed CNAME entry for the bucket
	 * Avoids unncessary overhead of HTTPS when we know we are on HTTP (the majority case) & makes the URL a bit more pretty without the vendor hostname
	 *
	 * @since 1.0
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
	 * Request company data from AngelList.
	 * Populate data in the class
	 *
	 * @since 1.0
	 */
	private function populate_data() {
		$response = wp_remote_get( 'http://api.angel.co/1/startups/' . $this->id, array(
			'httpversion' => '1.1',
			'redirection' => 0,
			'timeout' => 3,
			'headers' => array( 'Accept' => 'application/json' )
		) );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != '200' )
			return;

		$response_body = wp_remote_retrieve_body( $response );
		if ( empty( $response_body ) )
			return;
		$company = json_decode( $response_body );
		unset( $response_body );
		if ( empty( $company ) )
			return;

		// are we sharing a secret?
		if ( isset( $company->hidden ) && $company->hidden === true )
			return;

		// we should at least be able to reference a startup by its name
		if ( isset( $company->name ) )
			$this->name = trim( $company->name );
		else
			return;

		// is the startup participating in AngelList and has claimed their profile?
		if ( isset( $company->community_profile ) && $company->community_profile === false )
			$this->claimed = true;
		else
			$this->claimed = false;

		if ( isset( $company->company_url ) ) {
			$url = esc_url( $company->company_url, array( 'http', 'https' ) );
			if ( $url )
				$this->url = $url;
			unset( $url );
		}

		if ( isset( $company->angellist_url ) ) {
			$url = esc_url( $company->angellist_url, array( 'http', 'https' ) );
			if ( $url )
				$this->profile_url = $url;
			unset( $url );
		}

		// AngelList sends a 144x144 blank image we don't need
		$nopic = 'http://angel.co/images/icons/startup-nopic.png';
		if ( isset( $company->thumb_url ) && $company->thumb_url !== $nopic ) {
			$url = AngelList_Company::filter_static_asset_url( $company->thumb_url );
			if ( $url ) {
				$image = new stdClass();
				$image->url = $url;
				$image->width = $image->height = 100;
				$this->thumbnail = $image;
				unset( $image );
			}
			unset( $url );
		}

		if ( isset( $company->logo_url ) && $company->thumb_url !== $nopic ) {
			$url = AngelList_Company::filter_static_asset_url( $company->logo_url );
			if ( $url )
				$this->logo_url = $url;
			unset( $url );
		}
		unset( $no_pic );

		if ( isset( $company->high_concept ) ) {
			$concept = trim( $company->high_concept );
			if ( $concept )
				$this->concept = $concept;
			unset( $concept );
		}

		if ( isset( $company->product_desc ) ) {
			$description = trim( $company->product_desc );
			if ( $description )
				$this->description = $description;
			unset( $description );
		}

		if ( isset( $company->locations ) ) {
			// iterate until we find a URL we like
			foreach( $company->locations as $location ) {
				if ( isset( $location->angellist_url ) ) {
					$url = esc_url( $location->angellist_url, array( 'http', 'https' ) );
					if ( $url ) {
						$this->location_url = $url;
						break;
					}
					unset( $url );
				}
			}
		}
	}

	/**
	 * Generate a cache key based on site preferences and SSL requirements
	 *
	 * @since 1.0
	 * @return string WordPress cache or transient key
	 */
	private function generate_cache_key() {
		$cache_parts = array( 'angellist-company', $this->id );
		if ( is_ssl() )
			$cache_parts[] = 'ssl';
		if ( isset( $this->schema_org ) && ! $this->schema_org )
			$cache_parts[] = 'ns';
		if ( isset( $this->browsing_context ) && $this->browsing_context )
			$cache_parts[] = substr( $this->browsing_context, 1 );
		return implode( '-', $cache_parts );
	}

	/**
	 * Build HTML for a company
	 *
	 * @since 1.0
	 * @return string HTML markup
	 */
	public function render() {
		if ( ! ( isset( $this->name ) && isset( $this->profile_url ) ) )
			return '';

		if ( $this->browsing_context === '' )
			$anchor_target = ' target="' . $browsing_context . '"';
		else
			$anchor_target = '';
		unset( $browsing_context );

		$profile_url_title_attr = esc_attr( sprintf( __( '%s on AngelList', 'angellist' ), $this->name ) );

		$html = '<li class="angellist-company ';
		if ( $this->claimed )
			$html .= 'angellist-claimed-profile';
		else
			$html .= 'angellist-community-profile';
		$html .= '" data-startup_id="' . $this->id . '"';
		if ( $this->schema_org )
			$html .= ' itemscope itemtype="http://schema.org/Corporation"';
		$html .= '>';
		if ( $this->schema_org ) {
			if ( isset( $this->url ) )
				$html .= '<meta itemprop="url" content="' . $this->url . '" />';
			else
				$html .= '<meta itemprop="url" content="' . $this->profile_url . '" />';

			if ( isset( $this->description ) )
				$html .= '<meta itemprop="description" content="' . esc_attr( str_replace( "\n\n", ' ', $this->description ) ) . '" />';
			else if ( isset( $this->concept ) )
				$html .= '<meta itemprop="description" content="' . esc_attr( $this->concept ) . '" />';

			if ( isset( $this->logo_url ) )
				$html .= '<meta itemprop="image" content="' . $this->logo_url . '" />';
			else if ( isset( $this->thumbnail ) )
				$html .= '<meta itemprop="image" content="' . $this->thumbnail->url . '" />';

			if ( isset( $this->location_url ) ) {
				$html .= '<meta itemprop="location" content="' . $this->location_url . '" />';
			}
		}

		$html .= '<div class="angellist-company-summary">';
		if ( isset( $this->thumbnail ) ) {
			$html .= '<a class="angellist-company-image" href="' . $this->profile_url . '" title="' . $profile_url_title_attr . '"' . $anchor_target . '>';
			$html .= '<img alt="' . esc_attr( $this->name ) . '" src="' . $this->thumbnail->url . '" width="90" height="90" />';
			$html .= '</a>';
		}
		$html .= '<div class="angellist-company-summary-text">';
		$html .= '<a class="angellist-company-name" href="' . $this->profile_url . '" title="' . $profile_url_title_attr . '"' . $anchor_target;
		if ( $this->schema_org )
			$html .= ' itemprop="name"';
		$html .= '>' . esc_html( $this->name ) . '</a>';
		if ( isset( $this->concept ) )
			$html .= '<div class="angellist-company-concept">' . esc_html( $this->concept ) . '</div>';
		$html .= '</div>'; // summary-text
		$html .= '<span class="angellist-follow-button"><a href="' . $this->profile_url . '" title="' . $profile_url_title_attr . '"' . $anchor_target . '>' . esc_html( __( 'Follow on AngelList', 'angellist' ) ) . '</a></span>';
		$html .= '</div>'; // summary

		if ( isset( $this->description ) ) {
			// wrap the p in a detail div for easy expansion to users, read more on the <p>, etc
			$paragraphs = explode( "\n\n", $this->description );
			array_walk( $paragraphs, 'esc_html' );
			$html .= '<div class="angellist-company-detail"><p>' . implode( '</p><p>', $paragraphs ) . '</p></div>';
			unset( $paragraphs );
		}
		$html .= '</li>';

		// cache markup to save us some time on the next request
		set_transient( $this->cache_key, $html, 3600 );

		return $html;
	}
}
?>