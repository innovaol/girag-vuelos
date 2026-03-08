/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.5.26-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: innovaol_vuelos
-- ------------------------------------------------------
-- Server version	10.5.26-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auth_group`
--

DROP TABLE IF EXISTS `auth_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_group`
--

LOCK TABLES `auth_group` WRITE;
/*!40000 ALTER TABLE `auth_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_group_permissions`
--

DROP TABLE IF EXISTS `auth_group_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_group_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_group_permissions_group_id_permission_id_0cd325b0_uniq` (`group_id`,`permission_id`),
  KEY `auth_group_permissio_permission_id_84c5c92e_fk_auth_perm` (`permission_id`),
  CONSTRAINT `auth_group_permissio_permission_id_84c5c92e_fk_auth_perm` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
  CONSTRAINT `auth_group_permissions_group_id_b120cbf9_fk_auth_group_id` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_group_permissions`
--

LOCK TABLES `auth_group_permissions` WRITE;
/*!40000 ALTER TABLE `auth_group_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_group_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_permission`
--

DROP TABLE IF EXISTS `auth_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `content_type_id` int(11) NOT NULL,
  `codename` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_permission_content_type_id_codename_01ab375a_uniq` (`content_type_id`,`codename`),
  CONSTRAINT `auth_permission_content_type_id_2f476e4b_fk_django_co` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_permission`
--

LOCK TABLES `auth_permission` WRITE;
/*!40000 ALTER TABLE `auth_permission` DISABLE KEYS */;
INSERT INTO `auth_permission` VALUES (21,'Permiso especial Aprobar vuelo',14,'approve_flight'),(22,'Permiso especial Marcar vuelo como facturado',14,'mark_as_billed'),(23,'Permiso especial admin de vuelos',14,'admin_vuelos'),(24,'Ver detalle de auditoría',16,'view_audit_detail'),(25,'Acceso al Dashboard',6,'access_dashboard'),(26,'Crear vuelo',14,'create_flight'),(27,'Editar vuelo',14,'edit_flight'),(28,'Ver vuelo',14,'view_flight'),(29,'Eliminar vuelo',14,'delete_flight'),(30,'Ver notificaciones',14,'view_notifications'),(31,'Crear aerolínea',8,'create_airline'),(32,'Editar aerolínea',8,'edit_airline'),(33,'Ver aerolínea',8,'view_airline'),(34,'Eliminar aerolínea',8,'delete_airline'),(35,'Restaurar aerolínea',8,'restore_airline'),(36,'Crear aeronave',7,'create_aircraft'),(37,'Editar aeronave',7,'edit_aircraft'),(38,'Ver aeronave',7,'view_aircraft'),(39,'Eliminar aeronave',7,'delete_aircraft'),(40,'Restaurar aeronave',7,'restore_aircraft'),(41,'Cambiar contraseña de usuario',6,'change_password'),(42,'Crear usuario',6,'create_user'),(43,'Editar usuario',6,'edit_user'),(44,'Ver usuario',6,'view_user'),(45,'Eliminar usuario',6,'delete_user'),(46,'Restaurar usuario',6,'restore_user'),(47,'Crear grupo',9,'create_group'),(48,'Editar grupo',9,'edit_group'),(49,'Ver grupo',9,'view_group'),(50,'Eliminar grupo',9,'delete_group'),(51,'Restaurar grupo',9,'restore_group'),(52,'Crear tipo de documento',10,'create_document_type'),(53,'Editar tipo de documento',10,'edit_document_type'),(54,'Ver tipo de documento',10,'view_documenttype'),(55,'Eliminar tipo de documento',10,'delete_document_type'),(56,'Restaurar tipo de documento',10,'restore_document_type'),(57,'Ver auditoría',16,'view_audit'),(58,'Acceder a configuración',6,'access_settings');
/*!40000 ALTER TABLE `auth_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `django_admin_log`
--

DROP TABLE IF EXISTS `django_admin_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_time` datetime(6) NOT NULL,
  `object_id` longtext DEFAULT NULL,
  `object_repr` varchar(200) NOT NULL,
  `action_flag` smallint(5) unsigned NOT NULL CHECK (`action_flag` >= 0),
  `change_message` longtext NOT NULL,
  `content_type_id` int(11) DEFAULT NULL,
  `user_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `django_admin_log_content_type_id_c4bce8eb_fk_django_co` (`content_type_id`),
  KEY `django_admin_log_user_id_c564eba6_fk_main_customuser_id` (`user_id`),
  CONSTRAINT `django_admin_log_content_type_id_c4bce8eb_fk_django_co` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`),
  CONSTRAINT `django_admin_log_user_id_c564eba6_fk_main_customuser_id` FOREIGN KEY (`user_id`) REFERENCES `main_customuser` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `django_admin_log`
--

LOCK TABLES `django_admin_log` WRITE;
/*!40000 ALTER TABLE `django_admin_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `django_admin_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `django_content_type`
--

DROP TABLE IF EXISTS `django_content_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_content_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_label` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `django_content_type_app_label_model_76bd3d3b_uniq` (`app_label`,`model`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `django_content_type`
--

LOCK TABLES `django_content_type` WRITE;
/*!40000 ALTER TABLE `django_content_type` DISABLE KEYS */;
INSERT INTO `django_content_type` VALUES (1,'admin','logentry'),(3,'auth','group'),(2,'auth','permission'),(4,'contenttypes','contenttype'),(7,'main','aircraft'),(8,'main','airline'),(16,'main','auditlog'),(9,'main','customgroup'),(6,'main','customuser'),(24,'main','document'),(10,'main','documenttype'),(14,'main','flight'),(22,'main','groupextension'),(21,'main','navigationsection'),(23,'main','section'),(5,'sessions','session');
/*!40000 ALTER TABLE `django_content_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `django_migrations`
--

DROP TABLE IF EXISTS `django_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `applied` datetime(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `django_migrations`
--

LOCK TABLES `django_migrations` WRITE;
/*!40000 ALTER TABLE `django_migrations` DISABLE KEYS */;
INSERT INTO `django_migrations` VALUES (1,'contenttypes','0001_initial','2025-04-16 16:29:38.053981'),(2,'contenttypes','0002_remove_content_type_name','2025-04-16 16:29:38.078259'),(3,'auth','0001_initial','2025-04-16 16:35:29.805602'),(4,'auth','0002_alter_permission_name_max_length','2025-04-16 16:35:29.855553'),(5,'auth','0003_alter_user_email_max_length','2025-04-16 16:35:29.864276'),(6,'auth','0004_alter_user_username_opts','2025-04-16 16:35:29.872106'),(7,'auth','0005_alter_user_last_login_null','2025-04-16 16:35:29.879415'),(8,'auth','0006_require_contenttypes_0002','2025-04-16 16:35:29.881266'),(9,'auth','0007_alter_validators_add_error_messages','2025-04-16 16:35:29.887133'),(10,'auth','0008_alter_user_username_max_length','2025-04-16 16:35:29.892722'),(11,'auth','0009_alter_user_last_name_max_length','2025-04-16 16:35:29.898341'),(12,'auth','0010_alter_group_name_max_length','2025-04-16 16:35:29.907072'),(13,'auth','0011_update_proxy_permissions','2025-04-16 16:35:29.913575'),(14,'auth','0012_alter_user_first_name_max_length','2025-04-16 16:35:29.921034'),(15,'main','0001_initial','2025-04-16 16:35:30.924364'),(16,'admin','0001_initial','2025-04-16 16:35:31.032800'),(17,'admin','0002_logentry_remove_auto_add','2025-04-16 16:35:31.050742'),(18,'admin','0003_logentry_add_action_flag_choices','2025-04-16 16:35:31.069891'),(19,'sessions','0001_initial','2025-04-16 16:35:31.094282');
/*!40000 ALTER TABLE `django_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `django_session`
--

DROP TABLE IF EXISTS `django_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_session` (
  `session_key` varchar(40) NOT NULL,
  `session_data` longtext NOT NULL,
  `expire_date` datetime(6) NOT NULL,
  PRIMARY KEY (`session_key`),
  KEY `django_session_expire_date_a5c62663` (`expire_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `django_session`
--

LOCK TABLES `django_session` WRITE;
/*!40000 ALTER TABLE `django_session` DISABLE KEYS */;
INSERT INTO `django_session` VALUES ('2rxd69pg9mon38c4xx6e8bi5wb5vv35c','.eJxVjEEOwiAQRe_C2hCgM6S4dO8ZCDODUjWQlHbVeHdt0oVu_3vvbyqmdSlx7XmOk6izsur0u1HiZ647kEeq96a51WWeSO-KPmjX1yb5dTncv4OSevnWhBIcy3hjB2KYJBsEcJ5xQI9WSGgw1mBOfgQbDDm2g5APCbKBwOr9AfBgN-U:1u5uaA:Kx4yHupJRpAZntAC3zjbkUxmlTSsBsRo2f77gNbYpA8','2025-05-02 22:53:14.340801'),('7rpumx3m2vaeufv1x652uryiodgxuksk','.eJxVjEEOwiAQRe_C2hCgM6S4dO8ZCDODUjWQlHbVeHdt0oVu_3vvbyqmdSlx7XmOk6izsur0u1HiZ647kEeq96a51WWeSO-KPmjX1yb5dTncv4OSevnWhBIcy3hjB2KYJBsEcJ5xQI9WSGgw1mBOfgQbDDm2g5APCbKBwOr9AfBgN-U:1u5ART:UCqOxeqLGJqs1nJw3S7k6T6rpyf_H-hmB5A-kotVp8E','2025-04-30 21:37:11.658674'),('b3selmz8dhd8qzqetge96v8qvbv8ja4g','.eJxVjEEOwiAQRe_C2hCgM6S4dO8ZCDODUjWQlHbVeHdt0oVu_3vvbyqmdSlx7XmOk6izsur0u1HiZ647kEeq96a51WWeSO-KPmjX1yb5dTncv4OSevnWhBIcy3hjB2KYJBsEcJ5xQI9WSGgw1mBOfgQbDDm2g5APCbKBwOr9AfBgN-U:1u7Jdw:av4fJD8tRC2iTxPL-5KQ7OTGuLCU_SqFz8kGQLBxsK0','2025-05-06 19:50:56.329625'),('h92wclyr99pk3vtdc243o9scgsam4bv9','.eJxVjMsOwiAUBf-FtSFQuLR16d5vIPeBUjWQlHZl_HfbpAvdnpk5bxVxXXJcW5rjJOqsOnX63Qj5mcoO5IHlXjXXsswT6V3RB236WiW9Lof7d5Cx5a32PYdkPUrgwY0sfnTGmCTYWQgo5BCEyRA4B26TEayRG_SDIe5gJPX5AuaEN-U:1u57lX:ZxBORkPjlku2oNWucshGBDGc_-6-hqUv7FUPKzHYul0','2025-04-30 18:45:43.974751'),('k4m1oigo0em6ieriqif8rxgjuoraigzt','.eJxVjMsOwiAUBf-FtSFQuLR16d5vIPeBUjWQlHZl_HfbpAvdnpk5bxVxXXJcW5rjJOqsOnX63Qj5mcoO5IHlXjXXsswT6V3RB236WiW9Lof7d5Cx5a32PYdkPUrgwY0sfnTGmCTYWQgo5BCEyRA4B26TEayRG_SDIe5gJPX5AuaEN-U:1u58yz:96bhCQAPh3RHuT1kff7VYxy9qTXEYtDN8QbBSpNRkBE','2025-04-30 20:03:41.535399'),('ovu3ppw8offhd6qd6zvjcgq5pt1agv5y','.eJxVjEEOwiAQRe_C2hCgM6S4dO8ZCDODUjWQlHbVeHdt0oVu_3vvbyqmdSlx7XmOk6izsur0u1HiZ647kEeq96a51WWeSO-KPmjX1yb5dTncv4OSevnWhBIcy3hjB2KYJBsEcJ5xQI9WSGgw1mBOfgQbDDm2g5APCbKBwOr9AfBgN-U:1u55kn:cd3yjzBgJ3su2lzxDRSOBQErTHcfl0kCXzfFROC8lIM','2025-04-30 16:36:49.868754');
/*!40000 ALTER TABLE `django_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_aircraft`
--

DROP TABLE IF EXISTS `main_aircraft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_aircraft` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `is_archived` tinyint(1) NOT NULL,
  `aeronave` varchar(50) NOT NULL,
  `aerolinea_id` bigint(20) NOT NULL,
  `parent_aircraft_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `aeronave` (`aeronave`),
  KEY `main_aircraft_aerolinea_id_76fcc017_fk_main_airline_id` (`aerolinea_id`),
  KEY `main_aircraft_parent_aircraft_id_1ea9d276_fk_main_aircraft_id` (`parent_aircraft_id`),
  CONSTRAINT `main_aircraft_aerolinea_id_76fcc017_fk_main_airline_id` FOREIGN KEY (`aerolinea_id`) REFERENCES `main_airline` (`id`),
  CONSTRAINT `main_aircraft_parent_aircraft_id_1ea9d276_fk_main_aircraft_id` FOREIGN KEY (`parent_aircraft_id`) REFERENCES `main_aircraft` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_aircraft`
--

LOCK TABLES `main_aircraft` WRITE;
/*!40000 ALTER TABLE `main_aircraft` DISABLE KEYS */;
INSERT INTO `main_aircraft` VALUES (1,0,'AHH-5222',1,NULL),(2,0,'PYF-6549',2,NULL),(3,0,'ADG-3345',3,NULL),(4,0,'JKP-889',4,NULL),(5,0,'YYZ-675',5,NULL),(6,0,'QWE-R-0556',6,NULL);
/*!40000 ALTER TABLE `main_aircraft` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_airline`
--

DROP TABLE IF EXISTS `main_airline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_airline` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_archived` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_airline`
--

LOCK TABLES `main_airline` WRITE;
/*!40000 ALTER TABLE `main_airline` DISABLE KEYS */;
INSERT INTO `main_airline` VALUES (1,'Qatar Airways',0),(2,'Avianca',0),(3,'LAN Cargo',0),(4,'Latam',0),(5,'Cayman Airways',0),(6,'Iberia',0);
/*!40000 ALTER TABLE `main_airline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_auditlog`
--

DROP TABLE IF EXISTS `main_auditlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_auditlog` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `details` longtext DEFAULT NULL,
  `timestamp` datetime(6) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `main_auditlog_user_id_8c6c79db_fk_main_customuser_id` (`user_id`),
  CONSTRAINT `main_auditlog_user_id_8c6c79db_fk_main_customuser_id` FOREIGN KEY (`user_id`) REFERENCES `main_customuser` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_auditlog`
--

LOCK TABLES `main_auditlog` WRITE;
/*!40000 ALTER TABLE `main_auditlog` DISABLE KEYS */;
INSERT INTO `main_auditlog` VALUES (1,'Creó un vuelo','Vuelo ASD765432 creado.','2025-04-16 20:01:00.219057',1);
/*!40000 ALTER TABLE `main_auditlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_customgroup`
--

DROP TABLE IF EXISTS `main_customgroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_customgroup` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `is_archived` tinyint(1) NOT NULL,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_customgroup`
--

LOCK TABLES `main_customgroup` WRITE;
/*!40000 ALTER TABLE `main_customgroup` DISABLE KEYS */;
INSERT INTO `main_customgroup` VALUES (1,0,'Todos'),(2,0,'Sin restauración de registros'),(3,0,'Todos Ver'),(4,0,'Todos Editar'),(5,0,'Todos Eliminar'),(6,0,'Todos Crear'),(7,0,'Todos Restaurar');
/*!40000 ALTER TABLE `main_customgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_customgroup_permissions`
--

DROP TABLE IF EXISTS `main_customgroup_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_customgroup_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customgroup_id` bigint(20) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_customgroup_permiss_customgroup_id_permissio_15383a95_uniq` (`customgroup_id`,`permission_id`),
  KEY `main_customgroup_per_permission_id_985b188e_fk_auth_perm` (`permission_id`),
  CONSTRAINT `main_customgroup_per_customgroup_id_0c69c996_fk_main_cust` FOREIGN KEY (`customgroup_id`) REFERENCES `main_customgroup` (`id`),
  CONSTRAINT `main_customgroup_per_permission_id_985b188e_fk_auth_perm` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_customgroup_permissions`
--

LOCK TABLES `main_customgroup_permissions` WRITE;
/*!40000 ALTER TABLE `main_customgroup_permissions` DISABLE KEYS */;
INSERT INTO `main_customgroup_permissions` VALUES (1,1,24),(2,1,25),(3,1,26),(4,1,27),(5,1,28),(6,1,29),(7,1,30),(8,1,31),(9,1,32),(10,1,33),(11,1,34),(12,1,35),(13,1,36),(14,1,37),(15,1,38),(16,1,39),(17,1,40),(18,1,41),(19,1,42),(20,1,43),(21,1,44),(22,1,45),(23,1,46),(24,1,47),(25,1,48),(26,1,49),(27,1,50),(28,1,51),(29,1,52),(30,1,53),(31,1,54),(32,1,55),(33,1,56),(34,1,57),(35,1,58),(36,2,24),(37,2,25),(38,2,26),(39,2,27),(40,2,28),(41,2,29),(42,2,30),(43,2,31),(44,2,32),(45,2,33),(46,2,34),(47,2,36),(48,2,37),(49,2,38),(50,2,39),(51,2,41),(52,2,42),(53,2,43),(54,2,44),(55,2,45),(56,2,47),(57,2,48),(58,2,49),(59,2,50),(60,2,52),(61,2,53),(62,2,54),(63,2,55),(64,2,57),(65,2,58),(71,3,25),(72,3,28),(73,3,30),(66,3,33),(67,3,38),(68,3,44),(69,3,49),(70,3,54),(74,3,57),(85,4,24),(121,4,25),(87,4,27),(88,4,28),(89,4,30),(75,4,32),(76,4,33),(77,4,37),(78,4,38),(79,4,43),(80,4,44),(81,4,48),(82,4,49),(83,4,53),(84,4,54),(86,4,57),(100,5,24),(122,5,25),(102,5,27),(103,5,28),(104,5,30),(90,5,32),(91,5,33),(92,5,37),(93,5,38),(94,5,43),(95,5,44),(96,5,48),(97,5,49),(98,5,53),(99,5,54),(101,5,57),(115,6,24),(119,6,25),(117,6,26),(118,6,28),(114,6,30),(120,6,31),(105,6,33),(106,6,36),(107,6,38),(108,6,42),(109,6,44),(110,6,47),(111,6,49),(112,6,52),(113,6,54),(116,6,57),(133,7,24),(137,7,25),(136,7,28),(132,7,30),(123,7,33),(124,7,35),(125,7,38),(126,7,40),(127,7,44),(128,7,46),(129,7,49),(130,7,51),(131,7,54),(135,7,56),(134,7,57);
/*!40000 ALTER TABLE `main_customgroup_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_customuser`
--

DROP TABLE IF EXISTS `main_customuser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_customuser` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `password` varchar(128) NOT NULL,
  `last_login` datetime(6) DEFAULT NULL,
  `is_superuser` tinyint(1) NOT NULL,
  `username` varchar(150) NOT NULL,
  `first_name` varchar(150) NOT NULL,
  `last_name` varchar(150) NOT NULL,
  `email` varchar(254) NOT NULL,
  `is_staff` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `date_joined` datetime(6) NOT NULL,
  `is_archived` tinyint(1) NOT NULL,
  `is_flight_supervisor` tinyint(1) NOT NULL,
  `is_billing_supervisor` tinyint(1) NOT NULL,
  `is_admin_vuelos` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_customuser`
--

LOCK TABLES `main_customuser` WRITE;
/*!40000 ALTER TABLE `main_customuser` DISABLE KEYS */;
INSERT INTO `main_customuser` VALUES (1,'pbkdf2_sha256$600000$Wapx1YXhVXW1zavNRJKmQ5$P7UeA/ablokjtajAcv7i30qSZTUtyLucLycYS/c3ZVU=','2025-04-22 19:50:56.327154',1,'rodrigo.ferrer','Rodrigo','Ferrer','rodrigo.ferrer@girag.aero',1,1,'2025-04-16 16:36:44.429098',0,0,0,0),(2,'pbkdf2_sha256$600000$22MnjJCR664O7WExCOqMWi$ApI55WaBbL2VRF84v3uNzHyL27hlSaFGBwgtBYDjPZA=','2025-04-16 20:03:41.533226',0,'Prueba','Rodrigo','Ferrer','rodrigo@innovaol.com',0,1,'2025-04-16 18:43:40.362040',0,0,0,0);
/*!40000 ALTER TABLE `main_customuser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_customuser_groups`
--

DROP TABLE IF EXISTS `main_customuser_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_customuser_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customuser_id` bigint(20) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_customuser_groups_customuser_id_group_id_8a5023dd_uniq` (`customuser_id`,`group_id`),
  KEY `main_customuser_groups_group_id_8149f607_fk_auth_group_id` (`group_id`),
  CONSTRAINT `main_customuser_grou_customuser_id_13869e25_fk_main_cust` FOREIGN KEY (`customuser_id`) REFERENCES `main_customuser` (`id`),
  CONSTRAINT `main_customuser_groups_group_id_8149f607_fk_auth_group_id` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_customuser_groups`
--

LOCK TABLES `main_customuser_groups` WRITE;
/*!40000 ALTER TABLE `main_customuser_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `main_customuser_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_customuser_groups_custom`
--

DROP TABLE IF EXISTS `main_customuser_groups_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_customuser_groups_custom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customuser_id` bigint(20) NOT NULL,
  `customgroup_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_customuser_groups_c_customuser_id_customgrou_65187b4e_uniq` (`customuser_id`,`customgroup_id`),
  KEY `main_customuser_grou_customgroup_id_ff6547f9_fk_main_cust` (`customgroup_id`),
  CONSTRAINT `main_customuser_grou_customgroup_id_ff6547f9_fk_main_cust` FOREIGN KEY (`customgroup_id`) REFERENCES `main_customgroup` (`id`),
  CONSTRAINT `main_customuser_grou_customuser_id_4752a678_fk_main_cust` FOREIGN KEY (`customuser_id`) REFERENCES `main_customuser` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_customuser_groups_custom`
--

LOCK TABLES `main_customuser_groups_custom` WRITE;
/*!40000 ALTER TABLE `main_customuser_groups_custom` DISABLE KEYS */;
INSERT INTO `main_customuser_groups_custom` VALUES (1,2,3);
/*!40000 ALTER TABLE `main_customuser_groups_custom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_customuser_user_permissions`
--

DROP TABLE IF EXISTS `main_customuser_user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_customuser_user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customuser_id` bigint(20) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_customuser_user_per_customuser_id_permission_06a652d8_uniq` (`customuser_id`,`permission_id`),
  KEY `main_customuser_user_permission_id_38e6f657_fk_auth_perm` (`permission_id`),
  CONSTRAINT `main_customuser_user_customuser_id_34d37f86_fk_main_cust` FOREIGN KEY (`customuser_id`) REFERENCES `main_customuser` (`id`),
  CONSTRAINT `main_customuser_user_permission_id_38e6f657_fk_auth_perm` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_customuser_user_permissions`
--

LOCK TABLES `main_customuser_user_permissions` WRITE;
/*!40000 ALTER TABLE `main_customuser_user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `main_customuser_user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_document`
--

DROP TABLE IF EXISTS `main_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_document` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file` varchar(100) NOT NULL,
  `doc_type_id` bigint(20) NOT NULL,
  `flight_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `main_document_doc_type_id_21d360df_fk_main_documenttype_id` (`doc_type_id`),
  KEY `main_document_flight_id_240b6ead_fk_main_flight_id` (`flight_id`),
  CONSTRAINT `main_document_doc_type_id_21d360df_fk_main_documenttype_id` FOREIGN KEY (`doc_type_id`) REFERENCES `main_documenttype` (`id`),
  CONSTRAINT `main_document_flight_id_240b6ead_fk_main_flight_id` FOREIGN KEY (`flight_id`) REFERENCES `main_flight` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_document`
--

LOCK TABLES `main_document` WRITE;
/*!40000 ALTER TABLE `main_document` DISABLE KEYS */;
INSERT INTO `main_document` VALUES (1,'documents/asd765432-20250416_150100_9bacf58c.txt',2,1);
/*!40000 ALTER TABLE `main_document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_documenttype`
--

DROP TABLE IF EXISTS `main_documenttype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_documenttype` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `is_archived` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_documenttype`
--

LOCK TABLES `main_documenttype` WRITE;
/*!40000 ALTER TABLE `main_documenttype` DISABLE KEYS */;
INSERT INTO `main_documenttype` VALUES (1,0,'Personal'),(2,0,'Reporte de Vuelo'),(3,0,'OIRSA');
/*!40000 ALTER TABLE `main_documenttype` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_flight`
--

DROP TABLE IF EXISTS `main_flight`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_flight` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `flight_number` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `created_at` date NOT NULL,
  `status` varchar(20) NOT NULL,
  `aircraft_id` bigint(20) DEFAULT NULL,
  `airline_id` bigint(20) NOT NULL,
  `billing_user_id` bigint(20) DEFAULT NULL,
  `created_by_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `main_flight_aircraft_id_294b435c_fk_main_aircraft_id` (`aircraft_id`),
  KEY `main_flight_airline_id_663053de_fk_main_airline_id` (`airline_id`),
  KEY `main_flight_billing_user_id_44e76a83_fk_main_customuser_id` (`billing_user_id`),
  KEY `main_flight_created_by_id_0f2bfcd4_fk_main_customuser_id` (`created_by_id`),
  CONSTRAINT `main_flight_aircraft_id_294b435c_fk_main_aircraft_id` FOREIGN KEY (`aircraft_id`) REFERENCES `main_aircraft` (`id`),
  CONSTRAINT `main_flight_airline_id_663053de_fk_main_airline_id` FOREIGN KEY (`airline_id`) REFERENCES `main_airline` (`id`),
  CONSTRAINT `main_flight_billing_user_id_44e76a83_fk_main_customuser_id` FOREIGN KEY (`billing_user_id`) REFERENCES `main_customuser` (`id`),
  CONSTRAINT `main_flight_created_by_id_0f2bfcd4_fk_main_customuser_id` FOREIGN KEY (`created_by_id`) REFERENCES `main_customuser` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_flight`
--

LOCK TABLES `main_flight` WRITE;
/*!40000 ALTER TABLE `main_flight` DISABLE KEYS */;
INSERT INTO `main_flight` VALUES (1,'ASD765432','2025-04-16','2025-04-16','pending',1,1,NULL,1);
/*!40000 ALTER TABLE `main_flight` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_flight_supervisors`
--

DROP TABLE IF EXISTS `main_flight_supervisors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_flight_supervisors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flight_id` bigint(20) NOT NULL,
  `customuser_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_flight_supervisors_flight_id_customuser_id_9b23a001_uniq` (`flight_id`,`customuser_id`),
  KEY `main_flight_supervis_customuser_id_52c1ded7_fk_main_cust` (`customuser_id`),
  CONSTRAINT `main_flight_supervis_customuser_id_52c1ded7_fk_main_cust` FOREIGN KEY (`customuser_id`) REFERENCES `main_customuser` (`id`),
  CONSTRAINT `main_flight_supervisors_flight_id_f134d19f_fk_main_flight_id` FOREIGN KEY (`flight_id`) REFERENCES `main_flight` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_flight_supervisors`
--

LOCK TABLES `main_flight_supervisors` WRITE;
/*!40000 ALTER TABLE `main_flight_supervisors` DISABLE KEYS */;
/*!40000 ALTER TABLE `main_flight_supervisors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_groupextension`
--

DROP TABLE IF EXISTS `main_groupextension`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_groupextension` (
  `group_id` bigint(20) NOT NULL,
  PRIMARY KEY (`group_id`),
  CONSTRAINT `main_groupextension_group_id_b6a03041_fk_main_customgroup_id` FOREIGN KEY (`group_id`) REFERENCES `main_customgroup` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_groupextension`
--

LOCK TABLES `main_groupextension` WRITE;
/*!40000 ALTER TABLE `main_groupextension` DISABLE KEYS */;
INSERT INTO `main_groupextension` VALUES (1),(2),(3),(4),(5),(6),(7);
/*!40000 ALTER TABLE `main_groupextension` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_navigationsection`
--

DROP TABLE IF EXISTS `main_navigationsection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_navigationsection` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `url_name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_name` (`url_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_navigationsection`
--

LOCK TABLES `main_navigationsection` WRITE;
/*!40000 ALTER TABLE `main_navigationsection` DISABLE KEYS */;
/*!40000 ALTER TABLE `main_navigationsection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_section`
--

DROP TABLE IF EXISTS `main_section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_section` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_section`
--

LOCK TABLES `main_section` WRITE;
/*!40000 ALTER TABLE `main_section` DISABLE KEYS */;
/*!40000 ALTER TABLE `main_section` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_section_groups`
--

DROP TABLE IF EXISTS `main_section_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_section_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` bigint(20) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_section_groups_section_id_group_id_97e2e45f_uniq` (`section_id`,`group_id`),
  KEY `main_section_groups_group_id_dd5d57b0_fk_auth_group_id` (`group_id`),
  CONSTRAINT `main_section_groups_group_id_dd5d57b0_fk_auth_group_id` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `main_section_groups_section_id_99e5b458_fk_main_section_id` FOREIGN KEY (`section_id`) REFERENCES `main_section` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_section_groups`
--

LOCK TABLES `main_section_groups` WRITE;
/*!40000 ALTER TABLE `main_section_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `main_section_groups` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-23 10:49:28
