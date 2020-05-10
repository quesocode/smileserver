<?php

class Doctrine_Template_Linkable extends Doctrine_Template
{
    /**
     * Array of Sluggable options
     *
     * @var string
     */
    protected $_options = array('name'          =>  'link',
                                'alias'         =>  null,
                                'edit_alias'         =>  null,
                                'create_alias'         =>  null,
                                'delete_alias'         =>  null,
                                'manage_alias'         =>  null,
                                'link'  => null,
                                'edit_link_name' => 'edit_link',
                                'edit_link' => null,
                                'dependents' => array(),
                                'create_link_name' => 'create_link',
                                'create_link' => null,
                                'delete_link_name' => 'delete_link',
                                'delete_link' => null,
                                'custom_links' => array(),
                                'links'        =>  array(),
                                'fields'      =>  array(),
                                'indexName'     =>  'linkable'
    );

    /**
     * Set table definition for Sluggable behavior
     *
     * @return void
     */
    public function setTableDefinition()
    {
        if(!is_null($this->_options['link']))
        {
          $name = $this->_options['name'];
          if ($this->_options['alias']) {
              $name .= ' as ' . $this->_options['alias'];
          }
          $this->hasColumn($name, "string", 500, array());
        }
        $links = $this->_options['links'];
        foreach($links as $index=>$options)
        {
          $name = isset($options['name']) ? $options['name'] : "link" . $index;
          if (isset($options['alias'])) {
            $name .= ' as ' . $options['alias'];
          }
          $link = $options['link'];
          $this->hasColumn($name, "string", 500, array());
          
        }
        


        $this->addListener(new Doctrine_Template_Listener_Linkable($this->_options));
    }
}