-- MariaDB dump 10.20-11.8.9-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: db
-- ------------------------------------------------------
-- Server version	11.8.9-MariaDB-ubu2404-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inscripcion_id` int(11) NOT NULL,
  `semana` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_escaneo` time DEFAULT NULL,
  `estado` enum('presente','ausente','justificado') DEFAULT 'ausente',
  `justificacion` text DEFAULT NULL,
  `qr_code` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_asistencia` (`inscripcion_id`,`semana`,`fecha`),
  CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencias`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `detalle` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES
(1,1,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:18:32'),
(2,2,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:18:35'),
(3,3,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:18:39'),
(4,2,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:19:13'),
(5,3,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:19:19'),
(6,1,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:19:53'),
(7,1,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:20:01'),
(8,2,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:20:05'),
(9,3,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:20:09'),
(10,2,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:32:50'),
(11,2,'Inicio de sesión','Inicio de sesión exitoso','192.168.10.12','2026-11-06 22:47:04'),
(12,2,'Inicio de sesión','Inicio de sesión exitoso','192.168.10.12','2026-11-06 22:47:46'),
(13,2,'Inicio de sesión','Inicio de sesión exitoso','172.18.0.6','2026-11-06 22:47:59'),
(14,3,'Inicio de sesión','Inicio de sesión exitoso','172.18.0.6','2026-11-06 22:48:19'),
(15,2,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 22:49:13'),
(16,4,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 23:40:08'),
(17,4,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 23:40:13'),
(18,5,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 23:40:18'),
(19,5,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 23:40:23'),
(20,4,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-06 23:40:29'),
(21,5,'Generar QR','Curso ID: 39, Semana: 1, Sesion QR ID: 1','172.19.0.1','2026-11-06 23:40:36'),
(22,4,'Escaneo QR','Curso: LENGUAJE DE PROGRAMACIÓN IV (LPR1543), Semana: 1','172.19.0.1','2026-11-06 23:40:36'),
(23,3,'Cierre de sesión','Sesión cerrada voluntariamente','172.18.0.6','2026-11-06 23:52:26'),
(24,5,'Inicio de sesión','Inicio de sesión exitoso','172.18.0.6','2026-11-06 23:52:35'),
(25,2,'Cierre de sesión','Sesión cerrada voluntariamente','192.168.10.12','2026-11-06 23:53:02'),
(26,4,'Inicio de sesión','Inicio de sesión exitoso','192.168.10.12','2026-11-06 23:53:12'),
(27,4,'Inscripción manual','Materia ID: 41','192.168.10.12','2026-11-06 23:53:24'),
(28,5,'Generar QR','Curso ID: 39, Semana: 9, Sesion QR ID: 2','172.18.0.6','2026-11-06 23:53:47'),
(29,4,'Escaneo QR','Curso: LENGUAJE DE PROGRAMACIÓN IV (LPR1543), Semana: 9','192.168.10.12','2026-11-06 23:53:53'),
(30,4,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-07 00:14:53'),
(31,4,'Inscripción manual','Materia ID: 52','172.19.0.1','2026-11-07 00:15:16'),
(32,6,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-07 00:15:30'),
(33,7,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-07 00:15:30'),
(34,8,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-07 00:15:30'),
(35,9,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-07 00:15:30'),
(36,10,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-07 00:15:31'),
(37,11,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-07 00:15:31'),
(38,12,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-07 00:15:31'),
(39,13,'Inicio de sesión','Inicio de sesión exitoso','172.19.0.1','2026-11-07 00:15:31'),
(40,5,'Cierre de sesión','Sesión cerrada voluntariamente','172.18.0.6','2026-11-07 00:20:30'),
(41,13,'Inicio de sesión','Inicio de sesión exitoso','172.18.0.6','2026-11-07 00:22:39'),
(42,13,'Ajustes QR','Exp:5min Rot:10seg GPS:50m','172.18.0.6','2026-11-07 00:47:48');
/*!40000 ALTER TABLE `auditoria` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `calificaciones`
--

DROP TABLE IF EXISTS `calificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `calificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inscripcion_id` int(11) NOT NULL,
  `nota` decimal(5,2) DEFAULT NULL,
  `estatus` enum('Cursando','Aprobada','Reprobada','Remedial') NOT NULL DEFAULT 'Cursando',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `inscripcion_id` (`inscripcion_id`),
  CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calificaciones`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `calificaciones` WRITE;
/*!40000 ALTER TABLE `calificaciones` DISABLE KEYS */;
INSERT INTO `calificaciones` VALUES
(2,2,18.00,'Aprobada','2026-11-06 23:39:44'),
(3,3,18.00,'Aprobada','2026-11-06 23:39:44'),
(4,4,18.00,'Aprobada','2026-11-06 23:39:44'),
(5,5,18.00,'Aprobada','2026-11-06 23:39:44'),
(6,6,18.00,'Aprobada','2026-11-06 23:39:44'),
(7,7,18.00,'Aprobada','2026-11-06 23:39:44'),
(8,8,18.00,'Aprobada','2026-11-06 23:39:44'),
(9,9,18.00,'Aprobada','2026-11-06 23:39:44'),
(10,10,18.00,'Aprobada','2026-11-06 23:39:44'),
(11,11,18.00,'Aprobada','2026-11-06 23:39:44'),
(12,12,18.00,'Aprobada','2026-11-06 23:39:44'),
(13,13,18.00,'Aprobada','2026-11-06 23:39:44'),
(14,14,18.00,'Aprobada','2026-11-06 23:39:44'),
(15,15,18.00,'Aprobada','2026-11-06 23:39:44'),
(16,16,18.00,'Aprobada','2026-11-06 23:39:44'),
(17,17,18.00,'Aprobada','2026-11-06 23:39:44'),
(18,18,18.00,'Aprobada','2026-11-06 23:39:44'),
(19,19,18.00,'Aprobada','2026-11-06 23:39:44'),
(20,20,18.00,'Aprobada','2026-11-06 23:39:44'),
(21,21,18.00,'Aprobada','2026-11-06 23:39:44'),
(22,22,18.00,'Aprobada','2026-11-06 23:39:44'),
(23,23,18.00,'Aprobada','2026-11-06 23:39:44'),
(24,24,18.00,'Aprobada','2026-11-06 23:39:44'),
(25,25,18.00,'Aprobada','2026-11-06 23:39:44'),
(26,26,18.00,'Aprobada','2026-11-06 23:39:44'),
(27,27,18.00,'Aprobada','2026-11-06 23:39:44'),
(28,28,18.00,'Aprobada','2026-11-06 23:39:44'),
(29,29,18.00,'Aprobada','2026-11-06 23:39:44'),
(30,30,18.00,'Aprobada','2026-11-06 23:39:44'),
(31,31,18.00,'Aprobada','2026-11-06 23:39:44'),
(32,32,18.00,'Aprobada','2026-11-06 23:39:44'),
(33,33,18.00,'Aprobada','2026-11-06 23:39:44'),
(34,34,18.00,'Aprobada','2026-11-06 23:39:44'),
(35,35,18.00,'Aprobada','2026-11-06 23:39:44'),
(36,36,18.00,'Aprobada','2026-11-06 23:39:44'),
(37,37,18.00,'Aprobada','2026-11-06 23:39:44'),
(38,38,18.00,'Aprobada','2026-11-06 23:39:44');
/*!40000 ALTER TABLE `calificaciones` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `carreras`
--

DROP TABLE IF EXISTS `carreras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carreras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `nivel` varchar(50) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `tipo_periodo` enum('Trimestre','Cuatrimestre') NOT NULL DEFAULT 'Cuatrimestre',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carreras`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `carreras` WRITE;
/*!40000 ALTER TABLE `carreras` DISABLE KEYS */;
INSERT INTO `carreras` VALUES
(1,'Enfermería','TSU','ENF-TSU','Cuatrimestre'),
(2,'Enfermería','Licenciatura','ENF-LIC','Cuatrimestre'),
(3,'Fisioterapia','TSU','FIS-TSU','Cuatrimestre'),
(4,'Informática','TSU','INF-TSU','Cuatrimestre'),
(5,'Diseño Gráfico','TSU','DIS-TSU','Trimestre'),
(6,'Administración de Empresas','TSU','ADM-TSU','Cuatrimestre'),
(7,'Administración de Empresas','Licenciatura','ADM-LIC','Cuatrimestre'),
(8,'Contaduría Pública','TSU','CON-TSU','Cuatrimestre'),
(9,'Contaduría Pública','Licenciatura','CON-LIC','Cuatrimestre'),
(10,'Relaciones Industriales','TSU','RIN-TSU','Cuatrimestre'),
(11,'Gastronomía','TSU','GAS-TSU','Trimestre');
/*!40000 ALTER TABLE `carreras` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES
(1,'periodo_activo','2026-III','2026-11-06 23:39:44');
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `configuracion_profesor`
--

DROP TABLE IF EXISTS `configuracion_profesor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion_profesor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profesor_id` int(11) NOT NULL,
  `expiracion_minutos` int(11) NOT NULL DEFAULT 5,
  `rotacion_segundos` int(11) NOT NULL DEFAULT 30,
  `radio_gps` int(11) NOT NULL DEFAULT 50,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `profesor_id` (`profesor_id`),
  CONSTRAINT `configuracion_profesor_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion_profesor`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `configuracion_profesor` WRITE;
/*!40000 ALTER TABLE `configuracion_profesor` DISABLE KEYS */;
INSERT INTO `configuracion_profesor` VALUES
(1,5,5,30,50,'2026-11-06 23:39:44','2026-11-06 23:39:44'),
(2,6,5,30,50,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(3,7,5,30,50,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(4,8,5,30,50,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(5,9,5,30,50,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(6,10,5,30,50,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(7,11,5,30,50,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(8,12,5,30,50,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(9,13,5,10,50,'2026-11-07 00:14:32','2026-11-07 00:47:48');
/*!40000 ALTER TABLE `configuracion_profesor` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cursos`
--

DROP TABLE IF EXISTS `cursos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cursos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `carrera_id` int(11) DEFAULT NULL,
  `profesor_id` int(11) DEFAULT NULL,
  `aula` varchar(100) DEFAULT NULL,
  `seccion` varchar(20) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `trayecto` int(11) NOT NULL DEFAULT 1,
  `creditos` int(11) NOT NULL DEFAULT 0,
  `tipo` enum('Obligatoria','Electiva') NOT NULL DEFAULT 'Obligatoria',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `profesor_id` (`profesor_id`),
  KEY `carrera_id` (`carrera_id`),
  CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `cursos_ibfk_2` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cursos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cursos` WRITE;
/*!40000 ALTER TABLE `cursos` DISABLE KEYS */;
INSERT INTO `cursos` VALUES
(1,'ADMINISTRACIÓN','ADM1133',4,NULL,NULL,NULL,NULL,1,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(2,'EDUCACIÓN CIUDADANA','ECI1121',4,NULL,NULL,NULL,NULL,1,1,'Obligatoria',1,'2026-11-06 23:39:44'),
(3,'EDUCACIÓN FÍSICA Y DEPORTES','EFD1121',4,NULL,NULL,NULL,NULL,1,1,'Obligatoria',1,'2026-11-06 23:39:44'),
(4,'INFORMÁTICA BÁSICA','IBA1133',4,NULL,NULL,NULL,NULL,1,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(5,'INGLÉS I','ING1142',4,NULL,NULL,NULL,NULL,1,2,'Obligatoria',1,'2026-11-06 23:39:44'),
(6,'LENGUAJE Y COMUNICACIÓN','LCO1142',4,NULL,NULL,NULL,NULL,1,2,'Obligatoria',1,'2026-11-06 23:39:44'),
(7,'LÓGICA','LOG1142',4,NULL,NULL,NULL,NULL,1,2,'Obligatoria',1,'2026-11-06 23:39:44'),
(8,'MATEMÁTICA I','MAT1143',4,NULL,NULL,NULL,NULL,1,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(9,'TÉCNICAS DE ESTUDIO E INVESTIGACIÓN','TEI1143',4,NULL,NULL,NULL,NULL,1,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(10,'ELECTIVAS DE INFORMÁTICA','ELEINF',4,NULL,NULL,NULL,NULL,2,0,'Electiva',1,'2026-11-06 23:39:44'),
(11,'CÁTEDRA BOLIVARIANA','CBO1231',4,NULL,NULL,NULL,NULL,2,1,'Obligatoria',1,'2026-11-06 23:39:44'),
(12,'ESTRUCTURA DE DATOS','EDA1243',4,NULL,NULL,NULL,NULL,2,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(13,'INGLÉS II','ING1242',4,NULL,NULL,NULL,NULL,2,2,'Obligatoria',1,'2026-11-06 23:39:44'),
(14,'LENGUAJE DE PROGRAMACIÓN I','LPR1263',4,NULL,NULL,NULL,NULL,2,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(15,'MATEMÁTICA II','MAT1243',4,NULL,NULL,NULL,NULL,2,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(16,'METODOLOGÍA DE LA INVESTIGACIÓN','MIN1243',4,NULL,NULL,NULL,NULL,2,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(17,'ORGANIZACIÓN Y SISTEMAS','OSI1232',4,NULL,NULL,NULL,NULL,2,2,'Obligatoria',1,'2026-11-06 23:39:44'),
(18,'SISTEMAS DE INFORMACIÓN','SIN1243',4,NULL,NULL,NULL,NULL,2,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(19,'ARQUITECTURA DEL COMPUTADOR','ACO1343',4,NULL,NULL,NULL,NULL,3,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(20,'ANÁLISIS Y DISEÑO DE SISTEMAS I','ADS1343',4,NULL,NULL,NULL,NULL,3,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(21,'ADMINISTRACIÓN DE EMPRESAS','AEM1343',4,NULL,NULL,NULL,NULL,3,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(22,'BASE DE DATOS','BDA1343',4,NULL,NULL,NULL,NULL,3,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(23,'CÁLCULO MATRICIAL','CMA1342',4,NULL,NULL,NULL,NULL,3,2,'Obligatoria',1,'2026-11-06 23:39:44'),
(24,'EDUCACIÓN AMBIENTAL','EAM1321',4,NULL,NULL,NULL,NULL,3,1,'Obligatoria',1,'2026-11-06 23:39:44'),
(25,'ESTADÍSTICA GENERAL','EGE1343',4,NULL,NULL,NULL,NULL,3,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(26,'LENGUAJE DE PROGRAMACIÓN II','LPR1363',4,NULL,NULL,NULL,NULL,3,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(27,'ANÁLISIS Y DISEÑO DE SISTEMAS II','ADS1443',4,NULL,NULL,NULL,NULL,4,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(28,'ESTADÍSTICA PROBABILÍSTICA','EPR1443',4,NULL,NULL,NULL,NULL,4,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(29,'LENGUAJE DE PROGRAMACIÓN III','LPR1443',4,NULL,NULL,NULL,NULL,4,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(30,'MODELO DE DATOS','MDA1443',4,NULL,NULL,NULL,NULL,4,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(31,'SERVICIO COMUNITARIO','SERCOM01',4,NULL,NULL,NULL,NULL,4,0,'Obligatoria',1,'2026-11-06 23:39:44'),
(32,'SISTEMAS OPERATIVOS','SOP1443',4,NULL,NULL,NULL,NULL,4,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(33,'TÓPICOS ESPECIALES DE INFORMÁTICA','TEI1443',4,NULL,NULL,NULL,NULL,4,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(34,'TELEPROCESOS','TEL1443',4,NULL,NULL,NULL,NULL,4,3,'Obligatoria',1,'2026-11-06 23:39:44'),
(42,'PASANTÍAS','PAS1616',4,NULL,NULL,NULL,NULL,6,6,'Obligatoria',1,'2026-11-06 23:39:44'),
(43,'DIVERSIDAD E IDENTIDAD CULTURAL','DIC1221',4,NULL,NULL,NULL,NULL,2,1,'Electiva',1,'2026-11-06 23:39:44'),
(44,'EDITORIAL UNIVERSITARIA','EUN1221',4,NULL,NULL,NULL,NULL,2,1,'Electiva',1,'2026-11-06 23:39:44'),
(45,'INTERPRETACIÓN CORAL','ICO1221',4,NULL,NULL,NULL,NULL,2,1,'Electiva',1,'2026-11-06 23:39:44'),
(46,'PROTOCOLO INSTITUCIONAL','PIN1221',4,NULL,NULL,NULL,NULL,2,1,'Electiva',1,'2026-11-06 23:39:44'),
(47,'TEATRO','TEA1221',4,NULL,NULL,NULL,NULL,2,1,'Electiva',1,'2026-11-06 23:39:44'),
(48,'PLANIFICACIÓN DE PROYECTO','PPR1543',4,6,'A3','1D1','Lunes 07:45 - 10:15',5,3,'Obligatoria',1,'2026-11-07 00:14:32'),
(49,'INTELIGENCIA DE NEGOCIO','INE1532',4,7,'A5','1D1','Lunes 10:15 - 11:45',5,2,'Electiva',1,'2026-11-07 00:14:32'),
(50,'INFORMÁTICA Y ÉTICA','ETI1522',4,8,'A12','1D1','Martes 07:00 - 08:30',5,2,'Obligatoria',1,'2026-11-07 00:14:32'),
(51,'PROCESAMIENTO DATOS','PDA1542',4,9,'L1','1D1','Martes 08:45 - 10:15',5,2,'Obligatoria',1,'2026-11-07 00:14:32'),
(52,'PROGRAMACIÓN IV','PRO1554',4,10,'L2','1D1','Jueves 07:00 - 08:30 / Viernes 08:45 - 10:15',5,4,'Obligatoria',1,'2026-11-07 00:14:32'),
(53,'INVESTIGACIÓN DE OPERACIONES','IOP1543',4,11,'A4','1D1','Jueves 08:45 - 10:15',5,3,'Obligatoria',1,'2026-11-07 00:14:32'),
(54,'REDES II','RED1532',4,12,'A14','1D1','Viernes 07:00 - 08:30',5,2,'Obligatoria',1,'2026-11-07 00:14:32'),
(55,'AUDITORIA DE SISTEMAS COMPUTARIZADO','ASC1532',4,13,'A11','1D1','Viernes 10:15 - 11:45',5,2,'Obligatoria',1,'2026-11-07 00:14:32');
/*!40000 ALTER TABLE `cursos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `inscripciones`
--

DROP TABLE IF EXISTS `inscripciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `periodo` varchar(20) NOT NULL,
  `estatus` enum('Activa','Retirada') DEFAULT 'Activa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_inscripcion` (`estudiante_id`,`curso_id`,`periodo`),
  KEY `curso_id` (`curso_id`),
  CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscripciones`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `inscripciones` WRITE;
/*!40000 ALTER TABLE `inscripciones` DISABLE KEYS */;
INSERT INTO `inscripciones` VALUES
(2,4,1,'2026-I','Activa'),
(3,4,2,'2026-I','Activa'),
(4,4,3,'2026-I','Activa'),
(5,4,4,'2026-I','Activa'),
(6,4,5,'2026-I','Activa'),
(7,4,6,'2026-I','Activa'),
(8,4,7,'2026-I','Activa'),
(9,4,8,'2026-I','Activa'),
(10,4,9,'2026-I','Activa'),
(11,4,11,'2026-I','Activa'),
(12,4,12,'2026-I','Activa'),
(13,4,13,'2026-I','Activa'),
(14,4,14,'2026-I','Activa'),
(15,4,15,'2026-I','Activa'),
(16,4,16,'2026-I','Activa'),
(17,4,17,'2026-I','Activa'),
(18,4,18,'2026-I','Activa'),
(19,4,19,'2026-I','Activa'),
(20,4,20,'2026-I','Activa'),
(21,4,21,'2026-I','Activa'),
(22,4,22,'2026-I','Activa'),
(23,4,23,'2026-I','Activa'),
(24,4,24,'2026-I','Activa'),
(25,4,25,'2026-I','Activa'),
(26,4,26,'2026-I','Activa'),
(27,4,27,'2026-I','Activa'),
(28,4,28,'2026-I','Activa'),
(29,4,29,'2026-I','Activa'),
(30,4,30,'2026-I','Activa'),
(31,4,32,'2026-I','Activa'),
(32,4,33,'2026-I','Activa'),
(33,4,34,'2026-I','Activa'),
(34,4,43,'2026-I','Activa'),
(35,4,44,'2026-I','Activa'),
(36,4,45,'2026-I','Activa'),
(37,4,46,'2026-I','Activa'),
(38,4,47,'2026-I','Activa');
/*!40000 ALTER TABLE `inscripciones` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `periodos`
--

DROP TABLE IF EXISTS `periodos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_inicio_nuevos` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `periodos` WRITE;
/*!40000 ALTER TABLE `periodos` DISABLE KEYS */;
INSERT INTO `periodos` VALUES
(1,'2026-III','2026-09-07','2026-09-14','2026-11-06 23:39:44');
/*!40000 ALTER TABLE `periodos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `prerrequisitos`
--

DROP TABLE IF EXISTS `prerrequisitos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prerrequisitos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `curso_id` int(11) NOT NULL,
  `requiere_codigo` varchar(50) DEFAULT NULL,
  `nivel_requerido` tinyint(3) unsigned DEFAULT NULL,
  `creditos_minimos` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `curso_id` (`curso_id`,`requiere_codigo`),
  CONSTRAINT `prerrequisitos_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prerrequisitos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `prerrequisitos` WRITE;
/*!40000 ALTER TABLE `prerrequisitos` DISABLE KEYS */;
INSERT INTO `prerrequisitos` VALUES
(1,11,NULL,1,NULL),
(2,12,'LOG1142',NULL,NULL),
(3,13,'ING1142',NULL,NULL),
(4,14,'IBA1133',NULL,NULL),
(5,14,'LOG1142',NULL,NULL),
(6,15,'MAT1143',NULL,NULL),
(7,16,'TEI1143',NULL,NULL),
(8,17,'ADM1133',NULL,NULL),
(9,18,'TEI1143',NULL,NULL),
(10,18,'IBA1133',NULL,NULL),
(11,19,'IBA1133',NULL,NULL),
(12,19,NULL,2,NULL),
(13,20,'OSI1232',NULL,NULL),
(14,20,'SIN1243',NULL,NULL),
(15,21,'ADM1133',NULL,NULL),
(16,21,NULL,2,NULL),
(17,22,'EDA1243',NULL,NULL),
(18,23,'MAT1243',NULL,NULL),
(19,23,NULL,2,NULL),
(20,24,NULL,2,NULL),
(21,25,'MAT1143',NULL,NULL),
(22,25,NULL,2,NULL),
(23,26,'LPR1263',NULL,NULL),
(24,27,'ADS1343',NULL,NULL),
(25,28,'EGE1343',NULL,NULL),
(26,29,'LPR1363',NULL,NULL),
(27,30,'BDA1343',NULL,NULL),
(28,30,'CMA1342',NULL,NULL),
(29,31,NULL,3,NULL),
(30,32,'ACO1343',NULL,NULL),
(31,33,NULL,2,NULL),
(32,33,NULL,3,NULL),
(33,34,'ACO1343',NULL,NULL),
(45,42,NULL,NULL,99),
(46,46,NULL,1,NULL),
(47,48,'MIN1243',NULL,NULL),
(48,51,'BDA1343',NULL,NULL),
(49,52,'LPR1443',NULL,NULL),
(50,53,'CMA1342',NULL,NULL),
(51,54,'SOP1443',NULL,NULL),
(52,54,'TEL1443',NULL,NULL);
/*!40000 ALTER TABLE `prerrequisitos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sesiones_qr`
--

DROP TABLE IF EXISTS `sesiones_qr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones_qr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `curso_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `codigo_qr` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_expiracion` time NOT NULL,
  `semana` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_qr` (`codigo_qr`),
  KEY `curso_id` (`curso_id`),
  KEY `profesor_id` (`profesor_id`),
  CONSTRAINT `sesiones_qr_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  CONSTRAINT `sesiones_qr_ibfk_2` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_qr`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sesiones_qr` WRITE;
/*!40000 ALTER TABLE `sesiones_qr` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones_qr` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `nombre_completo` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `carrera_id` int(11) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('admin','profesor','estudiante') NOT NULL DEFAULT 'estudiante',
  `password_hash` varchar(255) NOT NULL,
  `password_temporal` tinyint(1) DEFAULT 1,
  `activo` tinyint(1) DEFAULT 1,
  `es_nuevo_ingreso` tinyint(1) DEFAULT 0,
  `creado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `username` (`username`),
  KEY `creado_por` (`creado_por`),
  KEY `carrera_id` (`carrera_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'admin','admin','Administrador','admin@iutecp.edu.ve',NULL,NULL,'admin','$2y$12$fdB85BmYRGO6m9p.tOqJPO8aI7IlPRBunbjfqBDACAmtbIip46a1O',0,1,0,NULL,'2026-11-06 22:18:13','2026-11-06 22:18:13'),
(2,'alumno','alumno','Alumno','alumno@iutecp.edu.ve',NULL,NULL,'estudiante','$2y$12$b7iwYelzujGsQScJFzHUretwvxwGCbBbr8tSOSfN0F8xWr9kdVxIu',0,1,0,NULL,'2026-11-06 22:18:14','2026-11-06 22:18:14'),
(3,'profesor','profesor','Profesor','profesor@iutecp.edu.ve',NULL,NULL,'profesor','$2y$12$TNaybCn0NkleBRtXMh.VPe3j2692u2mk964J5rE3g3s3XxhSTKUDK',0,1,0,NULL,'2026-11-06 22:18:14','2026-11-06 22:18:14'),
(4,'V31853086','31853086','Fernando Rodríguez Rada','fernando.rodriguez@iutecp.edu.ve',4,NULL,'estudiante','$2y$12$c995rWc3B8fEkkLGnO8sMOY9HxHUVwulvKUJ6GsVVIeAhOJbLQjaG',0,1,0,NULL,'2026-11-06 23:39:44','2026-11-06 23:39:44'),
(5,'V18189189','18189189','Alejandro Ojeda Chacón','alejandro.ojeda@iutecp.edu.ve',4,NULL,'profesor','$2y$12$c995rWc3B8fEkkLGnO8sMOY9HxHUVwulvKUJ6GsVVIeAhOJbLQjaG',0,1,0,NULL,'2026-11-06 23:39:44','2026-11-06 23:39:44'),
(6,'V11486263','11486263','Rosangela Fernandez','rosangela.fernandez@iutecp.edu.ve',4,NULL,'profesor','$2y$12$VFrgWVxCyK.97ka2tBuD2eRCEPXdt/pJ0l6R2k4HIWG.3014kVbFC',0,1,0,NULL,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(7,'V21134176','21134176','José Goncalvez','jose.goncalvez@iutecp.edu.ve',4,NULL,'profesor','$2y$12$VFrgWVxCyK.97ka2tBuD2eRCEPXdt/pJ0l6R2k4HIWG.3014kVbFC',0,1,0,NULL,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(8,'V21134177','21134177','Yajes Fuenmayor','yajes.fuenmayor@iutecp.edu.ve',4,NULL,'profesor','$2y$12$VFrgWVxCyK.97ka2tBuD2eRCEPXdt/pJ0l6R2k4HIWG.3014kVbFC',0,1,0,NULL,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(9,'V4273815','4273815','Jose Angel Ed','jose.angel@iutecp.edu.ve',4,NULL,'profesor','$2y$12$VFrgWVxCyK.97ka2tBuD2eRCEPXdt/pJ0l6R2k4HIWG.3014kVbFC',0,1,0,NULL,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(10,'V18403975','18403975','José Chacón','jose.chacon@iutecp.edu.ve',4,NULL,'profesor','$2y$12$VFrgWVxCyK.97ka2tBuD2eRCEPXdt/pJ0l6R2k4HIWG.3014kVbFC',0,1,0,NULL,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(11,'V15564116','15564116','Jhanan Ojeda','jhanan.ojeda@iutecp.edu.ve',4,NULL,'profesor','$2y$12$VFrgWVxCyK.97ka2tBuD2eRCEPXdt/pJ0l6R2k4HIWG.3014kVbFC',0,1,0,NULL,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(12,'V25327679','25327679','Pedro Yanez','pedro.yanez@iutecp.edu.ve',4,NULL,'profesor','$2y$12$VFrgWVxCyK.97ka2tBuD2eRCEPXdt/pJ0l6R2k4HIWG.3014kVbFC',0,1,0,NULL,'2026-11-07 00:14:32','2026-11-07 00:14:32'),
(13,'V4273816','4273816','José Mejias','jose.mejias@iutecp.edu.ve',4,NULL,'profesor','$2y$12$VFrgWVxCyK.97ka2tBuD2eRCEPXdt/pJ0l6R2k4HIWG.3014kVbFC',0,1,0,NULL,'2026-11-07 00:14:32','2026-11-07 00:14:32');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-11-06 20:58:03
