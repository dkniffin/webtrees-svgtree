<?php
/* This file can be deleted sometime down the line. The only purpose is to 
 * support versions of webtrees prior to 1.5, when WT_Person became 
 * WT_Individual
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Person extends WT_Individual{ }
?>
