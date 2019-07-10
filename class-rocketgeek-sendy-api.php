<?php
/**
 * Sendy API wrapper for WordPress applications.
 *
 * A wrapper class for the Sendy API using the WordPress
 * HTTP API.  Based on the Sendy API class by Nick Thompson
 * (https://github.com/nickian/Sendy-Extended-PHP-API-Wrapper) and modified for use in
 * Wordpress without cURL. Formatted to WordPress coding standards.
 *
 * Some additional utility functions were added to handle the WordPress
 * HTTP API as an array. This was originally taken from stackoverflow
 * user scozy and modified for this purpose. These functions are noted
 * individually in the object class.
 * Original concept:   https://stackoverflow.com/questions/23062537/
 * Modified framework: https://gist.github.com/rocketgeek/2f9fa1d36fd8fdcb788489e2cff4a276
 *
 * Key Documentation
 * Sendy API:          https://sendy.co/api
 * WordPress HTTP API: https://developer.wordpress.org/plugins/http-api/
 * This class:         https://github.com/rocketgeek/sendy-api
 * Nick's class:       https://github.com/nickian/Sendy-Extended-PHP-API-Wrapper
 *
 * @author  Chad Butler
 * @version 0.1.0
 */

/**
 * Usage:
 *
 * $settings = array( 'api_key'=>'your_sendy_api_key', 'api_url'=>'https://your_sendy_api_url.com' );
 * $sendy = new WP_Members_Sendy_API( $settings );
 * 
 * Subcribe a user:
 * $result = $sendy->subscribe( 'Joe', 'joe@smith.com', 'ASDFbaDF7se23Jad4JOH', true );
 * - results will be:
 * - - 'success' if the user was successfully subscribed
 * - - 'already_subscribed' if user is subscribed already
 * - - 'error' if the result failed
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class RocketGeek_Sendy_API {
	
	public $api_key;
	public $api_url;
	public $error;
	
	// Endpoints
	public $subscribe_endpoint = '/subscribe';
	public $unsubscribe_endpoint = '/unsubscribe';
	public $delete_endpoint = '/api/subscribers/delete.php'; // @todo!!
	public $subscription_status_endpoint = '/api/subscribers/subscription-status.php';
	public $active_subscriber_count_endpoint = '/api/subscribers/active-subscriber-count.php';
	public $create_campaign_endpoint = '/api/campaigns/create.php';
	
	/**
	 * Class constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct( $settings ) {
		$this->api_key = $settings['api_key'];
		$this->api_url = $settings['api_url'];
	}
	
	/**
	 * Post to Sendy Endpoints
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $url
	 * @param  array   $fields
	 * @param  bool    $use_curl
	 * @return json    $result
	 */
	public function post( $url, $fields, $use_curl = false ) {
		
		if ( $use_curl ) {
			$response = $this->curl_post( $url, $fields );
		} else {
			$response = wp_remote_post( esc_url_raw( $url ), array(
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
			$response = $this->get_response( $response );
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
	public function curl_post( $url, $fields ) {
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
		$campaign = $this->decode_response( $this->post( $url, $data ) );
	}
	
	/**
	 * Get active subscriber count.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $list_id
	 * @return int     $result
	 */
	public function subscriber_count( $list_id ) {
		$subscriber_count = $this->post(
			$this->api_url . $this->active_subscriber_count_endpoint, 
			$fields = array(
				'api_key' => $this->api_key, 
				'list_id' => $list_id
				)
			);
		$subscriber_count = $this->decode_response( $subscriber_count, 'subscriber_count' );
		return $subscriber_count['result'];
	}
	
	/**
	 * Subscriber status.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email
	 * @param  string  $list_id
	 * @return string  $result
	 */
	public function subscriber_status( $email, $list_id ) {
		$subscriber_status = $this->post(
			$this->api_url . $this->subscription_status_endpoint, 
			$fields = array(
				'api_key' => $this->api_key, 
				'email'   => $email, 
				'list_id' => $list_id
			)
		);
		$subscriber_status = $this->decode_response( $subscriber_status, 'subscriber_status' );
		return $subscriber_status['result'];
	}
	
	/**
	 * Subscribe a user to a list
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $name
	 * @param  string  $email
	 * @param  string  $list_id
	 * @param  bool    $boolean
	 * @param  array   $custom_fields
	 * @return string  $status
	 *
	 * @todo Get rid of $name and make it part of $custom_fields
	 */
	public function subscribe( $name, $email, $list_id, $boolean, $custom_fields = false ) {
		$url = $this->api_url . $this->subscribe_endpoint;
		$fields = array(
			'api_key' => $this->api_key,
			'name'    => $name,
			'email'   => $email, 
			'list'    => $list_id,
			'boolean' => $boolean
		);
		if ( $custom_fields ) {
			$custom_field_keys = array_keys( $custom_fields );
			$i = 0;
			foreach( $custom_fields as $custom_field ) {
				$fields[ $custom_field_keys[ $i ] ] = $custom_field;
				$i++;
			}
		}
		$subscribe = $this->decode_response( $this->post( $url, $fields ), 'subscribe' );
		return $subscribe['result'];
	}
	
	/**
	 * Unsubscribe a user.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $email
	 * @param  string  $list_id
	 * @param  bool    $boolean
	 * @return string  $status
	 */
	public function unsubscribe( $email, $list_id, $boolean ) {
		$url = $this->api_url . $this->unsubscribe_endpoint;
		$unsubscribe = $this->post( $url, $fields = array(
			'api_key' => $this->api_key, 
			'email'   => $email, 
			'list'    => $list_id
		));
		$unsubscribe = $this->decode_response( $this->post( $url, $fields ), 'unsubscribe' );
		return $unsubscribe['result'];
	}

	/**
	 * Prepares HTTP API response.
	 *
	 * @since 0.1.0
	 *
	 * @param  string $response
	 * @return array  $response
	 */
	function get_response( $response ) {
		$api_response = wp_remote_retrieve_body( $response );
		return $this->html_to_obj( $api_response );
	}
	
	/**
	 * Utility function converts HTML to DOM object
	 *
	 * A utility function used in coverting Sendy's HTML string response 
	 * to an array. This function uses the DOM object to convert.
	 * 
	 * Original concept from stackoverflow user scozy:
	 * https://stackoverflow.com/questions/23062537/
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $html
	 * @return object  $object
	 */
	public function html_to_obj( $html ) {
		$dom = new DOMDocument();
		$dom->loadHTML( $html );
		return $this->element_to_obj( $dom->documentElement );
	}

	/**
	 * Utility function converts HTML to DOM object
	 *
	 * A utility function used in coverting Sendy's HTML string response 
	 * to an array. This function is used by the html_to_obj method to 
	 * recurse the HTML result.
	 * 
	 * Original concept from stackoverflow user scozy:
	 * https://stackoverflow.com/questions/23062537/
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $element
	 * @return object  $obj
	 */
	function element_to_obj( $element ) {
		if ( isset( $element->tagName ) ) {
			$obj = array( 'tag' => $element->tagName );
		}
		if ( isset( $element->attributes ) ) {
			foreach ( $element->attributes as $attribute ) {
				$obj[ $attribute->name ] = $attribute->value;
			}
		}
		if ( isset( $element->childNodes ) ) {
			foreach ( $element->childNodes as $sub_element ) {
				if ( $sub_element->nodeType == XML_TEXT_NODE ) {
					$obj['html'] = $sub_element->wholeText;
				} elseif ( $sub_element->nodeType == XML_CDATA_SECTION_NODE ) {
					$obj['html'] = $sub_element->data;
				} else {
					$obj['children'][] = $this->element_to_obj( $sub_element );
				}
			}
		}
		return ( isset( $obj ) ) ? $obj : null;
	}

	/**
	 * Decodes the API response.
	 *
	 * @since 0.1.0
	 *
	 * @param  array   $response
	 * @param  string  $action
	 * @return array   $result
	 */
	function decode_response( $response, $action ) {

		switch ( $action ) {
			case 'subscribe':
				foreach ( $response['children'] as $child ) {
					if ( 'head' == $child['tag'] ) {
						foreach ( $child['children'] as $value ) {
							if ( 'title' == $value['tag'] ) {
								if ( "You're already subscribed!" == $value['html'] ) {
									$result['result']  = 'already_subscribed';
								} elseif ( "You're subscribed!" == $value['html'] ) {
									$result['result']  = 'success';
								} else {
									$result['result']  = 'error';
								}
								break;
							}
						}
					}
				}
				break;
			case 'unsubscribe':
				if ( "You're unsubscribed." == $response['children'][2]['children'][1]['children'][0]['html'] ) {
					$result['result']  = 'success';
				} elseif ( "Email does not exist." == $response['children'][2]['children'][1]['children'][0]['html'] ) {
					$result['result']  = 'user_not_found';
				} else {
					$result['result']  = 'error';
				}
				break;
			case 'subscriber_status':
				$value = $response['children'][0]['children'][0]['html'];
				if ( "Subscribed" == $value ) {
					$result['result'] = "subscribed";
				} elseif ( "Unsubscribed" == $value ) {
					$result['result'] = "unsubscribed";
				} elseif ( "Email does not exist in list" == $value ) {
					$result['result'] = "user_not_found";
				} else {
					$result['result'] = 'error';
				}
				break;
			case 'subscriber_count':
				$result['result'] =  $response['children'][0]['children'][0]['html'];
				break;
		}
		
		return $result;
	}
}
