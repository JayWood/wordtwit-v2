# WordTwit - WordPress Twitter Plugin
Contributors: BraveNewCode, dalemugford, duanestorey   
Requires at least: 4.5  
Tested up to: 4.0   
Stable tag: 4.0   

## Disclaimer/creds
I've personally been a fan of WordTwit for 3+ years now.  I have a client who still uses it to this day and has had it since 
before it was on the .org repository. However, after time, it would seem [Brave New Code](http://www.bravenewcode.com/) has abandoned the code in favor of it's more fruitful
[WpTouch Platform](http://wptouch.com). 

As of August 1st, 2016 - Version 3.7 of WordTwit has not been updated for 2+ years. I, myself, have had to make edits for clients
and even hack the core code of WordTwit due to the lack of filters in specific locations.

It is becuase of this, I have forked this copy directly from the WordPress repo at v3.7.  The original code since the fork will forever
be retained ( untouched for diff purposes ) on the [brave-new-code](https://github.com/JayWood/wordtwit-v2/tree/brave-new-code) branch of this repository.

_NOTE:_ The following information is pulled directly from the readme.txt included in the original plugin. Formatted for Markdown of course :smile:

## Description
WordTwit keeps track of when you publish new posts, and automatically informs your followers by pushing out a tweet to twitter in the format you specify.  All links are automatically converted to tiny URLs to save space.  *Most users see a substantial increase in blog traffic after tweeting their posts on a frequent basis using WordTwit.*

WordTwit includes:

* Support for multiple twitter accounts
* Per-author accounts
* Manual editing of tweets
* Hashtag support
* Tweet scheduling
* Support for a variety of shorteners, including yoURLs
* …and more!

Also check out our professional mobile plugin for WordPress, [WPtouch](http://www.bravenewcode.com/wptouch/?utm_campaign=wordtwit-front&utm_medium=web&utm_source=wordpressdotorg "WPtouch").

## Screenshots
![WordTwit Administration Panel](https://raw.githubusercontent.com/JayWood/wordtwit-v2/master/screenshot-1.png)
WordTwit Administration Panel

![WordTwit Publishing Widget](https://raw.githubusercontent.com/JayWood/wordtwit-v2/master/screenshot-2.png)
WordTwit Publishing Widget

![WordTwit Accounts Panel](https://raw.githubusercontent.com/JayWood/wordtwit-v2/master/screenshot-3.png)
WordTwit Accounts Panel

![WordTwit Tweetlog Panel](https://raw.githubusercontent.com/JayWood/wordtwit-v2/master/screenshot-4.png)
WordTwit Tweetlog Panel


## Changelog

### Version 4.0

* Update Javascript deprecated calls - Fixes [#7](https://github.com/JayWood/wordtwit-v2/issues/7)
* Logging Overhaul - Fixed [#8](https://github.com/JayWood/wordtwit-v2/issues/8)
* Removed `get_currentuserinfo()` deprecation - Fixed [#2](https://github.com/JayWood/wordtwit-v2/issues/2)
* A **TON** of code cleanup!

### Version 3.7

* Changed: Admin styling improvements and fixes

### Version 3.6

* Added: Link to user's guide in the admin panel
* Changed: Updated styling in admin panel

### Version 3.5.1

* Fix for save changes issue

### Version 3.5

* Verified WordPress 3.8 compatibility
* Fix for server time issue
* Compatible with January 14th new Twitter API SSL requirements
* Changed version number to reflect advancement past old Pro version

### Version 3.0.3

* Verified WordPress 3.7 compatibility

### Version 3.0.2

* Added: Serbian language translation
* Changed: Admin panel text and descriptions for clarify

### Version 3.0

* Changed: First release in WordPress.org repository