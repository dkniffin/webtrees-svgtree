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

class SVGTree_GenerationCollection {
	var $people = [];

	function __construct() {
		return $this;
	}
	
	public function addToGeneration($p,$gen,$sibgrp){
		$this->ensureGen($gen);
		if (!$this->inGen($p,$gen)){
			$this->ensureSibGrp($gen,$sibgrp);
			array_push($this->people[$gen][$sibgrp],$p);
		}
	}
	private function ensureGen($gen){
		if(!isset($this->people[$gen])){
			$this->people[$gen] = [];
		}
	}
	private function ensureSibGrp($gen,$sibgrp){
		if(!isset($this->people[$gen][$sibgrp])){
			$this->people[$gen][$sibgrp] = [];
		}
	}

	public function getNextSibGrp($gen){
		$this->ensureGen($gen);
		//echo 'next sibgrp for '.$gen.' is '.count($this->people[$gen]).'<br/>';
		return count($this->people[$gen]);
	}


	private function inGen($p,$gen){
		foreach ($this->getGeneration($gen) as $sibling_group){
			if (in_array($p,$sibling_group)){ 
				return true;
			}
		}
		return false;

	}


	public function getAllGenerations(){
		return $this->people;
	}
	public function getGeneration($gen){
		return $this->people[$gen];
	}

	public function getSiblingGroup($gen,$sibgrp){
		$generation = $this->getGeneration($gen);
		$sibgrp = $generation[$sibgrp];
	}
}
