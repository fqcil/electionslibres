<?php
// +-------------------------------------------------------------------+
// | WiFiDog Authentication Server                                     |
// | =============================                                     |
// |                                                                   |
// | The WiFiDog Authentication Server is part of the WiFiDog captive  |
// | portal suite.                                                     |
// +-------------------------------------------------------------------+
// | PHP version 5 required.                                           |
// +-------------------------------------------------------------------+
// | Homepage:     http://www.wifidog.org/                             |
// | Source Forge: http://sourceforge.net/projects/wifidog/            |
// +-------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or     |
// | modify it under the terms of the GNU General Public License as    |
// | published by the Free Software Foundation; either version 2 of    |
// | the License, or (at your option) any later version.               |
// |                                                                   |
// | This program is distributed in the hope that it will be useful,   |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of    |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     |
// | GNU General Public License for more details.                      |
// |                                                                   |
// | You should have received a copy of the GNU General Public License |
// | along with this program; if not, contact:                         |
// |                                                                   |
// | Free Software Foundation           Voice:  +1-617-542-5942        |
// | 59 Temple Place - Suite 330        Fax:    +1-617-542-2652        |
// | Boston, MA  02111-1307,  USA       gnu@gnu.org                    |
// |                                                                   |
// +-------------------------------------------------------------------+

/**
 * Network status page
 *
 * @package    WiFiDogAuthServer
 * @author     Benoit Grégoire <bock@step.polymtl.ca>
 * @copyright  2004-2006 Benoit Grégoire, Technologies Coeus inc.
 * @version    Subversion $Id: schema_validate.php 1384 2008-10-02 13:44:42Z networkfusion $
 * @link       http://www.wifidog.org/
 */

/**
 * Define current database schema version
 */
define('REQUIRED_SCHEMA_VERSION', 15);
/** Used to test a new shechma version before modyfying the database */
define('SCHEMA_UPDATE_TEST_MODE', false);

function parseSchemaVersion($rawVersion, &$currentSchVerMajor, &$currentSchVerFragment) {
    $currentSchVerFragments = explode('/', $rawVersion);
    $currentSchVerMajor = $currentSchVerFragments[0];
    if(isset($currentSchVerFragments[1])) {
        $currentSchVerFragment = $currentSchVerFragments[1];
    }
    else {
        $currentSchVerFragment = null;
    }
}
function getSchemaInfo($tag, &$currentSchVerMajor, &$currentSchVerFragment) {
    $db = DB::getDatabase('pointage');

    // Init values
    $row = null;
    $dbRetval = false;
    try {
        // Check the schema info
        $sql = "SELECT * FROM schema_info WHERE tag='{$tag}'";
        $result = $db->query($sql);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $retval = $row['value'];
        parseSchemaVersion($retval, &$currentSchVerMajor, &$currentSchVerFragment);
    }
    catch (Exception $e) {
        /* Be quiet */
        $retval = null;
    }
    return $retval;
}
/**
 * Check that the database schema is up to date.  If it isn't, offer to update it.
 *
 * @return void
 */
function validate_schema() {
    $currentSchVerMajor = null;
    $currentSchVerFragment = null;
    $schemaVersion = getSchemaInfo('schema_version', $currentSchVerMajor, $currentSchVerFragment);
    if (empty ($schemaVersion)) {
        echo "<html><body>";
        echo "<h1>" . _("I am unable to retrieve the schema version.") . "</h1>";
        echo "</html></body>";
        exit ();
    }
    else {
        if ($currentSchVerMajor < REQUIRED_SCHEMA_VERSION || $currentSchVerFragment !== null) {
            update_schema();
        }
    }
}

/**
 * Prints the standard update message to which version the database schema
 * will be updated
 *
 * @param int $version Version to which the database schema will be updated
 *
 * @return void
 */
function printUpdateVersion($version) {
    if (isset ($version)) {
        echo "<h2>Preparing SQL statements to update schema to version <i>$version</i></h2>";
        @ ob_flush();
        flush();
    }
}

/**
 * Try to bring the database schema up to date.
 *
 * @return void
 */
function update_schema() {

    $db = DB::getDatabase('pointage');

    echo "<html><head><h1>\n";
    echo _("Trying to update the database schema.");
    echo "</h1>\n";



    $retval = real_update_schema(REQUIRED_SCHEMA_VERSION);
    $i = REQUIRED_SCHEMA_VERSION;
    if ($retval===FALSE) {
        echo "<h1>Update to schema $i failed!</h1>";
        exit (1);
    }
    else {
        echo "<h2>Update to schema $i successfull</h2>";
    }
    @ ob_flush();
    flush();

    if (SCHEMA_UPDATE_TEST_MODE == false) {
        //echo "<h2>Vacuuming database (this might take a little while)</h2>";
        @ ob_flush();
        flush();
        //$db->exec("ANALYZE;\n");
        //echo "<h2>Vacuuming complete</h2>";
        @ ob_flush();
        flush();
    }
}
/**
 * Try to bring the database schema up to date.
 * @param $currentSchVer The target schema
 * @return void
 */
function real_update_schema($targetSchVer) {
    $sql = '';
    $db = DB::getDatabase('pointage');
    $currentSchVerMajor = null;
    $currentSchVerFragment = null;
    $currentSchVer = getSchemaInfo('schema_version', $currentSchVerMajor, $currentSchVerFragment); //Re-check the schema version, it could have been updated by another thread

    $newSchVer = 2;
    if ($currentSchVerMajor < $newSchVer && $newSchVer <= $targetSchVer) {

        $sql[$newSchVer][]= <<<EOT
CREATE TABLE allegeance (
  valeur varchar(25) NOT NULL,
  label varchar(255) NOT NULL,
  PRIMARY KEY  (valeur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO allegeance (valeur, label) VALUES
('FQCIL', 'Action Démocratique du Québec'),
('plq', 'Parti Libéral du Québec'),
('pq', 'Parti Québécois'),
('qs', 'Québec Solidaire'),
('pv', 'Parti Vert'),
('indecis', 'Indécis'),
('contre', 'Contre');  
  
CREATE TABLE resultat (
  valeur varchar(25) NOT NULL,
  label varchar(255) NOT NULL,
  PRIMARY KEY  (valeur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO resultat (valeur, label) VALUES
('noanswer', 'Ne répond pas'),
('wrongnumber', 'Mauvais numéro'),
('success', 'Succès');

CREATE TABLE type_contact (
  valeur varchar(25) NOT NULL,
  label varchar(255) NOT NULL,
  PRIMARY KEY  (valeur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO type_contact (valeur, label) VALUES
('autodialed', 'Auto dialed'),
('called', 'Téléphone'),
('visited', 'Porte-à-porte'),
('coindetable', 'Coin de table');

CREATE TABLE vote (
  valeur varchar(30) character set utf8 NOT NULL,
  label varchar(255) character set utf8 NOT NULL,
  PRIMARY KEY  (valeur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO vote (valeur, label) VALUES
('nvp', 'Ne vote pas'),
('voted', 'A voté'),
('bva', 'A voté par anticipation');

CREATE TABLE historique_pointage (
  HIST_ID int(11) unsigned NOT NULL auto_increment,
  `DATE` timestamp NOT NULL default CURRENT_TIMESTAMP,
  CONTACT varchar(25) NOT NULL,
  RESULTAT varchar(25) NOT NULL,
  ALLEGEANCE varchar(25) default NULL,
  VOTE varchar(25) default NULL,
  ELECT_ID int(11) NOT NULL,
  IS_DERNIER_POINTAGE boolean NOT NULL default FALSE,
  PRIMARY KEY  (HIST_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `pointage` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE historique_pointage ADD FOREIGN KEY (CONTACT) REFERENCES type_contact(valeur) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE historique_pointage ADD FOREIGN KEY (RESULTAT) REFERENCES resultat(valeur) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE historique_pointage ADD FOREIGN KEY (ELECT_ID) REFERENCES pointage(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE historique_pointage ADD FOREIGN KEY (ALLEGEANCE) REFERENCES allegeance(valeur) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE historique_pointage ADD FOREIGN KEY (VOTE) REFERENCES vote(valeur) ON UPDATE CASCADE ON DELETE RESTRICT;

EOT;
        $result = $db->query("SELECT * from pointage;");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)){
            $DATE = $row['DATE_TIME'];
            $ELECT_ID = $row['id'];
            $row['RSLT']?$ALLEGEANCE="'".$row['RSLT']."'":$ALLEGEANCE='NULL';
            $row['VOTE']?$VOTE="'".$row['VOTE']."'":$VOTE='NULL';

            if (!empty($row['VISIT'])){
                $CONTACT='visited';
                if($row['VISIT']=='visited') {
                    $RESULTAT = 'success';
                }
                elseif($row['VISIT']=='absent') {
                    $RESULTAT = 'noanswer';
                }
                else {
                    continue;
                }
            }
            else if(!empty($row['CALL'])){
                $CONTACT='called';
                if($row['CALL']=='wrongnumber') {
                    $RESULTAT = 'wrongnumber';
                }
                elseif($row['CALL']=='autodialed') {
                    if($row['AUTODIALED']=='noanswer')
                    $RESULTAT = 'noanswer';
                }
                else {
                    $RESULTAT = 'success';
                }
            }
            else {
                if($row['RSLT']) {
                    $CONTACT='called';
                    $RESULTAT = 'success';
                }
                else {
                    echo ("Données invalides");
                    var_dump($row);
                    continue;
                }
            }
            $sql[$newSchVer][]= "INSERT INTO historique_pointage (DATE, CONTACT, RESULTAT, ALLEGEANCE, VOTE, ELECT_ID, IS_DERNIER_POINTAGE) ";
            $sql[$newSchVer][]= "VALUES ('$DATE', '$CONTACT', '$RESULTAT', $ALLEGEANCE, $VOTE, $ELECT_ID, TRUE);\n";
        }//End while


        $sql[$newSchVer][]= "ALTER TABLE pointage DROP COLUMN RSLT, DROP COLUMN VISIT, DROP COLUMN `CALL`, DROP COLUMN AUTODIAL, DROP COLUMN MARKETING, DROP COLUMN VOTE;\n";
    }
    $newSchVer = 3;
    if ($currentSchVerMajor <= $newSchVer && $newSchVer <= $targetSchVer) {

        $sql[$newSchVer][]= "ALTER TABLE `historique_pointage` ADD INDEX ( `IS_DERNIER_POINTAGE` );\n";
    }

    $newSchVer = 4;
    if ($currentSchVerMajor <= $newSchVer && $newSchVer <= $targetSchVer) {

        $sql[$newSchVer][]= " ALTER TABLE `pointage` CHANGE `NOTE` `NOTE` TEXT NULL DEFAULT NULL;\n";
        $sql[$newSchVer][]= " UPDATE pointage (NOTE) SET (NULL) WHERE NOTE='';\n";
    }

    $newSchVer = 5;
    if ($currentSchVerMajor <= $newSchVer && $newSchVer <= $targetSchVer) {

        $sql[$newSchVer][]= " ALTER TABLE `historique_pointage` ADD `AUTODIALER_RESULT` VARCHAR( 255 ) NULL;\n";
    }

    $newSchVer = 6;
    if($currentSchVerMajor <= $newSchVer && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= "ALTER TABLE pointage DROP INDEX DERNIER_POINTAGE_NO_CIVQ_ELECT;\n";
        $sql[$newSchVer][]= "ALTER TABLE pointage DROP INDEX NM_ELECT;\n";
        $sql[$newSchVer][]= "ALTER TABLE pointage DROP INDEX DERNIER_POINTAGE_TELEPHONE;\n";
        $sql[$newSchVer][]= "ALTER TABLE pointage ADD INDEX NOCIVIQ_DANAISN_NM_PR (DERNIER_POINTAGE_NO_CIVQ_ELECT, DERNIER_POINTAGE_DA_NAISN_ELECT, NM_ELECT, PR_ELECT);\n";
    }

    $newSchVer = 7;
    if($currentSchVerMajor <= $newSchVer && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= " ALTER TABLE pointage ADD P_ID VARCHAR( 255 ) NOT NULL AFTER id ;\n";
        $sql[$newSchVer][]= " ALTER TABLE historique_pointage ADD H_ID VARCHAR( 255 ) NOT NULL AFTER ELECT_ID ;\n";

        $sql[$newSchVer][]= "SET autocommit=0;";
        $sql[$newSchVer][]= "SET unique_checks=0;";
        $sql[$newSchVer][]= "SET foreign_key_checks=0;";
        $sql[$newSchVer][]= "LOCK TABLES pointage WRITE, liste_dge WRITE, historique_pointage WRITE, schema_info WRITE;";
        $sql[$newSchVer][]= "        UPDATE pointage, liste_dge
            SET pointage.P_ID=liste_dge.ID_DGE
            WHERE 
liste_dge.NM_ELECT = pointage.NM_ELECT 
AND liste_dge.PR_ELECT = pointage.PR_ELECT  
AND liste_dge.DA_NAISN_ELECT = pointage.DERNIER_POINTAGE_DA_NAISN_ELECT
AND liste_dge.NO_CIVQ_ELECT = pointage.DERNIER_POINTAGE_NO_CIVQ_ELECT;
"; 
        $sql[$newSchVer][]= "UPDATE pointage, historique_pointage
            SET  historique_pointage.H_ID=pointage.P_ID
            WHERE historique_pointage.ELECT_ID = pointage.id;";
        $sql[$newSchVer][]= "SET foreign_key_checks=1;";
        $sql[$newSchVer][]= "SET unique_checks=1;";
        $sql[$newSchVer][]= "COMMIT;";
        $sql[$newSchVer][]= "UNLOCK TABLES;";
        $sql[$newSchVer][]= "SET autocommit=1;";
        $sql[$newSchVer][]= "DELETE FROM pointage WHERE pointage.P_ID='';\n";
        $sql[$newSchVer][]= "DELETE FROM historique_pointage WHERE historique_pointage.H_ID='';\n";


        $sql[$newSchVer][]= "ALTER TABLE pointage DROP COLUMN DERNIER_POINTAGE_NO_CIRCN_PROVN, DROP COLUMN DERNIER_POINTAGE_NM_CIRCN_PROVN;\n";

        // Remove the auto-increment
        $sql[$newSchVer][]= "ALTER TABLE pointage CHANGE id id INT( 11 ) NOT NULL;\n";
        // Drop the foreign key on historique_pointage
        $sql[$newSchVer][]= "ALTER TABLE historique_pointage DROP FOREIGN KEY `historique_pointage_ibfk_3`;\n";
        // Drop the column
        $sql[$newSchVer][]= "ALTER TABLE historique_pointage DROP `ELECT_ID`;\n";
        // Drop the current primary key and add a new
        $sql[$newSchVer][]= "ALTER IGNORE TABLE pointage DROP PRIMARY KEY , ADD PRIMARY KEY ( `P_ID` );\n";
        // Drop the column
        $sql[$newSchVer][]= "ALTER TABLE pointage DROP id;\n";
        // Restaure the constraint
        $sql[$newSchVer][]= "ALTER TABLE historique_pointage ADD FOREIGN KEY (`H_ID`) REFERENCES pointage(`P_ID`) ON UPDATE CASCADE ON DELETE CASCADE;\n";

        $sql[$newSchVer][]= "ALTER TABLE liste_telephones ADD INDEX CODEPOSTAL_NOCIVIQ_NM_PR (CO_POSTL, NO_CIVQ_ELECT, NM_ELECT, PR_ELECT);\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_telephones DROP INDEX NM_ELECT;\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_telephones DROP INDEX NO_CIVQ_ELECT;\n";
    }

    $newSchVer = 8;
    if($currentSchVerMajor <= $newSchVer && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= "ALTER TABLE pointage ADD COLUMN VOTE VARCHAR( 25 ) DEFAULT NULL AFTER TELEPHONE_MANUEL;\n";

        $sql[$newSchVer][]= "UPDATE pointage, historique_pointage
            SET  pointage.VOTE=historique_pointage.VOTE, pointage.DATE_TIME=NOW()
WHERE historique_pointage.H_ID = pointage.P_ID
AND historique_pointage.VOTE IS NOT NULL AND DATE > SUBDATE(NOW(), INTERVAL 2 MONTH)
";
        $sql[$newSchVer][]= "ALTER TABLE historique_pointage DROP FOREIGN KEY historique_pointage_ibfk_5;\n";
        $sql[$newSchVer][]= "ALTER TABLE historique_pointage DROP COLUMN VOTE;\n";
        $sql[$newSchVer][]= "ALTER TABLE pointage ADD FOREIGN KEY (VOTE) REFERENCES vote(valeur) ON UPDATE CASCADE ON DELETE RESTRICT;\n";

    }

    $newSchVer = 9;
    if($currentSchVerMajor <= $newSchVer && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= "CREATE TABLE liste_dge_new LIKE liste_dge;";
        $sql[$newSchVer][]= "LOCK TABLES liste_dge READ, liste_dge_new WRITE, schema_info WRITE;";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new DROP INDEX CO_POSTL;\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new DROP INDEX NO_CIVQ_ELECT;\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new DROP INDEX NM_ELECT;\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new DROP INDEX NO_CIRCN_PROVN;\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new DROP INDEX NO_SECTN_VOTE;\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new ADD INDEX NOCIRCNPROVN_NOSECTNVOTE (NO_CIRCN_PROVN, NO_SECTN_VOTE);\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new DISABLE KEYS;";
        $sql[$newSchVer][]= "INSERT INTO liste_dge_new SELECT * FROM liste_dge;";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new ENABLE KEYS;";
        $sql[$newSchVer][]= "UNLOCK TABLES;";
        $sql[$newSchVer][]= "RENAME TABLE liste_dge TO liste_dge_old, liste_dge_new TO liste_dge;\n";
        $sql[$newSchVer][]= "DROP TABLE liste_dge_old;";

    }
    $newSchVer = 10;
    if($currentSchVerMajor <= $newSchVer && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= "ALTER DATABASE `pointage` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE allegeance DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE circonscriptions DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE historique_pointage DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE liste_telephones DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE log DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE pointage DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE resultat DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE schema_info DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE type_contact DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE users DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE vote DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql[$newSchVer][]= "ALTER TABLE `liste_dge` CHANGE `ID_DGE` `ID_DGE` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `NM_ELECT` `NM_ELECT` VARCHAR( 90 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `PR_ELECT` `PR_ELECT` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `CO_SEXE` `CO_SEXE` ENUM( 'M', 'F' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'M',
CHANGE `AD_ELECT` `AD_ELECT` VARCHAR( 180 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `NO_CIVQ_ELECT` `NO_CIVQ_ELECT` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
CHANGE `NM_MUNCP` `NM_MUNCP` VARCHAR( 183 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `CO_POSTL` `CO_POSTL` VARCHAR( 7 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `STATUT_ELECT` `STATUT_ELECT` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";


        $sql[$newSchVer][]= "ALTER TABLE `liste_telephones` CHANGE `NM_ELECT` `NM_ELECT` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `PR_ELECT` `PR_ELECT` VARCHAR( 45 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `AD_ELECT` `AD_ELECT` VARCHAR( 120 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `NO_CIVQ_ELECT` `NO_CIVQ_ELECT` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
CHANGE `NO_APPRT_ELECT` `NO_APPRT_ELECT` VARCHAR( 11 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `NM_MUNCP` `NM_MUNCP` VARCHAR( 105 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `CO_POSTL` `CO_POSTL` VARCHAR( 7 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `TELEPHONE` `TELEPHONE` VARCHAR( 12 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
        $sql[$newSchVer][]= "ALTER TABLE `log` CHANGE `user` `user` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `action` `action` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;";
        $sql[$newSchVer][]= "ALTER TABLE `schema_info` CHANGE `tag` `tag` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `value` `value` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
        $sql[$newSchVer][]= "ALTER TABLE `users` CHANGE `username` `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `pw` `pw` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `access` `access` ENUM( 'writer', 'report', 'permanence', 'superadmin' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT 'writer',
CHANGE `first_name` `first_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `last_name` `last_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `comment` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;";
        $sql[$newSchVer][]= "SET foreign_key_checks = 0;";

        $sql[$newSchVer][]= "ALTER TABLE `allegeance` CHANGE `valeur` `valeur` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `label` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
        $sql[$newSchVer][]= "ALTER TABLE `resultat` CHANGE `valeur` `valeur` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `label` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;";
        $sql[$newSchVer][]= "ALTER TABLE `circonscriptions` CHANGE `NM_CIRCN_PROVN` `NM_CIRCN_PROVN` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;";
        $sql[$newSchVer][]= "ALTER TABLE `type_contact` CHANGE `valeur` `valeur` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `label` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;";
        $sql[$newSchVer][]= "ALTER TABLE `vote` CHANGE `valeur` `valeur` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `label` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;";
        $sql[$newSchVer][]= "ALTER TABLE `historique_pointage` CHANGE `CONTACT` `CONTACT` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `RESULTAT` `RESULTAT` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `ALLEGEANCE` `ALLEGEANCE` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `H_ID` `H_ID` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `AUTODIALER_RESULT` `AUTODIALER_RESULT` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ;";
        $sql[$newSchVer][]= "ALTER TABLE `pointage` CHANGE `P_ID` `P_ID` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
CHANGE `DERNIER_POINTAGE_TELEPHONE` `DERNIER_POINTAGE_TELEPHONE` VARCHAR(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, 
CHANGE `NM_ELECT` `NM_ELECT` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, 
CHANGE `PR_ELECT` `PR_ELECT` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, 
CHANGE `DERNIER_POINTAGE_AD_ELECT` `DERNIER_POINTAGE_AD_ELECT` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, 
CHANGE `DERNIER_POINTAGE_NO_CIVQ_ELECT` `DERNIER_POINTAGE_NO_CIVQ_ELECT` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0', 
CHANGE `TELEPHONE_MANUEL` `TELEPHONE_MANUEL` VARCHAR(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, 
CHANGE `VOTE` `VOTE` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `NM_ELECT_O` `NM_ELECT_O` ENUM( 'fr', 'other' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `ORIGIN` `ORIGIN` ENUM( 'same', 'new', 'moved' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `CATEGORY` `CATEGORY` ENUM( 'member', 'echu' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `NOTE` `NOTE` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `PREDICTION` `PREDICTION` ENUM( 'positive', 'negative' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;";

        $sql[$newSchVer][]= "SET foreign_key_checks = 1;";

        $sql[$newSchVer][]= "ALTER TABLE liste_dge ADD INDEX COPOSTL_DANAISNELECT (CO_POSTL, DA_NAISN_ELECT);";
    }

    $newSchVer = 11;
    if($currentSchVerMajor <= $newSchVer && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= "CREATE TABLE `pointage`.`regions` (
`id_region` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`nom` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`monFQCIL_region_og_group_id` INT NULL
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = 'Représente un région au sein du parti';";
        $sql[$newSchVer][]= "ALTER TABLE `regions` ADD UNIQUE INDEX ( `monFQCIL_region_og_group_id` ) \n";
        $sql[$newSchVer][]= "ALTER TABLE `circonscriptions` ADD `region` INT NULL ,
ADD `monFQCIL_circonscription_og_group_id` INT NULL;\n";
        $sql[$newSchVer][]= "ALTER TABLE `circonscriptions` ADD INDEX ( `region` ) \n";
        $sql[$newSchVer][]= "ALTER TABLE `circonscriptions` ADD UNIQUE INDEX ( `monFQCIL_circonscription_og_group_id` ) \n";
        $sql[$newSchVer][]= "ALTER TABLE `circonscriptions` ADD FOREIGN KEY ( `region` ) REFERENCES `pointage`.`regions` (
`id_region`
) ON DELETE RESTRICT ON UPDATE CASCADE ;\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (1, 'Gaspésie-Iles-de-la-Madeleine', 44);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (2, 'Bas-Saint-Laurent', 45);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (3, 'Côte-Nord et Nord du Québec', 46);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (4, 'Saguenay-Lac St-Jean', 43);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (5, 'Capitale Nationale', 47);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (6, 'Chaudière-Appalaches', 48);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (7, 'Mauricie', 49);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (8, 'Centre-du-Québec', 50);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (9, 'Estrie', 51);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (10, 'Montérégie-Est', 52);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (11, 'Montérégie-Centre', 53);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (12, 'Montérégie-Ouest', 54);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (13, 'Laval', 55);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (14, 'Lanaudière', 56);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (15, 'Laurentides', 57);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (16, 'Abitibi-Témiscamingue', 58);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (17, 'Outaouais', 59);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (18, 'Montréal-Est', 60);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (19, 'Montréal-Centre', 61);\n";
        $sql[$newSchVer][]= "INSERT INTO pointage.regions (id_region, nom, monFQCIL_region_og_group_id) VALUES (20, 'Montréal-Ouest', 62);\n";
    }


    $newSchVer = 12;
    if ($currentSchVerMajor < $newSchVer && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= "ALTER TABLE `users` ADD `id_region` INT NULL COMMENT 'If linked to a region, a user has access to the whole region',
ADD INDEX ( id_region ) \n";
        $sql[$newSchVer][]= "ALTER TABLE `users` ADD FOREIGN KEY ( `id_region` ) REFERENCES `pointage`.`regions` (
`id_region`) ON DELETE CASCADE ON UPDATE CASCADE ;\n";
    }

    $newSchVer = 13;
    if ($currentSchVerMajor < $newSchVer && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= "ALTER TABLE liste_telephones ADD no_telephone BIGINT NOT NULL COMMENT 'Telephone number as int, also used as primary key'; \n";
        $sql[$newSchVer][]= "UPDATE liste_telephones SET no_telephone = REPLACE(TELEPHONE, '-', '');\n";
        $sql[$newSchVer][]= "ALTER TABLE `liste_telephones` CHANGE `id` `external_id` INT( 11 ) UNSIGNED NULL COMMENT 'Id provided by the list (if any)'\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_telephones DROP PRIMARY KEY;\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_telephones DROP COLUMN TELEPHONE;\n";
        $sql[$newSchVer][]= "ALTER TABLE `liste_telephones` ADD INDEX ( `no_telephone` );\n";
        $sql[$newSchVer][]= "UPDATE liste_telephones SET NM_ELECT = REPLACE(NM_ELECT, '?', 'e'), PR_ELECT = REPLACE(PR_ELECT, '?', 'e'),NM_MUNCP = REPLACE(NM_MUNCP, '?', 'e');\n";
    }
     
    $newSchVer = 14;
    if ($currentSchVerMajor < $newSchVer && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= "ALTER TABLE `users` ADD `circn_sort_order` ENUM( 'REGION_CIRCN', 'CIRCN_REGION' ) NOT NULL COMMENT 'Tri de la liste des circonscriptions'\n";
    }
     
    $newSchVer = 15;
    if ((($currentSchVerMajor < $newSchVer)||($currentSchVerMajor == $newSchVer && $currentSchVerFragment != null)) && $newSchVer <= $targetSchVer) {
        $sql[$newSchVer][]= "CREATE TABLE liste_dge_new LIKE liste_dge;\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new DROP INDEX COPOSTL_DANAISNELECT;\n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new ADD INDEX codePostl_dateNaisn_noCivq (CO_POSTL, DA_NAISN_ELECT, NO_CIVQ_ELECT) \n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new ADD INDEX dateNaisn_nom_prenom (DA_NAISN_ELECT, NM_ELECT, PR_ELECT) \n";
        $sql[$newSchVer][]= "ALTER TABLE liste_dge_new ADD INDEX codePostl_noCivq_nom_prenom (CO_POSTL, NO_CIVQ_ELECT, NM_ELECT, PR_ELECT) \n";
        $sql[$newSchVer][]= "INSERT INTO liste_dge_new SELECT * from liste_dge;\n";
        $sql[$newSchVer][]= "ANALYZE TABLE liste_dge_new;\n";
        $sql[$newSchVer][]= "DROP TABLE liste_dge;\n";
        $sql[$newSchVer][]= "RENAME TABLE liste_dge_new TO liste_dge;\n";
    }
    /*
     $newSchVer = ;
     if ($currentSchVerMajor < $newSchVer && $newSchVer <= $targetSchVer) {
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     $sql[$newSchVer][]= "\n";
     }
     */

    echo "Preparing to update.  Taget schema: $targetSchVer, Current schema: $currentSchVerMajor, Fragment: $currentSchVerFragment<br>";
    //var_dump($sql);
    foreach($sql as $majorVersion => $sqlArray) {
        $lastFragmentNo = count($sqlArray)-1;
        foreach($sqlArray as $fragmentNo => $sqlStatement) {
            $versionFloat = (float)($majorVersion.'.'.$minorVersion);
            if(($currentSchVerMajor<$majorVersion) || ($currentSchVerMajor==$majorVersion && $currentSchVerFragment!==null && $currentSchVerFragment < $fragmentNo)) {
                echo "Updating to schema $majorVersion, fragment $fragmentNo<br>";
                if($fragmentNo == $lastFragmentNo) {
                    echo "LAST FRAGMENT";
                    $lastSuccessfullSchema = "$majorVersion";
                }
                else {
                    $lastSuccessfullSchema = "$majorVersion/$fragmentNo";
                }
                $lastSuccessfullSchemaSql = "\n\nUPDATE schema_info SET value='$lastSuccessfullSchema' WHERE tag='schema_version';\n";

                pretty_print_r($sqlStatement);
                @ob_flush();
                flush();
                if (SCHEMA_UPDATE_TEST_MODE) {
                    //$retval = $db->exec("BEGIN;\n$sql\nROLLBACK;\n");

                }
                else {


                    //pretty_print_r($sql);
                    if(safeExecSql($sqlStatement)){
                        $db->exec("$lastSuccessfullSchemaSql");
                    }

                }
                @ ob_flush();
                flush();
            }
        }
    }

    return $retval;

}

/** return true on success */
function safeExecSql($sqlStatement, $verbose=true) {
    $db = DB::getDatabase('pointage');
    if($verbose) {
        pretty_print_r($sqlStatement);
        @ ob_flush();
        flush();
    }
    $retval = false;
    list($usec, $sec) = explode(' ', microtime());
    $script_start = (float) $sec + (float) $usec;
    try {
        $sth = $db->prepare("$sqlStatement");
        $sth->execute();
        $count = $sth->rowCount();
        //$sth->fetchAll();
        $sth->closeCursor();
    }
    catch (PDOException $e) {
        echo "SQL error executing query:<br>$sqlStatement<br>";
        $errorInfo = $db->errorInfo();
        var_dump($errorInfo);
        throw $e;
    }
    if($sth === FALSE) {
        echo "SQL error executing query:<br>$sqlStatement<br>";
        $errorInfo = $db->errorInfo();
        var_dump($errorInfo);
    }
    else {
        $retval = true;
    }
    list($usec, $sec) = explode(' ', microtime());
    $script_end = (float) $sec + (float) $usec;
    $elapsed_time = $script_end - $script_start;
    echo "Elapsed time = ". $elapsed_time.", $count rows affected.<br/>";
    @ ob_flush();
    flush();
    return $retval;
}
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
