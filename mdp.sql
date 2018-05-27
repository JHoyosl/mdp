-- MySQL dump 10.13  Distrib 5.7.22, for Linux (x86_64)
--
-- Host: localhost    Database: mdp
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu0.16.04.1

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
-- Table structure for table `bancos`
--

DROP TABLE IF EXISTS `bancos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bancos` (
  `BANCO_ID` varchar(32) COLLATE utf8_bin NOT NULL,
  `NOMBRE` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `HEAD` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `RUTA` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `NOTAS` blob,
  PRIMARY KEY (`BANCO_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bancos`
--

LOCK TABLES `bancos` WRITE;
/*!40000 ALTER TABLE `bancos` DISABLE KEYS */;
INSERT INTO `bancos` VALUES ('860002964-4','BANCO DE BOGOTA','1','1',''),('860003020-1','BANCO BBVA','2','2',''),('860007335-4','BANCO BCSC COLMENA','3','3',''),('860007738-9','BANCO POPULAR','4','4',''),('860034313-7','BANCO DAVIVIENDA','5','5',''),('860034594-1','BANCO COLPATRIA','11','11',''),('860035827-5','BANCO AV VILLAS','6','6',''),('860066942-7','COMPENSAR','12','12',''),('890203088-9|','BANCO COOPCENTRAL','7','7',''),('890300279-4','BANCO DE OCCIDENTE','8','8',''),('890903937-0','BANCO ITAU','9','9',''),('890903938-8','BANCOLOMBIA','HEAD-22222','RUTA-22222','NOTAS EXTRAS');
/*!40000 ALTER TABLE `bancos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empresas` (
  `NIT` varchar(32) COLLATE utf8_bin NOT NULL,
  `RAZON_SOCIAL` varchar(100) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`NIT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
INSERT INTO `empresas` VALUES ('900862658-9','Globalsys.co S.A.S');
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maestro_area_empresa`
--

DROP TABLE IF EXISTS `maestro_area_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maestro_area_empresa` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIPCION` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maestro_area_empresa`
--

LOCK TABLES `maestro_area_empresa` WRITE;
/*!40000 ALTER TABLE `maestro_area_empresa` DISABLE KEYS */;
INSERT INTO `maestro_area_empresa` VALUES (1,'TESORERIA'),(2,'CONTABILIDAD'),(3,'OPERACIONES'),(4,'AUDITORIA'),(5,'ADMINISTRATIVA'),(6,'GERENCIA');
/*!40000 ALTER TABLE `maestro_area_empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maestro_genero`
--

DROP TABLE IF EXISTS `maestro_genero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maestro_genero` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIPCION` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maestro_genero`
--

LOCK TABLES `maestro_genero` WRITE;
/*!40000 ALTER TABLE `maestro_genero` DISABLE KEYS */;
INSERT INTO `maestro_genero` VALUES (1,'FEMENINO'),(2,'MASCULINO');
/*!40000 ALTER TABLE `maestro_genero` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maestro_tipo_doc`
--

DROP TABLE IF EXISTS `maestro_tipo_doc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maestro_tipo_doc` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIPCION` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maestro_tipo_doc`
--

LOCK TABLES `maestro_tipo_doc` WRITE;
/*!40000 ALTER TABLE `maestro_tipo_doc` DISABLE KEYS */;
INSERT INTO `maestro_tipo_doc` VALUES (1,'CÉDULA');
/*!40000 ALTER TABLE `maestro_tipo_doc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maestro_tipo_usuario`
--

DROP TABLE IF EXISTS `maestro_tipo_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maestro_tipo_usuario` (
  `ID` int(11) NOT NULL,
  `DESCRIPCION` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maestro_tipo_usuario`
--

LOCK TABLES `maestro_tipo_usuario` WRITE;
/*!40000 ALTER TABLE `maestro_tipo_usuario` DISABLE KEYS */;
INSERT INTO `maestro_tipo_usuario` VALUES (1,'SUPERADMIN'),(2,'ADMIN'),(3,'USUARIO');
/*!40000 ALTER TABLE `maestro_tipo_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_empresa`
--

DROP TABLE IF EXISTS `usuario_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_empresa` (
  `USUARIO_ID` int(11) NOT NULL,
  `EMPRESA` varchar(32) COLLATE utf8_bin NOT NULL,
  `AREA` int(11) DEFAULT NULL,
  `ID_INTERNO` varchar(32) COLLATE utf8_bin NOT NULL,
  `ACTIVO` int(255) DEFAULT '0',
  PRIMARY KEY (`USUARIO_ID`,`EMPRESA`,`ID_INTERNO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_empresa`
--

LOCK TABLES `usuario_empresa` WRITE;
/*!40000 ALTER TABLE `usuario_empresa` DISABLE KEYS */;
INSERT INTO `usuario_empresa` VALUES (0,'9999999999',4,'000000',0),(1,'900862658-9',5,'00000',0),(7,'900862658-9',5,'00000',0),(7,'9999999999',4,'000000',0),(8,'900862658-9',2,'Jorram',0);
/*!40000 ALTER TABLE `usuario_empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `EMAIL` varchar(255) COLLATE utf8_bin NOT NULL,
  `NOMBRES` varbinary(255) NOT NULL,
  `APELLIDOS` varchar(255) COLLATE utf8_bin NOT NULL,
  `ACTIVO` int(11) DEFAULT '0',
  `TELEFONO` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `TIPO` int(11) DEFAULT NULL,
  `PSSW` varchar(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`,`EMAIL`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admingsys','Global','Sys',0,'3333333333',1,'827ccb0eea8a706c4c34a16891f84e7b'),(7,'jhoyosl@globalsys.co','TEST GSYS','TEST GSYS',0,'2222222',NULL,'RuHTS3X9eu'),(8,'jramirez55@gmail.com','jorge','RRamirez',0,'3162205799',NULL,'5S1dwMTM6c');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-05-26 22:51:50
