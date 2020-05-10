<?php


class ShortLink extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('ShortLinks');
        $this->hasColumn('url', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
      
        
        
        $this->hasColumn('update_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        
        $this->hasColumn('author_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('visits', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        
        
    }

    public function setUp()
    {
        parent::setUp();
   

        $this->hasOne('User as Author', array(
             'local' => 'author_id',
             'foreign' => 'id'));
             
        $this->hasOne('Update', array(
             'local' => 'update_id',
             'foreign' => 'id'));
             
        $hashable = new Doctrine_Template_Hashable();
        
        $this->actAs($hashable);
    }
}