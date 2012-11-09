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
    $sondage = new telephone('sondage');
    $sondage->circonscription = $_POST['no_circn'];
    $sondage->date = $_POST['date_sondage'];
    $sondage->upload();
}
?>

<form enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" name="test" method="POST">
        <table>
            <tr>
                <td>Numéro de la circonscription: </td>
                <td><input type="text" name="no_circn" value=""/></td>
            </tr>
            <tr>
                <td>Date du sondage (aaaa/mm/dd hh:mm:ss): </td>
                <td><input type="text" name="date_sondage" value=""/></td>
            </tr>
            <tr>
                <td>Fichier &agrave; importer</td>
                <td><input name="userfile" type="file" /></td>
            </tr>
                <td colspan="2" align="center">
                    <input type="submit" value="Send File" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="104857600" />
                </td>
            </tr>
        </table>
    </form>
<?php 
include('include/footer.php');
