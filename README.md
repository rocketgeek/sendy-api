# RocketGeek Sendy API Wrapper for WordPress Plugins

This is an object class for WordPress plugins to make quick use of the Sendy API. You can include this library according to the instructions and use the Sendy API in your plugin (or theme).

You will need a copy of Sendy, as well as an API key for your Sendy installation in order for this to work.  If you do not have a copy of Sendy, please consider using my affiliate link.  Purchasing through an affiliate link is the same price as a regular license, and your purchase helps support further development of this project (and others like it).

* https://rkt.bz/sendy
* https://sendy.co/?ref=ZUdzM

## Getting Started

### Prerequisites

Sendy is a stand-alone email application. You will need to have a valid install of Sendy. [Get more information](https://rkt.bz/sendy).

You'll also need Amazon AWS SES (Simple Email Service). [Get more information](https://aws.amazon.com/ses/). 

### Initialize the object class

This is a wrapper class for the Sendy API.  To use it in your project, simply [clone it](https://github.com/rocketgeek/sendy-api.git) or [download it](https://github.com/rocketgeek/sendy-api/archive/master.zip) and include the PHP file in your project.

```
include_once( YOUR_PROJECT_PATH . 'rocketgeek-sendy-api/rocketgeek-sendy-api.php' );
```

To use the object class in your project, pass your Sendy API key, the Sendy API URL, and the list ID as an array when you initialize the class:
```
global $sendy;
$settings = array( 'api_key'=>'your_sendy_api_key', 'api_url'=>'https://your_sendy_api_url.com, 'list_id'=>'123ABC456DEG' );
$sendy = new RocketGeek_Sendy_API( $settings );
```
Note in the example above, I have globalized the instance. You can use whatever nomenclature you like, just consider the possibility of naming collisions. So choose wisely.

Also, it is optional to pass the list ID when initializing the class. But doing so at initilization makes later calls a little simpler. If you leave out the list ID, you'll need to pass it with each method.  The examples below generally assume you've set the list ID when initializing (although some examples using a custom list ID are included).

### Subscribe a user

The minimum data to subscribe a user is an email address.  Of course, this assumes that you passed your API key and API URL when initializing the class. If you did not do that, you can pass those values to the subscribe method as well. Even if you did, you can pass a custom list ID if you want to subscribe the user to a custom list ID.
```
// Just an email (minimum required data):
$result = $sendy->subscribe( 'joe@smith.com' );

// Email, name (custom field), and custom list ID:
$result = $sendy->subscribe( 'joe@smith.com', array( 'name'=>'Joe Smith' ), '123ABC456DEG' );
```
Possible results:
* true
* Some fields are missing.
* Invalid email address.
* Invalid list ID.
* Already subscribed.
* Email is suppressed

### Unsubscribe or Delete a user:

To unsubscribe a user, pass the user's plain text email address to the `unsubscribe()` method:
```
$result = $sendy->unsubscribe( $email );
```
Possible results:
* true
* Some fields are missing.
* Invalid email address.
* Email does not exist.

To delete a user, pass the user's plain text email address to the `delete()` method:

```
$result = $sendy->delete( $email );
```
Possible results:
* true
* No data passed
* API key not passed
* Invalid API key
* List ID not passed
* List does not exist
* Email address not passed
* Subscriber does not exist

### Get the subscriber count
```
$sendy->subscriber_count( $list_id );
```

### Check user status
```
$sendy->subscriber_status( $email );
```

### Create and send a campaign

You can create and send a campaign through the API by specifying the following in an array:

* `api_key` Required if not already set in when the wrapper class was initialized)
* `from_name` the 'From name' of your campaign
* `from_email` the 'From email' of your campaign
* `reply_to` the 'Reply to' of your campaign
* `title` the 'Title' of your campaign
* `subject` the 'Subject' of your campaign
* `plain_text` the 'Plain text version' of your campaign (optional)
* `html_text` the 'HTML version' of your campaign
* `list_ids` Required only if you set send_campaign to 1 and no segment_ids are passed in. List IDs should be single or comma-separated. The encrypted & hashed ids can be found under View all lists section named ID.
* `segment_ids` Required only if you set send_campaign to 1 and no list_ids are passed in. Segment IDs should be single or comma-separated. Segment ids can be found in the segments setup page.
* `exclude_list_ids` Lists to exclude from your campaign. List IDs should be single or comma-separated. The encrypted & hashed ids can be found under View all lists section named ID. (optional)
* `exclude_segments_ids` Segments to exclude from your campaign. Segment IDs should be single or comma-separated. Segment ids can be found in the segments setup page. (optional)
* `brand_id` Required only if you are creating a 'Draft' campaign (send_campaign set to 0 or left as default). Brand IDs can be found under 'Brands' page named ID
* `query_string` eg. Google Analytics tags (optional) 
* `send_campaign` Set to `1` if you wan tot send the campaign as well and not just create a draft. Default is `0`.

Example of sending a campaign:
```
// If no API key or List ID is passed, the default value
// for the initialized class will be used.
$data = array(
	'from_name'     => 'Sendy Test',
	'from_email'    => 'email_from@yourdomain.com',
	'reply_to'      => 'replyto@yourdomain.com',
	'title'         => 'My Test Campaign',
	'subject'       => 'A Sendy Campaign Test',
	'plain_text'    => 'The plain text content of a Sendy campaign test.',
	'html_text'     => '<p>The <strong>HTML</strong> text content of a Sendy campaign test</p>',
	'send_campaign' => 0 // Set to 1 to send campaign
);
$result = $sendy->create_campaign( $data );
```
Possible results:
* Campaign created
* Campaign created and now sending
* No data passed
* API key not passed
* Invalid API key
* From name not passed
* From email not passed
* Reply to email not passed
* Subject not passed
* HTML not passed
* List or segment ID(s) not passed
* One or more list IDs are invalid
* One or more segment IDs are invalid
* List or segment IDs does not belong to a single brand
* Brand ID not passed
* Unable to create campaign
* Unable to create and send campaign
* Unable to calculate totals

## Built With

* [WordPress](https://make.wordpress.org/)

## Contributing

I do accept pull requests. However, make sure your pull request is properly formatted. Also, make sure your request is generic in nature. In other words, don't submit things that are case specific - that's what forks are for. The library also has hooks that follow WP standards - use 'em.

## Versioning

I use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/rocketgeek/jquery_tabs/tags). 

## Authors

* **Chad Butler** - [ButlerBlog](https://github.com/butlerblog)
* **RocketGeek** - [RocketGeek](https://github.com/rocketgeek)

## License

This project is licensed under the GPL v3 License - see the [LICENSE.md](LICENSE.md) file for details.

I hope you find this project useful. If you use it your project, attribution is appreciated.