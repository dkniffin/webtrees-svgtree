<?php
// Class file for the tree navigator
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
// $Id: class_treeview.php 15320 2013-07-18 19:59:52Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class SVGTree {
	var $name;
	var $allPartners;
	var $rootPerson;
	var $genCol;
	var $maxGensUp;
	var $maxGensDown;
	var $people = array();
	var $sConns = array();
	var $pcConns = array();
	var $genSibConnOffset = array();

	/**
	* SVGTree Constructor
	*/
	function __construct() {
		$this->name = 'svgtree';

		// Read if all partners must be shown or not
		$allPartners = safe_GET('allPartners');
		// if allPartners not specified in url, we try to read the cookie
		if ($allPartners == '') {
			if (isset($_COOKIE['allPartners']))
				$allPartners = $_COOKIE['allPartners'];
			else
				$allPartners = 'true'; // That is now the default value
		}
		$allPartners = ($allPartners == 'true' ? true : false);
		$this->allPartners = $allPartners;
  		//$this->genCol = new SVGTree_GenerationCollection();
	}

	/**
	* Draw the viewport which creates the draggable/zoomable framework
	* Size is set by the container, as the viewport can scale itself automatically
	* @param string $rootPersonId the id of the root person
	* @param int $generations number of generations to draw
	*/
	public function drawViewport(WT_Individual $root, $generations) {
		global $GEDCOM, $controller;
		$this->rootPerson = $root;

		if (WT_SCRIPT_NAME == 'individual.php') {
			$path = 'individual.php?pid='.$this->rootPerson->getXref().'&amp;ged='.$GEDCOM.'&allPartners='.($this->allPartners ? "false" : "true").'#tree';
		} else {
			$path = 'module.php?mod=tree&amp;mod_action=treeview&amp;rootid='.$this->rootPerson->getXref().'&amp;allPartners='.($this->allPartners ? "false" : "true");
		}



		// Fill up $genCol
		$this->maxGensUp = $generations;
		$this->maxGensDown = $generations;
		$this->gatherPeople($this->rootPerson, $generations, 'up');

		$r = '<svg xmlns="http://www.w3.org/2000/svg">';
		$r .= $this->getTreeMarkup(
			//$this->rootPerson, 
			$generations, 
			0, // state
			null,// family
			0,
			0
		);
		$r .= '</svg>';
		
		return $r;
	}


	/**
	* Draw a person in the tree
	* @param Person $person The Person object to draw the box for
	* @param int $gen The number of generations up or down to print (0 means just render this generation)
	* @param int $state Whether we are going up or down the tree, -1 for descendents +1 for ancestors
	* @param Family $pfamily
	* @param string $order first (1), last(2), unique(0), or empty. Required for drawing lines between boxes
	*
	* Notes : "spouse" means explicitely married partners. Thus, the word "partner"
	* (for "life partner") here fits much better than "spouse" or "mate"
	* to translate properly the modern french meaning of "conjoint"
	*/
	private function getTreeMarkup(/*$person,*/ $gen, $state=0, $pfamily,
			$base_x, $base_y, $spouses=false,$parents=false,$children=false) {

		$r = '';

		// Position each box
		$x = 0; $y = 0;
		foreach($this->people as $gen => $generation){
			$x = 0;
			$y = 180*$gen;
			foreach($generation as $pobj){
				$pobj->setCoords($x,$y);
				$x = $pobj->getConnectionPoint('right')[0]+20;
			}
		}

		// Draw the spouse connections
		foreach ($this->sConns as $sC){
			//foreach ($sCouter as $sC){
				$r .= $sC->getConnectionMarkup();
			//}
		}

		// Draw the parent/child connections
		foreach ($this->pcConns as $pcC){
			$r .= $pcC->getConnectionMarkup();
		}

		// Draw the boxes
		// Note: we can't do this above, or the connections will get 
		// rendered on top of the boxes
		foreach($this->people as $gen => $generation){
			foreach($generation as $pobj){
				$r .= $pobj->getPersonBoxMarkup();
			}
		}
		// For each generation
		/*
		foreach($this->genCol->getAllGenerations() as $gennum => $sibgrps){
			$x = 0;
			$y = 150*$gennum;
			// For each sibling group
			foreach($sibgrps as $sibgrpnum => $sibgrp){
				$sib_boxes = [];
				// For each person
				foreach ($sibgrp as $person){
					// Create a new box for the person
					//$personBox = new SVGTree_PersonBox($person, 'thumbnail');
					//$personBox->setCoords($x,$y);

					// Add the box markup to the tree
					$r .= $personBox->getPersonBoxMarkup();
					
					// Add the person to $sibs
					array_push($sib_boxes,$personBox);

					// Set $x up for the next person
					$x = $personBox->getConnectionPoint('right')[0]+20;
				}
			}
		}	
		 */
		
		/* Return final HTML tree */
		return $r;
	}

	private function gatherPeople($person, $gen, $dir, $sibgrp=0){
		if ($dir == 'up'){
			if ($gen == 0){ 
				// We're at the top of the tree; gather descendants
				$this->gatherPeople($person, $gen, 'down', 0);
			} else {
				// For each family where $person is a child
				foreach($person->getChildFamilies() as $fam){
					$father = $fam->getHusband();
					$wife = $fam->getWife();
					
					if (!empty($father)){
						// Gather people starting with husband
						$this->gatherPeople($father, $gen-1, 'up');
					}
					if (!empty($wife)){
						// Gather people starting with wife
						$this->gatherPeople($wife, $gen-1, 'up');
					}
				}
			}
		} else if ($dir == 'down') {
			if ($gen > $this->maxGensUp + $this->maxGensDown){
				// If the current generation is greater than the max # of gens to 
				// render, return
				return;
			} else { // Else, add self, spouses, and descendants
				// If this person has already been processed, skip
				if (!empty($this->people[$gen])){
					if (array_key_exists($person->getXref(), $this->people[$gen])){
						return null;
					}
				}

				// if this is the first person for the generation, set up the value for $genSibConnOffset
				if (empty($this->genSibConnOffset[$gen])){
					$this->genSibConnOffset[$gen]=0;
				}

				// Add self
				$pobj = new SVGTree_PersonObj($person,'thumbnail');	
				$pobj->setGeneration($gen);
				$this->people[$gen][$person->getXref()] = $pobj;

				//$this->genCol->addToGeneration($person,$gen,$sibgrp);

				// For each family where $person is a spouse
				$i = 0;
				foreach($person->getSpouseFamilies() as $sp_fam){
					// For each spouse
					foreach($sp_fam->getSpouses() as $spouse){
						// Skip self
						if ($spouse === $person){ continue; }

						// Add spouse
						$sobj = new SVGTree_PersonObj($spouse,'thumbnail');	
						$sobj->setGeneration($gen);
						$this->people[$gen][$spouse->getXref()] = $sobj;

						// Create a spouseConnection from spouse to person
						$sconn = new SVGTree_spouseConnection($pobj,$sobj);
						$sconn->setMidpointYOffset($i*(-10));
						array_push($this->sConns,$sconn);
						#$this->sConns[$person->getXref()][$spouse->getXref()] = $sconn;
						#$this->sConns[$spouse->getXref()][$person->getXref()] = $sconn;

						//$this->genCol->addToGeneration($spouse,$gen,$sibgrp);
					}
					
					// Get next sibling group for generation
					//$newSibGrp = $this->genCol->getNextSibGrp($gen+1);
					// gatherPeople on children
					foreach($sp_fam->getChildren() as $child){

						// gather the tree for the child
						$cobj = $this->gatherPeople($child,$gen+1,'down',0);

						if (!empty($cobj)){
							$cpC = new SVGTree_parentChildConnection($cobj,$pobj,$sobj);
							$sGON = $this->genSibConnOffset[$gen]; // TODO: change this to conn_num
							echo "sGON: $sGON";
							$offset = 20 + pow(-1,($sGON%2))*3*($sGON-($sGON%2));
							#$sibGrpOffset = 0;
							$cpC->setMidPointYOffset($offset);
							$this->pcConns[$child->getXref()] = $cpC;
						}

					}
					$this->genSibConnOffset[$gen]++;
					$i++;
				}
				return $pobj;
			}
		}
	}
}
