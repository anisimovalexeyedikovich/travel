-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: travel_diary
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cultural_sites`
--

DROP TABLE IF EXISTS `cultural_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cultural_sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `travel_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `photo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `travel_id` (`travel_id`),
  CONSTRAINT `cultural_sites_ibfk_1` FOREIGN KEY (`travel_id`) REFERENCES `travels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cultural_sites`
--

LOCK TABLES `cultural_sites` WRITE;
/*!40000 ALTER TABLE `cultural_sites` DISABLE KEYS */;
INSERT INTO `cultural_sites` VALUES (1,1,'Эрмитаж','Крупнейший в России художественный и культурно-исторический музей','https://images.unsplash.com/photo-1556610961-2fecc5927173','2025-03-21 08:23:35'),(2,2,'Денисова пещера','Уникальный археологический памятник','https://images.unsplash.com/photo-1556610961-2fecc5927173','2025-03-21 08:23:35'),(3,3,'Успенский собор','Выдающийся памятник белокаменного зодчества','https://images.unsplash.com/photo-1556610961-2fecc5927173','2025-03-21 08:23:35');
/*!40000 ALTER TABLE `cultural_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `photos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `travel_id` int DEFAULT NULL,
  `photo_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_main` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `travel_id` (`travel_id`),
  CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`travel_id`) REFERENCES `travels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photos`
--

LOCK TABLES `photos` WRITE;
/*!40000 ALTER TABLE `photos` DISABLE KEYS */;
INSERT INTO `photos` VALUES (1,1,'https://images.unsplash.com/photo-1556610961-2fecc5927173','Исаакиевский собор',1,'2025-03-21 08:23:35'),(2,1,'https://images.unsplash.com/photo-1556610961-2fecc5927173','Петергоф',0,'2025-03-21 08:23:35'),(3,2,'https://images.unsplash.com/photo-1556610961-2fecc5927173','Телецкое озеро',1,'2025-03-21 08:23:35'),(4,2,'https://images.unsplash.com/photo-1556610961-2fecc5927173','Гора Белуха',0,'2025-03-21 08:23:35'),(5,3,'https://images.unsplash.com/photo-1556610961-2fecc5927173','Золотые ворота',1,'2025-03-21 08:23:35');
/*!40000 ALTER TABLE `photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `travels`
--

DROP TABLE IF EXISTS `travels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `travels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'RUB',
  `safety_rating` int DEFAULT NULL,
  `transport_rating` int DEFAULT NULL,
  `population_rating` int DEFAULT NULL,
  `nature_rating` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `travels_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `travels_chk_1` CHECK ((`safety_rating` between 1 and 5)),
  CONSTRAINT `travels_chk_2` CHECK ((`transport_rating` between 1 and 5)),
  CONSTRAINT `travels_chk_3` CHECK ((`population_rating` between 1 and 5)),
  CONSTRAINT `travels_chk_4` CHECK ((`nature_rating` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travels`
--

LOCK TABLES `travels` WRITE;
/*!40000 ALTER TABLE `travels` DISABLE KEYS */;
INSERT INTO `travels` VALUES (1,2,'Путешествие в Санкт-Петербург','Незабываемое путешествие в культурную столицу России','Санкт-Петербург',59.93430000,30.33510000,'2024-06-01','2024-06-07',45000.00,'RUB',5,5,4,4,'2025-03-21 08:23:35'),(2,2,'Горный Алтай','Поход по живописным местам Алтая','Республика Алтай',51.95810000,85.96030000,'2024-07-15','2024-07-25',65000.00,'RUB',4,3,2,5,'2025-03-21 08:23:35'),(3,3,'Золотое кольцо России','Тур по историческим городам','Владимир',56.13660000,40.39660000,'2024-05-10','2024-05-17',35000.00,'RUB',5,4,3,4,'2025-03-21 08:23:35');
/*!40000 ALTER TABLE `travels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '/assets/img/default-avatar.jpg',
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin123','admin@travel.ru','/assets/img/default-avatar.jpg',1,'2025-03-21 08:23:35'),(2,'ivan','ivan123','ivan@mail.ru','/assets/img/default-avatar.jpg',0,'2025-03-21 08:23:35'),(3,'maria','maria123','maria@mail.ru','/assets/img/default-avatar.jpg',0,'2025-03-21 08:23:35');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-21 12:28:42
