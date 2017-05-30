<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `koan_jamef_calculos`;

CREATE TABLE `koan_jamef_calculos` (
  `id_calculo` int(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
  `data_calculo` datetime DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `mensagem_erro` varchar(200) DEFAULT NULL,
  `cep_destino` varchar(10) DEFAULT NULL,
  `parametros_enviados` text,
  `xml_retorno` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
");

$installer->endSetup();
?>
