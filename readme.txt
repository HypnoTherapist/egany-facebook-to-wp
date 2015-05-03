=== Facebook to WordPress ===
Author: EGANY   
Link: http://egany.com/  Easy to Get ANYthing
Tags: facebook, group/page, cron, post, thread, wordpress, import, comments
Requires at least: 1.0 
Tested up to: 1.0  
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pull your Facebook group/page feed to WordPress  

== Description ==  

A simple plugin that imports posts from **public** Facebook group/pages to your WordPress blog, every half hour!  

= What it does & doesn't =  

* Imports from Facebook group/page and inserts as `egany_fb2wp_post` post type  
* No chance for duplication
* It imports comments as well
* Runs every half hour via WordPress cron system
* Adds group id, author name and ID, post link as post meta
* If you want to trigger the importing manually, go to `http://example.com/?fb2wp_test`
* Import historical (paginated) posts. To do this, go to `http://example.com/?fb2wp_hist` and it'll automatically start the import process. Only admins can run this task.


= Contribute =
This may have bugs and lack of many features. If you want to contribute on this project, you are more than welcome. Please give us any ideas. 

= Author =
Brought to you by [EGANY](http://egany.com) 

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

* [Create](https://developers.facebook.com/?ref=pf) a facebook app
* Fillup facebook app ID and secret
* Find the **numeric** group ID for your group. Use [this tool](http://lookup-id.com/) if needed.
* Insert your group ID in the settings
* Now you are done. You can choose "Run Immediately", (or It'll pull posts automatically, waiting for later version).

== Frequently Asked Questions ==

Nothing here yet

== Screenshots ==

1. Settings
2. Posts
3. Posts in frontend

== Changelog ==

= 0.1 =
Initial version released


== Upgrade Notice ==

Nothing here
