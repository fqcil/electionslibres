<?php
/*
 *  Copyright (C) 2012 Fédération Québécoise des Communautés et Industries du Libre.
 *
 *  Author: Emmanuel Milou <emmanuel.milou@fqcil.org>
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *   Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */


    require_once("./lib/user/user.class.php");
    require_once("./lib/circonscription.class.php");
	session_start();
	require_once("common.php");
	require_once("./config/config.php");
	require_once("./lib/db/db.class.php");

    /*
     * Routine principale
     */
    $userdata = $_POST;
    $user = $_SESSION['user'];

    switch( $userdata['apply'] ){
        case 'Effacer':
            $conn = DB::getDatabase('pointage');
            // Get the batch of user to delete
            $entrybatch = split(',', $_POST['displayed']);
            foreach($entrybatch as $key => $value){
                $value_esc = stripSpecialChars($value);
                if ($_POST['user_'.$value_esc] == $value && $value != ""){ 
                    // Delete the user
                    User::delete_user($conn, $value);
                }
            }
            break;
        case "Creer":
            // Create the user
            User::create_user( DB::getDatabase('pointage'), $userdata['username'], $userdata['password'], $userdata['first_name'], $userdata['last_name'], $userdata['permission'], $user->no_circn);
            break;
        default:
            break;
    }

header('Location: admin.php');
exit;


?>
