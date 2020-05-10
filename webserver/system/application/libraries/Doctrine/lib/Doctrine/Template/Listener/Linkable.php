<?php

class Doctrine_Template_Listener_Linkable extends Doctrine_Record_Listener
{
    /**
     * Array of sluggable options
     *
     * @var string
     */
    protected $_options = array();

    /**
     * __construct
     *
     * @param string $array 
     * @return void
     */
    public function __construct(array $options)
    {
      
        $this->_options = $options;
        
    }

    
    public function preInsert(Doctrine_Event $event)
    {
        $record = $event->getInvoker();
        $name = $record->getTable()->getFieldName($this->_options['name']);

        if ( ! $record->$name) {
            $record->$name = $this->buildLink($record, $this->_options['link']);
        }
        
        $links = $this->_options['links'];
        foreach($links as $index=>$options)
        {
          $name = isset($options['name']) ? $options['name'] : "link" . $index;
          $name = $record->getTable()->getFieldName($name);
          if ( ! $record->$name) {
            $record->$name = $this->buildLink($record, $options['link']);
          }
        }
    }
    
    
    public function preUpdate(Doctrine_Event $event)
    {
       $record = $event->getInvoker();
        $name = $record->getTable()->getFieldName($this->_options['name']);

        if ( ! $record->$name) {
            $record->$name = $this->buildLink($record, $this->_options['link']);
        }
        
        $links = $this->_options['links'];
        foreach($links as $index=>$options)
        {
          $name = isset($options['name']) ? $options['name'] : "link" . $index;
          $name = $record->getTable()->getFieldName($name);
          if ( ! $record->$name) {
            $record->$name = $this->buildLink($record, $options['link']);
          }
        }
        
    }
    
    public function postUpdate(Doctrine_Event $event)
    {
      $record = $event->getInvoker();
      $ref = empty($model['refValue']) ? $record->getTable()->getComponentName() : $model['refValue'];
      $ref .= "{".reset($record->getTable()->getIdentifierColumnNames()). "}";
      
      $name = $record->getTable()->getFieldName($this->_options['name']);
      $link_value = $record->$name;
      $dependents = $this->_options['dependents'];
      $conn = $record->getTable()->getConnection();
      $conn->beginTransaction();
      foreach($dependents as $model)
      {
        $q = Doctrine_Query::create()
          ->from($model['model'] . " m")
          ->set($model['linkColumn'], '?', $link_value)
          ->where($model['refColumn'] . " = ?", $this->buildLink($record, $ref));
        
        $q->update();
        $q->execute();

      }
      $conn->commit();
    }
    public function postInsert(Doctrine_Event $event)
    {
      $record = $event->getInvoker();
      $ref = empty($model['refValue']) ? $record->getTable()->getComponentName() : $model['refValue'];
      $newref = $ref . "{".reset($record->getTable()->getIdentifierColumnNames()). "}";
      $ref .= "__new";
      
      $name = $record->getTable()->getFieldName($this->_options['name']);
      $link_value = $record->$name;
      $dependents = $this->_options['dependents'];
      $conn = $record->getTable()->getConnection();
      $conn->beginTransaction();
      foreach($dependents as $model)
      {
        $q = Doctrine_Query::create()
          ->from($model['model'] . " m")
          ->set($model['linkColumn'], '?', ($link_value))
          ->set($model['refColumn'], "?", $this->buildLink($record, $newref))
          ->where($model['refColumn'] . " = ?", $this->buildLink($record, $ref));
        
        $q->update();
        $q->execute();

      }
      $conn->commit();
    }
    

    /**
     * Generate the slug for a given Doctrine_Record based on the configured options
     *
     * @param Doctrine_Record $record 
     * @return string $slug
     */
    protected function buildLink($record, $link)
    {
        
        if (empty($this->_options['fields'])) {
          
          foreach($record->toArray() as $k=>$v)
          {
            $var = "{" . $k . "}";
            $link = str_replace($var, $v, $link);
          }
            
        } else {
              
            foreach($this->_options['fields'] as $k)
            {
              $var = "{" . $k . "}";
              $v = $record->$k;
              $link = str_replace($var, $v, $link);
            }
        }
        
        
        $link = strtolower($link);
        return $link;
    }
}