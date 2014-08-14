<?php

abstract class charts extends \classes\Classes\Object{
    
    protected static $_first = true;
    protected static $_count = 0;

    protected $_divattr ='' ;
    protected $_chartType;

    protected $_data;
    protected $_skipFirstRow;
    protected $keys = array();
    
    public function __construct() {
        $this->LoadResource('html', 'html');
        $this->initChart();
    }
    
    /**
     * draws the chart
     */

    abstract public function draw($div = "", Array $options = array());
    
    /**
     * Load All libraries necessary to make charts
     */
    abstract public function initChart();
    
    /**
     * Transform data into json format
     */
    abstract protected function dataToJson($data);
    
    /**
     * loads the dataset and converts it to the correct format
     */
    public function load($data){
        $_data       = (is_array($data))? $this->dataToJson($data):$data;
        $this->_data = str_replace(array('"Date(', '))"'), array('new Date(', ")"), $_data);
        return $this;
    }
    
    public function init($chartType, $skipFirstRow = false){
        $this->_chartType = $chartType;
        $this->_skipFirstRow = $skipFirstRow;
        self::$_count++;
        return $this;
    }

    protected function reset(){
        self::$_first        = false;
        $this->_data         = array();
        $this->_skipFirstRow = false;
        $this->keys          = array();
        $this->_chartType    = "";
        $this->cols          = array();
    }

    protected $dados = array();
    public function setDados($array){
        $this->dados = $array;
        return $this;
    }

    protected $limit = '';
    public function setLimit($limit){
        $this->limit = $limit;
        return $this;
    }

    protected $offset = '';
    public function setOffset($offset){
        $this->offset = $offset;
        return $this;
    }

    protected $order = '';
    public function setOrder($order){
        $this->order = $order;
        return $this;
    }

    protected $where = '';
    public function setWhere($where){
        $this->where = $where;
        return $this;
    }
    
    protected $chartType = "LineChart";
    public function setChartType($chartType){
        $this->chartType = $chartType;
        return $this;
    }

    public function resetData(){
        $this->dados  = array();
        $this->offset = '';
        $this->limit  = '';
        $this->where  = '';
        $this->order  = '';
    }

    public function drawChartFromDatabase($modelname, $chartTitle, $seriesEixoX, $eixoYTitle, $eixox, $eixoy){
        $data = $this->LoadModel($modelname, 'model')->selecionar($this->dados, $this->where, $this->limit, $this->offset, $this->order);
        $this->resetData();
        $this->init($this->chartType, true)
             ->transformInChartData($data, $seriesEixoX, array(), $eixoYTitle, $eixox, $eixoy)
             ->draw('', array('title' => $chartTitle));
    }

    /**
     * @author Thompson <tigredonorte3@gmail.com>
     * Transforma um array simples da consulta do banco de dados em um array preparado para gerar o gráfico
     * e seta este array como o array de dados
     * @param array $data dados a serem transformados
     * @param string $seriesEixoX Séries que serão exibidas no eixo X
     * @param mixed $dadosDescription Dados que aparecerão quando o usuário passar o mouse por cima de um ponto do gráfico
     * @param mixed $eixoYTitle Título do eixo Y
     * @param string $eixox Chave que contém os dados do eito X
     * @param string $eixoy Chave que contém os dados do eito Y
     * 
     * @example A chamada
     * A chamada $this->transformInChartData(Array(
     *      Array ( [dscodconta] => 1    [dsconta] => Ativo Total                            [dtfim] => 2013-12-31 [nrvalor] => 68674019 ) 
     *      Array ( [dscodconta] => 3.01 [dsconta] => Receita de Venda de Bens e/ou Serviços [dtfim] => 2013-12-31 [nrvalor] => 34791391 ) 
     *      Array ( [dscodconta] => 3.03 [dsconta] => Resultado Bruto                        [dtfim] => 2013-12-31 [nrvalor] => 23393590 ) 
     * ), 'dsconta', array('dscodconta', 'dsconta'), 'Minha data', 'dtfim', 'nrvalor')
     * irá gerar um array contendo:
     * Array ( 
     *      [0] => Array ( [0] => Minha Data [1] => Ativo Total [2] => Receita de Venda de Bens e/ou Serviços [3] => Resultado Bruto) 
     *      [1] => Array ( [0] => 2013-12-31 [1] => 68674019    [2] => 34791391                               [3] => 23393590 )
     * )
     * @return object a instância de gcharts
     */
    public function transformInChartData($data, $seriesEixoX, $dadosDescription, $eixoYTitle, $eixox, $eixoy) {
        $out = array();
        if(empty($data)){
            $this->load($out);
            return $this;
        }
        if(!is_array($dadosDescription)) {$dadosDescription = array($dadosDescription);}
        $temp = array($eixoYTitle => '');
        foreach($data as $result){
            if(!isset($temp[$result[$seriesEixoX]])){
                $temp[$result[$seriesEixoX]] = '';
                foreach($dadosDescription as $kdesc){
                    $temp[$result[$seriesEixoX]] .= $result[$kdesc] . " ";
                }
            }
            if(!isset($out[$result[$eixox]])) {$out[$result[$eixox]][$eixoYTitle] = $result[$eixox];}
            $out[$result[$eixox]][$result[$seriesEixoX]] = $result[$eixoy];
        }
        $keys   = $this->getKeys($temp);
        $return = $this->getFormatedArray($out, $keys);
        uksort($return, function($a, $b){
            if($a == $b) return 0;
            return $a > $b?1:-1;
        });
        array_unshift($return, $keys);
        $this->load($return);
        return $this;
    }

    private function getKeys($array){
        $keys = array_keys($array);
        $keysout = array();
        foreach($keys as $k){
            $keysout[$k] = $k;
        }
        return $keysout;
    }

    private function getFormatedArray($out, $keys){
        $sample = $this->getSampleArray($keys);
        $outt = array();
        foreach($out as $k => $val){
            $temp = $sample;
            foreach($val as $key => $value){
                $temp[$key] = $value;
            }
            $outt[$k] = $temp;
        }
        return $outt;
    }

    private function getSampleArray($keys){
        $sample = array();
        foreach($keys as $k){
            $sample[$k] = 'nil';
        }
        return $sample;
    }

    public function setDivAttributes($string){
        $this->_divattr = $string;
        return $this;
    }

    public function setColum($id, $label, $type){
        $this->cols[] = array('id' => $id, 'label' => $label, 'type' => $type);
        if($id == "") $this->keys[] = $type;
        else          $this->keys[$id] = $type;
        return $this;
    }
    
    public function setOptions(&$options){
        $key = ($this->_chartType == "BarChart")?'hAxis':'vAxis';
        if(isset($options[$key])) return;
        $options[$key] = array(
            'minValue' => '0',
            'viewWindowMode' => 'explicit',
            'viewWindow' => array(
              'min' => '0'
            )
        );
    }
    
    /**
     * substracts the column names from the first and second row in the dataset
     */
    protected $cols  = array();
    protected function getColumns(&$data_arr){
        $data  = ($this->_skipFirstRow)?array_shift($data_arr):$data_arr[0];
        if(empty($data_arr)) return;
        $data2 = ($this->_skipFirstRow)?$data_arr[0]          :$data;
        if(!empty($this->cols)) return;
        foreach($data2 as $k => $value){
            $this->cols[] = $this->getCol($data[$k], $k, $value);
        }
    }
    
    protected function getCol($dt, $k, $value){
        $key = ($this->_skipFirstRow)?$dt:ucfirst($k);
        $arr = array('id' => '', 'label' => $key, 'type' => 'string');
        if(is_numeric($key))  {$arr['label'] = $value;}
        if(is_numeric($value) || $value == "nil"){$arr['type']  = 'number';}
        if(\classes\Classes\timeResource::isValidDate($value)) {$arr['type']  = 'date';}
        $this->keys[$k] = $arr['type'];
        return $arr;
    }
    
    protected function getRow($row, $type = 'v'){
        $c = array();
        foreach($row as $k => $v){
            if(isset($this->keys[$k])){
                $method = "format_". $this->keys[$k];
                if(method_exists($this, $method)){$v = $this->$method($v);}
            }
            $c[] = ($type === "v")? array('v' => $v):$v;
        }
        return $c;
    }
    
    private function format_date($date){
        $date = \classes\Classes\timeResource::getDbDate($date);
        $str  = str_replace("-", ',', $date);
        $e    = explode("-", $date);
        if(count($e) == 3){
            $e[1]--;
            $str = "{$e[0]},{$e[1]},{$e[2]}";
        }
        return "Date($str))";
    }

}