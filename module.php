<?php
// TreeView module class
//
// Tip : you could change the number of generations loaded before ajax calls both in individual page and in treeview page to optimize speed and server load 
//
// Copyright (C) 2013 webtrees development team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: module.php 15088 2013-06-23 21:59:58Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class svgtree_WT_Module extends WT_Module implements WT_Module_Tab {	
	var $headers; // CSS and script to include in the top of <head> section, before theme's CSS
	var $js; // the TreeViewHandler javascript
	
	// Extend WT_Module. This title should be normalized when this module will be added officially
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('SVG Tree');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Interactive tree” module */ WT_I18N::translate('An SVG-based tree view');
	}
	
	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 68;
	}

	// Implement WT_Module_Tab
	public function getTabContent() {
		global $controller;

		$this->includes();
		$tv = new TreeView('tvTab');
		list($html, $js) = $tv->drawViewport($controller->record, 3);
		return
			'<script src="' . $this->js() . '"></script>' .
			'<script>' . $js . '</script>' .
			$html;
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		global $SEARCH_SPIDER;
			
		return !$SEARCH_SPIDER;
	}
	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return false;
	}
	// Implement WT_Module_Tab
	public function canLoadAjax() {
		return true;
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		// We cannot use jQuery("head").append(<link rel="stylesheet" ...as jQuery is not loaded at this time
		return
			'<script>
			if (document.createStyleSheet) {
				document.createStyleSheet("'.$this->css().'"); // For Internet Explorer
			} else {
				var newSheet=document.createElement("link");
    		newSheet.setAttribute("rel","stylesheet");
    		newSheet.setAttribute("type","text/css");
   			newSheet.setAttribute("href","'.$this->css().'");
		    document.getElementsByTagName("head")[0].appendChild(newSheet);
			}
			</script>';
	}

	// Extend WT_Module
	// We define here actions to proceed when called, either by Ajax or not
	public function modAction($mod_action) {
		$this->includes();
		switch($mod_action) {
		case 'treeview':
				error_reporting(E_ALL);
				ini_set('display_errors', 'on');
				global $controller;
				$controller=new WT_Controller_Chart();

				/* Get URL params */
				$genup = safe_GET('genup');
				$gendown = safe_GET('gendown');
				$renderSiblings = safe_GET('renderSiblings');
				$renderAllSpouses = safe_GET('renderAllSpouses');
				$boxType = safe_GET('boxType');
				$orientation = safe_GET('orientation');


				// TODO: validate URL params


				// Get the person for whom the tree should be rendered
				$person=$controller->getSignificantIndividual();

				// Create a new SVGTree object
				$svgtree = new SVGTree($person,$genup,$gendown,$renderSiblings,$renderAllSpouses, $boxType, $orientation);

				// Add some SVG objects
				$html = $this->svg_defs();

				// Get the SVG for the tree
				$html .= $svgtree->drawViewport();

				$controller
					->setPageTitle(WT_I18N::translate('Interactive tree of %s', $person->getFullName()))
					->pageHeader()
					//->addExternalJavascript($this->js())
					->addExternalJavascript($this->url().'/js/svgweb/src/svg.js')
					->addExternalJavascript($this->url().'/js/jquery.panzoom.js')
					->addInlineJavascript('
					$(document).ready(function(){
						$("#treeContainer").panzoom({ });
					});
					')
					//->addInlineJavascript($js)
					->addInlineJavascript('
					if (document.createStyleSheet) {
						document.createStyleSheet("'.$this->css().'"); // For Internet Explorer
					} else {
						jQuery("head").append(\'<link rel="stylesheet" type="text/css" href="'.$this->css().'">\');
					}
				');

			if (WT_USE_LIGHTBOX) {
				$album = new lightbox_WT_Module();
				$album->getPreLoadContent();
			}
			echo $html;
			break;
		default:
			header('HTTP/1.0 404 Not Found');
			break;
		}
	}
	private function includes(){
		require_once WT_MODULES_DIR.$this->getName().'/classes/svgtree.php';
		require_once WT_MODULES_DIR.$this->getName().'/classes/personObj.php';
		require_once WT_MODULES_DIR.$this->getName().'/classes/helper.php';
		require_once WT_MODULES_DIR.$this->getName().'/classes/connection.php';
		require_once WT_MODULES_DIR.$this->getName().'/classes/spouseConnection.php';
		require_once WT_MODULES_DIR.$this->getName().'/classes/parentChildConnection.php';
	}

	public function url(){
		return WT_STATIC_URL.WT_MODULES_DIR.$this->getName();
	}

	public function css() {
		return $this->url().'/css/treeview.css';
	}
	
	public function js() {
		return $this->url().'/js/treeview.js';
	}

	public function svg_defs(){
		$r = file_get_contents($this->url().'/css/gradients.svg');
		return $r;
	}

}
