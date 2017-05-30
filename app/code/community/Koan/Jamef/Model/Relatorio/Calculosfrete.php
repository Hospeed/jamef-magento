<?php


class Koan_Jamef_Model_Relatorio_Calculosfrete extends Mage_Core_Model_Abstract
{

	const STATUS_SUCESSO = 'sucesso';

	const STATUS_ERRO = 'erro';

	public function _construct()
	{
		$this->_init('koan_jamef/relatorio_calculosfrete');
	}

}
