<?php
/**
 * Sendy API wrapper for WordPress applications.
 *
 * A wrapper class for the Sendy API using the WordPress
 * HTTP API.  Based on the Sendy API class by Nick Thompson
 * (https://github.com/nickian/Sendy-Extended-PHP-API-Wrapper) and modified for
 * use with the WordPress HTTP API instead of cURL. Formatted to WordPress 
 * coding standards.
 *
 * You can support this API project by using my affiliate link when you 
 * purchase Sendy. It's the same price either way, so why not help out 
 * the project with a purchase you'd make anyway?
 * https://rkt.bz/sendy
 * https://sendy.co/?ref=ZUdzM
 *
 * Key Documentation
 * Sendy API:          https://sendy.co/api
 * WordPress HTTP API: https://developer.wordpress.org/plugins/http-api/
 * This class:         https://github.com/rocketgeek/sendy-api
 * Nick's class:       https://github.com/nickian/Sendy-Extended-PHP-API-Wrapper
 *
 * @package    {Your Project Name}
 * @subpackage RocketGeek_Sendy_API
 * @version    1.0.0
 *
 * @link       https://github.com/rocketgeek/sendy-api/
 * @author     Chad Butler <https://butlerblog.com>
 * @author     RocketGeek <https://rocketgeek.com>
 * @copyright  Copyright (c) 2019 Chad Butler
 * @license    https://github.com/rocketgeek/jquery_tabs/blob/master/LICENSE.md GNU General Public License 3
 *
 * Copyright (c) 2019 Chad Butler, RocketGeek
 *
 * This library is open source and GPL licensed. I hope you find it useful
 * for your project(s). Attribution is appreciated ;-)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class RocketGeek_Sendy_API {
	
	/**
	 * The Sendy API key
	 *
	 * @var string
	 */
	public $api_key;
	
	/**
	 * The Sendy API URL (no trailing slash)
	 *
	 * @var string
	 */
	public $api_url;
	
	/**
	 * The Sendy List ID
	 *
	 * @var string
	 */
	public $list_id;
	
	/**
	 * cURL toggle
	 *
	 * @var boolean (true to enable cURL|false to use wp_remote_post())
	 */
	public $use_curl;
	
	/**
	 * Error container
	 *
	 * @var stdClass WP_Error object
	 */
	public $error;
	
	/**
	 * API Endpoints
	 *
	 * @var string
	 */
	public $subscribe_endpoint               = '/subscribe';
	public $unsubscribe_endpoint             = '/unsubscribe';
	public $delete_endpoint                  = '/api/subscribers/delete.php';
	public $subscription_status_endpoint     = '/api/subscribers/subscription-status.php';
	public $active_subscriber_count_endpoint = '/api/subscribers/active-subscriber-count.php';
	public $create_campaign_endpoint         = '/api/campaigns/create.php';
	
	/**
	 * Class constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param  array $settings {
	 *     An array of optional settings for the object.
	 *
	 *     @type  string  $api_key
	 *     @type  string  $api_url
	 *     @type  string  $list_id
	 *     @type  boolean $use_curl  True to use cURL, false to use wp_remote_post() (default:false)
	 * }
	 */
	public function __construct( $settings = array() ) {
		$this->api_key  = ( isset( $settings['api_key']  ) ) ? $settings['api_key']  : $this->api_key;
		$this->api_url  = ( isset( $settings['api_url']  ) ) ? $settings['api_url']  : $this->api_url;
		$this->list_id  = ( isset( $settings['list_id']  ) ) ? $settings['list_id']  : $this->list_id;
		$this->use_curl = ( isset( $settings['use_curl'] ) ) ? $settings['use_curl'] : false;
	}

	/**
	 * Determines the list ID.
	 *
	 * @since 0.1.0
	 *
	 * @param  string $list_id
	 * @return string $list_id
	 */
	public function get_list_id( $list_id ) {
		return ( $list_id ) ? $list_id : $this->list_id;
	}
	
	/**
	 * Get active subscriber count.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $list_id  List ID to check (optional|default: $this->list_id)
	 * @return int     $result
	 */
	public function subscriber_count( $list_id = false ) {
		$result = $this->post(
			$this->api_url . $this->active_subscriber_count_endpoint, 
			$fields = array(
				'api_key' => $this->api_key, 
				'list_id' => $this->get_list_id( $list_id ),
				)
			);
		return $result['body'];
	}
	
	/**
	 * Subscriber status.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email    The email to check (required)
	 * @param  string  $list_id  List ID to check (optional|default: $this->list_id)
	 * @return string  $result {
	 *     Possible return strings:
	 *     Success: Subscribed
	 *     Success: Unsubscribed
	 *     Success: Unconfirmed
	 *     Success: Bounced
	 *     Success: Soft bounced
	 *     Success: Complained
	 *     Error: No data passed
	 *     Error: API key not passed
	 *     Error: Invalid API key
	 *     Error: Email not passed
	 *     Error: List ID not passed
	 *     Error: Email does not exist in list
	 * }
	 */
	public function subscriber_status( $email, $list_id = false ) {
		$result  = $this->post(
			$this->api_url . $this->subscription_status_endpoint, 
			$fields = array(
				'api_key' => $this->api_key, 
				'email'   => $email, 
				'list_id' => $this->get_list_id( $list_id ),
			)
		);
		return $result['body'];
	}
	
	/**
	 * Subscribe a user to a list
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email          The email to subscribe (required)
	 * @param  array   $custom_fields  Any additional custom fields (optional)
	 * @param  string  $list_id        List ID to subscribe to (optional|default: $this->list_id)
	 * @return mixed   $result {
	 *     Boolean (true) on success. Other possibilities (errors) are strings:
	 *     Error:   Some fields are missing.
	 *     Error:   Invalid email address.
	 *     Error:   Invalid list ID.
	 *     Error:   Already subscribed.
	 *     Error:   Email is suppressed.
	 * }
	 */
	public function subscribe( $email, $custom_fields = false, $list_id = false ) {
		$fields = array(
			'api_key' => $this->api_key,
			'email'   => $email, 
			'list'    => $this->get_list_id( $list_id ),
			'boolean' => "true",
		);
		if ( $custom_fields ) {
			$custom_field_keys = array_keys( $custom_fields );
			$i = 0;
			foreach( $custom_fields as $key => $custom_field ) {
				if ( 'name' == $key ) {
					$fields['name'] = $custom_field;
				} else {
					$fields[ $custom_field_keys[ $i ] ] = $custom_field;
				}
				$i++;
			}
		}
		$result = $this->post( $this->api_url . $this->subscribe_endpoint, $fields );
		return $result['body'];
	}
	
	/**
	 * Unsubscribe a user.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email    The email to unsubscribe (required)
	 * @param  string  $list_id  List ID to unsubscribe from (optional|default: $this->list_id)
	 * @return mixed   $result {
	 *     Boolean (true) on success. Other possibilities (errors) are strings:
	 *     Error: Some fields are missing.
	 *     Error: Invalid email address.
	 *     Error: Email does not exist.
	 * }
	 */
	public function unsubscribe( $email, $list_id = false ) {
		$result = $this->post( $this->api_url . $this->unsubscribe_endpoint, $fields = array(
			'api_key' => $this->api_key, 
			'email'   => $email, 
			'list'    => $this->get_list_id( $list_id ),
			'boolean' => "true",
		) );
		return $result['body'];
	}
	
	/**
	 * Delete a user.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email    The email to delete (required)
	 * @param  string  $list_id  List ID to delete user from (optional|default: $this->list_id)
	 * @return mixed   $result {
	 *     Boolean (true) on success. Other possibilities (errors) are strings:
	 *     Error: No data passed
	 *     Error: API key not passed
	 *     Error: Invalid API key
	 *     Error: List ID not passed
	 *     Error: List does not exist
	 *     Error: Email address not passed
	 *     Error: Subscriber does not exist
	 * }
	 */
	public function delete( $email, $list_id = false ) {
		$result = $this->post( $this->api_url . $this->delete_endpoint, $fields = array(
			'api_key' => $this->api_key, 
			'email'   => $email, 
			'list_id' => $this->get_list_id( $list_id ),
		) );
		return $result['body'];
	}

	/**
	 * Create and send a Campaign
	 *
	 * @since 0.1.0
	 *
	 * @param  array  $data {
	 *     @type  string  $api_key
	 *     @type  string  $from_name
	 *     @type  string  $from_email
	 *     @type  string  $reply_to
	 *     @type  string  $subject
	 *     @type  string  $plain_text
	 *     @type  string  $html_text
	 *     @type  string  $list_ids              (comma-separated)
	 *     @type  string  $segment_ids           Required only if send_campaign is 1 & no list_ids are passed. Should be single or comma-separated. IDs are in the segments setup page.
	 *     @type  string  $exclude_list_ids      Lists to exclude from campaign. Should be single or comma-separated. Encrypted/hashed ids are under View all lists as ID. (optional)
	 *     @type  string  $exclude_segments_ids  Segments to exclude from campaign. Should be single or comma-separated. IDs are in the segments setup page. (optional)
	 *     @type  string  $brand_id              (required if you are creating a draft, send_campaign set to 0 or left as default)
	 *     @type  string  $query_string          eg. Google Analytics tags (optional)
	 *     @type  string  $send_campaign         (set to 1 if you want to send the campaign)
	 * }
	 * @return string {
	 *     Possible return strings
	 *     Success: Campaign created
	 *     Success: Campaign created and now sending
	 *     Error: No data passed
	 *     Error: API key not passed
	 *     Error: Invalid API key
	 *     Error: From name not passed
	 *     Error: From email not passed
	 *     Error: Reply to email not passed
	 *     Error: Subject not passed
	 *     Error: HTML not passed
	 *     Error: List or segment ID(s) not passed
	 *     Error: One or more list IDs are invalid
	 *     Error: One or more segment IDs are invalid
	 *     Error: List or segment IDs does not belong to a single brand
	 *     Error: Brand ID not passed
	 *     Error: Unable to create campaign
	 *     Error: Unable to create and send campaign
	 *     Error: Unable to calculate totals
	 * }
	 */
	public function create_campaign( $data )	{
		$url = $this->api_url . $this->create_campaign_endpoint;

		if ( ! isset( $data['api_key'] ) ) {
			$data['api_key'] = $this->api_key;
		}

		if ( ! isset( $data['list_ids'] ) ) {
			$data['list_ids'] = $this->list_id;
		}
		
		$campaign = $this->post( $url, $data );
		return $campaign['body'];
	}
	
	/**
	 * Post to Sendy Endpoints
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $endpoint
	 * @param  array   $fields
	 * @return string  $result
	 */
	private function post( $enpoint, $fields ) {
		
		if ( $this->use_curl ) {
			$response = $this->curl_post( $enpoint, $fields );
		} else {
			$response = wp_remote_post( esc_url_raw( $enpoint ), array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $fields,
				'cookies' => array()
				)
			);
		}
		
		if ( is_wp_error( $response ) ) {
			$this->error = $response->get_error_message();
			return "Error: " . $this->error;
		} else {
			return $response;
		}
	}
	
	/**
	 * Original post function using cURL.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $url
	 * @param  array   $fields
	 * @return string  $result
	 */
	private function curl_post( $url, $fields ) {
		$ch = curl_init();
		curl_setopt_array( $ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POSTFIELDS => http_build_query( $fields )
		));
		$result = curl_exec( $ch );
		$http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$result = array( 'result'=>$result, 'status'=>$http_status );
		curl_close( $ch );
		return $result;
	}
}
