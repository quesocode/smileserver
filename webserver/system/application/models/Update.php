<?php


class Update extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->setTableName('Updates');
		$this->hasColumn('title', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));

		$this->hasColumn('story', 'string', NULL, array(
				'type' => 'string'
			));
          $this->hasColumn('html', 'string', NULL, array(
				'type' => 'string'
			));
          $this->hasColumn('caption', 'string', NULL, array(
				'type' => 'string'
			));
          $this->hasColumn('name', 'string', NULL, array(
				'type' => 'string'
			));
		$this->hasColumn('link', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));
          $this->hasColumn('page', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));
          $this->hasColumn('img_curl', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));
          $this->hasColumn('img_cname', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));
          $this->hasColumn('photo', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));
          $this->hasColumn('video', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));
          $this->hasColumn('date', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));
		$this->hasColumn('tweet', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));
          $this->hasColumn('hashid', 'string', 100, array(
				'type' => 'string',
				'length' => '100',
			));

		$this->hasColumn('author_id', 'integer', 11, array(
				'type' => 'integer',
				'length' => '11',
			));
		$this->hasColumn('visits', 'integer', 11, array(
				'type' => 'integer',
				'length' => '11',
			));
		$this->hasColumn('plugs', 'integer', 11, array(
				'type' => 'integer',
				'length' => '11',
			));
		$this->hasColumn('type', 'enum', 50, array(
				'type' => 'enum',
				'length' => '50',
				'values'=>array('update', 'text', 'blog', 'photo', 'video', 'music', 'download', 'product', 'podcast', 'news', 'other', 'link'),
			));
		$this->hasColumn('latitude', 'string', 100, array(
				'type' => 'string',
				'length' => '100',
			));
		$this->hasColumn('longitude', 'string', 100, array(
				'type' => 'string',
				'length' => '100',
			));
          $this->hasColumn('public', 'enum', 3, array(
             'type' => 'enum',
             'values'=>array('no','yes'),
             'length' => '3',
             ));

	}

	public function setUp()
	{
		parent::setUp();


		$this->hasOne('User as Author', array(
				'local' => 'author_id',
				'foreign' => 'id'));



		$timestampable0 = new Doctrine_Template_Timestampable(array(
				'created' =>
				array(
					'name' => 'date_created',
					'type' => 'timestamp',
					'format' => 'Y-m-d H:i:s',
				)
			));
		$hashable = new Doctrine_Template_Hashable();

		$this->actAs($hashable);

		$this->actAs($timestampable0);
	}
     
	public function increaseViews()
	{
          $this->visits++;
          $this->save();
	}
	
	public function increasePlugs()
	{
          $this->plugs++;
          $this->save();
          
          $this->Author->plugs++;
	    $this->Author->save();
	}
	
	public function postInsert()
	{
	    $this->Author->updates++;
	    $this->Author->save();
	}
	
}