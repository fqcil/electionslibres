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

require_once("./lib/db/db.class.php");
class user
{
  private $db = NULL;
  public $username    = NULL;
  public $access      = NULL;
  public $first_name  = NULL;
  public $last_name   = NULL;
  public $no_circn    = NULL;
  public $nm_circn    = NULL;
  public $comment  = NULL;
  public $circn_sort_order  = NULL;

  /*
   * Génère aléatoirement un mot de passe
   */
  public static function genPassword($pass = null){
    if(!$pass)
    {
      $pass = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890'),10,8);
    }
    return self::hashSSHA($pass);
  }

  static function delete_user( $db, $value ){

    // Delete the user
    $sql = "DELETE from users WHERE username = '{$value}';";
    $db->query($sql);
  }

  static function create_user( $db, $username, $pass, $first_name, $last_name, $access, $no_circn, $no_region ){
    $password = self::genPassword($pass);

    // Ecraser l'entrée si elle existe déjà
    $sql = "SELECT * from users WHERE username='${username}'";
    $result = $db->query($sql);
    if($result->fetch(PDO::FETCH_ASSOC))
    {
      // remove the user
      self::delete_user($db, $username);
    }
    // Trouver la première circonscription de la région
    //$sql = "SELECT circonscriptions.NO_CIRCN_PROVN FROM circonscriptions JOIN regions ON (circonscriptions.region=regions.id_region) WHERE regions.id_region=$no_region ORDER BY nom, NM_CIRCN_PROVN LIMIT 1";
    //var_dump($sql);
    //$result = $db->query($sql);
    //$row = $result->fetch(PDO::FETCH_ASSOC);
    //$firstCircnId = $row['NO_CIRCN_PROVN'];

    //$no_circn?$no_circn_sql="'{$no_circn}'":$no_circn_sql="'{$firstCircnId}'";
    //$no_region?$no_region_sql="'{$no_region}'":$no_region_sql="NULL";
    // Insert the entry in the users table
    $sql = "INSERT INTO users (username,pw,access,first_name,last_name, NO_CIRCN_PROVN, id_region)
      VALUES('{$username}','{$password}','{$access}','{$first_name}','{$last_name}', $no_circn, NULL)";
    //var_dump($sql);
    $db->query($sql);
  }

  /*
   * Pour une circonscription donnée, crée un usager ro et un usager r
   */
  static function create_users($circ_no, $db){

    // Retrieve the conscription name
    $circ_nm = Circonscription::circn_name_from_no( $circ_no, $db );

    $circ_nm = stripSpecialChars($circ_nm);

    // Build the usernames with the conscription name
    //var_dump($circ_no);
    if(strstr($circ_no, 'region_')) {
      $regionId = substr($circ_no, strlen('region_'));
      // the read-only user
      $username = $circ_no.'_r';
      $access = 'report';
      $first_name="";
      $last_name="";
      self::create_user($db, $username, $first_name, $last_name, $access, null, $regionId);

      // the read-write user, none for regions
    }
    else if( $circ_nm != "" )
    {
      // the read-only user
      $username = $circ_nm.'r';
      $access = 'report';
      $first_name="";
      $last_name="";
      self::create_user($db, $username, $first_name, $last_name, $access, $circ_no, null);

      // the read-write user
      $username = $circ_nm.'w';
      $access = 'writer';
      self::create_user($db, $username, $first_name, $last_name, $access, $circ_no, null);
    }
  }

  function __construct()
  {
    $this->setDB();
  }

  function __wakeup()
  {

    $this->setDB();
  }

  function __sleep()
  {
    return array('username', 'password', 'access', 'first_name', 'last_name', 'no_circn', 'nm_circn', 'circn_sort_order');
  }

  function setDB()
  {
    $this->db = DB::getDatabase('pointage');
  }

  function getDB()
  {
    return $this->db;
  }

  /**
   *
   * @param $data The POST data
   * @return true or false
   */
  function login($data)
  {
    // $sql = "SELECT * FROM users WHERE username='{$data['user']}' AND pw='{$password}'";
    // Get user
    $sql = "SELECT * FROM users WHERE username='{$data['user']}'";
    $result = $this->db->query($sql);
    $userdata = $result->fetch(PDO::FETCH_ASSOC);

    if($userdata)
    {
      // Retrieve stored password
      $storedPassword = $userdata['pw'];
      // Fetch salt from stored password
      $salt = substr($storedPassword, 1, 4);
      // Encrypt user entry with given salt 
      $password = self::hashSSHA($data['password'], $salt);

      // Validate authentication
      if($password == $storedPassword)
      {
        $this->no_circn = $userdata['NO_CIRCN_PROVN'];
        // Get the name of the circonscription from the number
        $sql = "SELECT `NM_CIRCN_PROVN` FROM circonscriptions WHERE NO_CIRCN_PROVN= '{$this->no_circn}'";
        $result = $this->db->query($sql);
        $district = $result->fetch(PDO::FETCH_ASSOC);
        $this->nm_circn = $district['NM_CIRCN_PROVN'];
        $this->username = $userdata['username'];
        $this->access = $userdata['access'];
        $this->first_name = $userdata['first_name'];
        $this->last_name = $userdata['last_name'];
        $this->no_region = $userdata['id_region'];
        $this->circn_sort_order = $userdata['circn_sort_order'];
        return TRUE;
      }
    }
    else 
    {
      return FALSE;
    }
  }

  function insert($data)
  {
    $sql = "INSERT INTO users (username,pw,access,first_name,last_name, NO_CIRCN_PROVN)
      VALUES('{$this->username}','{$this->password}','{$this->access}','{$this->first_name}','{$this->last_name}', '{$this->no_circn}')";

    //if($this->db->query($sql))
    //{
    //  return TRUE;
    //}
    //return FALSE;
    return TRUE;
  }

  function update()
  {

  }


  public static function hashSSHA($password, $useSalt = FALSE)
  {
    if( $useSalt)
    {
      $salt = $useSalt;
    } 
    else 
    {
      // $[salt]$[pass]
      $salt = sha1(rand());
      $salt = substr($salt, 0, 4);
    }
    // Encrypt with salt
    $hash = '$' . $salt . '$' . base64_encode(sha1($password . $salt, true) );
    return $hash;
  } 
}

?>
