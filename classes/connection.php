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

class SVGTree_Connection {
	var $mid_y_offset = 0;

	function __construct() {
		return $this;
	}

	public function setMidpointYOffset($offset){
		$this->mid_y_offset = $offset;
	}

	/**
	 *  Helper function to get the polyline markup for a connection
	 *  @param array $start x,y coords of the startpoint
	 *  @param array $end x,y coords of the endpoint
	 *  @param string $class CSS class to use for the connection
	 */
	public function getConnectionMarkupFromPoints($start,$end,$class){
		/* Get the points string */
		// Start with the startpoint
		$points = $start[0].','.$start[1].' ';

		// Get a middle y to bend at 
		#echo "midyoffset: ".$this->mid_y_offset."<br />";
		$midy = ($start[1] + $end[1])/2 + $this->mid_y_offset;

		// Add the bend points
		$points .= $start[0].','.$midy.' ';
		$points .= $end[0].','.$midy.' ';

		// End with the endpoint
		$points .= $end[0].','.$end[1];//+$this->mid_y_offset;

		// Return the markup
		return '<polyline points="'.$points.'" class="connection '.$class.'" />';
	}


}
