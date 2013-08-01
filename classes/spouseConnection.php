<?php
// Helper class file for svgtree
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

class SVGTree_spouseConnection extends SVGTree_Connection {
	var $sp1;
	var $sp2;
	var $type;
	var $cssClass = 'marriageConnection';

	function __construct($spouse1box,$spouse2box) {
		$this->sp1 = $spouse1box;
		$this->sp2 = $spouse2box;
		return $this;
	}

	public function getConnectionMarkup(){
		$startEnd = $this->getStartEnd($this->sp1, $this->sp2);
		$start = $startEnd[0];
		$end = $startEnd[1];

		#echo "drawing spouse connection for ".$this->sp1->p->getFullName()." and ".$this->sp2->p->getFullName()."<br/>";
		return $this->getConnectionMarkupFromPoints($start, $end, $this->cssClass);
	}
	static public function getStartEnd($sp1, $sp2){
		// Calculate the two closer points
		$sp1right = $sp1->getConnectionPoint('right');
		$sp1left = $sp1->getConnectionPoint('left');
		$sp2right = $sp2->getConnectionPoint('right');
		$sp2left = $sp2->getConnectionPoint('left');

		$cmp1 = $sp2left[0] - $sp1right[0];
		$cmp2 = $sp1left[0] - $sp2right[0];
		#echo "rendering connection between ";
		#echo "sp1: ".$sp1->p->getFullName().', '.'sp2: '.$sp2->p->getFullName().'<br />';
		#echo "sp1right: ".json_encode($sp1right).", ";
		#echo "sp1left: ".json_encode($sp1left).", ";
		#echo "sp2right: ".json_encode($sp2right).", ";
		#echo "sp2left: ".json_encode($sp2left)."<br />";
		#echo "cmp1: $cmp1, cmp2: $cmp2 <br />";
		if ($cmp1 > $cmp2){
			$start = $sp1right;
			$end = $sp2left;
		} else {
			$start = $sp2right;
			$end = $sp1left;
		}
		return array($start,$end);
	}


	public function setMarriageType($type){ }

	public function appendToCssClass($addition){
		$this->cssClass .= " $addition";
	}
}
