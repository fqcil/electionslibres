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


require_once("./config/config.php");
require_once("html.class.php");

class DropdownFactory {

    private $data = array();

    function __construct()
    {
    }

    /** Create a dropdown for editing history fields 
     * @param $label
     * @param $table Label table name
     * @param $name
     * @param $default_value
     * @param $idDge
     * @return unknown_type
     */
    static function build_edit_new( $label, $table, $name=NULL,$default_value=NULL, $idDge=NULL)
    {
        
        if(!empty($label))
            $titre = $label.":&nbsp";

        $default_value = empty($default_value)?'bof':$default_value;
        $chck_name = is_null($name)?$table:$name;
        $javascript = 'onchange="processSelectBoxChange(\''.$idDge.'\', \''.$chck_name.'\');"';


        $labelValeurArray = Historique::getLabelValeurArray($table);

        $data = array();
        $data[] = array('label'=>'','value'=>'bof');
        foreach ($labelValeurArray as $valeur => $label)
        {
            $data[] = array('label'=>$label, 'value'=>$valeur);
        }
        $html = html::genDropdown($chck_name,$data,$default_value,$javascript);
        if(empty($name))
        {
            $html = $label .' '.$html.' ';  
        }

        return $titre.$html;
    }

    static function build_filter_new( $label, $table ){
        
        // Print the label
        print("$label:&nbsp");
        print("<select name=$table onchange='this.form.submit();'>");
        empty($_SESSION[$table])?$selected='selected':$selected='';
        print("<option value=\"\" {$selected}>Tous</option>");
        $labelValeurArray = Historique::getLabelValeurArray($table);
        
                foreach ($labelValeurArray as $valeur => $label)
        {
            //foreach($row as $key=>$value){
                ($_SESSION[$table]==$valeur)?$selected='selected':$selected='';
                print("<option value='{$valeur}' $selected>");
                echo $label;
                print("</option>");
            //}
        }
        print("</select>  "); 
    }

    
    static function build_input_text( $name, $value )
    {
        $html = "<input type=\"text\" size=\"40\" maxlength=\"12\" value=".$value." name=".$name.">"; 
        return $html;
    }

    /* 
     * Build the widget panel used to edit entries ( at the bottom of the page )
     */
    function build_footer(){
        /*         
                   echo $this->build_edit("Résultat", "RSLT");
                   echo $this->build_edit("Visite", "VISIT");
                   echo $this->build_edit("Appel", "CALL");
                   echo $this->build_edit("Vote", "VOTE");
         */
    }
}

?>
