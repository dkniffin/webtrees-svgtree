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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class SVGTree {
	var $rootPerson; // The rootPerson of the tree
	var $maxGensUp; // The max number of generations to render upward
	var $maxGensDown; // The max number of generations to render downward
	var $renderSiblings; // Whether or not to render siblings
	var $renderAllSpouses; // Whether or not to render spouses who are not blood-related to rootPerson
	var $boxType; // How to render each person's box
	var $orientation; // Portrait or Landscape
	var $people = array(); // People who will be rendered
	var $sConns = array(); // Spouse connections
	var $pcConns = array(); // Parent/Child connections
	var $genSibConnOffset = array(); // A helper array for spacing between sibling connection lines
	var $directAncDes = array(); // An array containing direct ancestors and descendants of rootPerson

	/**
	* SVGTree Constructor
	*/
	function __construct($root, $genup=4, $gendown='all',
	       	$renderSiblings=true, $renderAllSpouses=true, $boxType='thumbnail', $orientation='portrait') {

		// Set the settings
		$this->rootPerson = $root;
		$this->maxGensUp = $genup;
		if (empty($this->maxGensUp)){
			$this->maxGensUp = 4;
		}
		$this->maxGensDown = $gendown;
		$this->renderSiblings = $renderSiblings;
		$this->renderAllSpouses = $renderAllSpouses;
		$this->boxType = $boxType;
		$this->orientation = $orientation;
	}

	/**
	* Draw the viewport which creates the draggable/zoomable framework
	* Size is set by the container, as the viewport can scale itself automatically
	* @param string $rootPersonId the id of the root person
	* @param int $generations number of generations to draw
	*/
	public function drawViewport() {

		global $GEDCOM, $controller;

		/*
		if (WT_SCRIPT_NAME == 'individual.php') {
			$path = 'individual.php?pid='.$this->rootPerson->getXref().'&amp;ged='.$GEDCOM.'&allPartners='.($this->allPartners ? "false" : "true").'#tree';
		} else {
			$path = 'module.php?mod=tree&amp;mod_action=treeview&amp;rootid='.$this->rootPerson->getXref().'&amp;allPartners='.($this->allPartners ? "false" : "true");
		}
		 */



		// Gather all the people that need to be rendered
		$this->gatherPeople($this->rootPerson, $this->maxGensUp, 'up');

		$r = '';
		//$r .= "<button id=zoomIn>Zoom In</button>";
		//$r .= "<button id=zoomOut>Zoom Out</button>";
		$r .= '<div id=treeDiv><svg id=treeContainer width=10000 height=10000 xmlns="http://www.w3.org/2000/svg">';
		$r .= $this->getTreeMarkup();
		$r .= '</svg></div>';
		
		return $r;
	}


	/**
	* Draw the tree
	*/
	private function getTreeMarkup() {

		$r = '';

		// Position each box
		$x = 0; $y = 0;
		foreach($this->people as $gen => $generation){
			$x = 0; // Can't assume 0....not sure how to figure this out
			$y = 180*$gen;
			foreach($generation as $person_object){
				if ($person_object->render){
					$childTreeWidth = $this->treeWidth($person_object->p);
					$person_object->setCoords($x+($childTreeWidth/2),$y); // Set the coordinates for the box
					//$x = $person_object->getConnectionPoint('right')[0]+20;
					$tmp = $person_object->getConnectionPoint('right');
					// TODO: This is where we'll adjust spacing to get an organized tree

					$x = $tmp[0]+($childTreeWidth*110);


				}
			}
		}

		// Draw the spouse connections
		foreach ($this->sConns as $spouse_connection){
			if ($spouse_connection->render){
				$r .= $spouse_connection->getConnectionMarkup();
			}
		}

		// Draw the parent/child connections
		foreach ($this->pcConns as $parent_child_connection){
			if ($parent_child_connection->render){
				$r .= $parent_child_connection->getConnectionMarkup();
			}
		}

		// Draw the boxes
		// Note: we can't do this above, or the connections will get 
		// rendered on top of the boxes
		foreach($this->people as $gen => $generation){
			foreach($generation as $person_object){
				if ($person_object->render){
					$r .= $person_object->getPersonBoxMarkup();
					
				}
			}
		}
		
		/* Return final tree markup */
		return $r;
	}

	private function gatherPeople($person, $gen, $dir, $sibgrp=0){
		if ($dir == 'up'){
			// This person is a direct ancestor of rootPerson, so add to the array of direct ancestors and descendants
			//push_array($this->directAncDes,$person);
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
				$person_object = new SVGTree_PersonObj($person,'thumbnail');	
				$person_object->setGeneration($gen);
				$this->people[$gen][$person->getXref()] = $person_object;


				// For each family where $person is a spouse
				$i = 0;
				foreach($person->getSpouseFamilies() as $spouse_family){
					$spouse_object = null;
					foreach($spouse_family->getSpouses() as $spouse){ // For each spouse
						if ($spouse === $person){ continue; } // Skip self

						// Add spouse
						$spouse_object = new SVGTree_PersonObj($spouse,'thumbnail');	
						$spouse_object->setGeneration($gen);
						/*
						if ($this->renderAllSpouses === 'false'){
							$spouse_object->render = false;
						}
						 */

						$this->people[$gen][$spouse->getXref()] = $spouse_object;

						// Create a spouseConnection from spouse to person
						$spouse_connection = new SVGTree_spouseConnection($person_object,$spouse_object);
						$spouse_connection->setMidpointYOffset($i*(-10));
						$spouse_connection->appendToCssClass("marriage".($i+1));
						// TODO: set marriage type
						array_push($this->sConns,$spouse_connection);

					}
					
					// For each child
					foreach($spouse_family->getChildren() as $child){

						// gather the tree for the child
						$child_obj = $this->gatherPeople($child,$gen+1,'down',0);

						if (!empty($child_obj)){
							$child_parent_connector = new SVGTree_parentChildConnection($child_obj,$person_object,$spouse_object);
							$conn_num = $this->genSibConnOffset[$gen]; 
							$offset = 20 + pow(-1,($conn_num%2))*3*($conn_num-($conn_num%2));
							$child_parent_connector->setMidPointYOffset($offset);
							$this->pcConns[$child->getXref()] = $child_parent_connector;
						}

					}
					$this->genSibConnOffset[$gen]++;
					$i++;
				}
				return $person_object;
			}
		}
	}

	/**
	* Get the width (in people) of a person's tree
	*/
	private function treeWidth($parent) {
		$width = 1; // Default to 1
		foreach($parent->getSpouseFamilies() as $spouse_family){
			$childTreeWidth = 0;
			foreach($spouse_family->getChildren() as $child) {
				$childTreeWidth += $this->treeWidth($child);
			}
			if ($childTreeWidth > $width) {
					$width = $childTreeWidth;
			}
		}
		return $width;
	}
}
