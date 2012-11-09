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

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
	require_once("./config/config.php");	
	require_once("./lib/user/user.class.php");
	require_once("./lib/db/db.class.php");

	//******************************************************************
	//Display a generic error message
	//******************************************************************	
	function dislayErrorMessage($source, $function){
		$errorMessage = "<font color=\"#FF0000\"><b>ERROR!</b><BR>SOURCE: ".$source."<BR>  IN LOCATION: ".$function."</font>";
		die($errorMessage);	
	}
	
	function pretty_print_r($param) {
	    echo "<pre>";
	    print_r($param);
	    echo "</pre>";
	}
	//******************************************************************
	//Write a text output to a file
	//******************************************************************	
	function writeOutputToFile($filename, $output){
	
		//TODO:METTRE DU ERROR HANDLING...
		
		if(file_exists($filename)){
			//Backup
			copy($filename, $filename.".old");
		}
		
		if( $fileHandle = fopen($filename, 'w') ){
			fwrite($fileHandle, $output);
			fclose($fileHandle);
		}
		else{		
			dislayErrorMessage("Could not open the file", "writeOutputToFile common.php");
		}
	}	
	
	//******************************************************************
	//SPAM
	//******************************************************************	
	function sendNewsletter($addresses, $ccAddress, $bccAddress, $fromName, $fromAddress, $subject, $htmlMessage, $textMessage, $mode){

		$num = count($addresses);
		$mail = new PHPMailer();
		
		//TODO: ...
		$bccAddress = "FQCIL@yamba.ca";
		
		//SET HEADER
		$header = "From: ".$fromName." <".$fromAddress.">\r\n";
		if($ccAddress != ""){
			$header = $header."Cc: ".$ccAddress."\r\n";
			$mail->AddCC($ccAddress);			
		}
		if($bccAddress != ""){
			$header = $header."BCc: ".$bccAddress."\r\n";
			$mail->AddBCC($bccAddress);	
		}
		
		//SET MESSAGE
		if($mode == "TEXT"){
			$header = $header."Content-Type: text/plain;";
			$message = $textMessage;
		}
		else if($mode == "HTML"){
			$header = $header."Content-Type: text/html;";		
			$message = $htmlMessage;
		}
		else if($mode == "MULTI"){
			
			//$mail->IsSMTP();                                   // send via SMTP
			//$mail->Host     = "smtp1.site.com;smtp2.site.com"; // SMTP servers
			//$mail->SMTPAuth = true;     // turn on SMTP authentication
			//$mail->Username = "jswan";  // SMTP username
			//$mail->Password = "secret"; // SMTP password
			
			$mail->From     = $fromAddress;
			$mail->FromName = $fromName;
			//$mail->AddAddress("josh@site.com","Josh Adams"); 
			//$mail->AddAddress("ellen@site.com");               // optional name
			//$mail->AddReplyTo("FQCILmercier@FQCIL.qc.ca","FQCIL");
			$mail->WordWrap = 50;                              // set word wrap
			//$mail->AddAttachment("/var/tmp/file.tar.gz");      // attachment
			//$mail->AddAttachment("/tmp/image.jpg", "new.jpg"); 
			$mail->IsHTML(true);                               // send as HTML
			$mail->Subject  =  $subject;
			$mail->Body     =  $htmlMessage;
			$mail->AltBody  =  $textMessage;
						
		}

		//TODO: ARRRANGER PLUS TARD
		$i = 0;
		while($i < $num){
			$address = $addresses[$i];
			
			if($mode == "HTML" || $mode == "TEXT"){
				if( mail($address, $subject, $message, $header) ){
					$success = $success.$address."<br>";
				}
				else{
					$error = $error .$address."<br>";
				}
			}
			else if($mode == "MULTI"){
				$mail->AddAddress($address);
				if( $mail->Send() ){
					$success = $success.$address."<br>";
				}
				else{
					$error = $error .$address."<br>";
				}
				$mail->ClearAddresses();				
			}
			$i++;
		}
		return "<b>SUCCESSFUL:</b><br>".$success."<br><br><b>ERRORS:</b><br>".$error;
	}	
	
	//******************************************************************
	//VERIFY EMAIL FORMAT WITH REGULAR EXPRESSION
	//******************************************************************	
	function validateEmailAddresses($addresses){
		//PARSE LE VECTEUR ET APPEND LES MAUVAISES ADRESSES
		$regex = "/(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}/";
		$wrongAddresses = "";
		$num = count($addresses);
		$i = 0;
		while($i < $num){
			$address = $addresses[$i];
			if(preg_match_all($regex, $address, $matches)){
				$bof = count($matches);
				if($bof < 1){
					if($wrongAddresses != ""){
						$wrongAddresses = $wrongAddresses.$address."<br>";
					}
				}
			}
			else{
				if($wrongAddresses != ""){
					$wrongAddresses = $wrongAddresses.$address."<br>";
				}
			}
			$i++;
		}
		return $wrongAddresses;
	}
	
	//******************************************************************
	//Allow some tags...
	//******************************************************************	
	function allowSomeTags($chunk){

		$chunk = str_replace("&lt;strong&gt;", "<strong>", $chunk);
		$chunk = str_replace("&lt;/strong&gt;", "</strong>", $chunk);
		$chunk = str_replace("&lt;center&gt;", "<center>", $chunk);
		$chunk = str_replace("&lt;/center&gt;", "</center>", $chunk);		
		$chunk = str_replace("&lt;b&gt;", "<b>", $chunk);
		$chunk = str_replace("&lt;/b&gt;", "</b>", $chunk);
		$chunk = str_replace("&lt;img", "<img", $chunk);
		$chunk = str_replace("&lt;a", "<a", $chunk);
		$chunk = str_replace("&lt;/a&gt;", "</a>", $chunk);
		$chunk = str_replace("&lt;em&gt;", "<em>", $chunk);
		$chunk = str_replace("&lt;/em&gt;", "</em>", $chunk);
		$chunk = str_replace("&lt;h1&gt;", "<h1>", $chunk);
		$chunk = str_replace("&lt;/h1&gt;", "</h1>", $chunk);
		$chunk = str_replace("&lt;h2&gt;", "<h2>", $chunk);
		$chunk = str_replace("&lt;/h2&gt;", "</h2>", $chunk);
		$chunk = str_replace("&lt;h3&gt;", "<h3>", $chunk);
		$chunk = str_replace("&lt;/h3&gt;", "</h3>", $chunk);
		$chunk = str_replace("&lt;font", "<font", $chunk);
		$chunk = str_replace("&lt;/font&gt;", "</font>", $chunk);
		$chunk = str_replace("&lt;table", "<table", $chunk);
		$chunk = str_replace("&lt;/table&gt;", "</table>", $chunk);
		$chunk = str_replace("&lt;td", "<td", $chunk);
		$chunk = str_replace("&lt;/td&gt;", "</td>", $chunk);
		$chunk = str_replace("&lt;tr", "<tr", $chunk);
		$chunk = str_replace("&lt;/tr&gt;", "</tr>", $chunk);				
		
		$chunk = str_replace("\"&gt;", "\">", $chunk);						
		$chunk = str_replace("/&gt;", "/>", $chunk);		
		
		return $chunk;
	}			
	
	function logthis($action){
			
            $connection = $_SESSION['user']->getDB();
            $dbQuery = "INSERT INTO `log` (`user`, `action`, `ip_address`) VALUES ('".$_SESSION['user']->username."', '".addslashes($action)."', '".ip2long($_SERVER['REMOTE_ADDR'])."')"; 
			
            // pdo - exec function for INSERT
            if (!($result = $connection->exec($dbQuery))) dislayErrorMessage($dbQuery, "common.php");
            $electorId = $connection->lastInsertId();		
	}

	    function stripSpecialChars($string,$decode=true)
    {
        if(!isset($translate_char))
        {
            static $translate_char = array();
            $translate_char['À'] = 'a';
            $translate_char['Á'] = 'a';
            $translate_char['Â'] = 'a';
            $translate_char['Ã'] = 'a';
            $translate_char['Ä'] = 'a';
            $translate_char['Å'] = 'a';
            $translate_char['Æ'] = 'a';
            $translate_char['Æ'] = 'a';
            $translate_char['Ç'] = 'c';
            $translate_char['È'] = 'e';
            $translate_char['É'] = 'e';
            $translate_char['Ê'] = 'e';
            $translate_char['Ë'] = 'e';
            $translate_char['Ì'] = 'i';
            $translate_char['Í'] = 'i';
            $translate_char['Î'] = 'i';
            $translate_char['Ï'] = 'i';
            $translate_char['Ð'] = 'd';
            $translate_char['Ñ'] = 'n';
            $translate_char['Ò'] = 'o';
            $translate_char['Ó'] = 'o';
            $translate_char['Ô'] = 'o';
            $translate_char['Õ'] = 'o';
            $translate_char['Ö'] = 'o';
            $translate_char['Ø'] = 'o';
            $translate_char['Ù'] = 'u';
            $translate_char['Ú'] = 'u';
            $translate_char['Û'] = 'u';
            $translate_char['Ü'] = 'u';
            $translate_char['Ý'] = 'y';
            $translate_char['Þ'] = 'b';
            $translate_char['ß'] = 's';
            $translate_char['à'] = 'a';
            $translate_char['á'] = 'a';
            $translate_char['â'] = 'a';
            $translate_char['ã'] = 'a';
            $translate_char['ä'] = 'a';
            $translate_char['å'] = 'a';
            $translate_char['æ'] = 'a';
            $translate_char['ç'] = 'c';
            $translate_char['è'] = 'e';
            $translate_char['é'] = 'e';
            $translate_char['ê'] = 'e';
            $translate_char['ë'] = 'e';
            $translate_char['ì'] = 'i';
            $translate_char['í'] = 'i';
            $translate_char['î'] = 'i';
            $translate_char['ï'] = 'i';
            $translate_char['ð'] = 'd';
            $translate_char['ñ'] = 'n';
            $translate_char['ò'] = 'o';
            $translate_char['ó'] = 'o';
            $translate_char['ô'] = 'o';
            $translate_char['õ'] = 'o';
            $translate_char['ö'] = 'o';
            $translate_char['ø'] = 'o';
            $translate_char['ù'] = 'u';
            $translate_char['ú'] = 'u';
            $translate_char['û'] = 'u';
            $translate_char['ý'] = 'y';
            $translate_char['ý'] = 'y';
            $translate_char['þ'] = 'b';
            $translate_char['ÿ'] = 'y';
            $translate_char['Ŕ'] = 'R';
            $translate_char['ŕ'] = 'r';
            $translate_char['\''] = '';
            $translate_char['"'] = '';
            $translate_char[' '] = '_';
        }

        if($decode == false)
        {
            return strtr(utf8_decode(strip_tags($string)),$translate_char);
        }
        return strtr(strip_tags($string),$translate_char);
    }

	
?>
