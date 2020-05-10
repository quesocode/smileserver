<?php


class UserConnection extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('UserConnections');
        $this->hasColumn('username', 'string', 50, array(
             'type' => 'string',
             'length' => '50',
             ));
        $this->hasColumn('password', 'string', 100, array(
             'type' => 'string',
             'length' => '100',
             ));
        $this->hasColumn('account_type', 'enum', 50, array(
             'type' => 'enum',
             'length' => '50',
             'values'=>array('twitter','facebook','flickr','myspace'),
             ));

       
        $this->hasColumn('user_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('mugshot_url', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('activated', 'enum', 3, array(
             'type' => 'enum',
             'values'=>array('no','yes'),
             'length' => '3',
             ));
        $this->hasColumn('primary', 'enum', 1, array(
             'type' => 'enum',
             'values'=>array('0','1'),
             'length' => '1',
             ));
        $this->hasColumn('data', 'string', NULL, array(
             'type' => 'string'
             ));
        $this->hasColumn('token', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('secret', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('displayname', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        
    }

    public function setUp()
    {
        parent::setUp();
   

        $this->hasOne('User', array(
             'local' => 'user_id',
             'foreign' => 'id'));

        
    }
    public function activate($token,$secret=NULL, $data=NULL)
    {
          $datatypes = array('id','secret','token');
          if(is_object($data) && !is_null($data))
          {
               $a = array();
               foreach($data as $k=>$v) 
               {
                    
                    if(!in_array($k,$datatypes)) $a[$k] = $v;
               }
               $data = $a;
          }
          
          
          
          if(!empty($data) && count($data))
          {

               $this->cleanData($data);
               $this->fromArray($data);
          }
          

          
          $this->token($token);
          $this->secret($secret);
          $this->activated = 'yes';
          
                    
          $this->save();
    }
    public function token($str=NULL)
    {
          if(!is_null($str)) 
          {
               $this->token = $this->format($str);
               return $str;
          }
          return isset($this->token) && $this->token ? $this->format($this->token) : NULL;
    }
    public function secret($str=NULL)
    {
          if(!is_null($str)) 
          {
               $this->secret = $this->format($str);
               return $str;
          }
          return isset($this->secret) && $this->secret ? $this->format($this->secret) : NULL;
    }
    
    public function isActive()
    {
          return $this->activated == 'yes';
    }
    
    public function is($type)
    {
          $types = explode(',',strtolower($type));
          return in_array(strtolower($this->account_type), $types);
          
    }
    
    private function format($Str_Message) { 
     //Function : encrypt/decrypt a string message v.1.0  without a known key 
     //Author   : Aitor Solozabal Merino (spain) 
     //Email    : aitor-3@euskalnet.net 
     //Date     : 01-04-2005 
         $Len_Str_Message=STRLEN($Str_Message); 
         $Str_Encrypted_Message=""; 
         FOR ($Position = 0;$Position<$Len_Str_Message;$Position++){ 
             // long code of the function to explain the algoritm 
             //this function can be tailored by the programmer modifyng the formula 
             //to calculate the key to use for every character in the string. 
             $Key_To_Use = (($Len_Str_Message+$Position)+1); // (+5 or *3 or ^2) 
             //after that we need a module division because can´t be greater than 255 
             $Key_To_Use = (255+$Key_To_Use) % 255; 
             $Byte_To_Be_Encrypted = SUBSTR($Str_Message, $Position, 1); 
             $Ascii_Num_Byte_To_Encrypt = ORD($Byte_To_Be_Encrypted); 
             $Xored_Byte = $Ascii_Num_Byte_To_Encrypt ^ $Key_To_Use;  //xor operation 
             $Encrypted_Byte = CHR($Xored_Byte); 
             $Str_Encrypted_Message .= $Encrypted_Byte; 
             
             //short code of  the function once explained 
             //$str_encrypted_message .= chr((ord(substr($str_message, $position, 1))) ^ ((255+(($len_str_message+$position)+1)) % 255)); 
         } 
         RETURN $Str_Encrypted_Message; 
     } //end function 
    
    
    
}