<?php


class Plug extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('Plugs');
        

        $this->hasColumn('update_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('author_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('plugger_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('type', 'string', 100, array(
				'type' => 'string',
				'length' => '100',
			));
        
    }

    public function setUp()
    {
        parent::setUp();
   
        $this->hasOne('Update', array(
             'local' => 'update_id',
             'foreign' => 'id'));
        $this->hasOne('User as Author', array(
             'local' => 'author_id',
             'foreign' => 'id'));
        $this->hasOne('User as Plugger', array(
             'local' => 'plugger_id',
             'foreign' => 'id'));
             
        
        $timestampable0 = new Doctrine_Template_Timestampable(array(
             'created' => 
             array(
              'name' => 'date_created',
              'type' => 'timestamp',
              'format' => 'Y-m-d H:i:s',
             ),
             'updated' => 
             array(
              'name' => 'date_updated',
              'type' => 'timestamp',
              'format' => 'Y-m-d H:i:s',
             )
             ));
        $this->actAs($timestampable0);
    }
}