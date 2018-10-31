-- MySQL dump 10.13  Distrib 5.7.24, for Linux (x86_64)
--
-- Host: localhost    Database: mysql-db-test
-- ------------------------------------------------------
-- Server version	5.7.23

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
-- Table structure for table `conversation`
--

DROP TABLE IF EXISTS `conversation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversation` (
  `conversation_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation`
--

LOCK TABLES `conversation` WRITE;
/*!40000 ALTER TABLE `conversation` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversation_user`
--

DROP TABLE IF EXISTS `conversation_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversation_user` (
  `conversation` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`conversation`,`user_id`),
  KEY `IDX_5AECB5558A8E26E9` (`conversation`),
  KEY `IDX_5AECB555A76ED395` (`user_id`),
  CONSTRAINT `FK_5AECB5558A8E26E9` FOREIGN KEY (`conversation`) REFERENCES `conversation` (`conversation_id`),
  CONSTRAINT `FK_5AECB555A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation_user`
--

LOCK TABLES `conversation_user` WRITE;
/*!40000 ALTER TABLE `conversation_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversation_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(10000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `send_user_id` int(11) NOT NULL,
  `send_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications_user`
--

DROP TABLE IF EXISTS `notifications_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications_user` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`notification_id`,`user_id`),
  KEY `IDX_666C86E6EF1A9D84` (`notification_id`),
  KEY `IDX_666C86E6A76ED395` (`user_id`),
  CONSTRAINT `FK_666C86E6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_666C86E6EF1A9D84` FOREIGN KEY (`notification_id`) REFERENCES `notification` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications_user`
--

LOCK TABLES `notifications_user` WRITE;
/*!40000 ALTER TABLE `notifications_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(16000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `userId` int(11) DEFAULT NULL,
  PRIMARY KEY (`post_id`),
  KEY `IDX_5A8A6C8D64B64DCC` (`userId`),
  CONSTRAINT `FK_5A8A6C8D64B64DCC` FOREIGN KEY (`userId`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post`
--

LOCK TABLES `post` WRITE;
/*!40000 ALTER TABLE `post` DISABLE KEYS */;
/*!40000 ALTER TABLE `post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token_id` int(11) NOT NULL,
  `first_name` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(34) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `roles` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sex` int(11) NOT NULL,
  `date_birth` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  KEY `IDX_8D93D64941DEE7B9` (`token_id`),
  CONSTRAINT `FK_8D93D64941DEE7B9` FOREIGN KEY (`token_id`) REFERENCES `user_token` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,1,'Administrator','Admin','kontakt@lukaszstaniszewski.pl','$2y$13$Wha8beWmXghhuc.m2ldC/e0XUI747eLvy0Idtmo.KIS8.oGzwbhD6',1,'ROLE_USER',0,'1898-01-01 00:00:00','2018-10-11 21:02:40'),(2,2,'User','Search','malinowa98@gmail.com','$2y$13$Wha8beWmXghhuc.m2ldC/e0XUI747eLvy0Idtmo.KIS8.oGzwbhD6',1,'ROLE_USER',1,'1998-12-24 00:00:00','2018-09-28 23:38:40');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_token`
--

DROP TABLE IF EXISTS `user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refresh_public_token` datetime NOT NULL,
  `public_token` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `private_web_token` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_web_token` datetime NOT NULL,
  `private_mobile_token` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_mobile_token` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BDF55A63AE981E3B` (`public_token`),
  UNIQUE KEY `UNIQ_BDF55A633AE4EC2F` (`private_web_token`),
  UNIQUE KEY `UNIQ_BDF55A6347ACB875` (`private_mobile_token`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_token`
--

LOCK TABLES `user_token` WRITE;
/*!40000 ALTER TABLE `user_token` DISABLE KEYS */;
INSERT INTO `user_token` VALUES (1,'2018-11-10 20:02:40','12b57c17-18d5-46b7-bb34-ef1d54c54c0e','81489066-6360-4770-a13e-ac7e304365fb','2018-11-01 16:22:09','a242fec4-82d7-4299-99f6-2998aefad227','2018-11-07 10:11:26'),(2,'2018-10-28 22:38:40','4492b8e2-d984-42b0-b8f0-80bfb5672d78','6f5975c7-334d-43d9-990f-a949cf37f1db','2018-09-29 23:38:40','bb1acf3f-2bff-4e18-afdc-44ac617cc713','2018-10-08 23:38:40'),(3,'2018-11-23 14:15:25','9db21dd5-6427-4469-9212-846c5b5c2ff3','ef7ec8c9-0b13-4d14-b355-2b88f7eccb40','2018-10-25 15:15:25','694d08b6-9aa7-4c49-9663-5ccdbf49e732','2018-11-03 14:15:25'),(4,'2018-11-23 14:21:16','15d5b583-889d-45a8-a92a-c20e3a6c97db','a8732371-f897-4cce-b53b-22938034c0d6','2018-10-25 15:21:16','db381aba-714a-4cc9-b06f-fd2682018055','2018-11-03 14:21:16'),(5,'2018-11-23 16:29:27','18bf0c13-85d8-4be3-bc82-e2d54f86a2c8','ea20f16d-677c-46d5-ac3e-2686577c7691','2018-10-25 17:29:27','8718bbd4-c71f-4e25-b076-ca9e7e402d04','2018-11-03 16:29:27'),(6,'2018-11-27 11:23:01','5c23a1a5-daa0-41f0-9c2d-257ff28e2f75','87ae8cdf-9894-4379-b9b5-2b424b5ccd3d','2018-10-29 11:23:01','b486572f-0e1b-4f37-82f0-6703bd946f30','2018-11-07 11:23:01'),(7,'2018-11-27 11:25:07','3f5855f8-8d88-4af2-8f3d-2ea24f7c1f5c','616dff3e-96f4-4141-9328-9d32a4bb3d1e','2018-10-29 11:25:07','34be1a99-bea5-4514-bd03-043f6b532105','2018-11-07 11:25:07'),(8,'2018-11-27 11:25:35','c108196d-af03-4c2e-9c61-77c21d685c93','1a28d216-36e4-4601-a359-0098506e4fb4','2018-10-29 11:25:35','0cb4b7cb-4186-4943-b108-1b0fe0ee592c','2018-11-07 11:25:35'),(9,'2018-11-27 11:26:46','51dd0f10-5363-49e8-95d3-de0221149fd0','61255498-e3d1-444b-86c5-282215bbf9c4','2018-10-29 11:26:46','1907ea3f-f330-4e32-ad97-588f06d184cc','2018-11-07 11:26:46'),(10,'2018-11-27 11:28:32','1d03a415-1fcd-4aca-a5f6-51ca5ee22d1f','96ca76bc-a6a5-4819-bc14-20a953115ebf','2018-10-29 11:28:32','9b13bd65-8b18-48c2-bf7d-7b5157761387','2018-11-07 11:28:32'),(11,'2018-11-27 11:28:55','5ff45f56-d121-4e95-ae38-4dd3a0e66620','d6942ace-2ac6-40e0-ab41-4bd92b547aab','2018-10-29 11:28:55','001d8e70-4526-4d20-a2b8-5a4876ade468','2018-11-07 11:28:55'),(12,'2018-11-27 11:29:13','f784e6cf-04b3-48e7-a571-b2d711d4e2c5','26f3573b-d324-4c97-9f18-874a655cb043','2018-10-29 11:29:13','49f9e1a9-b419-4556-9ea0-a0005a25e6ef','2018-11-07 11:29:13'),(13,'2018-11-27 11:30:08','503cd888-8f3b-4591-a763-2d8f12d91ccb','f5e5d0d4-5a91-468f-9f3c-d466db06f8a4','2018-10-29 11:30:08','cbe6288b-55a8-4f2f-8444-f29bf4a8214c','2018-11-07 11:30:08'),(14,'2018-11-27 15:17:13','e24481a8-caab-49dd-a285-4f1d9c7a7af1','7991ef73-1830-4ff3-a06f-1cbf41e6ea98','2018-10-29 15:17:13','816c2baa-a95a-4461-9351-b7eef023f39b','2018-11-07 15:17:13'),(15,'2018-11-27 15:46:41','3281f6ef-f4f3-4b66-8df2-a86a34ea13bb','167038fd-85aa-4dfe-88a9-b42716b0877c','2018-10-29 15:46:41','46f89e61-ff99-4e2a-9480-f6455da0abe7','2018-11-07 15:46:41'),(16,'2018-11-28 17:28:11','1b140c8e-cae0-43e3-9ee2-524573a4d2ff','66d41a2a-a272-4995-b06e-8d5404315b3e','2018-10-30 17:28:11','12b92a1f-c91d-4842-9b4b-122fe3dff809','2018-11-08 17:28:11'),(17,'2018-11-28 17:38:18','963fb601-664c-406a-b8fe-ed2dc74d8a85','5f9ccfe5-3129-4ca9-a084-33388cd19ae8','2018-10-30 17:38:18','fa8d1232-bd4f-4f87-9efe-527cb4aec889','2018-11-08 17:38:18'),(18,'2018-11-28 18:10:12','579c0227-1be8-4c35-805d-99c89f522bf6','5de09fe2-13da-406a-9634-9dec7708adf6','2018-10-30 18:10:12','f90450aa-bbdc-4d3e-a305-e9f995775e9c','2018-11-08 18:10:12'),(19,'2018-11-28 18:12:30','8ecad23e-fcf5-41f7-849c-c4f3d27eabb8','8361adc2-a1aa-425c-918d-6254805d43f8','2018-10-30 18:12:30','09864449-a1e2-4609-a181-064f9be0413d','2018-11-08 18:12:30'),(20,'2018-11-28 19:57:23','89d12183-56e7-4440-a32e-a07847dec8c8','ec33467a-9c49-4fcc-a39f-1908be00dc29','2018-10-30 19:57:23','4d1a985b-5f4c-45e0-a19b-4628c3823daa','2018-11-08 19:57:23'),(21,'2018-11-30 17:22:19','73b435a6-dc7b-4817-ab1e-eda0acea5d50','5757d49f-95b5-4faa-9a84-0dfeedc75d34','2018-11-01 17:22:19','b3d51193-14f5-46b2-9a23-a8cc2ba97a65','2018-11-10 17:22:19'),(22,'2018-11-30 17:23:11','d0025f3c-8ef6-4b91-9458-9ddc3c2a693c','8d99dfea-e4f8-4105-9313-9f70d15462e0','2018-11-01 17:23:11','105fa02e-67b2-4738-bdb7-42a6a600998d','2018-11-10 17:23:11'),(23,'2018-11-30 17:23:52','b838d3ea-7fd4-4cca-88bd-94a7ec2b6a49','02bd17aa-5b3e-4acc-bc67-befeffbc09a4','2018-11-01 17:23:52','50367dca-13d7-484d-b66a-6e957dc5db68','2018-11-10 17:23:52');
/*!40000 ALTER TABLE `user_token` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-10-31 19:05:48
