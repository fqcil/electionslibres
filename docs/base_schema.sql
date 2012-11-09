-- MySQL dump 10.13  Distrib 5.1.63, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: pointage
-- ------------------------------------------------------
-- Server version	5.1.63-0ubuntu0.11.10.1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `allegeance`
--

DROP TABLE IF EXISTS `allegeance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allegeance` (
  `valeur` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`valeur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `allegeance`
--

LOCK TABLES `allegeance` WRITE;
/*!40000 ALTER TABLE `allegeance` DISABLE KEYS */;
INSERT INTO `allegeance` VALUES ('bp','Bloc pot'),('caq','Coalition avenir Québec'),('contre','Contre'),('indecis','Indécis'),('on','Option nationale'),('plq','Parti libéral du Québec'),('pmlq','Parti marxiste-léniniste du Québec '),('pn','Parti nul'),('pq','Parti québécois'),('pv','Parti vert du Québec'),('qs','Québec solidaire');
/*!40000 ALTER TABLE `allegeance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `circonscriptions`
--

DROP TABLE IF EXISTS `circonscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circonscriptions` (
  `NO_CIRCN_PROVN` int(11) NOT NULL,
  `NM_CIRCN_PROVN` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `region` int(11) DEFAULT NULL,
  PRIMARY KEY (`NO_CIRCN_PROVN`),
  KEY `region` (`region`),
  CONSTRAINT `circonscriptions_ibfk_1` FOREIGN KEY (`region`) REFERENCES `regions` (`id_region`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `circonscriptions`
--

LOCK TABLES `circonscriptions` WRITE;
/*!40000 ALTER TABLE `circonscriptions` DISABLE KEYS */;
INSERT INTO `circonscriptions` VALUES (102,'Mégantic-Compton',0,9),(108,'Saint-François',0,9),(114,'Sherbrooke',0,9),(122,'Orford',0,9),(128,'Brome-Missisquoi',0,9),(134,'Shefford',0,9),(142,'Iberville',0,10),(148,'Huntingdon',0,10),(154,'Beauharnois',0,12),(162,'Soulanges',0,12),(168,'Vaudreuil',0,12),(174,'Châteauguay',0,12),(182,'La Prairie',0,12),(188,'Saint-Jean',0,10),(194,'Chambly',0,10),(202,'La Pinière',0,12),(208,'Laporte',0,12),(214,'Marie-Victorin',0,11),(222,'Taillon',0,11),(228,'Vachon',0,11),(234,'Marguerite-D\'Youville',0,11),(242,'Borduas',0,11),(248,'Verchères',0,11),(254,'Richelieu',0,11),(262,'Saint-Hyacinthe',0,10),(268,'Johnson',0,10),(274,'Drummond',0,8),(282,'Richmond',0,8),(288,'Frontenac',0,6),(294,'Beauce-Sud',0,6),(302,'Bellechasse',0,6),(308,'Beauce-Nord',0,6),(314,'Lotbinière',0,8),(322,'Arthabaska',0,8),(328,'Nicolet-Yamaska',0,8),(334,'Trois-Rivières',0,7),(342,'Saint-Maurice',0,7),(348,'Maskinongé',0,7),(354,'Berthier',0,14),(362,'Joliette',0,14),(368,'L\'Assomption',0,14),(372,'Pointe-aux-Trembles',0,18),(374,'LaFontaine',0,18),(376,'Anjou',0,18),(378,'Bourget',0,18),(382,'Rosemont',0,18),(384,'Gouin',0,19),(386,'Mercier',0,19),(388,'Hochelaga-Maisonneuve',0,18),(392,'Sainte-Marie-Saint-Jacques',0,19),(394,'Westmount-Saint-Louis',0,20),(396,'Saint-Henri-Sainte-Anne',0,19),(398,'Verdun',0,20),(402,'Marguerite-Bourgeoys',0,20),(404,'Notre-Dame-de-Grâce',0,20),(406,'D\'Arcy-McGee',0,20),(408,'Marquette',0,20),(412,'Jacques-Cartier',0,20),(414,'Nelligan',0,20),(416,'Robert-Baldwin',0,20),(418,'Saint-Laurent',0,20),(422,'Mont-Royal',0,19),(424,'Outremont',0,19),(426,'Laurier-Dorion',0,19),(428,'Viau',0,18),(432,'Jeanne-Mance-Viger',0,18),(434,'Bourassa-Sauvé',0,18),(436,'Crémazie',0,19),(438,'Acadie',0,19),(442,'Laval-des-Rapides',0,13),(444,'Chomedey',0,13),(446,'Fabre',0,13),(448,'Vimont',0,13),(452,'Mille-Îles',0,13),(458,'Terrebonne',0,14),(464,'Masson',0,14),(472,'Blainville',0,15),(478,'Groulx',0,15),(484,'Deux-Montagnes',0,15),(492,'Mirabel',0,15),(498,'Prévost',0,15),(504,'Rousseau',0,14),(512,'Bertrand',0,14),(518,'Argenteuil',0,15),(524,'Labelle',0,15),(532,'Papineau',0,17),(538,'Gatineau',0,17),(544,'Chapleau',0,17),(552,'Hull',0,17),(558,'Pontiac',0,17),(564,'Rouyn-Noranda-Témiscamingue',0,16),(572,'Abitibi-Ouest',0,16),(578,'Abitibi-Est',0,16),(584,'Laviolette',0,7),(592,'Champlain',0,7),(598,'Portneuf',0,7),(604,'La Peltrie',0,5),(612,'Chauveau',0,5),(618,'Charlesbourg',0,5),(624,'Jean-Lesage',0,5),(632,'Taschereau',0,5),(638,'Vanier',0,5),(644,'Jean-Talon',0,5),(652,'Louis-Hébert',0,5),(658,'Chutes-de-la-Chaudière',0,6),(664,'Lévis',0,6),(672,'Montmorency',0,5),(678,'Charlevoix',0,5),(684,'Montmagny-L\'Islet',0,6),(692,'Kamouraska-Témiscouata',0,2),(698,'Rivière-du-Loup',0,2),(704,'Rimouski',0,2),(712,'Matapédia',0,2),(718,'Bonaventure',0,1),(724,'Matane',0,1),(732,'Gaspé',0,1),(738,'Îles-de-la-Madeleine',0,1),(744,'Duplessis',0,3),(752,'René-Lévesque',0,3),(758,'Dubuc',0,4),(764,'Chicoutimi',0,4),(772,'Jonquière',0,4),(778,'Lac-Saint-Jean',0,4),(784,'Roberval',0,4),(792,'Ungava',0,3);
/*!40000 ALTER TABLE `circonscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historique_pointage`
--

DROP TABLE IF EXISTS `historique_pointage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historique_pointage` (
  `HIST_ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CONTACT` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `RESULTAT` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `ALLEGEANCE` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `H_ID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `IS_DERNIER_POINTAGE` tinyint(1) NOT NULL DEFAULT '0',
  `AUTODIALER_RESULT` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`HIST_ID`),
  KEY `CONTACT` (`CONTACT`),
  KEY `RESULTAT` (`RESULTAT`),
  KEY `ALLEGEANCE` (`ALLEGEANCE`),
  KEY `IS_DERNIER_POINTAGE` (`IS_DERNIER_POINTAGE`),
  KEY `H_ID` (`H_ID`),
  CONSTRAINT `historique_pointage_ibfk_1` FOREIGN KEY (`CONTACT`) REFERENCES `type_contact` (`valeur`) ON UPDATE CASCADE,
  CONSTRAINT `historique_pointage_ibfk_2` FOREIGN KEY (`RESULTAT`) REFERENCES `resultat` (`valeur`) ON UPDATE CASCADE,
  CONSTRAINT `historique_pointage_ibfk_4` FOREIGN KEY (`ALLEGEANCE`) REFERENCES `allegeance` (`valeur`) ON UPDATE CASCADE,
  CONSTRAINT `historique_pointage_ibfk_6` FOREIGN KEY (`H_ID`) REFERENCES `pointage` (`P_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historique_pointage`
--

LOCK TABLES `historique_pointage` WRITE;
/*!40000 ALTER TABLE `historique_pointage` DISABLE KEYS */;
INSERT INTO `historique_pointage` VALUES (1,'2012-11-09 21:53:35','called','success','qs','102_1.00_1.00_1',0,NULL),(2,'2012-11-09 21:53:53','called','success','pn','102_1.00_1.00_1',1,NULL);
/*!40000 ALTER TABLE `historique_pointage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `liste_dge`
--

DROP TABLE IF EXISTS `liste_dge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `liste_dge` (
  `ID_DGE` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `NO_CIRCN_PROVN` int(11) NOT NULL,
  `NO_SECTR_ELECT` int(11) NOT NULL DEFAULT '0',
  `NO_SECTN_VOTE` int(11) NOT NULL DEFAULT '0',
  `NM_ELECT` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `PR_ELECT` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `DA_NAISN_ELECT` date NOT NULL DEFAULT '0000-00-00',
  `CO_SEXE` enum('M','F') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'M',
  `AD_ELECT` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `NO_CIVQ_ELECT` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `NO_APPRT_ELECT` int(11) NOT NULL DEFAULT '0',
  `NM_MUNCP` varchar(183) COLLATE utf8_unicode_ci NOT NULL,
  `CO_POSTL` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `NO_ELECT` int(11) NOT NULL DEFAULT '0',
  `STATUT_ELECT` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `DATE_TIME` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_DGE`),
  KEY `NOCIRCNPROVN_NOSECTNVOTE` (`NO_CIRCN_PROVN`,`NO_SECTN_VOTE`),
  KEY `NO_CIRCN_PROVN` (`NO_CIRCN_PROVN`),
  KEY `codePostl_dateNaisn_noCivq` (`CO_POSTL`,`DA_NAISN_ELECT`,`NO_CIVQ_ELECT`),
  KEY `dateNaisn_nom_prenom` (`DA_NAISN_ELECT`,`NM_ELECT`,`PR_ELECT`),
  KEY `codePostl_noCivq_nom_prenom` (`CO_POSTL`,`NO_CIVQ_ELECT`,`NM_ELECT`,`PR_ELECT`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `liste_dge`
--

LOCK TABLES `liste_dge` WRITE;
/*!40000 ALTER TABLE `liste_dge` DISABLE KEYS */;
INSERT INTO `liste_dge` VALUES ('102_1.00_1.00_1',102,1,1,'Test','User','2012-11-09','M','13, rue kinexistepas','0024',0,'Montreal','H2P2Y5',1,'','2012-11-09 19:43:58');
/*!40000 ALTER TABLE `liste_dge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `liste_telephones`
--

DROP TABLE IF EXISTS `liste_telephones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `liste_telephones` (
  `external_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Id provided by the list (if any)',
  `NM_ELECT` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `PR_ELECT` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `AD_ELECT` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `NO_CIVQ_ELECT` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `NO_APPRT_ELECT` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NM_MUNCP` varchar(105) COLLATE utf8_unicode_ci NOT NULL,
  `CO_POSTL` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `DATE_TIME` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `no_telephone` bigint(20) NOT NULL COMMENT 'Telephone number as int, also used as primary key',
  KEY `CO_POSTL` (`CO_POSTL`),
  KEY `CODEPOSTAL_NOCIVIQ_NM_PR` (`CO_POSTL`,`NO_CIVQ_ELECT`,`NM_ELECT`,`PR_ELECT`),
  KEY `no_telephone` (`no_telephone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `liste_telephones`
--

LOCK TABLES `liste_telephones` WRITE;
/*!40000 ALTER TABLE `liste_telephones` DISABLE KEYS */;
/*!40000 ALTER TABLE `liste_telephones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
INSERT INTO `log` VALUES (1,'emmanuelm','SUCESS LOGIN','2012-11-09 19:45:44',2130706433),(2,'emmanuelm','SUCESS LOGIN','2012-11-09 20:00:35',2130706433),(3,'emmanuelm','SUCESS LOGIN','2012-11-09 20:00:38',2130706433),(4,'emmanuelm','SUCESS LOGIN','2012-11-09 20:03:33',2130706433),(5,'emmanuelm','SUCESS LOGIN','2012-11-09 20:09:52',2130706433),(6,'admin','SUCESS LOGIN','2012-11-09 20:10:32',2130706433),(7,'admin','SUCESS LOGIN','2012-11-09 20:10:58',2130706433),(8,'admin','SUCESS LOGIN','2012-11-09 20:13:17',2130706433),(9,'admin','SUCESS LOGIN','2012-11-09 20:29:00',2130706433),(10,'admin','SUCESS LOGIN','2012-11-09 20:29:22',2130706433),(11,'admin','SUCESS LOGIN','2012-11-09 20:52:20',2130706433),(12,'admin','SUCESS LOGIN','2012-11-09 21:05:46',2130706433),(13,'admin','SUCESS LOGIN','2012-11-09 21:14:36',2130706433),(14,'admin','SUCESS LOGIN','2012-11-09 21:15:11',2130706433),(15,'admin','SUCESS LOGIN','2012-11-09 21:15:52',2130706433),(16,'admin','SUCESS LOGIN','2012-11-09 21:20:10',2130706433),(17,'admin','SUCESS LOGIN','2012-11-09 21:24:06',2130706433),(18,'admin','SUCESS LOGIN','2012-11-09 21:24:18',2130706433),(19,'admin','SUCESS LOGIN','2012-11-09 21:26:34',2130706433),(20,'admin','SUCESS LOGIN','2012-11-09 21:30:38',2130706433),(21,'admin','SUCESS LOGIN','2012-11-09 21:42:46',2130706433),(22,'test2','SUCESS LOGIN','2012-11-09 21:43:35',2130706433),(23,'admin','SUCESS LOGIN','2012-11-09 22:01:42',2130706433),(24,'admin','SUCESS LOGIN','2012-11-09 22:15:29',2130706433),(25,'admin','SUCESS LOGIN','2012-11-09 22:17:48',2130706433);
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pointage`
--

DROP TABLE IF EXISTS `pointage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pointage` (
  `P_ID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `DERNIER_POINTAGE_TELEPHONE` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `NM_ELECT` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PR_ELECT` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `DERNIER_POINTAGE_DA_NAISN_ELECT` date NOT NULL DEFAULT '0000-00-00',
  `DERNIER_POINTAGE_AD_ELECT` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `DERNIER_POINTAGE_NO_CIVQ_ELECT` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `DERNIER_POINTAGE_NO_APPRT_ELECT` int(11) NOT NULL DEFAULT '0',
  `TELEPHONE_MANUEL` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VOTE` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NM_ELECT_O` enum('fr','other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ORIGIN` enum('same','new','moved') COLLATE utf8_unicode_ci DEFAULT NULL,
  `CATEGORY` enum('member','echu') COLLATE utf8_unicode_ci DEFAULT NULL,
  `NOTE` text COLLATE utf8_unicode_ci,
  `PREDICTION` enum('positive','negative') COLLATE utf8_unicode_ci DEFAULT NULL,
  `DATE_TIME` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`P_ID`),
  KEY `NOCIVIQ_DANAISN_NM_PR` (`DERNIER_POINTAGE_NO_CIVQ_ELECT`,`DERNIER_POINTAGE_DA_NAISN_ELECT`,`NM_ELECT`,`PR_ELECT`),
  KEY `VOTE` (`VOTE`),
  CONSTRAINT `pointage_ibfk_1` FOREIGN KEY (`VOTE`) REFERENCES `vote` (`valeur`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pointage`
--

LOCK TABLES `pointage` WRITE;
/*!40000 ALTER TABLE `pointage` DISABLE KEYS */;
INSERT INTO `pointage` VALUES ('102_1.00_1.00_1','','Test','User','2012-11-09','13, rue kinexistepas','0024',0,'5145551212','bva',NULL,NULL,NULL,'Ajout d\'un commentaire ici',NULL,'2012-11-09 21:53:35');
/*!40000 ALTER TABLE `pointage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regions` (
  `id_region` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_region`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Représente un région au sein du parti';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES (1,'Gaspésie-Iles-de-la-Madeleine'),(2,'Bas-Saint-Laurent'),(3,'Côte-Nord et Nord du Québec'),(4,'Saguenay-Lac St-Jean'),(5,'Capitale Nationale'),(6,'Chaudière-Appalaches'),(7,'Mauricie'),(8,'Centre-du-Québec'),(9,'Estrie'),(10,'Montérégie-Est'),(11,'Montérégie-Centre'),(12,'Montérégie-Ouest'),(13,'Laval'),(14,'Lanaudière'),(15,'Laurentides'),(16,'Abitibi-Témiscamingue'),(17,'Outaouais'),(18,'Montréal-Est'),(19,'Montréal-Centre'),(20,'Montréal-Ouest');
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resultat`
--

DROP TABLE IF EXISTS `resultat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resultat` (
  `valeur` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`valeur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resultat`
--

LOCK TABLES `resultat` WRITE;
/*!40000 ALTER TABLE `resultat` DISABLE KEYS */;
INSERT INTO `resultat` VALUES ('noanswer','Ne répond pas'),('success','Succès'),('wrongnumber','Mauvais numéro');
/*!40000 ALTER TABLE `resultat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schema_info`
--

DROP TABLE IF EXISTS `schema_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schema_info` (
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schema_info`
--

LOCK TABLES `schema_info` WRITE;
/*!40000 ALTER TABLE `schema_info` DISABLE KEYS */;
INSERT INTO `schema_info` VALUES ('schema_version','15');
/*!40000 ALTER TABLE `schema_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `type_contact`
--

DROP TABLE IF EXISTS `type_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_contact` (
  `valeur` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`valeur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `type_contact`
--

LOCK TABLES `type_contact` WRITE;
/*!40000 ALTER TABLE `type_contact` DISABLE KEYS */;
INSERT INTO `type_contact` VALUES ('autodialed','Auto dialed'),('called','Téléphone'),('coindetable','Coin de table'),('visited','Porte-à-porte');
/*!40000 ALTER TABLE `type_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pw` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `access` enum('writer','report','permanence','superadmin') COLLATE utf8_unicode_ci DEFAULT 'writer',
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `NO_CIRCN_PROVN` int(11) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `id_region` int(11) DEFAULT NULL COMMENT 'If linked to a region, a user has access to the whole region',
  `circn_sort_order` enum('REGION_CIRCN','CIRCN_REGION') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Tri de la liste des circonscriptions',
  PRIMARY KEY (`username`),
  KEY `NO_CIRCN_PROVN` (`NO_CIRCN_PROVN`),
  KEY `id_region` (`id_region`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`NO_CIRCN_PROVN`) REFERENCES `circonscriptions` (`NO_CIRCN_PROVN`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`id_region`) REFERENCES `regions` (`id_region`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('admin','$a275$jsiU+wTbuDqR8g+rLroVPEmeSBA=','superadmin','','',458,'',NULL,'REGION_CIRCN');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote`
--

DROP TABLE IF EXISTS `vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote` (
  `valeur` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`valeur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote`
--

LOCK TABLES `vote` WRITE;
/*!40000 ALTER TABLE `vote` DISABLE KEYS */;
INSERT INTO `vote` VALUES ('bva','A voté par anticipation'),('nvp','Ne vote pas'),('voted','A voté');
/*!40000 ALTER TABLE `vote` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-11-09 17:45:40
