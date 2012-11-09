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


class import
{
    protected $db         = NULL;
    protected $tmp_dir    = NULL;
    protected $source     = NULL;
    protected $filename;
    function __construct($datatype=NULL)
    {
        $this->db = DB::getDatabase('pointage');
        $this->tmp_dir = sys_get_temp_dir();
        $this->source = $datatype;
    }

    function upload()
    {
        if(!empty($_REQUEST['serverfile'])) {

            $this->destination = $this->tmp_dir.'/'.pathinfo($_REQUEST['serverfile'],PATHINFO_FILENAME);
            $this->filename = $_REQUEST['serverfile'];
        }
        else if(!empty($_FILES['userfile'])){
            if(!is_uploaded_file($_FILES['userfile']['tmp_name']))
            {
                throw new exception("Fichier téléchargé introuvable");
            }
            $this->destination = $this->tmp_dir.'/'.pathinfo($_FILES['userfile']['name'],PATHINFO_FILENAME);
            $this->filename = $this->tmp_dir.'/'.basename($_FILES['userfile']['name']);
            if(is_dir($this->destination))
            {
                $this->removeDir($this->destination);
            }
            mkdir($this->destination,0777);
            move_uploaded_file($_FILES['userfile']['tmp_name'],$this->filename);
        }
        else {
            throw new exception("Aucun fichier téléchargé ni chemin local spécifié");
        }


        if(stripos($this->filename,'.zip') !== FALSE)
        {
            $this->unzip($this->filename,$this->destination,$this->password);
        }
        else {
            echo "Pas une archive zip, on laisse le fichier {$this->filename} tel quel<br>";
        }
        $this->import();
    }

    static function shellExec($cmd) {
        echo "<p>Executing command $cmd:</p>";
        exec($cmd, $output_lines, $retval);
        foreach($output_lines as $line) {
            echo "$line<br/> ";
        }
        @ob_flush();flush();
    }
    function unzip($file,$destination,$password=NULL, $deleteArchiveAfterUnzip=true)
    {
        //echo "file: $file, destination: $destination, password: $password, deleteArchiveAfterUnzip: $deleteArchiveAfterUnzip";
        !empty($password)?$password="-P$password":NULL;

        $cmd = "unzip $password {$file} -d {$destination}";
        self::shellExec($cmd);

        if($deleteArchiveAfterUnzip) {
            unlink($file);
        }
        if(count($this->findFiles($destination)) === 0)
        {
            throw new exception("Extracted files not found");
        }

    }
    /**
     * Recursive file search
    @param types:  an array of strings the file must contain (all of them must be in the file).  If empty, all files except directories will be returned
    */
    function findFiles($destination,$types=array(), $debug=false)
    {
        if($debug){
            echo "Finding ".implode(', ',$types) ." files in ".$this->destination."<br/>";
        }
        $files = array();
        $dh = opendir($destination);
        while(FALSE !== ($file = readdir($dh)))
        {
            //echo "$file<br>";
            if($file != '.' && $file != '..')
            {
                $file = $destination.'/'.$file;
                if(is_dir($file))
                {
                    $files = array_merge($files,$this->findFiles($file,$types));
                }
                else if(empty($types)) {
                    $files[] = $file;
                }
                else {
                    foreach($types AS $type)
                    {
                        //echo "Comparing '$file' and '$type'<br/>";
                        if(strpos($file,$type) === FALSE)
                        {
                            //echo "NO MATCH! Skipping file...<br/>";
                            continue 2;
                        }
                    }
                    $files[] = $file;
                }
            }
        }
        closedir($dh);
        if($debug){
            echo "Found: "; var_dump($files);
        }
        return $files;
    }

    function removeDir($dir)
    {
        $files = $this->findFiles($dir);
        foreach($files AS $file)
        {
            if(is_dir($file))
            {
                $this->removeDir($file);
                continue;
            }
            unlink($file);
        }
        rmdir($dir);
    }
}

?>
