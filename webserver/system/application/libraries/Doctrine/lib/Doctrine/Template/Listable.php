<?php

class Doctrine_Template_Listable extends Doctrine_Template
{
    /**
     * Array of Sluggable options
     *
     * @var string
     */
    protected $_options = array('text'=>null,
                                'fields'      =>  array(),
                                'dateFormat' => "m/d/Y",
                                'indexName'     =>  'listable'
    );

    
    public function toString()
    {
        $record = $this->getInvoker();
        return $this->convertText($record, $this->_options['text']);
    }
    protected function convertText($record, $text=NULL)
    {
        if(is_null($text))
        {
          $text = '';
          foreach ($this->_options['fields'] as $field) {
              $text .= $record->$field . ' ';
          }
        }
        else
        {
          if (empty($this->_options['fields'])) {
            
            foreach($record->toArray() as $k=>$v)
            {
              $var = "{" . $k . "}";
              $text = str_replace($var, $v, $text);
            }
              
          } else {
               
              foreach($this->_options['fields'] as $k)
              {
                $var = "{" . $k . "}";
                $v = $record->$k;
                $text = str_replace($var, $v, $text);
              }
          }
        
        }
        return $text;
    }
    /**
     * Gets the timestamp in the correct format based on the way the behavior is configured
     *
     * @param string $type 
     * @return void
     */
    public function getTimestamp($type, $value)
    {
        
    }
}