<?php
// Helper class file for generating the markup for a person's box
//
// webtrees: Web based Family History software
// Copyright (C) 2013 webtrees development team.
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
// TODO: This whole class might be able to be integrated with WT_Individual in 
// the future, if webtrees moves to SVG for all the trees

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class SVGTree_PersonObj {
	var $p;
	var $x = 0;
	var $y = 0;
	var $format; // 'thumbnail' or 'full'
	var $generation;

	/**
	* svgtree_Person Constructor
	* @param WT_Individual $person The person object to use
	* @param string $f The format to use for the box
	*/
	function __construct(WT_Individual $person, $f) {
		$this->p = $person;
		$this->format = $f;
		return $this;
	}

	/**
	 * Getter and setter functions for $generation
	 */
	public function setGeneration($gen){ $this->generation = $gen; }

	public function getGeneration(){ return $this->generation; }


	/**
	 * Get the markup for the person's box
	 */
	public function getPersonBoxMarkup(){
		switch ($this->format){
		case 'thumbnail':
			$box = '<!--'.strip_tags($this->p->getFullName()).'-->';
			$box .= '<g ';
			$box .= 'class="personBox '.$this->getGenderClass().'" ';
			$box .= '>';
			//$box .= '<a xlink:href='.$this->p->getHtmlUrl().'> ';
			$box .= '<svg ';
			$box .= 'x='.$this->x.' ';
			$box .= 'y='.$this->y.' ';
			$box .= '>';
			$box .= '<rect ';
			$box .= 'width='.$this->getWidth().' ';
			$box .= 'height='.$this->getHeight().' ';
			$box .= '>';
			$box .= '</rect>';
			$box .= '<foreignObject class="personBoxHTML" width='.$this->getWidth().' height='.$this->getHeight().'><body xmlns="http://www.w3.org/1999/xhtml">';
			$box .= '<div class="portraitContainer">';
			$box .= $this->getThumbnailMarkup();
			$box .= '</div>';
			$box .= '<div class="nameContainer">';
			$box .= $this->getNameMarkup();
			$box .= '</div>';
			$box .= '</body></foreignObject>';
			$box .= '</svg>';
			//$box .= '</a>';
			$box .= '</g>';
			break;
		case 'full':
			// TODO: Implement full box type
			break;
		default:
			// TODO: Create an error for unknown box types
		}
		return $box;
	}

	private function getWidth(){
		switch ($this->format){
		case 'thumbnail':
			return 110;
		case 'full':
			return 180;
		}
	}
	private function getHeight(){
		switch ($this->format){
		case 'thumbnail':
			return 130;
		case 'full':
			return 90;
		}
	}

	private function getGenderClass(){
		return 'gender'.$this->p->getSex();
	}

	/**
	* Return the markup for the person's name
	*/
	private function getNameMarkup() {
		$r = '<a href="'.$this->p->getHtmlUrl().'">'.$this->p->getFullName().'</a>';
		//$r = '<a>'.$this->p->getFullName().'</a>';
		return $r;
	}

	/**
	* Get the thumbnail image for the person
	*/
	private function getThumbnailMarkup() {
		global $SHOW_HIGHLIGHT_IMAGES;

		if ($SHOW_HIGHLIGHT_IMAGES) {
			$r = $this->p->displayImage();
		} else {
			$r = '';
		}
		return $r;
	}

	public function setX($x){
		$this->x = $x;
	}
	public function setY($y){
		$this->y = $y;
	}
	public function setCoords($x,$y){
		$this->setX($x);
		$this->setY($y);
	}

	/**
	 * Get the x,y coords of the connection point
	 * @param string $loc The side of the box to get the connection coords for
	 */
	public function getConnectionPoint($loc){
		switch(strtolower($loc)){
		case 'top':
			$x = ($this->x + $this->getWidth()/2);
			$y = ($this->y);
			break;
		case 'bottom':
			$x = ($this->x + $this->getWidth()/2);
			$y = ($this->y + $this->getHeight);
			break;
		case 'left':
			$x = ($this->x);
			$y = ($this->y + $this->getHeight()/2);
			break;
		case 'right':
			$x = ($this->x + $this->getWidth());
			$y = ($this->y + $this->getHeight()/2);
			break;
		default:
		}
		return array($x,$y);
	}

}
