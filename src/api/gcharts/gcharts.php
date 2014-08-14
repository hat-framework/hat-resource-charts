<?php

class gcharts extends charts{
        
    public function draw($div = "", Array $options = array()){
        static $divname = 0;
        if($div == ""){
            $divname++;
            $div = "charts$divname";
        }
        $output = '';
        $this->setOptions($options);
        if(self::$_first)$output .= $this->initChart();

        // start a code block
        $output .= '<script type="text/javascript">';

        // set callback function
        $output .= 'google.setOnLoadCallback(drawChart' . self::$_count . ');';

        // create callback function
        $fnname  = "drawChart".self::$_count;
        $output .= "function $fnname(divid){";

        $data    = is_array($this->_data)?"":$this->_data;
        $data    = str_replace('"nil"', "null", $data);
        $output .= 'var data = new google.visualization.DataTable(' . $data . '); ';

        // set the options
        $output .= 'var options = ' . json_encode($options) . ';';

        // create and draw the chart
        $output .= "var div = '$div'; "
                . "if(typeof divid === 'string'){div = divid;}";
        $output .= 'var chart = new google.visualization.' . $this->_chartType . '(document.getElementById(div));';
        $output .= 'chart.draw(data, options);';

        $output .= "} </script><div id='$div' $this->_divattr></div> <div id='fullscreen_$div' style='display:none;'></div>";
        $this->reset();
        return $output;
    }
    
    /**
     * load jsapi
     */
    public function initChart(){
        self::$_first = false;

        $output = '';
        $output .= '<script type="text/javascript" src="https://www.google.com/jsapi"></script>'."\n";
        $output .= '<script type="text/javascript">google.load(\'visualization\', \'1.0\', {\'packages\':[\'corechart\'], \'language\': \'pt-br\'});</script>'."\n";
        echo $output;
    }
    
    /**
     * loads the dataset and converts it to the correct format
     */
    public function load($data){
        $this->_data = (is_array($data))?$this->dataToJson($data):$data;
        return $this;
    }
    
    /**
     * convert array data to json
     * info: http://code.google.com/intl/nl-NL/apis/chart/interactive/docs/datatables_dataviews.html#javascriptliteral
     */
    protected function dataToJson($data){
        $data = array_values($data);
        $rows = array();
        $this->getColumns($data);
        foreach($data as $row){
            $c      = $this->getRow($row);
            $rows[] = array('c' => $c);
        }
        return json_encode(array('cols' => $this->cols, 'rows' => $rows));
    }
}
