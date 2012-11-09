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


//setlocale(LC_ALL, 'fr_CA.iso-8859-1');  
//setlocale(LC_MONETARY, 'fr_CA');

//Config
$fileName = 'S17320_Part_2.csv';
$targetTable = 'iberville';

//Input file
$inputFile = fopen($fileName, 'r');
$numberOfFields = 11;

//Converted files
$outputFile = fopen('./Converted/Output_'.$fileName, 'w');

//Scripts
$sqlQueryOneBegin = "UPDATE `$targetTable` SET `TELEPHONE1` = ";

//Par the file one line at a time
while($theLine = fgetcsv($inputFile)){ 
		
	//Skip the header line
	if($theLine[0] != 'infoCANADA_ID_C'){
	
		//$counter++;
		
		//Init
		$sqlQueryOne = $sqlQueryOneBegin;
		$sqlQueryWhere = "";
	
		/*
		$counter = $counter + 1;
		
		if($counter < 2){
			print_r($theLine);
		}
		*/
			
		
		//some cleanup
		array_walk($theLine, 'cleanupVar');
		
		//check columns count
		if(count($theLine) != $numberOfFields){
			//$errors[] = "bad amount of fields";
		}						
		
		//Query One
		$area = mysql_escape_string($theLine[9]);
		$line = mysql_escape_string($theLine[10]);
		$telephone = $area.'-'.$line;
		$sqlQueryOne = $sqlQueryOne." '$telephone' ";
		
		//WHERE
		
		$postal_code = mysql_escape_string($theLine[7]);
		$postal_code = str_replace(' ', '', $postal_code);
		$sqlQueryWhere = $sqlQueryWhere." WHERE `CO_POSTL` = '$postal_code' ";
		
		$address = $theLine[3];
		$index = strpos($address, ' ');
		$noCivqElect = substr($address, 0, $index + 1);
		$noCivqElect = trim($noCivqElect);
		$sqlQueryWhere = $sqlQueryWhere."AND `NO_CIVQ_ELECT` = '$noCivqElect' ";
		
		//echo $noCivqElect."\n";
		
		$address = substr($address, $index + 1);
		
		$address = str_replace('RUE ', '', $address);
		$address = str_replace('AV ', '', $address);
		$address = str_replace('BOUL ', '', $address);
		$address = str_replace('CH ', '', $address);
		$address = str_replace('COTE ', '', $address);
		$address = str_replace('RANG ', '', $address);
		$address = str_replace('RG ', '', $address);
		$address = str_replace('RTE ', '', $address);
		$address = str_replace('ROUTE ', '', $address);
		$address = str_replace('PLACE ', '', $address);
		$address = str_replace('ALLEE ', '', $address);
		$address = str_replace('CHEMIN DE LA ', '', $address);
		
		$address = trim($address);
		
		$address = str_replace("DE L'", '', $address);
		$address = str_replace('DES ', '', $address);
		$address = str_replace('DE ', '', $address);
		$address = str_replace('DU ', '', $address);
		$address = str_replace('DE LA ', '', $address);
		
		//Merde
		$address = str_replace('DELA ', '', $address);
		$address = str_replace('BDDU ', '', $address);
		$address = str_replace("DEL'", '', $address);
		$address = str_replace(' RTE', '', $address);
		$address = str_replace('LA ', '', $address);
		
		$address = mysql_escape_string($address);
		
		$sqlQueryWhere = $sqlQueryWhere."AND `AD_ELECT` LIKE ('%$address%') ";
		
		//echo $address."\n";
		
		$last_name = mysql_escape_string($theLine[2]);
		$sqlQueryWhere = $sqlQueryWhere."AND `NM_ELECT` = '$last_name' ";

		$first_letter = substr(mysql_escape_string($theLine[1]), 0, 1);
		$sqlQueryWhere = $sqlQueryWhere."AND SUBSTRING(`PR_ELECT`, 1, 1) = '$first_letter' ";
		
		//$city = mysql_escape_string($theLine[5]);
		//$sqlQueryWhere = $sqlQueryWhere."AND `NM_MUNCP` LIKE('%$city%')";
					
		$sqlQueryOne = $sqlQueryOne.$sqlQueryWhere." AND `TELEPHONE1` = '';";			
					
		fwrite($outputFile, $sqlQueryOne."\n");
		
	}
}

fclose($inputFile);
fclose($outputFile);

//If Errors
//$errorFile = fopen('./Converted/Errors/'.$fileName.'_errors.txt', 'w');
//fclose($errorFile);

//cleanup routine to be called from array_walk
function cleanupVar(&$var) {
    //leading and trailing space
    $var = trim($var);
    // Vertical tab
    //$var = str_replace("\x0B", " ", $var);
    // special single quote like: Prud’homme,Nathalie (in contrast to ')
   // $var = str_replace("’", "'", $var);
	//$var = str_replace('"', '', $var); //Removing double quotes a cause des surnoms sur la liste du DGE
    // consecutive extra space
    //$var = preg_replace('/\s{2,}/u', " ", $var);
    
    return;
}

?>
