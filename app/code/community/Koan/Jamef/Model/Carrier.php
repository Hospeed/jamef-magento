<?php


class Koan_Jamef_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'koan_jamef';


    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('title'));
    }


    public function collectRates(Mage_Shipping_Model_Rate_Request $rateRequest)
    {
        $cepOrigem = $this->trataCep(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
        $cepDestino = $this->trataCep($rateRequest->getDestPostcode());

        if(!$this->estaAtivo()){
        	$relatorioCalculosFrete = Mage::getModel('koan_jamef/relatorio_calculosfrete')
        						->setDataCalculo(date('Y-m-d H:i:s'))
        						->setCepDestino($cepDestino)
        						->setMensagemErro('O módulo não está ativo')
            					->setStatus(Koan_Jamef_Model_Relatorio_Calculosfrete::STATUS_ERRO)
            					->save();

            return false;
        }

        $idPaisDestino = $rateRequest->getDestCountryId();

        if(!$this->lojaEstaNoBrasil() || !$this->estaNoBrasil($idPaisDestino)){

        	$msgErro = !$this->lojaEstaNoBrasil() ? 'O país configurado para a loja não é o Brasil' :
        													'O país informado pelo usuário não é o Brasil';

        	$relatorioCalculosFrete = Mage::getModel('koan_jamef/relatorio_calculosfrete')
        						->setDataCalculo(date('Y-m-d H:i:s'))
        						->setCepDestino($cepDestino)
        						->setMensagemErro($msgErro)
            					->setStatus(Koan_Jamef_Model_Relatorio_Calculosfrete::STATUS_ERRO)
            					->save();
            return false;
        }

        $valorEncomenda = $rateRequest->getBaseCurrency()->convert($rateRequest->getPackageValue(), $rateRequest->getPackageCurrency());

        $cnpjEmpresa = $this->getConfigData('p_cic_negc');
        $regionalJamef = $this->getConfigData('p_cod_regn');
        $ufJamef = $this->getConfigData('p_uf');

        $peso = $rateRequest->getPackageWeight();

        if(!$this->validaCep($cepOrigem) || !$this->validaCep($cepDestino)){

        	$msgErro = !$this->validaCep($cepOrigem) ? 'CEP de origem inválido' : 'CEP de destino inválido';

        	$relatorioCalculosFrete = Mage::getModel('koan_jamef/relatorio_calculosfrete')
        						->setDataCalculo(date('Y-m-d H:i:s'))
        						->setCepDestino($cepDestino)
        						->setMensagemErro($msgErro)
            					->setStatus(Koan_Jamef_Model_Relatorio_Calculosfrete::STATUS_ERRO)
            					->save();

            return false;
        }


        $request = Mage::getModel('koan_jamef/carrier_request');

        $request->setCepOrigem($cepOrigem);
        $request->setCepDestino($cepDestino);
        $request->setValorEncomenda($valorEncomenda);


        $request->setCnpjEmpresa($cnpjEmpresa);
        $request->setRegionalJamef($regionalJamef);
        $request->setUfJamef($ufJamef);

        $request->setPeso($peso);



        $rateResult = Mage::getModel('shipping/rate_result');


	        $servicos = $request->send();

	        if($servicos !== false){
	             foreach($servicos->frete as $servico){
	                 if($servico->status == 1){
	                     $cod = '0';

	                     $method = Mage::getModel('shipping/rate_result_method');

	                     $method->setCarrier($this->_code);
	                     $method->setCarrierTitle($this->getTitle());

	                     $method->setMethod($cod);

	                     $methodTitle = Koan_Jamef_Model_Carrier_Request::$labels[$cod];


	                     $method->setMethodTitle($methodTitle);

	                     $valor = str_replace(',', '.', $servico->valor);

	                     $method->setPrice($valor);
	                     $method->setCost($valor);

	                     $rateResult->append($method);
	                 }
	             }
	        }
	        else{
	            $error = Mage::getModel('shipping/rate_result_error');

	            $error->setCarrier($this->_code);
	            $error->setCarrierTitle($this->getTitle());
	            $error->setErrorMessage($this->getMensagemDeErro());

	            $rateResult->append($error);

	            return $rateResult;
	        }

        return $rateResult;
    }



    private function _getArrayDeliveryDateTime($data)
    {
    	$brLocale = new Zend_Locale('pt_BR');

		list($_deliveryDate, $_deliveryTime) = explode(' ', $data);

		$objDeliveryDate = new Zend_Date($_deliveryDate, 'dd/MM/YYYY', $brLocale);
		$_deliveryDate = $objDeliveryDate->toString('YYYY-MM-dd');

		return array($_deliveryDate, $_deliveryTime);
    }



    /*
     * Métodos próprios do model
     */

    private function validaCep($cep)
    {
        if(!preg_match('/^\d{8}$/', $cep)){
            return false;
        }

        return true;
    }

    public function estaAtivo()
    {
        return $this->getConfigFlag('active') == 1;
    }


    public function getTitulo()
    {
        return $this->getConfigData('title');
    }


    public function getMensagemDeErro()
    {
        return $this->getConfigData('msgerro');
    }

    public function lojaEstaNoBrasil()
    {
        $idPais = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());

        return $this->estaNoBrasil($idPais);
    }

    public function estaNoBrasil($idPais)
    {
        return $idPais == 'BR';
    }

    public function trataCep($cep)
    {
        return preg_replace('/\D/', '', $cep);
    }

}
