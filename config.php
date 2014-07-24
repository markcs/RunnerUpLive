<?php
/* phpTrackme
 *
 * Copyright(C) 2013 Bartek Fabiszewski (www.fabiszewski.net)
 * Copyright(C) 2014 Mark Campbell-Smith (campbellsmith.me)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Library General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
$version = "0.1"; 

$mapapi = "gmaps";

// you may set your google maps api key
// this is not obligatory by now
// $gkey = "AIzaSyC2CYwhwpM2NLhTm6L6ZKg9-SBpvft4P4U";

// MySQL config
$dbhost = "127.0.0.1"; // mysql host, eg. localhost
$dbuser = "user"; // database user
$dbpass = "pass"; // database pass
$dbname = "runneruplive"; // database name
$salt = ""; // fill in random string here, it will increase security of password hashes

// other
// require login/password authentication 
// (0 = no, 1 = yes)
$require_authentication = 0;

// admin user who has access to all users locations
$admin_user = " ";

// allow automatic registration of new users 
// (0 = no, 1 = yes)
$allow_registration = 1;

// Default interval in seconds for live auto reload
$interval = 10; 

// Default language
// (en, pl, de)
$lang = "en";
//$lang = "pl";
//$lang = "de";

// units
$units = "metric";

?>
