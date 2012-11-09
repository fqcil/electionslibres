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


class html
{
    /**
     * 
     * @param $name
     * @param $data Array à 2 dimensions
     * @param $default
     * @param $javascript
     * @return unknown_type
     */
    static function genDropdown($name,$data=array(),$default=NULL,$javascript=NULL, $specialFormat=NULL)
    {
        $html = "<select name=\"$name\" id=\"$name\" $javascript>";
        foreach($data AS $key => $value)
        {
            $select = NULL;
            if($value['value'] == $default)
            {
                $select = 'selected';
            }
            if($specialFormat)
                $displayValue = $value['label']." - ".$value['value'];
            else
                $displayValue = $value['label'];
            $html .= "<option value=\"{$value['value']}\" $select>".$displayValue."</option>";
        }
        $html .= '</select>';
        return $html;
    }

    static function genCheckbox($name,$value,$checked=FALSE)
    {
        $html = NULL;
        if(!empty($_REQUEST[$name]) || $checked=TRUE)
        {
            $checked = TRUE;
        }
        return "<input type=\"checkbox\" name=\"$name\" value=\"$value\">";
    }

    static function genList($titre,$data,$header=array())
    {
        if(!empty($header))
        {
            $row = self::drawRow($header,TRUE);
            $thead = '<thead>'.$row.'</thead>';
            $tfoot = '<tfoot>'.$row.'</tfoot>';
        }
        $html = "
            <table id=\"$titre\" class=\"sort-table\" border=\"1\" cellpadding=\"4\" cellspacing=\"0\">
            $thead
            $tfoot
            <tbody>";
            foreach($data AS $values)
            {
                $html .= self::drawRow($values);
            }
            $html .= "</tbody>
           </table>";
        $html .= <<<EOT
<script type="text/javascript">
<!--
var $titre = new SortableTable(document.getElementById("$titre"),
	[	
	"None", 
	"Number", 	
	"CaseInsensitiveString"
	]);
	//$titre.sort(1);
-->
</script>
EOT;
           return $html;
    }

    static function drawRow($data,$is_header=FALSE)
    {
        $tag = 'td';
        if($is_header === TRUE)
        {
            $tag = 'td';
        }
        $html = "<tr><$tag>".implode("</$tag><$tag>",$data)."</$tag></tr>\n";
        return $html;
    }
}

?>
