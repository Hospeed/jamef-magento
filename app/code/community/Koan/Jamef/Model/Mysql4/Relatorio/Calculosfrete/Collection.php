<?php


class Koan_Jamef_Model_Mysql4_Relatorio_Calculosfrete_Collection extends Varien_Data_Collection_Db
{
    public function __construct()
    {
        $resources = Mage::getSingleton('core/resource');

        parent::__construct($resources->getConnection('koan_jamef_read'));

        $this->_select->from(
        	array('subscriber' => $resources->getTableName('koan_jamef/relatorio_calculosfrete')),
 	       	array('*')
        );

        $this->setItemObjectClass(Mage::getConfig()->getModelClassName('koan_jamef/relatorio_calculosfrete'));
    }
}
