# WP Read-Only #

* Contributors: alfreddatakillen
* Tags: wordpress, amazon, s3, readonly
* Requires at least: 3.3
* Tested up to: 3.3.1
* Stable tag: 1.2
* License: GPLv2

Plugin for running your Wordpress site without Write Access to the
web directory. Amazon S3 is used for uploads/binary storage.
This plugin was made with cluster/load balancing server setups in
mind - where you do not want your WordPress to write anything to
the local web directory.

## Description ##

This plugin will put your media uploads on Amazon S3. Unlike other
S3 plugins, this plugin does not require your uploads to first be
stored in your server's upload directory, so this plugin will work
fine on WordPress sites where the web server have read-only access
to the web directory.

*	Wordpress image editing will still work fine (just somewhat slower).
*	Full support for XMLRPC uploads.

This plugin was made for Wordpress sites deployed in a (load balancing)
cluster across multiple webservers, where you do not want your WordPress
to write anything to the local web directory.

Note: You still need write access to the system /tmp directory for
this plugin to work. It will use the system /tmp directory for
temporary storage during uploads, image editing/scaling, etc.

### Wordpress MU/Multisite ###

This plugin works out-of-the box with Wordpress Multisite/MU.

You will find the settings for this plugin in the Network Admin, when
in a MU/Multisite environment.

## Installation ##

1. Put the plugin in the Wordpress `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Enter your Amazon S3 settings in `Settings` > `WPRO Settings`.

### Alternative: Configure by constants in wp-config.php ###

Instead of configuring the plugin in `Settings` > `WPRO Settings`,
you may use constants in your `wp-config.php`. This might be an
option for you, if you want the plugin to be a "must-use plugin",
or if you do not want your users to access the settings from the
admin.

Those are the constants

*	define('WPRO_ON', true); // Enables the plugin and use
	configuration from contants.
*	define('WPRO_SERVICE', 's3'); // Amazon S3 is the service.
*	define('WPRO_FOLDER', 'some/path/here'); // Prepend all URI paths
	at S3 with this folder. In most cases, you probably want this
	to be empty.
*	define('WPRO_AWS_KEY', 'your aws key');
*	define('WPRO_AWS_SECRET', 'your aws secret');
*	define('WPRO_AWS_BUCKET', 'MyBucket'); // The name of the Amazon
	S3 bucket where your files should be stored.
*	define('WPRO_AWS_VIRTHOST', 'files.example.org'); // If you have
	a virthost for your Amazon S3 bucket, it should be there.
*	define('WPRO_AWS_ENDPOINT', 's3-eu-west-1.amazonaws.com'); // The
	Amazon endpoint datacenter where your S3 bucket is. Se list of
	endpoints below.

Those are the AWS endpoints:

*	`s3.amazonaws.com` - US East Region (Standard)
*	`s3-us-west-2.amazonaws.com` - US West (Oregon) Region
*	`s3-us-west-1.amazonaws.com` - US West (Northern California) Region
*	`s3-eu-west-1.amazonaws.com` - 'EU (Ireland) Region
*	`s3-ap-southeast-1.amazonaws.com` - Asia Pacific (Singapore) Region
*	`s3-ap-northeast-1.amazonaws.com` - Asia Pacific (Tokyo) Region
*	`s3-sa-east-1.amazonaws.com` - South America (Sao Paulo) Region

## Adding backends ##

You can extend the WPRO functionality by registering your own backend, in
your theme's function.php or in another plugin. Just wait for the
wpro_setup_backends action to happen, and then wpro()->backends->register(...);
Something like this:

	class MyBackend {
		...
	}
	$my_backend = new MyBackend();
	function add_my_backend() {
		wpro()->backends->register($my_backend);
	}
	add_action('wpro_setup_backends', 'add_my_backend');

## Q & A ##

### Will this plugin work in Wordpress MU/Multisite environments? ###

Yes.

### Where do I report bugs? ###

Report any issues at the github issue tracker:
https://github.com/alfreddatakillen/wpro/issues

### Where do I contribute with code, bug fixes, etc.? ###

At github:
https://github.com/alfreddatakillen/wpro

And, plz, use tabs for indenting! :)

### What should I think of when digging the code? ###

If you define the constant WPRO_DEBUG in your wp-config.php, then
some debug data will be written to your PHP error log.

This is how I check me logs (just a tip):

	sudo stdbuf -oL -eL tail -f /var/log/apache2/wpro-error.log | stdbuf -oL -eL sed -r 's/^[^]]+][^]]+][^]]+][^]]+]//' | sed -r 's/, referer: [^ ]+$//'

There is a Makefile, which will help you to run the unit tests.
Note: You need [composer](https://getcomposer.org/ "composer") to do the unit testing.

### Why are my thumbnails not regenerated when editing an image in the image editor? ###

That is an issue with WordPress itself. WordPress will only
generate thumbs that are smaller than the original. So, let's
say you first upload a large image and all thumbs are generated.
Then, you crop the image to a size smaller than the largest
thumb. Then the largest thumb will not be regenerated, only the
thumbs smaller than the cropped image.

This is a WordPress issue, not a WPRO issue.

### What about the license? ###

Read more about GPLv2 here:
http://www.gnu.org/licenses/gpl-2.0.html

### Do you like beer? ###

If we meet some day, and you think this stuff is worth it, you may buy
me a gluten free beer (or a glass of red wine) in return.
(GPLv2 still applies.)

## Changelog ##

### 1.3 ###

*	Split code into different files and more specific classes.
*	The plugin is more modular than before. Use WordPress style hooks,
	actions and registering to add/change functionality.
*	Added a lot of debug logging.
*	Added some unit testing. More tests needs to be written.
*	Added backend: Custom filesystem directory.

### 1.2 ###

*	Added temp directory configuration option.
*	Fixed issue with /tmp directory filling up with empty folders. (Issue #3)

### 1.1 ###

*	Added support for configuring by constants in `wp-config.php`.
*	Plugin now works in open_basedir and safe_mode environments.
*	Implemented our own sys_get_temp_dir for PHP < 5.2.1 compatibility.
*	Fixed bug that left a lot of temporary directories in the system tmp.
*	In a Multisite/MU environment, the settings are global for all sites,
	in the Network Admin.

Creds to [Sergio Livi](https://github.com/serl "Sergio Livi")
and [Keitaroh Kobayashi](https://github.com/keichan34 "Keitaroh Kobayashi")
for contributing with code! Also, thanks to
[mavesell](https://github.com/maveseli "mavesell")
and [nmagee](https://github.com/nmagee "nmagee") for feedback and comments!

### 1.0 ###

*	The first public release.

## Roadmap ##

Todo list:

*	Have a look at those commits:
		https://github.com/roryatbrightoak/wpro/commit/f594b7699ec49e1ff8d14f5898b68cdfeeb6e6cf
		https://github.com/dechuck/wpro/commit/3b9fd6b158963a16c2a9ce541a5af241986776b5#diff-d41d8cd98f00b204e9800998ecf8427e
		https://github.com/webngay/wpro/commit/72d12f723097716065ebee995a8762a6a3e2770b
		https://github.com/genu/wpro/commits/master
		https://github.com/Link7/wpro/commits/master
		https://github.com/loenex/wpro/commits/master
		https://github.com/remkade/wpro/commit/5992e052337c4f1cf72585d00f4c44401f69f85a
		https://github.com/serl/wpro/commit/ef61c4f8b6c59a8847ef500fd607de9bc5cda3e0

*	Add support for FTP:ing uploads to somewhere, as an alternative to
	Amazon S3.
*	For WPMU: Store media in a single bucket, but separate them by site, in
	sub-folders.
*	Only handle `new` medias when activating this plugin on an existing
	site. Today it's an all-or-nothing approach, and you will have to
	migrate your media to S3.
*	Are we supporting all S3 regions?
*	Check out JSON API support.
*	Buddypress-avatar upload does not work. Is this related? https://github.com/ramalveyra/wpro/commit/5e74ac0729f020b0f231766e4504c1a2fff4f919
*	Delete media support.
*	Testa wordpress importer.
*	Custom header images does not work. I guess this is unfixable?
*	Is CURLOPT_FOLLOWLOCATION still a problem? Test it.
*	Make sure we are tagging the versions in the git repo properly. Also check out: https://getcomposer.org/doc/articles/aliases.md under "branch alias".
*	At unsuccessful upload, an attachment is still created in the wp database. Thats wrong.

