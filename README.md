# RocketGeek Sendy API Wrapper for WordPress Plugins

This is an object class for WordPress plugins to make quick use of the Sendy API. You can include this library according to the instructions and use the Sendy API in your plugin (or theme).

You will need a copy of Sendy, as well as an API key for your Sendy installation in order for this to work.  If you do not have a copy of Sendy, please consider using my affiliate link.  Purchasing through an affiliate link is the same price as a regular license, and your purchase helps support further development of this project (and others like it).

* https://rkt.bz/sendy
* https://sendy.co/?ref=ZUdzM

## Getting Started

### Prerequisites

### Using the object class

Include the object class.

```
include_once( YOUR_PROJECT_PATH . 'rocketgeek-sendy-api/rocketgeek-sendy-api.php' );
```

Add the object class to your project:
```
global $sendy;
$settings = array( 'api_key'=>'your_sendy_api_key', 'api_url'=>'https://your_sendy_api_url.com' );
$sendy = new RocketGeek_Sendy_API( $settings );
```

Subscribe a user:
```
// Just an email (minimum required data):
$result = $sendy->subscribe( 'joe@smith.com' );

// Email, name (custom field), and custom list ID:
$result = $sendy->subscribe( 'joe@smith.com', array( 'name'=>'Joe Smith' ), '123ABC456DEG' );
```

Results will be:
 * 'success' if the user was successfully subscribed
 * 'already_subscribed' if user is subscribed already
 * 'error' if the result failed

Unsubscribe a user:
```
$result = $sendy->unsubscribe( $email );
```

Get the subscriber count of a specific list:
```
$sendy->subscriber_count( $list_id );
```

Check a user's list status:
```
$sendy->subscriber_status( $email );
```

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