-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: viaduct.proxy.rlwy.net    Database: railway
-- ------------------------------------------------------
-- Server version	9.4.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alertas`
--

DROP TABLE IF EXISTS `alertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alertas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `tipo` enum('stock_bajo','vencimiento','vencido') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leida` tinyint(1) DEFAULT '0',
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas`
--

LOCK TABLES `alertas` WRITE;
/*!40000 ALTER TABLE `alertas` DISABLE KEYS */;
INSERT INTO `alertas` VALUES (1,6,'vencimiento','Por vencer: \"Jugo de China 1L\" vence el 2026-05-15 (7 días)',1,'2026-05-08 13:53:42'),(2,7,'vencimiento','Por vencer: \"Leche Entera 1L\" vence el 2026-05-10 (2 días)',1,'2026-05-08 13:53:42'),(4,2,'stock_bajo','Stock bajo: \"Aceite Vegetal 1L\" tiene 7 unidades (mínimo: 8)',1,'2026-05-08 18:01:04'),(5,2,'stock_bajo','Stock bajo: \"Aceite Vegetal 1L\" tiene 6 unidades (mínimo: 8)',1,'2026-05-10 23:48:33'),(6,6,'vencimiento','Por vencer: \"Jugo de China 1L\" vence el 2026-05-15 (5 días)',1,'2026-05-10 23:48:33'),(7,10,'stock_bajo','Stock bajo: \"Jabon en Polvo 500g\" tiene 6 unidades (mínimo: 8)',1,'2026-05-12 17:57:49'),(8,10,'stock_bajo','Stock bajo: \"Jabon en Polvo 500g\" tiene 6 unidades (mínimo: 8)',1,'2026-05-14 00:30:48'),(9,8,'vencimiento','Por vencer: \"Queso Blanco 500g\" vence el 2026-05-20 (6 días)',1,'2026-05-14 00:30:48'),(10,5,'stock_bajo','Stock bajo: \"Agua Purificada 1.5L\" tiene 19 unidades (mínimo: 20)',1,'2026-05-17 07:20:42'),(11,5,'vencimiento','Por vencer: \"Agua Purificada 1.5L\" vence el 2026-05-17 (0 días)',1,'2026-05-17 15:00:22'),(12,31,'stock_bajo','Stock bajo: \"Aceituna Verde 250g\" tiene 1 unidades (mínimo: 5)',1,'2026-05-17 21:09:01'),(13,31,'stock_bajo','Stock bajo: \"Aceituna Verde 250g\" tiene 1 unidades (mínimo: 5)',0,'2026-05-18 03:03:24'),(14,7,'vencimiento','Por vencer: \"Leche Entera 1L\" vence el 2026-05-25 (7 días)',0,'2026-05-18 03:03:24'),(15,7,'vencimiento','Por vencer: \"Leche Entera 1L\" vence el 2026-05-25 (6 días)',0,'2026-05-19 03:10:02'),(16,7,'vencimiento','Por vencer: \"Leche Entera 1L\" vence el 2026-05-25 (4 días)',0,'2026-05-21 03:26:28'),(17,7,'vencimiento','Por vencer: \"Leche Entera 1L\" vence el 2026-05-25 (3 días)',0,'2026-05-22 18:42:27');
/*!40000 ALTER TABLE `alertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alertas_whatsapp`
--

DROP TABLE IF EXISTS `alertas_whatsapp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alertas_whatsapp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` enum('stock_bajo','vencimiento','sin_alerta') NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `leido` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas_whatsapp`
--

LOCK TABLES `alertas_whatsapp` WRITE;
/*!40000 ALTER TABLE `alertas_whatsapp` DISABLE KEYS */;
INSERT INTO `alertas_whatsapp` VALUES (1,'stock_bajo','⚠️ Stock Bajo: Hay productos con stock bajo. Revisa el inventario.','2026-05-17 07:20:04',1),(2,'stock_bajo','⚠️ Stock Bajo: Hay productos con stock bajo. Revisa el inventario.','2026-05-17 07:20:14',1),(3,'stock_bajo','⚠️ Stock Bajo: Hay productos con stock bajo. Revisa el inventario.','2026-05-17 07:27:30',1),(4,'stock_bajo','⚠️ Stock Bajo: Hay productos con stock bajo. Revisa el inventario.','2026-05-17 07:28:15',1),(5,'stock_bajo','⚠️ Stock Bajo: Hay productos con stock bajo. Revisa el inventario.','2026-05-17 07:31:48',1),(6,'vencimiento','⚠️ Productos por Vencer: Hay productos que vencen en los próximos 7 días.','2026-05-17 07:31:49',1);
/*!40000 ALTER TABLE `alertas_whatsapp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Alimentos','Productos alimenticios no perecederos','2026-05-08 12:37:51'),(2,'Bebidas','Refrescos, jugos, agua y bebidas energeticas','2026-05-08 12:37:51'),(3,'Lacteos','Leche, queso, yogur y derivados','2026-05-08 12:37:51'),(4,'Limpieza','Productos de limpieza del hogar','2026-05-08 12:37:51'),(5,'Higiene Personal','Articulos de higiene y cuidado personal','2026-05-08 12:37:51'),(6,'Miscelanea','Otros productos variados','2026-05-08 12:37:51');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Cliente General','000-0000000-0','000-000-0000',NULL,NULL,1,NULL,'2026-05-08 12:37:51'),(2,'Ana Perez','001-1234567-8','809-555-2001',NULL,NULL,1,NULL,'2026-05-08 12:37:51'),(3,'Carlos Marte','002-2345678-9','809-555-2002',NULL,NULL,1,NULL,'2026-05-08 12:37:51'),(4,'Nashley Contreras Sanchez','402-1318796-2','8096601782','nashcontrSanchez@gmail.com',NULL,1,'Calle sanchez edificio 2,','2026-05-08 13:49:55'),(5,'Derek Hijo de Deurys','402-1318796-2','829-993-9411','derek.hijomio@gmail.com',NULL,1,'Calle sanchez edificio 2,','2026-05-08 15:38:09'),(6,'jenmarie','402-1318796-2','8092345678','Jenma@gmail.com',NULL,1,'Calle sanchez edificio 2,','2026-05-08 15:45:16'),(7,'Juan De La Rosa','402-23456-1','849-099-0000','ambardelarosa@gmail.com',NULL,1,'Calle sanchez edificio 2,','2026-05-08 16:12:55'),(8,'Belice Moquete','402-1325469-1','829-789-9009','belice.moquete@gmail.com','$2y$10$rhuq5CstvXB4nzW/mrptr.MgXGvINbUMvVXul2pxsyNJleggsBpPe',1,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','2026-05-08 17:01:14'),(9,'Wilbert Cordero','402-9084520-1','8299133672','WILSINCORDEROB@GMAIL.COM',NULL,1,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','2026-05-08 17:28:51'),(10,'Luisa Albania','402-0912877-2','809-674-6892','albaniaywilbert2015@gmail.com','$2y$12$/k/RzJABeJHbE.1gVzDm9ObbYDbzthdjLAYBklLaLS/l6GnPlUHd.',1,'Calle Fray Antón de Montesinos 45','2026-05-10 19:10:02'),(11,'Elias Sanchez','402-0912877-1','829-524-7856','sanchezsotoelias21@gmail.com',NULL,1,'Calle Fray Antón de Montesinos 45','2026-05-10 20:35:11'),(12,'Deurys','402-0912877-0','809-674-6892','deurys35@gmail.com',NULL,1,'Calle Fray Antón de Montesinos 45','2026-05-10 20:36:56'),(13,'Elias Soto','402-2127564-0','809-674-6892','sanchezsotoelias21@gmail.com',NULL,1,'Calle Fray Antón de Montesinos 45','2026-05-10 23:29:00'),(14,'Deurys Moquete','001-1234567-5','809-674-6892','kabra7170@gmail.com',NULL,1,'Calle Fray Antón de Montesinos 45','2026-05-10 23:30:16'),(15,'Deurs el mejor','402-1318796-9','8299133672','wilsincorderob@gmail.com',NULL,1,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','2026-05-11 04:09:59'),(16,'Vismairy Santana','402-1325469-9','849-802-6097','santanafelizv@gmail.com',NULL,1,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','2026-05-11 14:35:10'),(17,'Rachel Dazla','402-1318796-7','8299133672','racheldashla@gmail.com',NULL,1,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','2026-05-12 14:47:20'),(18,'Melvin Berroa','0123021303000','8297422409','melvinberroa09882@gmail.com',NULL,1,'calle#32','2026-05-13 11:45:42'),(19,'Amín Almonte','402-0912877-6','829-789-9000','amin305622@gmail.com',NULL,1,'Calle Fray Antón de Montesinos 45','2026-05-14 02:53:49'),(20,'Ambar De La Ros','402-1318796-1','8299133672','delarosanicolambar@gmail.com',NULL,1,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','2026-05-15 14:43:47'),(21,'Vismairy Santana Feliz','001-1234567-0','829-980-7849','vismairysantana@gmail.com','$2y$12$4glUUUeKjao.ZY4DhGilp.seisXK/.mCOOi8/8zCeXrd2M2Iml7TK',1,'Calle sanchez edificio 2,','2026-05-15 16:10:39'),(22,'Wilmer Zair','402-1318796-2','829-789-9009','zair.cordero@gmail.com',NULL,1,'Calle Fray Antón de Montesinos 45','2026-05-17 16:18:43'),(25,'alla','3435657568568','8299133672','WILSINCORDEROB@GMAIL.COM',NULL,1,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','2026-05-21 21:57:53'),(26,'ddsds','3435657568568','5043522153','',NULL,1,'7404 Rachel st marrero la 70072','2026-05-21 22:00:09'),(27,'ggg','001-1234567-8','849-857-2028','colamdo.yeya@gmail.com',NULL,1,'Calle Fray Antón de Montesinos 45','2026-05-22 22:07:48');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proveedor_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `num_orden` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estado` enum('pendiente','recibida','pagada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_orden` (`num_orden`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_compras`
--

DROP TABLE IF EXISTS `detalle_compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_compras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `compra_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `compra_id` (`compra_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_compras_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  CONSTRAINT `detalle_compras_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_compras`
--

LOCK TABLES `detalle_compras` WRITE;
/*!40000 ALTER TABLE `detalle_compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_pedidos`
--

DROP TABLE IF EXISTS `detalle_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_pedidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pedido_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedidos`
--

LOCK TABLES `detalle_pedidos` WRITE;
/*!40000 ALTER TABLE `detalle_pedidos` DISABLE KEYS */;
INSERT INTO `detalle_pedidos` VALUES (1,1,4,5,95.00,475.00),(2,2,8,15,175.00,2625.00),(3,6,9,2,55.00,110.00),(4,7,2,1,155.00,155.00),(5,8,9,1,55.00,55.00),(6,9,5,1,35.00,35.00),(7,10,7,1,90.00,90.00),(8,12,1,1,130.00,130.00),(9,12,5,1,35.00,35.00),(10,13,9,1,55.00,55.00),(11,14,9,1,55.00,55.00),(12,15,11,2,95.00,190.00),(13,16,5,1,35.00,35.00),(14,17,12,1,75.00,75.00),(15,18,9,1,55.00,55.00),(16,19,1,1,130.00,130.00),(17,20,5,1,35.00,35.00),(18,21,10,1,85.00,85.00),(19,22,10,1,85.00,85.00),(20,23,1,1,130.00,130.00),(21,24,2,1,155.00,155.00),(22,24,5,1,35.00,35.00),(23,25,6,2,70.00,140.00),(24,26,6,1,70.00,70.00),(25,27,1,1,130.00,130.00),(26,28,2,2,155.00,310.00),(27,29,1,1,130.00,130.00),(28,30,11,1,95.00,95.00),(29,30,11,1,95.00,95.00),(30,31,9,1,55.00,55.00),(31,32,2,10,155.00,1550.00),(32,33,5,1,35.00,35.00),(33,34,5,1,35.00,35.00),(34,34,31,1,90.00,90.00),(35,35,9,4,55.00,220.00),(36,36,13,1,110.00,110.00);
/*!40000 ALTER TABLE `detalle_pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_ventas`
--

DROP TABLE IF EXISTS `detalle_ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_ventas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `venta_id` (`venta_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ventas`
--

LOCK TABLES `detalle_ventas` WRITE;
/*!40000 ALTER TABLE `detalle_ventas` DISABLE KEYS */;
INSERT INTO `detalle_ventas` VALUES (1,2,6,20,70.00,1400.00),(2,3,8,15,175.00,2625.00),(3,4,2,1,155.00,155.00),(4,4,5,1,35.00,35.00),(5,5,5,3,35.00,105.00),(6,6,5,3,35.00,105.00),(7,7,1,1,130.00,130.00),(8,7,9,1,55.00,55.00),(9,8,1,2,130.00,260.00),(10,8,5,3,35.00,105.00),(11,9,2,1,155.00,155.00),(12,9,5,1,35.00,35.00),(13,10,1,1,130.00,130.00),(14,10,5,1,35.00,35.00),(15,11,10,1,85.00,85.00),(16,12,1,1,130.00,130.00),(17,13,2,1,155.00,155.00),(18,13,5,1,35.00,35.00),(19,14,1,1,130.00,130.00),(20,15,9,1,55.00,55.00),(21,17,21,1,130.00,130.00),(22,17,22,1,55.00,55.00),(23,18,1,5,130.00,650.00),(24,19,5,1,35.00,35.00),(25,19,31,1,90.00,90.00),(26,20,9,4,55.00,220.00);
/*!40000 ALTER TABLE `detalle_ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensajes`
--

DROP TABLE IF EXISTS `mensajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensajes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asunto` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leido` tinyint(1) DEFAULT '0',
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensajes`
--

LOCK TABLES `mensajes` WRITE;
/*!40000 ALTER TABLE `mensajes` DISABLE KEYS */;
INSERT INTO `mensajes` VALUES (5,'Melvin Bereo','melvin.berea@gmail.com','Soporte técnico','ayudame',0,'2026-05-13 11:42:15'),(6,'Elias','sanchezsotoelias21@gmail.com','Soporte técnico','auuuu',0,'2026-05-21 12:30:19'),(7,'Ambar Dee la Rosa','ambar.rosa@gmil.com','Propuesta de mejora','holi soy sakura maids',0,'2026-05-22 13:43:57');
/*!40000 ALTER TABLE `mensajes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `num_pedido` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','en_proceso','listo','entregado','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `tipo` enum('mostrador','domicilio') COLLATE utf8mb4_unicode_ci DEFAULT 'mostrador',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notas` text COLLATE utf8mb4_unicode_ci,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_pedido` (`num_pedido`),
  KEY `cliente_id` (`cliente_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (1,NULL,4,'PED-2B51A3','entregado','mostrador',475.00,475.00,'','2026-05-08 15:38:58'),(2,5,4,'PED-A0F20C','entregado','mostrador',2625.00,2625.00,'Espero que le guste','2026-05-08 15:43:22'),(3,8,1,'PED-CLI-39A85B','entregado','domicilio',190.00,340.00,'','2026-05-08 17:02:11'),(4,8,1,'PED-CLI-88ED44','pendiente','domicilio',185.00,335.00,'','2026-05-10 19:04:08'),(5,10,1,'PED-CLI-CBA667','pendiente','domicilio',365.00,515.00,'Frente al Pichy guey','2026-05-10 19:11:08'),(6,11,4,'PED-BDB558','pendiente','mostrador',110.00,110.00,'qlok','2026-05-10 20:35:23'),(7,12,4,'PED-16BD54','pendiente','mostrador',155.00,155.00,'fff','2026-05-10 20:37:05'),(8,11,4,'PED-60DD30','entregado','mostrador',55.00,55.00,'hhhh','2026-05-10 21:00:38'),(9,12,4,'PED-9399B3','en_proceso','mostrador',35.00,35.00,'','2026-05-10 21:02:49'),(10,12,4,'PED-3B2C43','entregado','mostrador',90.00,90.00,'dede','2026-05-10 21:09:39'),(11,8,1,'PED-CLI-DC4D48','pendiente','domicilio',190.00,340.00,'','2026-05-10 22:02:37'),(12,8,1,'PED-CLI-1C310A','pendiente','domicilio',165.00,315.00,'Frente al aprque','2026-05-10 22:49:37'),(13,12,4,'PED-76C406','pendiente','mostrador',55.00,55.00,'dd','2026-05-10 23:09:43'),(14,12,4,'PED-B5DB09','entregado','mostrador',55.00,55.00,'vv','2026-05-10 23:21:47'),(15,11,4,'PED-E2864C','entregado','mostrador',190.00,190.00,'fff','2026-05-10 23:27:10'),(16,13,4,'PED-9EF8A5','entregado','mostrador',35.00,35.00,'f','2026-05-10 23:29:13'),(17,14,4,'PED-1995D2','entregado','mostrador',75.00,75.00,'','2026-05-10 23:30:25'),(18,14,4,'PED-77F1C2','entregado','mostrador',55.00,55.00,'vv','2026-05-11 03:47:19'),(19,14,4,'PED-888F08','entregado','mostrador',130.00,130.00,'bb','2026-05-11 03:49:28'),(20,15,4,'PED-63ECF5','entregado','mostrador',35.00,35.00,'la cabra','2026-05-11 04:10:46'),(21,8,1,'PED-CLI-D39ECC','pendiente','domicilio',85.00,235.00,'','2026-05-11 04:11:57'),(22,16,4,'PED-1E607B','entregado','mostrador',85.00,85.00,'qlok','2026-05-11 14:36:01'),(23,8,1,'PED-CLI-B8DF8C','pendiente','domicilio',130.00,280.00,'','2026-05-11 14:37:47'),(24,8,1,'PED-CLI-8AD282','pendiente','domicilio',190.00,340.00,'','2026-05-11 19:16:24'),(25,17,4,'PED-F3A532','entregado','mostrador',140.00,140.00,'Hola','2026-05-12 14:47:59'),(26,17,4,'PED-31FADA','entregado','mostrador',70.00,70.00,'','2026-05-12 14:49:39'),(27,18,4,'PED-8BD28A','entregado','mostrador',130.00,130.00,'alla','2026-05-13 11:46:32'),(28,19,4,'PED-7A3136','entregado','mostrador',310.00,310.00,'alla','2026-05-14 02:54:31'),(29,8,1,'PED-CLI-91509C','pendiente','domicilio',130.00,280.00,'','2026-05-14 12:13:13'),(30,20,4,'PED-4B6BBB','entregado','mostrador',270.00,270.00,'holi','2026-05-15 14:45:08'),(31,21,1,'PED-CLI-0C7540','pendiente','domicilio',55.00,205.00,'','2026-05-15 16:11:28'),(32,12,4,'PED-7F3C42','pendiente','mostrador',1550.00,1550.00,'','2026-05-15 16:13:28'),(33,14,4,'PED-A31A29','entregado','mostrador',35.00,35.00,'aaaaa','2026-05-21 03:50:18'),(34,8,1,'PED-CLI-880E9A','pendiente','domicilio',125.00,275.00,'','2026-05-21 03:59:36'),(35,8,1,'PED-CLI-D48843','pendiente','domicilio',220.00,370.00,'espero que te guste mucho','2026-05-22 13:41:49'),(36,13,4,'PED-072813','entregado','mostrador',110.00,110.00,'aaaa','2026-05-22 17:26:56');
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos_domicilio`
--

DROP TABLE IF EXISTS `pedidos_domicilio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos_domicilio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pedido_id` int NOT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia` text COLLATE utf8mb4_unicode_ci,
  `repartidor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_entrega` enum('pendiente','en_camino','entregado','fallido') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `costo_envio` decimal(10,2) DEFAULT '0.00',
  `fecha_entrega` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pedido_id` (`pedido_id`),
  CONSTRAINT `pedidos_domicilio_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos_domicilio`
--

LOCK TABLES `pedidos_domicilio` WRITE;
/*!40000 ALTER TABLE `pedidos_domicilio` DISABLE KEYS */;
INSERT INTO `pedidos_domicilio` VALUES (1,3,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','',NULL,'pendiente',150.00,NULL),(2,4,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','',NULL,'pendiente',150.00,NULL),(3,5,'Calle Fray Antón de Montesinos 45','809-674-6892','Frente al Pichy guey',NULL,'pendiente',150.00,NULL),(4,11,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','',NULL,'pendiente',150.00,NULL),(5,12,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','Frente al aprque',NULL,'pendiente',150.00,NULL),(6,21,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','',NULL,'pendiente',150.00,NULL),(7,23,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','',NULL,'pendiente',150.00,NULL),(8,24,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','',NULL,'pendiente',150.00,NULL),(9,29,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','',NULL,'pendiente',150.00,NULL),(10,31,'Calle sanchez edificio 2,','829-980-7849','',NULL,'pendiente',150.00,NULL),(11,34,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','',NULL,'pendiente',150.00,NULL),(12,35,'CALLE SÁNCHEZ, EDIFICIO 2, APARTAMENTO 3','829-789-9009','espero que te guste mucho',NULL,'pendiente',150.00,NULL);
/*!40000 ALTER TABLE `pedidos_domicilio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categoria_id` int NOT NULL,
  `proveedor_id` int NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_barras` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock_actual` int NOT NULL DEFAULT '0',
  `stock_minimo` int NOT NULL DEFAULT '5',
  `fecha_vencimiento` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_barras` (`codigo_barras`),
  KEY `categoria_id` (`categoria_id`),
  KEY `proveedor_id` (`proveedor_id`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,1,1,'Arroz Selecto 5lb','7400001000011',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778961377/arroz_selecto_5lb_y8ryf9.jpg',90.00,130.00,39,10,'2026-12-31',1,'2026-05-08 12:37:51'),(2,1,1,'Aceite Vegetal 1L','7400001000012',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/c_auto,g_auto,h_1.00,w_0.50/aceite_vegetal_1l_zoh61k',110.00,155.00,59,8,'2027-06-30',1,'2026-05-08 12:37:51'),(3,1,1,'Pasta Espagueti 400g','7400001000013',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778959931/pasta_espagueti_400g_vrnull.jpg',35.00,55.00,60,15,'2027-03-01',1,'2026-05-08 12:37:51'),(4,2,2,'Refresco Cola 2L','7400002000021',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778959932/refresco_cola_2l_yl2axz.jpg',65.00,95.00,48,12,'2026-08-15',1,'2026-05-08 12:37:51'),(5,2,2,'Agua Purificada 1.5L','7400002000022',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/c_auto,g_auto,h_1.00,w_0.50/agua_purificada_1_5l_xzjrqr',20.00,35.00,85,20,'2026-05-17',1,'2026-05-08 12:37:51'),(6,2,2,'Jugo de China 1L','7400002000023',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778961377/jugo_china_1l_nqznjs.jpg',45.00,70.00,80,10,'2026-05-31',1,'2026-05-08 12:37:51'),(7,3,3,'Leche Entera 1L','7400003000031',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778961377/leche_entera_1l_shdo94.jpg',60.00,90.00,25,10,'2026-05-25',1,'2026-05-08 12:37:51'),(8,3,3,'Queso Blanco 500g','7400003000032',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778959931/queso_blanco_500g_vc3kka.jpg',120.00,175.00,70,5,'2030-12-31',1,'2026-05-08 12:37:51'),(9,4,4,'Cloro 1L','7400004000041',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778961807/cloro_1l_tv9jbe.jpg',30.00,55.00,34,10,'2027-12-31',1,'2026-05-08 12:37:51'),(10,4,4,'Jabon en Polvo 500g','7400004000042',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778961377/jabon_polvo_500g_sol1gf.jpg',55.00,85.00,50,8,'2028-01-01',1,'2026-05-08 12:37:51'),(11,5,1,'Jabon de Bano x3','7400005000051',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778959931/jabon_bano_x3_ogmp1v.jpg',60.00,95.00,35,10,'2028-06-30',1,'2026-05-08 12:37:51'),(12,5,1,'Pasta Dental 100ml','7400005000052',NULL,'https://res.cloudinary.com/dutvxrjml/image/upload/v1778961377/pasta_dental_100ml_sdnpoo.jpg',45.00,75.00,28,8,'2027-09-30',1,'2026-05-08 12:37:51'),(13,1,1,'Arroz Blanco 5lb','ARR-BL-5LB','Arroz blanco 5 libras','arroz_blanco_5lb.jfif',85.00,110.00,50,10,NULL,1,'2026-05-16 19:26:40'),(14,3,1,'Leche Evaporada 410g','LEC-EV-410G','Leche evaporada 410g','leche_evaporada_410g.jfif',55.00,75.00,40,10,NULL,1,'2026-05-16 19:26:40'),(15,1,1,'Pasta Tomate 200g','PAS-TOM-200G','Pasta de tomate 200g','pasta_tomate_200g.jfif',25.00,40.00,50,10,NULL,1,'2026-05-16 19:26:40'),(16,1,1,'Frijoles Negros 400g','FRI-NEG-400G','Frijoles negros 400g','frijoles_negros_400g.jfif',35.00,55.00,40,10,NULL,1,'2026-05-16 19:26:40'),(17,1,1,'Avena Instantanea 500g','AVE-INS-500G','Avena instantánea 500g','avena_instantanea_500g.jfif',45.00,65.00,6,10,NULL,1,'2026-05-16 19:26:40'),(18,6,1,'Vinagre Blanco 500ml','VIN-BL-500ML','Vinagre blanco 500ml','vinagre_blanco_500ml.jfif',30.00,45.00,30,5,NULL,1,'2026-05-16 19:26:40'),(19,1,1,'Azucar Blanca 2lb','AZU-BL-2LB','Azúcar blanca 2 libras','azucar_blanca_2lb.jfif',40.00,60.00,50,10,NULL,1,'2026-05-16 19:26:40'),(20,6,1,'Sal Yodada 500g','SAL-YOD-500G','Sal yodada 500g','https://res.cloudinary.com/dutvxrjml/image/upload/v1779071872/1_hebmwj.jpg',15.00,25.00,60,10,NULL,1,'2026-05-16 19:26:40'),(21,2,1,'Cafe Molido 250g','CAF-MOL-250G','Café molido 250g','https://res.cloudinary.com/dutvxrjml/image/upload/v1779071873/10_tyeifl.jpg',90.00,130.00,29,5,NULL,1,'2026-05-16 19:26:40'),(22,1,1,'Galletas Vainilla 12u','GAL-VAI-12U','Galletas de vainilla 12 unidades','https://res.cloudinary.com/dutvxrjml/image/upload/v1779071872/3_lwngw8.jpg',35.00,55.00,39,10,NULL,1,'2026-05-16 19:26:40'),(23,1,1,'Mayonesa 500ml','MAY-500ML','Mayonesa 500ml','mayonesa_500ml.jfif',65.00,95.00,30,5,NULL,1,'2026-05-16 19:26:40'),(24,1,1,'Salsa Tomate 400g','SAL-TOM-400G','Salsa de tomate 400g','salsa_tomate_400g.jfif',40.00,60.00,35,10,NULL,1,'2026-05-16 19:26:40'),(25,1,1,'Atun Enlatado 170g','ATU-ENL-170G','Atún enlatado 170g','atun_enlatado_170g.jfif',45.00,70.00,40,10,NULL,1,'2026-05-16 19:26:40'),(26,3,1,'Mantequilla 250g','MAN-250G','Mantequilla 250g','mantequilla_250g.jfif',75.00,105.00,25,5,NULL,1,'2026-05-16 19:26:40'),(27,4,3,'Detergente Liquido 1L','DET-LIQ-1L','Detergente líquido 1 litro','detergente_liquido_1l.jfif',80.00,120.00,25,5,NULL,1,'2026-05-16 19:26:40'),(28,5,3,'Papel Higienico x4','PAP-HIG-X4','Papel higiénico paquete x4','papel_higienico_x4.jfif',55.00,85.00,40,10,NULL,1,'2026-05-16 19:26:40'),(29,4,3,'Suavizante Ropa 500ml','SUA-ROP-500ML','Suavizante de ropa 500ml','suavizante_ropa_500ml.jfif',70.00,100.00,44,5,'2026-05-31',1,'2026-05-16 19:26:40'),(30,5,3,'Jabon de Barra 3u','JAB-BAR-3U','Jabón de barra 3 unidades','jabon_barra_3u.jfif',45.00,65.00,35,10,NULL,1,'2026-05-16 19:26:40'),(31,1,1,'Aceituna Verde 250g','ACE-VER-250G','Aceitunas verdes 250g','https://res.cloudinary.com/dutvxrjml/image/upload/v1779071872/2_yhzjex.jpg',60.00,90.00,77,5,NULL,1,'2026-05-16 19:26:40'),(32,1,1,'Harina Trigo 1kg','HAR-TRI-1KG','Harina de trigo 1kg','https://res.cloudinary.com/dutvxrjml/image/upload/v1779071872/4_kcn3ki.jpg',50.00,75.00,40,10,NULL,1,'2026-05-16 19:26:40'),(33,1,1,'Huevos Blancos 12u','HUE-BL-12U','Huevos blancos docena','https://res.cloudinary.com/dutvxrjml/image/upload/v1779071873/5_quqemx.jpg',90.00,130.00,30,5,NULL,1,'2026-05-16 19:26:40'),(34,3,2,'Mantequilla 200g','MAN-200G','Mantequilla 200g','https://res.cloudinary.com/dutvxrjml/image/upload/v1779071872/6_kes7d7.jpg',65.00,95.00,25,5,NULL,1,'2026-05-16 19:26:40'),(35,1,1,'Sardinas Enlatadas 155g','SAR-ENL-155G','Sardinas enlatadas 155g','https://res.cloudinary.com/dutvxrjml/image/upload/v1779071873/9_v8y0wg.jpg',40.00,60.00,35,10,NULL,1,'2026-05-16 19:26:40'),(36,3,2,'Sal Yodada 1lb','SAL-YOD-1LB','Sal yodada 1 libra','https://res.cloudinary.com/dutvxrjml/image/upload/v1779071873/8_bl95xd.jpg',12.00,20.00,50,10,NULL,1,'2026-05-16 19:26:40');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rnc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Distribuidora El Sol','101-23456-7','809-555-1001','elsol@gmail.com','Av. Duarte 45, Santo Domingo',1,'2026-05-08 12:37:51'),(2,'Comercial Ramirez','102-34567-8','809-555-1002','ramirez@gmail.com','Calle 5, Zona Industrial',1,'2026-05-08 12:37:51'),(3,'Lacteos del Norte','103-45678-9','809-555-1003','lacteosnorte@gmail.com','Av. 27 de Febrero 112',1,'2026-05-08 12:37:51'),(4,'Limpieza Total SA','104-56789-0','809-555-1004','limpiezatotal@gmail.com','Calle Mella 88',1,'2026-05-08 12:37:51'),(5,'Sakura Maids','478672656276','8299133672','ambardelarosa@gmail.com','alli',1,'2026-05-22 13:46:35');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('administrador','cajero','gerente') COLLATE utf8mb4_unicode_ci DEFAULT 'cajero',
  `activo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Admin','admin@minimarket.com','$2y$12$tX4yrlou/HZx3LHlLwIbCuB51BVbDr3XNiDEOWsZB0m0JGe9HHg3.','cajero',1,'2026-05-08 12:37:51'),(2,'Juan Cajero','cajero@minimarket.com','$2y$12$m5Ey6pNkRDvoA1/NdxwSYuxoz64PwO5OvwMR4Z/.32BI9Ieak7CVO','cajero',1,'2026-05-08 12:37:51'),(3,'Maria Gerente','gerente@minimarket.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','cajero',1,'2026-05-08 12:37:51'),(4,'Deurys Carvajal','deurys35@gmail.com','$2y$12$uTWdsixszt//D/gqrFe9feMhqk0Ibm69kWWTlSD8pIW4o37LqzIcq','administrador',1,'2026-05-08 12:41:54'),(5,'Nashla','nashlamichelle@gmail.com','$2y$12$uoEkMKzoBmsrv2J8mukik.i9qVFz.GXe3/710/bPmriN6M4adlqs.','cajero',1,'2026-05-08 15:00:46');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `v_mas_vendidos`
--

DROP TABLE IF EXISTS `v_mas_vendidos`;
/*!50001 DROP VIEW IF EXISTS `v_mas_vendidos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_mas_vendidos` AS SELECT 
 1 AS `id`,
 1 AS `nombre`,
 1 AS `total_vendido`,
 1 AS `total_ingresos`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_proximos_vencer`
--

DROP TABLE IF EXISTS `v_proximos_vencer`;
/*!50001 DROP VIEW IF EXISTS `v_proximos_vencer`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_proximos_vencer` AS SELECT 
 1 AS `id`,
 1 AS `nombre`,
 1 AS `fecha_vencimiento`,
 1 AS `dias_restantes`,
 1 AS `stock_actual`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_stock_bajo`
--

DROP TABLE IF EXISTS `v_stock_bajo`;
/*!50001 DROP VIEW IF EXISTS `v_stock_bajo`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_stock_bajo` AS SELECT 
 1 AS `id`,
 1 AS `nombre`,
 1 AS `stock_actual`,
 1 AS `stock_minimo`,
 1 AS `categoria`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_ventas_hoy`
--

DROP TABLE IF EXISTS `v_ventas_hoy`;
/*!50001 DROP VIEW IF EXISTS `v_ventas_hoy`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_ventas_hoy` AS SELECT 
 1 AS `total_ventas`,
 1 AS `ingresos_hoy`,
 1 AS `fecha`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `cliente_id` int DEFAULT NULL,
  `num_factura` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` enum('efectivo','tarjeta','transferencia') COLLATE utf8mb4_unicode_ci DEFAULT 'efectivo',
  `estado` enum('completada','anulada') COLLATE utf8mb4_unicode_ci DEFAULT 'completada',
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_factura` (`num_factura`),
  KEY `usuario_id` (`usuario_id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (2,4,4,'FAC-20260508-0444',0.00,0.00,1400.00,'tarjeta','anulada','2026-05-08 13:51:59'),(3,4,5,'FAC-20260508-8945',0.00,0.00,2625.00,'transferencia','completada','2026-05-08 15:41:42'),(4,1,8,'FAC-CLI-398524',190.00,0.00,340.00,'efectivo','completada','2026-05-08 17:02:11'),(5,4,8,'FAC-20260508-7688',0.00,0.00,105.00,'tarjeta','completada','2026-05-08 17:29:21'),(6,4,8,'FAC-20260508-4754',0.00,0.00,105.00,'tarjeta','completada','2026-05-08 17:30:11'),(7,1,8,'FAC-CLI-88D878',185.00,0.00,335.00,'efectivo','completada','2026-05-10 19:04:08'),(8,1,10,'FAC-CLI-CB9645',365.00,0.00,515.00,'efectivo','completada','2026-05-10 19:11:08'),(9,1,8,'FAC-CLI-DC0E04',190.00,0.00,340.00,'efectivo','completada','2026-05-10 22:02:37'),(10,1,8,'FAC-CLI-1BC56B',165.00,0.00,315.00,'efectivo','completada','2026-05-10 22:49:37'),(11,1,8,'FAC-CLI-D33157',85.00,0.00,235.00,'efectivo','completada','2026-05-11 04:11:57'),(12,1,8,'FAC-CLI-B8AF1B',130.00,0.00,280.00,'efectivo','completada','2026-05-11 14:37:47'),(13,1,8,'FAC-CLI-8A848F',190.00,0.00,340.00,'efectivo','completada','2026-05-11 19:16:24'),(14,1,8,'FAC-CLI-90738C',130.00,0.00,280.00,'efectivo','completada','2026-05-14 12:13:13'),(15,1,21,'FAC-CLI-0C6D66',55.00,0.00,205.00,'efectivo','anulada','2026-05-15 16:11:28'),(17,4,2,'FAC-20260518-6758',0.00,0.00,185.00,'efectivo','completada','2026-05-18 03:35:47'),(18,2,3,'FAC-20260519-9765',0.00,0.00,650.00,'efectivo','completada','2026-05-19 03:11:20'),(19,1,8,'FAC-CLI-87DEEE',125.00,0.00,275.00,'efectivo','completada','2026-05-21 03:59:36'),(20,1,8,'FAC-CLI-D45D2C',220.00,0.00,370.00,'efectivo','completada','2026-05-22 13:41:49');
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `v_mas_vendidos`
--

/*!50001 DROP VIEW IF EXISTS `v_mas_vendidos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_mas_vendidos` AS select `p`.`id` AS `id`,`p`.`nombre` AS `nombre`,sum(`dv`.`cantidad`) AS `total_vendido`,sum(`dv`.`subtotal`) AS `total_ingresos` from ((`detalle_ventas` `dv` join `productos` `p` on((`dv`.`producto_id` = `p`.`id`))) join `ventas` `v` on((`dv`.`venta_id` = `v`.`id`))) where (`v`.`estado` = 'completada') group by `p`.`id`,`p`.`nombre` order by sum(`dv`.`cantidad`) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_proximos_vencer`
--

/*!50001 DROP VIEW IF EXISTS `v_proximos_vencer`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_proximos_vencer` AS select `p`.`id` AS `id`,`p`.`nombre` AS `nombre`,`p`.`fecha_vencimiento` AS `fecha_vencimiento`,(to_days(`p`.`fecha_vencimiento`) - to_days(curdate())) AS `dias_restantes`,`p`.`stock_actual` AS `stock_actual` from `productos` `p` where ((`p`.`fecha_vencimiento` is not null) and (`p`.`fecha_vencimiento` <= (curdate() + interval 7 day)) and (`p`.`fecha_vencimiento` >= curdate()) and (`p`.`activo` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_stock_bajo`
--

/*!50001 DROP VIEW IF EXISTS `v_stock_bajo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_stock_bajo` AS select `p`.`id` AS `id`,`p`.`nombre` AS `nombre`,`p`.`stock_actual` AS `stock_actual`,`p`.`stock_minimo` AS `stock_minimo`,`c`.`nombre` AS `categoria` from (`productos` `p` join `categorias` `c` on((`p`.`categoria_id` = `c`.`id`))) where ((`p`.`stock_actual` <= `p`.`stock_minimo`) and (`p`.`activo` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_ventas_hoy`
--

/*!50001 DROP VIEW IF EXISTS `v_ventas_hoy`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_ventas_hoy` AS select count(0) AS `total_ventas`,sum(`ventas`.`total`) AS `ingresos_hoy`,cast(`ventas`.`fecha` as date) AS `fecha` from `ventas` where ((cast(`ventas`.`fecha` as date) = curdate()) and (`ventas`.`estado` = 'completada')) group by cast(`ventas`.`fecha` as date) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-22 20:14:04
