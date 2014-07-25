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
// TrackMe API
// http://forum.xda-developers.com/showpost.php?p=3250539&postcount=2

require_once("config.php");
$debug = 0;
$activityType = array("","Running", "Bike");
$inputJSON = file_get_contents('php://input');


    $input= json_decode( $inputJSON, TRUE ); //convert JSON into array
    $user = $input['userName'];
    $pass = $input['password'];
    $lat = $input['lat'];
    $long = $input['long'];
    $pace = $input['Pace'];
    $TotalDistance = $input['TotalDistance'];
    $eventType = $input['runningEventType'];
    $TotalTime = $input['TotalTime'];
    $dateoccurred = time();
    $tripname = $user.'-'.date("Ymd"); 
    $iconid = 50;
//    error_log("$tripname");
//    foreach ($input as $key => $value) {
//        echo "<tr>";
//        echo "<td>";
//        echo $key;
//        echo "</td>";
//        echo "<td>";
//        echo $value;
//        echo "</td>";
//        echo "</tr>";
//        error_log("key $key value $value");
//    }

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno) {
  //Result:4 Unable to connect database.
  quit(4);
}

if ((!$user) || (!$pass)){ 
  //Result:3 User or password not specified.
  error_log("user or pass not specified ($user / $pass)");
  quit(3);
}
$query = $mysqli->prepare("SELECT ID,username,password FROM users WHERE username=? LIMIT 1");
$query->bind_param('s', $user);
$query->execute();
$query->store_result();
$query->bind_result($userid, $rec_user, $rec_pass);
$query->fetch();
$num = $query->num_rows;
$query->free_result();
$query->close();

if ($num) {
  if (($user==$rec_user) && ($pass!=$rec_pass)) {
    //Result:1 User correct, invalid password.
    error_log("user correct, invalid password ($user / $pass <> $rec_pass)");
    quit(1);
  }
}
else {
  if ($allow_registration) {
    // User unknown, let's create it
    $query = $mysqli->prepare("INSERT INTO users (username,password) VALUES (?,?)");
    $query->bind_param('ss', $user, $pass);
    $query->execute();
    $userid = $mysqli->insert_id;
    $query->close();
    if (!$userid) {
      //Result:2 User did not exist but after being created couldn't be found.
      // Or rather something went wrong while updating database
    error_log("User not created");
    quit(2);
    }
  } 
  else {
    // User unknown, we don't allow autoregistration
    // Let's use this one:
    //Result:1 User correct, invalid password.
    error_log("User unknown");
    quit(1);    
  }
}

   if ($tripname) {
      // get tripid
      //$query = $mysqli->prepare("SELECT ID FROM trips WHERE FK_Users_ID=? AND Name=? LIMIT 1");      
      //$query->bind_param('is', $userid, $tripname);
      $query = $mysqli->prepare("SELECT ID,Name FROM trips WHERE FK_Users_ID=? AND Name LIKE '$tripname%' ORDER BY Name DESC limit 1");
      $query->bind_param('i', $userid );
      $query->execute();
      $query->store_result();
      $query->bind_result($tripid,$tripname);
      $query->fetch();
      $num = $query->num_rows;
      $query->free_result();
      $query->close();
            
      $query = $mysqli->prepare("SELECT EventType FROM positions where FK_Users_ID=? and FK_Trips_ID=? ORDER BY ID DESC limit 1");
      $query->bind_param('ii', $userid, $tripid);
      $query->execute();
      $query->store_result();
      $query->bind_result($eventTypeLast);
      $query->fetch();
      $query->free_result();
      $query->close();     
                         
      if ((!$num) || ($eventTypeLast == 3)) {
        // create trip
        error_log("create trip");
        if (substr_count($tripname, '-') == 1) {
           $tripname = $tripname."-1";
           error_log("create trip 1 $tripname");
        }
        else {
           $ptn = "/(.*)-(\d+$)/";
           preg_match($ptn, $tripname, $matches);
           $matches[2]=$matches[2]+1;
           $tripname = $matches[1]."-".$matches[2];
           error_log("create trip regex 2 $tripname");
        }

        $query = $mysqli->prepare("INSERT INTO trips (FK_Users_ID,Name) VALUES (?,?)");
        $query->bind_param('is', $userid, $tripname);
        $query->execute();
        $tripid = $mysqli->insert_id;
        $query->close();
        if (!$tripid) {
          //Result:6 Trip didn't exist and system was unable to create it.
          quit(6);
        }
    }
   
    $sql = "INSERT INTO positions "
          ."(FK_Users_ID,FK_Trips_ID,Latitude,Longitude,FK_Icons_ID,"
          ."Pace,TotalDistance,TotalTime,EventType) VALUES (?,?,?,?,?,?,?,?,?)";
    $query = $mysqli->prepare($sql);
    $query->bind_param('iiddisisi',$userid,$tripid,$lat,$long,$iconid,$pace,$TotalDistance,$TotalTime,$eventType);

    $query->execute();
    $query->close();
    if ($mysqli->errno) {
      //Result:7|SQLERROR   Insert statement failed.
      error_log("Mysql error on upload 1");
      quit(7,$mysqli->error);
    }
    quit(0);
    break;
}

function quit($errno,$param=""){
  print "Result:".$errno.(($param)?"|$param":"");
//  error_log( "Result:".$errno.(($param)?"|$param":""));

  exit();
}

$mysqli->close();
?>

