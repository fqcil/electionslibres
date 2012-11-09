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

require_once('common.php');
require_once('config/config.php');
error_reporting(E_ALL);
require_once('lib/telephone/telephone.class.php');
include('include/header.php');

if(!empty($_POST) && !empty($_FILES))
{
    $tel = new telephone('liste');
    $tel->password = $_POST['password'];
    $tel->upload();
}
?>

<form enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" name="test" method="POST">
        <table>
            <tr>
                <td>Mot de passe pour le fichier ZIP</td>
                <td><input type="text" name="password" value=""/></td>
            </tr>
            <tr>
                <td>Fichier &agrave; importer</td>
                <td><input name="userfile" type="file" /></td>
            </tr>
                <td colspan="2" align="center">
                    <input type="submit" value="Send File" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="33554432" />
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
<?php 
include('include/footer.php');
