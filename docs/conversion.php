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


/*

Convert files received from DGE from OEM to ANSI

Run the script in order to import data in the following table structure:

CREATE TABLE `200801` (
  `ID_ELECT` int(11) NOT NULL auto_increment,
  `ID_CIRCN_PROVN` int(11) NOT NULL,
  `NM_CIRCN_PROVN` varchar(255) NOT NULL default '',
  `NO_SECTR_ELECT` int(11) NOT NULL default '0',
  `NO_SECTN_VOTE` int(11) NOT NULL default '0',
  `NM_ELECT` varchar(255) NOT NULL default '',
  `PR_ELECT` varchar(255) NOT NULL default '',
  `DA_NAISN_ELECT` date NOT NULL default '0000-00-00',
  `CO_SEXE` enum('M','F') NOT NULL default 'M',
  `AD_ELECT` varchar(255) NOT NULL default '',
  `NO_CIVQ_ELECT` varchar(10) NOT NULL default '0',
  `NO_APPRT_ELECT` int(11) NOT NULL default '0',
  `NM_MUNCP` varchar(255) NOT NULL default '',
  `CO_POSTL` varchar(7) NOT NULL default '',
  `NO_ELECT` int(11) NOT NULL default '0',
  `STATUT_ELECT` varchar(5) NOT NULL default '',
  UNIQUE KEY `ID_ELECT` (`ID_ELECT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

*/


	//Directory
	$dir = ".";
	$exportDir = "./Export/";
	$dh = opendir($dir);
	$fileCounter = 0;
	$fileStep = 5;
	$cumulFileName = 'cumulatif.csv';
	$cumulFileCounter = 0;
	$newFile = false;
	
	//Looping into directory
	while(($fileName = readdir($dh)) != false){
		
		if(substr($fileName, 0, 5) == 'ppcir'){
		
			$newFile = false;
		
			$lineCounter = 0;
			$idCirc	= substr($fileName, 5, 3);
			
			$fr = fopen($fileName, 'r');
			$fw = fopen($exportDir.$fileName, 'w');
			
			if($fileCounter%5 == 0 || $fileCounter == 0){
				
				if($fileCounter > 1){
					fclose($cfw);
				}
				
				$cumulFileCounter++;
				if(strlen($cumulFileCounter) == 1){
					$cumulFileCounter = '0'.$cumulFileCounter;
				}
				$cfw = fopen($exportDir.$cumulFileCounter.$cumulFileName, 'w');
				$newFile = true;
			
			}
			
			while($line = fgets($fr)){
				$lineCounter++;
				
				if($lineCounter > 2 && $line != '' && strlen($line) > 55){	
					
                    echo($line);
					$line = str_replace('"', '', $line);
					$line = str_replace('+', utf8_decode('È'), $line);
					$line = str_replace(';', '","', $line);
					
					$line = substr($line, 0, strlen($line) - 4);
					
					$line = '"","'.$idCirc.'","'.$line;
					
					if($lineCounter == 3){
						fwrite($fw, $line);
					}
					else{
						fwrite($fw, "\n".$line);
					}
					
					if($newFile){
						fwrite($cfw, $line);
						$newFile = false;
					}
					else{
						fwrite($cfw, "\n".$line);
					}
					
				}
				
			}
			
			fclose($fr);
			fclose($fw);
			$fileCounter++;
		}
	}
	
	fclose($cfw);

?>
