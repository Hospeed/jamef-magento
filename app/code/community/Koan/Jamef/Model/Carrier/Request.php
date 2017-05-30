<?php


class Koan_Jamef_Model_Carrier_Request
{

    const SERVICO_JAMEF = '0';


    const URL = 'http://www.jamef.com.br/internet/e-comerce/calculafrete_xml.asp';

    /**
     * Contém os labels que serão exibidos quando
     * o frete for calculado.
     *
     * @var array
     */
    public static $labels;

    /**
     * Cep de destino sem traço
     *
     * @var integer
     */
    private $_cepDestino;

    /**
     * Cep de origem sem traço
     *
     * @var integer
     */
    private $_cepOrigem;

    /**
     * Dados Jamef
     *
     * @var integer
     */
    private $_cnpjEmpresa;

    private $_regionalJamef;

    private $_ufJamef;

    /**
     * Valor total da encomenda
     *
     * @var float
     */
    private $_valorEncomenda;

    /**
     * Soma dos pesos dos produtos
     *
     * @var float
     */
    private $_peso;

    /**
     * Contém o código dos serviços consultados.
     * Eles são selecionados na área administrativa do Magento.
     *
     * @var array
     */
    //private $_servicos = array();

    /**
     * Comprimento da encomenda
     *
     * @var integer
     */
    private $_comprimento;

    /**
     * Altura da encomenda
     *
     * @var integer
     */
    private $_altura;

    /**
     * Largura da encomenda
     *
     * @var integer
     */
    private $_largura;

    /**
     * Diâmetro da encomenda
     *
     * @var integer
     */
    private $_diametro;


    public function __construct()
    {
        $this->_createLabels();
    }

    private function _createLabels()
    {
        self::$labels = array(
            self::SERVICO_JAMEF => Mage::helper('adminhtml')->__('Entrega via Jamef - ' )
        );
    }

    public function setCepDestino($cep)
    {
        $this->_cepDestino = $cep;
    }

    public function setCepOrigem($cep)
    {
        $this->_cepOrigem = $cep;
    }


    public function setCnpjEmpresa($cnpjEmpresa)
    {
        $this->_cnpjEmpresa = $cnpjEmpresa;
    }

    public function setRegionalJamef($regionalJamef)
    {
        $this->_regionalJamef = $regionalJamef;
    }

    public function setUfJamef($ufJamef)
    {
        $this->_ufJamef = $ufJamef;
    }

    public function setValorEncomenda($valor)
    {
        $this->_valorEncomenda = str_replace('.', ',', $valor);
    }

    public function setPeso($peso)
    {
        $this->_peso = $peso;
    }

    public function setComprimento($comprimento)
    {
        $this->_comprimento = $comprimento;
    }

    public function setLargura($largura)
    {
        $this->_largura = $largura;
    }

    public function setAltura($altura)
    {
        $this->_altura = $altura;
    }

    public function setDiametro($diametro)
    {
        $this->_diametro = $diametro;
    }


    public function addServico($servico)
    {
        if(is_array($servico)){
            foreach($servico as $s){
                $this->_servicos[] = $s;
            }
        }
        else if(is_string($servico)){
            $this->_servicos[] = $servico;
        }
    }

    private function _buildParams()
    {
        $params = array(
            'P_CEP' => $this->_cepDestino,
            'P_PESO_KG' => $this->_peso,
            //'nVlComprimento' => $this->_comprimento,
            //'nVlAltura' => $this->_altura,
            //'nVlLargura' => $this->_largura,
            //'nVlDiametro' => $this->_diametro,
            'P_VLR_CARG' => $this->_valorEncomenda,
            //'P_CUBG' => $this->_Volume,
            'P_CIC_NEGC' => $this->_cnpjEmpresa,
            'P_COD_REGN' => $this->_regionalJamef,
            'P_UF' => $this->_ufJamef
        );

        if(isset($this->_cnpjEmpresa) && isset($this->_senhaEmpresa) && isset($this->_ufJamef)){
            $params['P_CIC_NEGC'] = $this->_cnpjEmpresa;
            $params['P_COD_REGN'] = $this->_regionalJamef;
            $params['P_UF'] = $this->_ufJamef;
        }

        $p = array();
        foreach($params as $k => $v){
            $p[] = "{$k}={$v}";
        }

        return implode('&', $p);
    }

    public function send()
    {

        $relatorioCalculosFrete = Mage::getModel('koan_jamef/relatorio_calculosfrete');

    	$params = $this->_buildParams();

        $url = self::URL . '?' . $params;

        $relatorioCalculosFrete->setCepDestino($this->_cepDestino)
        					->setDataCalculo(date('Y-m-d H:i:s'))
        					->setParametrosEnviados($params);


        try {
            $client = new Zend_Http_Client($url);
            $content = $client->request();

            $xml = simplexml_load_string($content->getBody());

            $relatorioCalculosFrete->setXmlRetorno($content->getBody())
            					->setStatus(Koan_Jamef_Model_Relatorio_Calculosfrete::STATUS_SUCESSO)
            					->save();

            return $xml;
        } catch (Exception $e) {

            $relatorioCalculosFrete->setMensagemErro($e->getMessage())
            					->setStatus(Koan_Jamef_Model_Relatorio_Calculosfrete::STATUS_ERRO)
            					->save();

            return false;
        }
    }

}
