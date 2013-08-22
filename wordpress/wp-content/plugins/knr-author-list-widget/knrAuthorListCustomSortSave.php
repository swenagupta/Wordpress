<?php
/*  Copyright 2009 Nitin Reddy  (email : k_nitin_r@yahoo.co.in , k.nitin.r@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
	WARRANTY AND CUSTOMIZATION
	Warranty and customization for this software is available. Contact the
	author for more details.
*/

include( dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php' );

global $wpdb;

foreach ($_GET['listItem'] as $position => $item) :
	if (filter_var($position, FILTER_VALIDATE_INT)!==false && filter_var($item, FILTER_VALIDATE_INT)!==false):
		$iterSql = "UPDATE $wpdb->users SET knr_author_order = $position WHERE ID = $item";
		$wpdb->query($iterSql);
	else:
		echo '<p>Invalid data. '."position: $position, item: $item".'</p>';
	endif;
endforeach;
echo 'Saved on '.date('r');
?>
