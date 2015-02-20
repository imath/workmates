WorkMates
=========

This is a BuddyPress plugin that will run if the BuddyPress friends component is not active to allow members invite others in the groups they are members/admins or mods of, and to make possible the use of the compose screen to send private messages.

This plugin will surely evolve... So far i just wanted to use BackBone.js to test this new group invite UI ;)

<img src="http://farm4.staticflickr.com/3804/11260587876_0fe9fcb399.jpg" width="500" height="296" alt="workmates editor">

You'll need at least BuddyPress 1.9-beta2 to have fun with it, but it should work for 1.8.1... To allow it for this version you can edit `'bp_version_required'` parameter of the `$init_vars` at line 56 of the main plugin file.

If you don't like the name of the group nav ( Invite Workmates ) you can filter it using the `'workmates_get_group_component_name'` filter.

Available in french and english.


Configuration needed
--------------------

+ WordPress 4.1.1 and BuddyPress 2.2.1

Installation
------------

Before activating the plugin, make sure all the files of the plugin are located in `/wp-content/plugins/workmates` folder.
