<?php


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('salesrule')}`
    ADD COLUMN `koan_jamef_desconto` varchar (10);
");

$installer->run("
ALTER TABLE `{$this->getTable('salesrule')}`
    ADD COLUMN `koan_jamef_tipo_desconto` varchar (20)
        AFTER `koan_jamef_desconto`;
");

$installer->run("
ALTER TABLE `{$this->getTable('salesrule')}`
    ADD COLUMN `koan_jamef_label_fixo` varchar (100)
        AFTER `koan_jamef_tipo_desconto`;
");

$installer->endSetup();
?>
