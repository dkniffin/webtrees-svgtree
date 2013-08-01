webtrees-svgtree
================

An alternative tree module for the webtrees program. This is still a work in progress.

Installation
============
Simply extract the contents of this repo into a directory called svgtre in your
webtrees modules_v3 directory.

    cd <webtrees root>/modules_v3/
    git clone https://github.com/oddityoverseer13/webtrees-svgtree.git svgtree

NOTE: This module uses the SVN version of webtrees atm, so it won't work with
most installations. Eventually, it'll use a standard webtrees version (probably
1.5)

Usage
=====
At the moment, The only way to actually see the module's display is to go directly to the module URL:
    http://<webtreesurl>/module.php?mod=svgtree&mod_action=treeview&ged=<ged>&rootid=<person_id>

At the moment, the module doesn't show up in any menus, but in the future I plan for it to (like the interactive tree module).

It also creates a tab on each persons page, but atm, it doesn't display anything.

Styling
=======
All colors and such are stylable via CSS and SVG documents. More details to come later.

To Do
===========
* Smash some bugs
* URL options
** genup - # of generations to render upward
** gendown - # of generations to render downward
** renderSiblings - Boolean indicating whether to render siblings or not
** renderSpouses - Boolean indicating whether to render spouses or not
** boxType - Thumbnail, full, etc
** orientation - portrait or landscape
* A "full" box type, which will display more details about the individual
* A menu options to display the module
* Get the tabs working
* Maybe have some global options in the admin pages for things like connection spacing, etc

