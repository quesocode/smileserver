<?php
require_once APPPATH . '/controllers/base.php';

class Gateway extends Base
{
     function __construct ()
     {
          // initialize the controller
          parent::__construct();
          // set the types of messages possible
          $this->types = array('SMS','mms-gif','mms-jpg','mms-jar','mms-wav');
          $this->load->helper(array('form', 'url'));
     }
     
     function index()
     {
          $this->load->view('mobile/form.html', array('error' => ' ' ));
     }
     
	function send_msg()
	{

	    /* MESSAGE CONFIG */
	    
	    
	    // number message is sent to
	    $number  = $this->input->get_post('number');
	    
	    $carrier = $this->input->get_post('carrier');
	    if((!$carrier || $carrier === '') && $number)
	    {
	         $number_data = $this->getCarrier($number);
	         if($number_data && $number_data['mms_email'])
	         {
	              $email = $number_data['mms_email'];
	         }
	    }
	    else
	    {
	         $email = $number.'@'.$carrier;
	    }
	    
	    // generate the message
	    $message = $this->getMessage();
	    $url = $this->getUploadedFileUrl();

	    
	    
	     $this->load->helper('url');
          $response = $this->sendEmail($email,$message);
          if($response)
          {
               $redirect = $this->input->get_post('success_redirect');
               if($redirect)
               {
                    redirect($redirect);
               }
               
               
          }
          else
          {
               $redirect = $this->input->get_post('error_redirect');
               if($redirect)
               {
                    redirect($redirect);
               }
               
          }
          return $response;
          
	    
	     
	}
	function getCarrier ($number)
	{
	     $username = "blackpulp";   // Must be changed to your username
          $password = "blAck1181";       // Must be changed to your password
          
          // Build the URL
          $url = "https://api.data24-7.com/textat.php?username=$username&password=$password";
          
          $url .= "&p1=" . $number;
          
          
          // Send the query
          $xml = simplexml_load_file($url);
          
          if(!$xml) return;
          
          
          $data = array(
          
               'number'=>$xml->results->result[0]->number,
               'status'=>$xml->results->result[0]->status,
               'carrier'=>$xml->results->result[0]->carrier_name,
               'carrier_id'=>$xml->results->result[0]->carrier_id,
               'sms_email'=>$xml->results->result[0]->sms_address,
               'mms_email'=>$xml->results->result[0]->mms_address
          
          );
          
          return $data;

	}
	function sendEmail ($email,$msg)
	{
	     $username = $this->input->get_post('username') ? $this->input->get_post('username') : 'hello';
	     $message = "\n\n";
          $boundary =  md5( uniqid ( rand() ) ); 
          $boundary2 =  md5( uniqid ( rand() ) ); 
          
          $filepath = $this->file_data['full_path'];
          $mime = $this->file_data['file_type'];
          $name = $this->file_data['raw_name'];
          $theFile = $this->file_data['file_name'];
          
          

          
          $message .= '--'.$boundary. "\n";
          
          $message .= "Content-Type: ".$mime."; name=\"".$theFile."\"\n"; 
          $message .= "Content-Disposition: attachment; filename=\"$theFile\"\n"; 
          $message .= "Content-Transfer-Encoding: base64\n\n"; 
          
          
          $path = $filepath;
          $fp = fopen($path, 'r'); 
          $content = NULL;
          do //we loop until there is no data left 
          { 
                  $data = fread($fp, 8192); 
                  if (strlen($data) == 0) break; 
                  $content .= $data; 
                } while (true); 
          $content_encode = chunk_split(base64_encode($content)); 
          
          $message .= $content_encode . "\n\n"; 
          $message .= '--'.$boundary . "--" . "\n"; 
          
          $headers = 'Return-path: <'.$username.'@swmp.it>'."\n";
          $headers .= 'Envelope-to: '.$email."\n";
          $headers .= "From: ".$username." <".$username."@swmp.it>" ."\n"; 
          $headers .= 'Bcc: willie@tinystudios.com' . "\n";
          $headers .= "MIME-Version: 1.0\n"; 
          $headers .= "Content-Type: multipart/mixed; boundary=$boundary" ."\n"; 
          
          
          $headers .= 'X-Spam-Status: No, score=-2.6'."\n";
          
          $headers .= 'X-Spam-Score: -25'."\n";
          $headers .= 'X-Spam-Bar: --'."\n";
          $headers .= 'X-Spam-Flag: NO'."\n";
          $headers .= 'Delivery-date: Tue, 06 Jul 2010 23:00:32 -0500'."\n";
          $headers .= 'X-Sender: '.$username.'@swmp.it'."\n";
          $headers .= 'X-Mailer: Swmp.it'."\n";
          $headers .= 'X-Priority: 3 (Normal)'."\n";
          $headers .= 'Message-ID: <'.md5(rand()).'@swmp.it>'."\n";


          
          
          
          $sent = mail($email, $msg, $message, $headers,"-f ".$username."@swmp.it"); 
          error_log('mail: ' . $sent);
           if($sent)
           {
               return true;
           }
           else
           {
               show_error('Message was not sent. There was a server error.');
           } 
          
         
          
          
          
          
          
          

	}
	
	
	function getMessageType ()
	{
	    $type  = $this->input->get_post('type');
	    // check if its a valid type
	    
	    if(!in_array($type,$this->types))
	    {
	         $type = 'mms-' . $type;
	         var_dump($type);
	         if(!in_array($type,$this->types))
	         {
	              // if its not, show an error
     	         show_error('Invalid message type');
     	         exit();
	         }
	         
	    }
	    var_dump($type);
	    return $type;
	}
	
	function getMessage ()
	{
	    $msg = $this->input->get_post('msg');
	    if(!$msg) $msg = $this->input->get_post('message');
	    
	    return $msg;
	}
	function isMMS()
	{
	    return strtolower($this->type) != 'sms';
	}
	
	function getFileMessage()
	{
	    $url = $this->getFileUrl();
	    if(!$url)
	    {
	         show_error('Error: Unable to get a valid URL to make message');
			exit();
	    }
	    var_dump($url);
	    return $this->getFileHex($url);
	    
	    
	}
	function getFileUrl()
	{
	    $url = $this->input->get_post('url');
	    if(!$url) $url = $this->getUploadedFileUrl();
	    return $url;
	}
	function getUploadedFileUrl()
	{
	    $url = NULL;
	    $config['upload_path'] = FCPATH . 'uploads/';
		$config['allowed_types'] = 'gif|jpg|wav|jar|pdf|mp3|mpg|mov';
		$config['max_size']	= '5000';
		$config['max_width']  = '0';
		$config['max_height']  = '0';
		
		$this->load->library('upload', $config);
	    $field_name = "file";
		if ( ! $this->upload->do_upload($field_name))
		{
			$error = array('error' => $this->upload->display_errors());
			
			show_error('Upload Error: '.$this->upload->display_errors('<p>', '</p>'));
			exit();
			
		}	
		else
		{
		   /*
		   Array
               (
                   [file_name]    => mypic.jpg
                   [file_type]    => image/jpeg
                   [file_path]    => /path/to/your/upload/
                   [full_path]    => /path/to/your/upload/jpg.jpg
                   [raw_name]     => mypic
                   [orig_name]    => mypic.jpg
                   [file_ext]     => .jpg
                   [file_size]    => 22.2
                   [is_image]     => 1
                   [image_width]  => 800
                   [image_height] => 600
                   [image_type]   => jpeg
                   [image_size_str] => width="800" height="200"
               )
               */
			$data = $this->upload->data();
			$this->file_data = $data;
			
			$url = base_url() . 'uploads/'. $data['file_name'];
		}
		return $url;
	    
	}
	
	function getFileHex ($fileurl)
	{
          
          // reads file using standard PHP file system functions
          $file = fopen( $fileurl, "r" );
          if (!$file) exit;
          $fileContent = "";
          while ( !feof($file) ) {
          $fileContent .= fread( $file, 1024 );
          }
          fclose( $file );
          // hex encode image data
          $encodedImage='';
          for ($i=0; $i < strlen($fileContent); $i++)
          {
               $encodedImage .= sprintf("%02X",(ord($fileContent[$i])));
          }
          return $encodedImage;
	}
	
	
}

