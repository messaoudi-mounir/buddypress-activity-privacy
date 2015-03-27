=== BuddyPress Activity Privacy ===
Contributors: megainfo
Tags: buddypress,activity,privacy,visibility,stream,private,public,only me,admins only,friends, followers,vie privée,confidentialité
Requires at least: WordPress 3.4, BuddyPress 1.5
Tested up to: WordPress 3.4.1 / BuddyPress 2.2.1
Stable tag: 1.3.6


== Description ==

BuddyPress Activity Privacy plugin add a privacy level to activity stream component.

The plugin add the ability for members to choose who can read his activity (Anyone, Logged In Users, My Friends, Admins Only, Only me, My Friends in Group , Group Members ...etc). 


= What's news In Buddypress Activity Privacy 1.3.x ? = 

- Admin have abitility to enable/disable FontAwsome icons.

- Admin have abitility to enable/disable viewing and editing the privacy of all activities.

- Admin have abitility to enable/disable editing the privacy of posts for all members.

- Admin have abitility show/hide the privacy label in selexbox.

- Admin have abitility show/hide the privacy in activity meta.


- **Integration with Buddypress Media plugin** (https://wordpress.org/plugins/buddypress-media/).

  Make sure to :
    * Set the privacy settings to **OFF** in rtMedia settings.
    * A new select-box (Privacy) is added to Edit Media form under Description Textarea.

- The plugin work now on multi site Netowork.
- A New privacy level (@mentioned only). When a member choose this privacy level, only mentioned members (and admin of course) can see the activity.

Remark: Members mentioned in activity can see it's content whatever the privacy level. 


- New Drop down system with a nice icons (font awsome).

- Admin Option Area, Admin can update Enable/Disable privacy level, Sort the privacy levels and change the default privacy level. 


What's news In Buddypress Activity Privacy 1.x ?

- Members can now change the privacy of the activity already posted.
- Admins can update the privacy of all activities. 

- Integration with BuddyPress Follow Plugin (http://wordpress.org/extend/plugins/buddypress-followers/ ).

- Integration With Buddypress Activity Plus Plugin (http://wordpress.org/extend/plugins/buddypress-activity-plus/ ).

-The plugin is now extensible for new privacy levels !! ( Check the integration of BuddyPress Follow in bp-activity-privacy-integrations.php ).


== Installation ==

Download and upload the plugin to your plugins folder. 

Then Activate the plugin.

You can choose the visibility level from the select box in the Activity Post Form.

== Screenshots ==
1. **Privacy for My Profile Activity** - Allow your users select a visibility level for the activity posted in the profile.
2. **Privacy for Groups Activity** - Allow your users select a visibility level for the activity posted in the group.
3. **Integration with BuddyPress Follow plugin ** - Allow your users select a visibility level (My Followers) for the activity posted in the profile.
4. **Integration with BuddyPress Follow plugin for groups ** - Allow your users select a visibility level (My Followers In Group ) for the activity posted in the group.
5. **Integration with BuddyPress Activity Plus plugin** - Allow your users select a visibility level  for the activity posted with Buddypress Activity plus.
6. **Member can update the privacy of the old activity stream (new selectbox in activity meta).

== Frequently Asked Questions ==

== Frequently Asked Questions ==

#### Where to find support? ####

Please post on the [BuddyPress Activity Privacy support forum](http://buddypress.org/community/groups/buddypress-activity-privacy/forum/) at buddypress.org.
The forums on wordpress.org are rarely checked.

Or In GitHub

https://github.com/dzmounir/buddypress-activity-privacy

== Changelog ==
= 1.3.6 =
- Fix integration with Buddypress Follow

= 1.3.5 =
- Fix bug activity detail links caused by overidding activity allowed_tags

= 1.3.4 =
- Admin have abitility to enable/disable FontAwsome icons.
- Admin have abitility to enable/disable view and editing the privacy of all activities.
- Admin have abitility to enable/disable viewing/editing the visibility for all members.
- Admin have abitility show/hide the privacy label in selexbox.
- Admin have abitility show/hide the privacy in activity meta.

- Fix warning message bp_setup_current_user was called incorrectly
- Fix bug visibility of "Last update" activity on member header and members loop.
- Fix translation file loading
- Fix some issues in rtMedia plugin integration

= 1.3.3 =
- Fix bugs on integration with rtMedia.

= 1.3.2 =
- Fix issues when Buddypress Follow is activate.
- Fix visibility of "Last update" activity on member header and members loop.
- Beta Integration with buddypress-media plugin (rtMedia for WordPress, BuddyPress and bbPress).
- add .POT file for translation.

= 1.3.1 =
- Work on Multisite Network.
- Fix "new post" activities dropdown privacy as profile activites privacy.

= 1.3 =
- Fix localization loading.
- Fix integration with Followers plugin.

= 1.2.2 =
- Fix bug, visibility not work. 

= 1.2.1 =
- New privacy level (@mentioned only). 
  Members mentioned in the acitivity can see the content of acitivity whatever the privacy level. 

= 1.2 =
- New Dropdown system with a nice icons ( By Font Awesome http://fontawesome.io/ ).
- Admin Option Area, Admin can update Enable/Disable privacy level, Sort the privacy levels and change the default privacy level.
- Fix the visibility of the last activity in members directory and member profile page.

= 1.1.2 =
- Fix js bug due the use of bp_get_cookies() function (added since bp 1.8).

= 1.1.1 =
- Fix jquery select option on change event ( submit privacy selection on previous items )

= 1.1 =
- Members can now change the privacy of the activity already posted.
- Admins can change the privacy of all activities. 

= 1.0.4.3 =
* Fix Privacy selectbox.
* Add french tranlation.

= 1.0.4.2 =
* Fix Privacy selectbox load group privacy in profile page.
* Add Spanish tranlation (thanks to Andrés Felipe).

= 1.0.4.1 =
* Fix localization load (add param to load_textdomain).

= 1.0.4 =
* Fix localization load.

= 1.0.3 =
* Integration With Buddypress Follow (http://wordpress.org/extend/plugins/buddypress-followers/ ).
* Integration With Buddypress Activity Plus (http://wordpress.org/extend/plugins/buddypress-activity-plus/ ).
* Fix the array of privacy levels.
* Add More Filters to make plugin extensible for new privacy levels ( Check  bp-activity-privacy-integrations.php ).

= 1.0.2 =
* Fix if no activity, plugin show 'load more' button instead of message.

= 1.0.1 =
* Fix small CSS problem in Activity Post Form.

= 1.0 =
* Initial release.
