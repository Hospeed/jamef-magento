<?php


class Koan_Jamef_Model_Tipofrete
{
    public function toOptionArray()
    {
        return array(

            array( 'value' => Koan_Jamef_Model_Carrier_Request::SERVICO_JAMEF,
                'label' => Mage::helper('adminhtml')->__(Koan_Jamef_Model_Carrier_Request::SERVICO_JAMEF . ' - Transportadora Jamef' ) )
        );
    }

}
