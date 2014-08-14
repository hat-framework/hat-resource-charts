<?php

class angularcharts extends charts{
    
    /**
     * load jsapi
     */
    public function initChart(){
        echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
        $this->html->LoadAngular();
        $this->html->LoadAngular('charts/f-angucharts.min.js');
    }
    
    /**
     * convert array data to json
     * info: http://code.google.com/intl/nl-NL/apis/chart/interactive/docs/datatables_dataviews.html#javascriptliteral
     */
    protected function dataToJson($data){
        
        $data = array_values($data);
        $this->getColumns($data);
        $names = $types = array(); 
        foreach($this->cols as $col){
            $names[] = $col['label'];
            $types[] = $col['type'];
        }
        foreach($data as &$dt){
            $dt = array_values($dt);
        }
        array_unshift($data, $types);
        array_unshift($data, $names);
        return json_encode($data, JSON_NUMERIC_CHECK);
    }
    
    public function draw($div = "", Array $options = array()){
        $title = "";
        if(isset($options['title'])){$title = $options['title']; unset($options['title']);}
        $options = empty($options)?"":"options='".json_encode($options)."'";
        $id = "angularcharts_".self::$_count;
        
        $data    = str_replace('"nil"', "null", $this->_data);
        $output = "
        \n<div id='$id'>".
            "<div ng-controller='fanguchartCtrl' ng-init='title=\"{$title}\";type=\"$this->_chartType\";dados={$data};'>".
                "<charts title='title' type='type' localdata='dados' $options></charts>".
            "</div>".
         "</div>\n";
        $this->reset();
        $this->html->LoadJsFunction("angular.bootstrap(document.getElementById('$id'),['myApp']);");
        return $output;
    }
}