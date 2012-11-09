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

require_once("common.php");
require_once("lib/user/user.class.php");
require_once("./config/config.php");
session_start();

// Create an user object
$user = new user();
$data = $_POST;

if (isset($_POST['password'])) {

    // Start login routine
    $auth = $user->login($data);

    // if authentification failed
    if(!$auth) {
        $wrongPassword = 1;
    }
    // if succceed
    else {
        $wrongPassword = false;
        $_SESSION['user'] = $user;
        //var_dump($_SESSION);
        $_SESSION['logged'] = 1;
        logthis('SUCESS LOGIN');
    }

    
    if( $wrongPassword == 1){
    
        $_SESSION['logged'] = 0;
        $_SESSION['failed'] = $_SESSION['failed'] + 1;
        
        if($_SESSION['failed'] > 3){
            logthis('FAILED LOGIN');
        }
    

        ?>
            <br />
            <br />
            <center>
            <font color="#FF0000"><b>Le nom d'usager et/ou le mot de passe sont erron&eacute;s!</b></font>
            </center>
            <br />
            <br />
        <?php
    }		
    
}

if($_SESSION['logged'] != 1){
    ?>
    <br /><br />
        <form action="<? $_SERVER['PHP_SELF'] ?>" method="post">
        <table align="center" border="0" bordercolor="#0000FF" cellpadding="2" cellspacing="0">
            <tr>
                <td align="right">Usager:</td>
              <td align="left"><input type="text" name="user"></td>
                <td rowspan="3" align="center" valign="middle"><img border="0" src="images/logo.png" /></td>
            </tr>
            <tr>
                <td align="right">Mot de passe:</td>
              <td align="left"><input type="password" name="password"></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input type="submit" value="Connecter"></td>
            </tr>
        </table>		
        </form>
    <?php
}

?>
