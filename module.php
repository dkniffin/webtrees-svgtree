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

class svgtree_WT_Module extends WT_Module implements WT_Module_Tab, WT_Module_Menu {	
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
		return $this->cssJS();
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder(){
		return 12;
	}

	// Implement WT_Module_Menu
   public function getMenu() {
      global $controller, $SEARCH_SPIDER;      
      
      if ($SEARCH_SPIDER) return null;
      
      // Quick loading of css to prevent page flickering.
      echo $this->cssJS();
		
		$person=$controller->getSignificantIndividual();
		
		$menulabel = WT_I18N::translate('SVG Tree');
		$menulink = 'module.php?mod='.$this->getName().'&amp;mod_action=menupage&amp;rootid='.$person->getXref();
		$menuid = 'menu-svgtree';
      
      $menu = new WT_Menu($menulabel, $menulink, $menuid);

		$menu->addSubmenu(
			new WT_Menu(
			WT_I18N::translate('Kinship Chart for %s', $person->getFullName()), 
			'module.php?mod='.$this->getName().'&amp;mod_action=kinship&amp;rootid='.$person->getXref(),
			'menu-svg-kinship')
		);
      
      return $menu;
   } 

	// Extend WT_Module
	// We define here actions to proceed when called, either by Ajax or not
	public function modAction($mod_action) {
		error_reporting(E_ALL);
		ini_set('display_errors', 'on');

		$this->includes();
		switch($mod_action) {
		case 'menupage':
			// TODO: Create a menu page view
			global $controller;
			$controller=new WT_Controller_Page();

			$person=$controller->getSignificantIndividual();

			$controller
				->setPageTitle(WT_I18N::translate('SVG Tree Menu'))
				->pageHeader();
				//->addInlineJavascript($this->cssJS(false));

			$html = '<h2>SVG Tree Menu</h2>';
			$html .= '<div id=svgMenuPgContainer>';
			$html .= '<a class=svgMenuPgBtn
				href=module.php?mod='.$this->getName().'&amp;mod_action=kinship&amp;rootid='.$person->getXref().'>
				Kinship Chart for '.$person->getFullName()
				.'</a>';
			$html .= '</div>';

			echo $html;

			break;
		case 'kinship':
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

			$html = '<h2>'.WT_I18N::translate('Kinship Chart for %s', $person->getFullName()).'</h2>';

			// Add some SVG objects
			$html .= $this->svg_defs();

			// Get the SVG for the tree
			$html .= $svgtree->drawViewport();

			$controller
				->setPageTitle(WT_I18N::translate('Kinship Chart for %s', $person->getFullName()))
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
				->addInlineJavascript($this->cssJS(false));

			if (WT_USE_LIGHTBOX) {
				$album = new lightbox_WT_Module();
				$album->getPreLoadContent();
			}
			echo $html;
			break;
		case 'pedigree':
			// TODO: Create a pedigree view
			break;
		default:
			header('HTTP/1.0 404 Not Found');
			break;
		}
	}
	private function includes(){
		// Support WT versions prior to 1.5
		if (version_compare(WT_VERSION, '1.5.0') < 0){
		   require_once WT_MODULES_DIR.$this->getName().'/classes/individualExt.php';
		}
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

/*
	public function css() {
		return $this->url().'/css/treeview.css';
	}
*/

	/* === Taken from Fancy Tree View Module === */
	private function cssJS($wrapped=true) {
		$module_dir = WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/';
                if (file_exists($module_dir.WT_THEME_URL.'menu.css')) {
                        $css = $this->getScript($module_dir.WT_THEME_URL.'menu.css');
                }    
                else {
                        $css = $this->getScript($module_dir.'themes/base/menu.css');
                }    
                if(safe_GET('mod') == $this->getName()) {
                        $css .= $this->getScript($module_dir.'themes/base/style.css');
                        if (file_exists($module_dir.WT_THEME_URL.'style.css')) {
                                $css .= $this->getScript($module_dir.WT_THEME_URL.'style.css');
                        }                    
                }                            
		if ($wrapped){
                	return '<script>'.$css.'</script>';
		} else {
                	return $css;
		}

	}

	private function getScript($css){
		// To prevent page flickering we must load the css asap. So we cannot use jQuery("head").append(<link rel="stylesheet" ... 
                // as jQuery is not loaded at this time
                return
                        'if (document.createStyleSheet) {
                                document.createStyleSheet("'.$css.'"); // For Internet Explorer
                        } else {
                                var newSheet=document.createElement("link");
                                newSheet.setAttribute("rel","stylesheet");
                                newSheet.setAttribute("type","text/css");
                                newSheet.setAttribute("href","'.$css.'");
                                document.getElementsByTagName("head")[0].appendChild(newSheet);
                        }';

	}

	/* === End "taken from FTV" === */
	
	public function js() {
		return $this->url().'/js/treeview.js';
	}

	public function svg_defs(){
		$module_dir = WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/';
		if (file_exists($module_dir.WT_THEME_URL.'gradients.svg')) {
			$r = file_get_contents($module_dir.WT_THEME_URL.'/gradients.svg');
		} else {
			$r =  file_get_contents($module_dir.'themes/base/gradients.svg');
		}
		return $r;
	}

}
