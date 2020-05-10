<?php


class SFile extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->setTableName('SFiles');


          $this->hasColumn('update_id', 'integer', 11, array(
				'type' => 'integer',
				'length' => '11',
			));
		$this->hasColumn('author_id', 'integer', 11, array(
				'type' => 'integer',
				'length' => '11',
			));
		$this->hasColumn('size', 'integer', 11, array(
				'type' => 'integer',
				'length' => '11',
			));
          $this->hasColumn('views', 'integer', 11, array(
				'type' => 'integer',
				'length' => '11',
			));

		$this->hasColumn('type', 'string', 20, array(
				'type' => 'string',
				'length' => '20',
			));
		$this->hasColumn('extension', 'string', 10, array(
				'type' => 'string',
				'length' => '10',
			));
		$this->hasColumn('mime', 'string', 50, array(
				'type' => 'string',
				'length' => '50',
			));
		$this->hasColumn('path', 'string', 255, array(
				'type' => 'string',
				'length' => '255',
			));
		$this->hasColumn('filename', 'string', 255, array(
			'type' => 'string',
			'length' => '255',
		));
          $this->hasColumn('url', 'string', 255, array(
			'type' => 'string',
			'length' => '255',
		));
		 $this->hasColumn('uri', 'string', 255, array(
			'type' => 'string',
			'length' => '255',
		));
		$this->hasColumn('ref_url', 'string', 255, array(
			'type' => 'string',
			'length' => '255',
		));
		$this->hasColumn('ref_web', 'string', 255, array(
			'type' => 'string',
			'length' => '255',
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
		
		
		$hashable = new Doctrine_Template_Hashable();

		$this->actAs($hashable);
	}
}