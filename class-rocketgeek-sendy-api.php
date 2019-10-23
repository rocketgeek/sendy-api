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
 * @version    0.1.0
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
	
	public $api_key;
	public $api_url;
	public $list_id;
	public $use_curl;
	public $error;
	
	// Endpoints
	public $subscribe_endpoint = '/subscribe';
	public $unsubscribe_endpoint = '/unsubscribe';
	public $delete_endpoint = '/api/subscribers/delete.php';
	public $subscription_status_endpoint = '/api/subscribers/subscription-status.php';
	public $active_subscriber_count_endpoint = '/api/subscribers/active-subscriber-count.php';
	public $create_campaign_endpoint = '/api/campaigns/create.php';
	
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
	 * }
	 */
	public function __construct( $settings = array() ) {
		$this->api_key = ( isset( $settings['api_key'] ) ) ? $settings['api_key'] : $this->api_key;
		$this->api_url = ( isset( $settings['api_url'] ) ) ? $settings['api_url'] : $this->api_url;
		$this->list_id = ( isset( $settings['list_id'] ) ) ? $settings['list_id'] : $this->list_id;
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
	 *     @type  string  $list_ids (comma-separated)
	 *     @type  string  $brand_id (required if you are creating a draft, send_campaign set to 0 or left as default)
	 *     @type  string  $send_campaign (set to 1 if you want to send the campaign)
	 * }
	 */
	public function create_campaign( $data )	{
		$url = $this->api_url . $this->create_campaign_endpoint;
		$data['api_key'] = $this->api_key;
		$campaign = $this->post( $url, $data );
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
		return $result;
	}
	
	/**
	 * Subscriber status.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email    The email to check (required)
	 * @param  string  $list_id  List ID to check (optional|default: $this->list_id)
	 * @return string  $result
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
		return $result;
	}
	
	/**
	 * Subscribe a user to a list
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email          The email to subscribe (required)
	 * @param  boolean $boolean
	 * @param  array   $custom_fields  Any additional custom fields (optional)
	 * @param  string  $list_id        List ID to subscribe to (optional|default: $this->list_id)
	 * @return string  $result
	 *
	 * @todo Get rid of $name and make it part of $custom_fields
	 */
	public function subscribe( $email, $boolean, $custom_fields = false, $list_id = false ) {
		$fields = array(
			'api_key' => $this->api_key,
			'email'   => $email, 
			'list'    => $this->get_list_id( $list_id ),
			'boolean' => "true",
		);
		if ( $custom_fields ) {
			$custom_field_keys = array_keys( $custom_fields );
			$i = 0;
			foreach( $custom_fields as $custom_field ) {
				$fields[ $custom_field_keys[ $i ] ] = $custom_field;
				$i++;
			}
			if ( isset( $custom_fields['name'] ) ) {
				$fields['name'] = $custom_fields['name'];
			}
		}
		$result = $this->post( $this->api_url . $this->subscribe_endpoint, $fields );
		return $result;
	}
	
	/**
	 * Unsubscribe a user.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email    The email to unsubscribe (required)
	 * @param  string  $list_id  List ID to unsubscribe from (optional|default: $this->list_id)
	 * @return string  $result
	 */
	public function unsubscribe( $email, $list_id = false ) {
		$result = $this->post( $this->api_url . $this->unsubscribe_endpoint, $fields = array(
			'api_key' => $this->api_key, 
			'email'   => $email, 
			'list'    => $this->get_list_id( $list_id ),
			'boolean' => "true",
		) );
		return $result;
	}
	
	/**
	 * Delete a user.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email    The email to delete (required)
	 * @param  string  $list_id  List ID to delete user from (optional|default: $this->list_id)
	 * @return string  $result
	 */
	public function delete( $email, $list_id = false ) {
		$result = $this->post( $this->api_url . $this->delete_endpoint, $fields = array(
			'api_key' => $this->api_key, 
			'email'   => $email, 
			'list_id' => $this->get_list_id( $list_id ),
		) );
		return $result;
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
