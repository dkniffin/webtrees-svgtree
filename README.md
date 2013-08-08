webtrees-svgtree
================
An alternative tree module for the webtrees program. This is still a work in progress.

Installation
------------
Simply extract the contents of this repo into a directory called svgtree in
your webtrees modules_v3 directory.

    cd <webtrees root>/modules_v3/
    git clone https://github.com/oddityoverseer13/webtrees-svgtree.git svgtree

Once you've done that, you'll need to enable the plugin in the administration panel.

Usage
-----
You can access the charts provided by the module via the SVG Tree menu. If you
don't see it, you might need to enable the menu in the administration panel.

There are also some URL options you may pass directly to the module:

* genup - # of generations to display upward from the root person

Styling
-------
All colors and such are stylable via CSS and SVG documents. More details to come later.

To Do
-----
* Smash some bugs
* URL options
 * gendown - # of generations to render downward
 * renderSiblings - Boolean indicating whether to render siblings or not
 * renderSpouses - Boolean indicating whether to render spouses or not
 * boxType - Thumbnail, full, etc
 * orientation - portrait or landscape
* A "full" box type, which will display more details about the individual
* Get the tabs working
* Maybe have some global options in the admin pages for things like connection spacing, etc
* Add some export functionality (SVG and PDF, hopefully).
