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

class SVGTree_parentChildConnection extends SVGTree_Connection {
	var $child;
	var $p1;
	var $p2; // Might be null
	var $type;

	function __construct($childbox,$parent1box,$parent2box=null) {
		$this->child = $childbox;
		$this->p1 = $parent1box;
		$this->p2 = $parent2box;
		return $this;
	}

	public function getConnectionMarkup(){
		if (isset($this->p2)){
			$parentStartEnd = SVGTree_spouseConnection::getStartEnd($this->p1, $this->p2);
			$Pstart = $parentStartEnd[0];
			$Pend = $parentStartEnd[1];
		} else {
			$Pstart = $this->p1->getConnectionPoint('right');
			$Pend = array($start[0]+20,$start[1]);
		}

		$midpointx = (($Pstart[0] + $Pend[0])/2);
		$midpointy = (($Pstart[1] + $Pend[1])/2);

		$start = array($midpointx, $midpointy);
		$end = $this->child->getConnectionPoint('top');

		#echo $this->child->p->getFullName()."<br/>";
		return $this->getConnectionMarkupFromPoints($start, $end, 'childConnection');
	}


	public function setRelationshipType($type){ }
}
