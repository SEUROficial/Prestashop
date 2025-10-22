<?php
/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_ccc` (
`id_seur_ccc` int(10) NOT NULL AUTO_INCREMENT,
`cit` varchar(10),
`ccc` varchar(5),
`nombre_personalizado` varchar(255),
`franchise` varchar(5),
`street_type` varchar(5),
`street_name` varchar(60),
`street_number` varchar(10),
`staircase` varchar(10),
`floor` varchar(10),
`door` varchar(10),
`post_code` varchar(12),
`town` varchar(50),
`state` varchar(50),
`country` varchar(15),
`phone` varchar(10),
`email` varchar(50),
`e_devoluciones` tinyint(1) NOT NULL,
`url_devoluciones` varchar(255),
`is_default` tinyint(1) NOT NULL,
`geolabel` tinyint(1) DEFAULT 0,
PRIMARY KEY (`id_seur_ccc`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_carrier` (
`id_seur_carrier` int(10) NOT NULL AUTO_INCREMENT,
`carrier_reference` int(10),
`id_seur_ccc` int(10) NOT NULL,
`shipping_type` int(10) NOT NULL,
`service` varchar(5),
`product` varchar(5),
`free_shipping` tinyint(1),
`free_shipping_weight` decimal(10,2),
`free_shipping_price` decimal(10,2),
PRIMARY KEY (`id_seur_carrier`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_services_type` (
`id_seur_services_type` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(150) NOT NULL,
PRIMARY KEY (`id_seur_services_type`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'seur2_services_type` (`id_seur_services_type`, `name`) VALUES
(1, "SEUR Nacional (España, Portugal y Andorra)"),
(2, "SEUR Puntos PickUp"),
(3, "SEUR Internacional");';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_seur_services` int(11) NOT NULL,
  `id_seur_services_type` int(11) NOT NULL, 
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';


$sql[] = 'INSERT INTO `'._DB_PREFIX_.'seur2_services` (`id_seur_services`, `id_seur_services_type`, `name`) VALUES
(1, 2, "SEUR 24"),
(3, 1, "SEUR 10"),
(7, 3, "COURIER"),
(9, 1, "SEUR 13:30"),
(13, 1, "SEUR - 72"),
(15, 1, "SEUR 48"),
(17, 3, "MARITIMO"),
(19, 3, "NETEXPRESS"),
(31, 1, "ENTREGA PARTIC"),
(57, 1, "SEUR SATURDAY"),
(77, 3, "CLASSIC"),
(83, 1, "SEUR 8:30"),
(59, 1, "PRIORITY")';



$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_seur_product` int(11) NOT NULL,
  `id_seur_services_type` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'seur2_products`
  ADD KEY `id_seur_product` (`id_seur_product`) USING BTREE';

$sql[] = "INSERT INTO `"._DB_PREFIX_."seur2_products` (`id_seur_product`, `id_seur_services_type`, `name`) VALUES
(2, 1, 'ESTANDAR'),
(18, 1, 'FRIO'),
(54, 1, 'DOCUMENTOS'),
(108, 3, 'MUESTRAS'),
(114, 3, 'FRIO INTERNACIONAL'),
(48, 2, '2SHOP'),
(118, 1, 'SEUR VINO'),
(70, 3, 'INTERNACIONAL TERRESTRE'),
(234, 3, 'VINO INTERNACIONAL'),
(222, 3, 'INTERNACIONAL AEREO'),
(104, 3, 'CROSSBORDER')";

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_order_pos` (
`id_cart` int(10) NOT NULL,
`id_seur_pos` varchar(50) NOT NULL,
`company` varchar(50) NOT NULL ,
`address` varchar(100) NOT NULL ,
`city` varchar(15) NOT NULL ,
`postal_code` varchar(12) NOT NULL ,
`timetable` varchar(50) NOT NULL,
`phone` varchar(20) NOT NULL,
PRIMARY KEY (`id_cart`,`id_seur_pos`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_order` (
`id_seur_order` int(10) NOT NULL AUTO_INCREMENT,
`id_seur_ccc` int(10) NOT NULL,
`id_order` int(10) NOT NULL,
`id_address_delivery` int(10) NOT NULL,
`id_status` int(10) NOT NULL,
`status_text` VARCHAR(100),
`id_seur_carrier` int(10) NOT NULL,
`service` varchar(5),
`product` varchar(5),
`numero_bultos` int(10) NOT NULL,
`peso_bultos` float(10) NOT NULL,
`ecb` varchar(68),
`labeled` tinyint(1) NOT NULL,
`manifested` tinyint(1) NOT NULL,
`date_labeled` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`codfee` decimal(13,6),
`cashondelivery` decimal(13,6),
`total_paid` decimal(20,6),
`firstname` varchar(32),
`lastname` varchar(32),
`id_country` int(10) NOT NULL,
`id_state` int(10) NOT NULL,
`address1` varchar(128),
`address2` varchar(128),
`postcode` varchar(12),
`city` varchar(64),
`dni` varchar(16),
`other` varchar(256),
`phone` varchar(32),
`phone_mobile` varchar(32),
PRIMARY KEY (`id_seur_order`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_pickup` (
`id_seur_pickup` int(10) NOT NULL AUTO_INCREMENT,
`localizer` varchar(20) NOT NULL,
`num_pickup` varchar(20) NOT NULL,
`tasacion` float NOT NULL,
`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id_seur_pickup`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_history` (
`id_seur_carrier` int(10) NOT NULL,
`type` varchar(3),
`active` tinyint(1),
PRIMARY KEY (`id_seur_carrier`,`type`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_status` (
`id_status` int(11) NOT NULL,
  `cod_situ` varchar(10) NOT NULL,
  `grupo` varchar(60) NOT NULL
) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=415 DEFAULT CHARSET=utf8';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'seur2_status`
  ADD PRIMARY KEY (`id_status`),
  ADD KEY `grupo` (`grupo`),
  ADD KEY `cod_situ` (`cod_situ`)';

$sql[] = "INSERT INTO `"._DB_PREFIX_."seur2_status` (`id_status`, `cod_situ`, `grupo`) VALUES
(1, 'LC002', 'EN TRÁNSITO'),
(2, 'LC001', 'EN TRÁNSITO'),
(3, 'LC005', 'EN TRÁNSITO'),
(4, 'LC006', 'EN TRÁNSITO'),
(5, 'LC101', 'EN TRÁNSITO'),
(6, 'LC003', 'EN TRÁNSITO'),
(7, 'LC004', 'EN TRÁNSITO'),
(8, 'LC845', 'EN TRÁNSITO'),
(9, 'LC860', 'EN TRÁNSITO'),
(10, 'SC845', 'EN TRÁNSITO'),
(11, 'SC883', 'EN TRÁNSITO'),
(12, 'SC999', 'EN TRÁNSITO'),
(13, 'SC001', 'EN TRÁNSITO'),
(14, 'LD221', 'EN TRÁNSITO'),
(15, 'LD223', 'EN TRÁNSITO'),
(16, 'LD846', 'EN TRÁNSITO'),
(17, 'LD841', 'EN TRÁNSITO'),
(18, 'II718', 'EN TRÁNSITO'),
(19, 'II701', 'DEVOLUCIÓN EN CURSO'),
(20, 'II721', 'APORTAR SOLUCIÓN'),
(21, 'II717', 'APORTAR SOLUCIÓN'),
(22, 'II719', 'APORTAR SOLUCIÓN'),
(23, 'II722', 'APORTAR SOLUCIÓN'),
(24, 'II732', 'APORTAR SOLUCIÓN'),
(25, 'II740', 'EN TRÁNSITO'),
(26, 'II731', 'APORTAR SOLUCIÓN'),
(27, 'II741', 'APORTAR SOLUCIÓN'),
(28, 'II743', 'APORTAR SOLUCIÓN'),
(29, 'II748', 'APORTAR SOLUCIÓN'),
(33, 'II753', 'APORTAR SOLUCIÓN'),
(34, 'II754', 'APORTAR SOLUCIÓN'),
(35, 'II742', 'APORTAR SOLUCIÓN'),
(36, 'II755', 'APORTAR SOLUCIÓN'),
(37, 'II744', 'APORTAR SOLUCIÓN'),
(38, 'II746', 'APORTAR SOLUCIÓN'),
(39, 'II745', 'APORTAR SOLUCIÓN'),
(40, 'II756', 'APORTAR SOLUCIÓN'),
(41, 'II757', 'APORTAR SOLUCIÓN'),
(42, 'II758', 'APORTAR SOLUCIÓN'),
(43, 'II759', 'APORTAR SOLUCIÓN'),
(45, 'II761', 'APORTAR SOLUCIÓN'),
(46, 'II762', 'APORTAR SOLUCIÓN'),
(48, 'LI566', 'EN TRÁNSITO'),
(49, 'LI567', 'EN TRÁNSITO'),
(50, 'LI717', 'APORTAR SOLUCIÓN'),
(51, 'LI718', 'APORTAR SOLUCIÓN'),
(52, 'LI719', 'APORTAR SOLUCIÓN'),
(53, 'LI721', 'APORTAR SOLUCIÓN'),
(54, 'LI722', 'APORTAR SOLUCIÓN'),
(55, 'LI731', 'APORTAR SOLUCIÓN'),
(56, 'LI732', 'APORTAR SOLUCIÓN'),
(57, 'LI452', 'EN TRÁNSITO'),
(58, 'LI456', 'EN TRÁNSITO'),
(59, 'LI457', 'EN TRÁNSITO'),
(60, 'LI460', 'EN TRÁNSITO'),
(61, 'LI461', 'EN TRÁNSITO'),
(62, 'LI462', 'EN TRÁNSITO'),
(63, 'LI463', 'EN TRÁNSITO'),
(64, 'LI464', 'EN TRÁNSITO'),
(65, 'LI406', 'EN TRÁNSITO'),
(66, 'LI407', 'EN TRÁNSITO'),
(67, 'LI409', 'EN TRÁNSITO'),
(68, 'LI328', 'EN TRÁNSITO'),
(69, 'LI403', 'EN TRÁNSITO'),
(70, 'LI335', 'EN TRÁNSITO'),
(71, 'LI410', 'EN TRÁNSITO'),
(72, 'LI411', 'EN TRÁNSITO'),
(73, 'LI434', 'EN TRÁNSITO'),
(74, 'LI439', 'EN TRÁNSITO'),
(75, 'LI438', 'EN TRÁNSITO'),
(76, 'LI436', 'EN TRÁNSITO'),
(77, 'LI437', 'EN TRÁNSITO'),
(78, 'LI444', 'EN TRÁNSITO'),
(79, 'LI443', 'EN TRÁNSITO'),
(80, 'LI412', 'EN TRÁNSITO'),
(81, 'LI440', 'EN TRÁNSITO'),
(82, 'LI433', 'EN TRÁNSITO'),
(83, 'LI441', 'EN TRÁNSITO'),
(84, 'LI435', 'EN TRÁNSITO'),
(85, 'LI423', 'EN TRÁNSITO'),
(86, 'LI442', 'EN TRÁNSITO'),
(87, 'LI446', 'EN TRÁNSITO'),
(88, 'LI447', 'EN TRÁNSITO'),
(89, 'LI458', 'EN TRÁNSITO'),
(90, 'LI459', 'EN TRÁNSITO'),
(91, 'LI497', 'EN TRÁNSITO'),
(92, 'LI510', 'INCIDENCIA'),
(93, 'LI515', 'EN TRÁNSITO'),
(94, 'LI516', 'EN TRÁNSITO'),
(95, 'LI517', 'INCIDENCIA'),
(96, 'LI518', 'EN TRÁNSITO'),
(97, 'LI519', 'INCIDENCIA'),
(98, 'LI520', 'INCIDENCIA'),
(99, 'LI521', 'INCIDENCIA'),
(100, 'LI522', 'INCIDENCIA'),
(101, 'LI523', 'INCIDENCIA'),
(102, 'LI524', 'INCIDENCIA'),
(103, 'LI525', 'EN TRÁNSITO'),
(104, 'LI530', 'DISPONIBLE PARA RECOGER EN TIENDA'),
(105, 'LI531', 'INCIDENCIA'),
(106, 'LI532', 'APORTAR SOLUCIÓN'),
(107, 'LI533', 'DEVOLUCIÓN EN CURSO'),
(108, 'LI535', 'EN TRÁNSITO'),
(109, 'LI536', 'INCIDENCIA'),
(110, 'LI537', 'INCIDENCIA'),
(111, 'LI548', 'EN TRÁNSITO'),
(112, 'LI550', 'INCIDENCIA'),
(113, 'LI553', 'INCIDENCIA'),
(114, 'LI554', 'INCIDENCIA'),
(115, 'LI563', 'EN TRÁNSITO'),
(116, 'LI564', 'ENTREGADO'),
(117, 'LI565', 'EN TRÁNSITO'),
(118, 'LI725', 'APORTAR SOLUCIÓN'),
(119, 'LI465', 'ENTREGADO'),
(120, 'LI466', 'ENTREGADO'),
(121, 'LI467', 'ENTREGADO'),
(122, 'LI469', 'ENTREGADO'),
(123, 'LI470', 'ENTREGADO'),
(124, 'LI472', 'ENTREGADO'),
(125, 'LI474', 'ENTREGADO'),
(126, 'LI476', 'ENTREGADO'),
(127, 'LI477', 'ENTREGADO'),
(128, 'LI478', 'ENTREGADO'),
(129, 'LI480', 'ENTREGADO'),
(130, 'LI486', 'ENTREGADO'),
(131, 'LI492', 'ENTREGADO'),
(132, 'LI493', 'ENTREGADO'),
(133, 'LI494', 'ENTREGADO'),
(134, 'LI495', 'ENTREGADO'),
(135, 'LI308', 'ENTREGADO'),
(136, 'LI346', 'ENTREGADO'),
(137, 'LI349', 'ENTREGADO'),
(138, 'LI401', 'ENTREGADO'),
(139, 'LI402', 'ENTREGADO'),
(140, 'LI408', 'ENTREGADO'),
(141, 'LI420', 'ENTREGADO'),
(142, 'LI421', 'ENTREGADO'),
(143, 'LI422', 'ENTREGADO'),
(144, 'LI425', 'ENTREGADO'),
(145, 'LI426', 'ENTREGADO'),
(146, 'LI427', 'ENTREGADO'),
(147, 'LI428', 'ENTREGADO'),
(148, 'LI431', 'ENTREGADO'),
(149, 'LI313', 'ENTREGADO'),
(150, 'LI350', 'ENTREGADO'),
(151, 'LI367', 'ENTREGADO'),
(152, 'LI368', 'ENTREGADO'),
(153, 'LI416', 'ENTREGADO'),
(154, 'LI417', 'ENTREGADO'),
(155, 'LI418', 'ENTREGADO'),
(156, 'LI445', 'ENTREGADO'),
(157, 'LI448', 'ENTREGADO'),
(158, 'LI449', 'ENTREGADO'),
(159, 'LI453', 'ENTREGADO'),
(160, 'LI471', 'ENTREGADO'),
(161, 'LI475', 'ENTREGADO'),
(162, 'LI482', 'ENTREGADO'),
(163, 'LI484', 'ENTREGADO'),
(164, 'LI488', 'ENTREGADO'),
(165, 'LI490', 'ENTREGADO'),
(166, 'LI496', 'ENTREGADO'),
(167, 'LI498', 'ENTREGADO'),
(168, 'LI499', 'ENTREGADO'),
(169, 'LI501', 'INCIDENCIA'),
(170, 'LI511', 'ENTREGADO'),
(171, 'LI512', 'ENTREGADO'),
(172, 'LI513', 'APORTAR SOLUCIÓN'),
(173, 'LI526', 'INCIDENCIA'),
(174, 'LI528', 'INCIDENCIA'),
(175, 'LI529', 'INCIDENCIA'),
(176, 'LI538', 'INCIDENCIA'),
(177, 'LI540', 'APORTAR SOLUCIÓN'),
(178, 'LI542', 'INCIDENCIA'),
(179, 'LI545', 'INCIDENCIA'),
(180, 'LI546', 'INCIDENCIA'),
(181, 'LI547', 'EN TRÁNSITO'),
(182, 'LI552', 'INCIDENCIA'),
(183, 'LI555', 'INCIDENCIA'),
(184, 'LI556', 'INCIDENCIA'),
(185, 'LI560', 'INCIDENCIA'),
(186, 'LI561', 'INCIDENCIA'),
(187, 'LI527', 'INCIDENCIA'),
(188, 'LI539', 'INCIDENCIA'),
(189, 'LI541', 'INCIDENCIA'),
(190, 'LI310', 'EN TRÁNSITO'),
(191, 'LI364', 'EN TRÁNSITO'),
(192, 'LI575', 'INCIDENCIA'),
(193, 'LI405', 'EN TRÁNSITO'),
(194, 'LI413', 'EN TRÁNSITO'),
(195, 'LI419', 'EN TRÁNSITO'),
(196, 'LI450', 'EN TRÁNSITO'),
(197, 'LI489', 'EN TRÁNSITO'),
(198, 'LI483', 'EN TRÁNSITO'),
(199, 'LI491', 'EN TRÁNSITO'),
(200, 'LI534', 'APORTAR SOLUCIÓN'),
(201, 'LI551', 'INCIDENCIA'),
(202, 'LI799', 'EN TRÁNSITO'),
(203, 'LI544', 'INCIDENCIA'),
(204, 'LI388', 'EN TRÁNSITO'),
(205, 'LI404', 'EN TRÁNSITO'),
(206, 'LI424', 'EN TRÁNSITO'),
(207, 'LI429', 'EN TRÁNSITO'),
(208, 'LI468', 'EN TRÁNSITO'),
(209, 'LI569', 'DISPONIBLE PARA RECOGER EN TIENDA'),
(210, 'LI432', 'EN TRÁNSITO'),
(211, 'LI451', 'EN TRÁNSITO'),
(212, 'LI314', 'EN TRÁNSITO'),
(213, 'LI329', 'EN TRÁNSITO'),
(214, 'LI414', 'EN TRÁNSITO'),
(215, 'LI415', 'EN TRÁNSITO'),
(216, 'LI454', 'EN TRÁNSITO'),
(217, 'LI479', 'EN TRÁNSITO'),
(218, 'LI481', 'EN TRÁNSITO'),
(219, 'LI485', 'EN TRÁNSITO'),
(220, 'LI487', 'EN TRÁNSITO'),
(221, 'LI873', 'DISPONIBLE PARA RECOGER EN TIENDA'),
(222, 'LI880', 'INCIDENCIA'),
(223, 'LI882', 'INCIDENCIA'),
(224, 'LI862', 'INCIDENCIA'),
(225, 'LI866', 'INCIDENCIA'),
(226, 'LI868', 'INCIDENCIA'),
(227, 'LI869', 'INCIDENCIA'),
(228, 'LI849', 'INCIDENCIA'),
(229, 'LI863', 'INCIDENCIA'),
(230, 'LI876', 'INCIDENCIA'),
(231, 'LI879', 'INCIDENCIA'),
(232, 'LI853', 'INCIDENCIA'),
(233, 'LI847', 'INCIDENCIA'),
(234, 'LI848', 'INCIDENCIA'),
(235, 'LI864', 'APORTAR SOLUCIÓN'),
(236, 'LI865', 'INCIDENCIA'),
(237, 'LI881', 'INCIDENCIA'),
(238, 'LI399', 'EN TRÁNSITO'),
(239, 'LI752', 'APORTAR SOLUCIÓN'),
(240, 'LI549', 'EN TRÁNSITO'),
(241, 'LI571', 'DEVOLUCIÓN EN CURSO'),
(242, 'LI572', 'DEVOLUCIÓN EN CURSO'),
(243, 'LI574', 'DISPONIBLE PARA RECOGER EN TIENDA'),
(244, 'LI580', 'EN TRÁNSITO'),
(245, 'LI559', 'APORTAR SOLUCIÓN'),
(246, 'LI543', 'DEVOLUCIÓN EN CURSO'),
(247, 'LI558', 'EN TRÁNSITO'),
(248, 'LI576', 'DISPONIBLE PARA RECOGER EN TIENDA'),
(249, 'LI557', 'EN TRÁNSITO'),
(250, 'LI473', 'EN TRÁNSITO'),
(251, 'LI323', 'EN TRÁNSITO'),
(252, 'LI455', 'EN TRÁNSITO'),
(253, 'LI430', 'EN TRÁNSITO'),
(254, 'LI369', 'EN TRÁNSITO'),
(255, 'LI370', 'EN TRÁNSITO'),
(256, 'LI380', 'EN TRÁNSITO'),
(257, 'LI351', 'EN TRÁNSITO'),
(258, 'LI352', 'EN TRÁNSITO'),
(259, 'LI353', 'EN TRÁNSITO'),
(260, 'LI354', 'EN TRÁNSITO'),
(261, 'LI355', 'EN TRÁNSITO'),
(262, 'LI356', 'EN TRÁNSITO'),
(263, 'LI357', 'EN TRÁNSITO'),
(264, 'LI359', 'EN TRÁNSITO'),
(265, 'LI371', 'EN TRÁNSITO'),
(266, 'LI374', 'EN TRÁNSITO'),
(267, 'LI375', 'EN TRÁNSITO'),
(268, 'LI377', 'EN TRÁNSITO'),
(269, 'LI381', 'EN TRÁNSITO'),
(270, 'LI358', 'EN TRÁNSITO'),
(271, 'LI361', 'EN TRÁNSITO'),
(272, 'LI362', 'EN TRÁNSITO'),
(273, 'LI363', 'EN TRÁNSITO'),
(274, 'LI365', 'EN TRÁNSITO'),
(275, 'LI366', 'EN TRÁNSITO'),
(276, 'LI372', 'EN TRÁNSITO'),
(277, 'LI373', 'EN TRÁNSITO'),
(278, 'LI376', 'EN TRÁNSITO'),
(279, 'LI378', 'EN TRÁNSITO'),
(280, 'LI379', 'EN TRÁNSITO'),
(281, 'LI382', 'EN TRÁNSITO'),
(282, 'LI383', 'EN TRÁNSITO'),
(283, 'LI384', 'EN TRÁNSITO'),
(284, 'LI577', 'DISPONIBLE PARA RECOGER EN TIENDA'),
(285, 'LI578', 'DISPONIBLE PARA RECOGER EN TIENDA'),
(286, 'LI573', 'INCIDENCIA'),
(287, 'LJ100', 'EN TRÁNSITO'),
(288, 'LJ105', 'EN TRÁNSITO'),
(289, 'LJ524', 'EN TRÁNSITO'),
(290, 'LJ833', 'DEVOLUCIÓN EN CURSO'),
(291, 'LJ303', 'DOCUMENTACIÓN RECTIFICADA'),
(292, 'LK552', 'DOCUMENTACIÓN RECTIFICADA'),
(293, 'LK553', 'DOCUMENTACIÓN RECTIFICADA'),
(294, 'LK554', 'DOCUMENTACIÓN RECTIFICADA'),
(295, 'LK555', 'DOCUMENTACIÓN RECTIFICADA'),
(296, 'LK556', 'DOCUMENTACIÓN RECTIFICADA'),
(297, 'LK560', 'INCIDENCIA'),
(298, 'LK573', 'INCIDENCIA'),
(299, 'LK583', 'DOCUMENTACIÓN RECTIFICADA'),
(300, 'LK510', 'DOCUMENTACIÓN RECTIFICADA'),
(301, 'LK511', 'DOCUMENTACIÓN RECTIFICADA'),
(302, 'LK512', 'DOCUMENTACIÓN RECTIFICADA'),
(303, 'LK513', 'DOCUMENTACIÓN RECTIFICADA'),
(304, 'LK514', 'DOCUMENTACIÓN RECTIFICADA'),
(305, 'LK515', 'DOCUMENTACIÓN RECTIFICADA'),
(306, 'LK516', 'DOCUMENTACIÓN RECTIFICADA'),
(307, 'LK517', 'DOCUMENTACIÓN RECTIFICADA'),
(308, 'LK521', 'INCIDENCIA'),
(309, 'LK522', 'INCIDENCIA'),
(310, 'LK531', 'DOCUMENTACIÓN RECTIFICADA'),
(311, 'LK530', 'DOCUMENTACIÓN RECTIFICADA'),
(312, 'LK532', 'DOCUMENTACIÓN RECTIFICADA'),
(313, 'LK545', 'DOCUMENTACIÓN RECTIFICADA'),
(314, 'LK523', 'INCIDENCIA'),
(315, 'LK536', 'DOCUMENTACIÓN RECTIFICADA'),
(316, 'LK524', 'DOCUMENTACIÓN RECTIFICADA'),
(317, 'LK537', 'DOCUMENTACIÓN RECTIFICADA'),
(318, 'LK533', 'DOCUMENTACIÓN RECTIFICADA'),
(319, 'LK551', 'DOCUMENTACIÓN RECTIFICADA'),
(320, 'SK610', 'DOCUMENTACIÓN RECTIFICADA'),
(321, 'SK611', 'DOCUMENTACIÓN RECTIFICADA'),
(322, 'SK612', 'DOCUMENTACIÓN RECTIFICADA'),
(323, 'SK613', 'DOCUMENTACIÓN RECTIFICADA'),
(324, 'SK614', 'DOCUMENTACIÓN RECTIFICADA'),
(325, 'SK615', 'DOCUMENTACIÓN RECTIFICADA'),
(326, 'SK616', 'DOCUMENTACIÓN RECTIFICADA'),
(327, 'SK622', 'INCIDENCIA'),
(328, 'SK623', 'INCIDENCIA'),
(329, 'SK636', 'DOCUMENTACIÓN RECTIFICADA'),
(330, 'SK637', 'DOCUMENTACIÓN RECTIFICADA'),
(331, 'SK651', 'DOCUMENTACIÓN RECTIFICADA'),
(332, 'SK652', 'DOCUMENTACIÓN RECTIFICADA'),
(333, 'SK653', 'DOCUMENTACIÓN RECTIFICADA'),
(334, 'SK660', 'INCIDENCIA'),
(335, 'LL001', 'ENTREGADO'),
(336, 'LL003', 'ENTREGADO'),
(337, 'LL007', 'ENTREGADO'),
(338, 'LL060', 'ENTREGADO'),
(339, 'LL091', 'INCIDENCIA'),
(340, 'LL300', 'DEVOLUCIÓN EN CURSO'),
(341, 'LL301', 'DOCUMENTACIÓN RECTIFICADA'),
(342, 'LL303', 'APORTAR SOLUCIÓN'),
(343, 'LL872', 'ENTREGADO'),
(344, 'LL873', 'ENTREGADO'),
(345, 'LL874', 'INCIDENCIA'),
(346, 'LL833', 'DEVOLUCIÓN EN CURSO'),
(347, 'LL010', 'ENTREGADO'),
(348, 'LL020', 'ENTREGADO'),
(349, 'LL030', 'ENTREGADO'),
(351, 'LM001', 'ENTREGADO'),
(352, 'LM002', 'ENTREGADO'),
(353, 'LM003', 'ENTREGADO'),
(354, 'LM004', 'ENTREGADO'),
(355, 'LM005', 'ENTREGADO'),
(356, 'LM006', 'ENTREGADO'),
(357, 'LM009', 'ENTREGADO'),
(358, 'LM010', 'ENTREGADO'),
(359, 'LM011', 'ENTREGADO'),
(360, 'LM012', 'ENTREGADO'),
(361, 'LM013', 'ENTREGADO'),
(362, 'LM014', 'ENTREGADO'),
(363, 'LM015', 'ENTREGADO'),
(364, 'LM016', 'ENTREGADO'),
(365, 'LM017', 'ENTREGADO'),
(366, 'LM019', 'ENTREGADO'),
(367, 'LM018', 'ENTREGADO'),
(368, 'LM020', 'ENTREGADO'),
(369, 'LM021', 'ENTREGADO'),
(370, 'LM022', 'ENTREGADO'),
(371, 'LM060', 'ENTREGADO'),
(372, 'LM999', 'ENTREGADO'),
(373, 'LM025', 'ENTREGADO'),
(374, 'LM024', 'ENTREGADO'),
(375, 'LM026', 'ENTREGADO'),
(376, 'LM027', 'ENTREGADO'),
(377, 'LM023', 'ENTREGADO'),
(378, 'LO001', 'EN TRÁNSITO'),
(379, 'LO002', 'INCIDENCIA'),
(380, 'LR802', 'INCIDENCIA'),
(381, 'IS322', 'EN TRÁNSITO'),
(382, 'LS004', 'EN TRÁNSITO'),
(383, 'LS857', 'EN TRÁNSITO'),
(384, 'LT859', 'EN TRÁNSITO'),
(385, 'SW089', 'EN TRÁNSITO'),
(386, 'SW189', 'EN TRÁNSITO'),
(387, 'SW999', 'EN TRÁNSITO'),
(388, 'LX510', 'DOCUMENTACIÓN RECTIFICADA'),
(389, 'LX511', 'DOCUMENTACIÓN RECTIFICADA'),
(390, 'LX512', 'DOCUMENTACIÓN RECTIFICADA'),
(391, 'LX513', 'DOCUMENTACIÓN RECTIFICADA'),
(392, 'LX515', 'DOCUMENTACIÓN RECTIFICADA'),
(393, 'LX516', 'DOCUMENTACIÓN RECTIFICADA'),
(394, 'LX517', 'DOCUMENTACIÓN RECTIFICADA'),
(395, 'LX524', 'DOCUMENTACIÓN RECTIFICADA'),
(396, 'LX530', 'DOCUMENTACIÓN RECTIFICADA'),
(397, 'LX531', 'DOCUMENTACIÓN RECTIFICADA'),
(398, 'LX532', 'DOCUMENTACIÓN RECTIFICADA'),
(399, 'LX533', 'DOCUMENTACIÓN RECTIFICADA'),
(400, 'LX536', 'DOCUMENTACIÓN RECTIFICADA'),
(401, 'LX537', 'DOCUMENTACIÓN RECTIFICADA'),
(402, 'LX545', 'DOCUMENTACIÓN RECTIFICADA'),
(403, 'LX551', 'DOCUMENTACIÓN RECTIFICADA'),
(404, 'LX552', 'DOCUMENTACIÓN RECTIFICADA'),
(405, 'LX553', 'DOCUMENTACIÓN RECTIFICADA'),
(406, 'LX554', 'DOCUMENTACIÓN RECTIFICADA'),
(407, 'LX555', 'DOCUMENTACIÓN RECTIFICADA'),
(408, 'LX556', 'DOCUMENTACIÓN RECTIFICADA'),
(409, 'LX573', 'APORTAR SOLUCIÓN'),
(410, 'LX583', 'DOCUMENTACIÓN RECTIFICADA'),
(411, 'SX001', 'EN TRÁNSITO'),
(412, 'SX010', 'EN TRÁNSITO')";


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_query` (
        `id_order` int(11) NOT NULL,
        `date_query` datetime,
    PRIMARY KEY (`id_order`,`date_query`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_pickup` ADD COLUMN id_seur_ccc int(10) NOT NULL;";
$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_pickup` ADD COLUMN pudoId tinyint(1) DEFAULT NULL;";
$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_pickup` ADD COLUMN frio tinyint(1) DEFAULT 0;";
$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_pickup` ADD UNIQUE INDEX `UNIQUE_SEUR2_PICKUP` (`id_seur_ccc` ASC, `date` ASC, `frio` ASC);";

$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_ccc` ADD COLUMN id_shop int(11) NULL DEFAULT 1;";

$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN brexit tinyint(1) NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN tariff tinyint(1) NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN brexit_date timestamp NULL;";
$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN tariff_date timestamp NULL;";

$sql[] = "INSERT INTO `"._DB_PREFIX_."seur2_products` (id_seur_product, id_seur_services_type, name) 
            VALUES (116, 3, 'MULTIPARCEL')";

$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN label_file varchar(255) NULL;";

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_european_countries` (
  `id` int(11) NOT NULL,
  `iso_code` varchar(5) NOT NULL,
  `country` varchar(100) NOT NULL
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'seur2_european_countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iso_code` (`iso_code`)';

$sql[] = "INSERT INTO `"._DB_PREFIX_."seur2_european_countries` (`id`, `iso_code`, `country`) VALUES
(1, 'AT', 'Austria'),
(2, 'BE', 'Bélgica'),
(3, 'BG', 'Bulgaria'),
(4, 'CY', 'Chipre'),
(5, 'CZ', 'República Checa'),
(6, 'DE', 'Alemania'),
(7, 'DK', 'Dinamarca'),
(8, 'EE', 'Estonia'),
(9, 'ES', 'España'),
(10, 'FI', 'Finlandia'),
(11, 'FR', 'Francia'),
(12, 'GR', 'Grecia'),
(13, 'HR', 'Croacia'),
(14, 'HU', 'Hungría'),
(15, 'IE', 'Irlanda'),
(16, 'IT', 'Italia'),
(17, 'LT', 'Lituania'),
(18, 'LU', 'Luxemburgo'),
(19, 'LV', 'Letonia'),
(20, 'MT', 'Malta'),
(21, 'NL', 'Países Bajos'),
(22, 'PL', 'Polonia'),
(23, 'PT', 'Portugal'),
(24, 'RO', 'Rumanía'),
(25, 'SE', 'Suecia'),
(26, 'SI', 'Eslovenia'),
(27, 'SK', 'Eslovaquia')";


$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN ecbs varchar(500) NULL DEFAULT '';";
$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN parcelNumbers varchar(500) NULL DEFAULT '';";
$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN expeditionCode varchar(255) NULL DEFAULT '';";
$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN label_files varchar(1000) NULL DEFAULT '';";

$sql[] = "INSERT INTO `"._DB_PREFIX_."seur2_services` (id_seur_services, id_seur_services_type, name) 
            VALUES (77, 2, 'CLASSIC')";

$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN insured tinyint(1) NULL DEFAULT 0;";

$sql[] = "INSERT INTO `"._DB_PREFIX_."seur2_services` (id_seur_services, id_seur_services_type, name) 
            VALUES (1, 1, 'S24')";

$sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_query` ADD COLUMN failed_attempts INT DEFAULT 0;";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
