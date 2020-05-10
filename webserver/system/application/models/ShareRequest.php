<?php

include_once(APPPATH . '/libraries/Swmpd.php');


class ShareRequest extends Doctrine_Record
{
     public function sendMessage ()
     {
          $gateway = false;
          $uri = "/app/?swmp=1";
          $input = $_GET+$_POST;
          if(isset($input['gateway']))
          {
               $purl = parse_url($input['gateway']);
               $purl['query'] = isset($purl['query']) ? "&" . $purl['query'] : "";
               $purl['path'] = isset($purl['path']) ? $purl['path'] : "/";
               $gateway = $purl['host'];
               $uri = substr($purl['path'],1) . "?swmp=1" . $purl['query'];
               
          }
          else
          {
            // if there was no custom gateway provided
            $gateway = "swmp.it";
          }
          
          
          Swmpd::setBaseUrl($gateway);
          $uri = $uri . "&method=processMessage&destination=" . $this->destination . "&message=" . $this->message . "&device=".$this->device;
          
          
          $file = $this->file_url;
          $p = parse_url($file);
          parse_str($p['query'],$arr);
          $n = pathinfo($arr['path']);
          
          $sourceFilePath = $arr['path'];
          
          $CI =& get_instance();
          
          
          $path = $CI->filePathForURL($this->file_url);
          if(!is_file($path))
          {
             $contents = file_get_contents($this->file_url);
          }
          
          
          
          $mime = "image/jpeg";
          if(strstr($n['basename'],'png'))
          {
               $mime = "image/png";
          }
          
          if(!is_file($path))
          {
                var_dump('no file found');
               error_log("Smilebooth iPad Error: File not found. Please find file and put it in the correct folder.");
               error_log("Looking for File: ".$image);
     		       $this->sent = "no";
     		       $this->error = "yes";
              $this->save();
     
           		return 'File not found: ' . $image;
          }
          error_log("Uploading image: ".$this->file_url);
          $files = array("file"=>array(
                         'tmp_name'=>$path,
                         'name'=>$n['basename'],
                         'type'=>$mime)
                    );
          $msg = Swmpd::post($uri,$this->toArray(),$files,true);
          //error_log("return: " . $msg);
          $this->sent = "yes";
          $this->save();
          //echo 'result: ' . $msg;
          return $msg;
     } 
    public function setTableDefinition()
    {
        $this->setTableName('ShareRequests');
        

        $this->hasColumn('sent', 'enum', 3, array(
             'type' => 'enum',
             'values' => array('yes','no'),
             'default' => 'no',
             'length' => '3',
             ));
        $this->hasColumn('error', 'enum', 3, array(
             'type' => 'enum',
             'values' => array('yes','no'),
             'default' => 'no',
             'length' => '3',
             ));
        $this->hasColumn('code', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('username', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('file_url', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('message', 'string', 999999, array(
             'type' => 'string',
             'length' => '999999',
             ));
        $this->hasColumn('destination', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('device', 'string', 100, array(
				'type' => 'string',
				'length' => '100',
			));
        
    }

    public function setUp()
    {
        parent::setUp();
   
        
             
        
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