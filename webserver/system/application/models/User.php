<?php


class User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('Users');
        $this->hasColumn('email', 'string', 50, array(
             'type' => 'string',
             'length' => '50',
             ));
        $this->hasColumn('password', 'string', 100, array(
             'type' => 'string',
             'length' => '100',
             ));
        $this->hasColumn('first', 'string', 50, array(
             'type' => 'string',
             'length' => '50',
             ));

        $this->hasColumn('last', 'string', 50, array(
             'type' => 'string',
             'length' => '50',
             ));
        $this->hasColumn('displayname', 'string', 100, array(
             'type' => 'string',
             'length' => '100',
             ));
        $this->hasColumn('gender', 'string', 6, array(
             'type' => 'string',
             'length' => '6',
             ));
       
        $this->hasColumn('dob', 'timestamp', 25, array(
             'type' => 'timestamp',
             'length' => '25',
             ));
        $this->hasColumn('updates', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('plugs', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('custom_domain', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        
    }

    public function setUp()
    {
        parent::setUp();
   
          $this->hasMany('UserConnection as Connections', array(
             'local' => 'id',
             'foreign' => 'user_id'));
        
        $sluggable0 = new Doctrine_Template_Sluggable(array(
             'fields' => 
             array(
              0 => 'first',
              1 => 'last',
             ),
             'name' => 'slug',
             'type' => 'string',
             'length' => '255',
             ));
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
             ),
             ));
        $this->actAs($sluggable0);
        $this->actAs($timestampable0);
    }
}