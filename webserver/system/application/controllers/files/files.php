<?php
require_once APPPATH . '/controllers/base.php';


class Files extends Base
{
     public $file_dir = '/images/thumbs/';
	function index()
	{
	     //error_reporting(0);
		$file = $this->getFile();

		// if there are no results.... RESULT NOT FOUND
		if (!$file) {
			return $this->requestNotFound();
		}
		// if the file type is not allowed
		/*
if (!Core::path()->isAllowedFileType($file['type'])) {
			$resp = $this->notAllowedType();

			//throw new Controller_Http_Response(406, $resp);
		}
*/
		// if there was a server error
		if (isset($this->badRequest) && $this->badRequest) {
			$resp = $this->badRequest();

			//throw new Controller_Http_Response(400, $resp);
		}
		// if there was a server error
		if (isset($this->serverError) && $this->serverError) {

			$resp = $this->serverError();

			//throw new Controller_Http_Response(500, $resp);
		}

		header("Accept-Ranges: bytes");
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file['path'])).' GMT');
		header('Content-type: ' . $file['mime'] . '; charset=base64_encode');
          header("Cache-Control: no-store, no-cache, must-revalidate");
          header("Cache-Control: post-check=0, pre-check=0");
          header("Pragma: no-cache");
          header('Content-Length: ' . $file['size']);
          
		if (is_file($file['path']) && (!isset($this->image_notFound) || !$this->image_notFound)) {
			//header('Content-Disposition: filename="'.$file['filename'].'";');
               //header('Content-Encoding: base64_encode');
			
			
			/* $contents = file_get_contents($file['path']); */
			readfile($file['path']);
			
			//$this->output->set_output($contents);
		}
		else {

			return $this->requestNotFound();
		}
	}

	public function setFile($file)
	{
		$this->file = $file;
	}
	public function getFile()
	{
		$this->image_notFound = false;
		if (isset($this->file)) return $this->file;


		$id = $this->uri->segment(3);
		if($id && is_numeric($id))
		{
               $q = Doctrine_Query::create()->from("Thumbnail t")->where("t.id = ?", $id);
               $file = $q->execute()->getFirst();
               if($file) $file = $file->toArray();
		}
		else
		{
               $this->load->helper('url');
               $url = $this->input->get_post('img');
               
               $urlp = $this->parseUrl($url);
                    
               
               if($url) 
               {
                    
                    $urlp = $this->parseUrl($url);
                    
                    $dir = FCPATH . 'images/thumbs/' . $urlp['host'] . substr($urlp['path'], 0, strrpos($urlp['path'], '/')+1);
                    $file_path = $dir . $urlp['file'];
                    
                    $this->load->helper('url');
                    $new_url = site_url($this->file_dir . $urlp['host'] . $urlp['path']);
                    $new_uri = ($this->file_dir . $urlp['host'] . $urlp['path']);
                    
                    if(!is_file($file_path))
                    {
                         $contents = file_get_contents($url);
                         if($contents)
                         {
                              Filer::save($contents, $urlp['file'], $dir, false);
                              $file = array('path'=>$file_path, 'ref_url'=>$url,'ref_web'=>$urlp['host'], 'type'=>Filer::type($file_path), 'mime'=>Filer::mime($file_path), 'size'=>Filer::size($file_path), 'filename'=>Filer::filename($file_path), 'extension'=>Filer::extension($file_path), 'last_modified'=>Filer::modified($file_path), 'last_accessed'=>Filer::accessed($file_path), 'url'=>$new_url, 'uri'=>$new_uri);
                              
                              $sfile = new SFile();
                              $sfile->fromArray($file);
                              $sfile->save();
                              $file = $sfile->toArray();
                         }
                         else
                         {
                              exit('Unable to download file');
                         }
                    }
                    else
                    {
                         $file = array('ref_url'=>$url,'ref_web'=>$urlp['host'],'path'=>$file_path, 'type'=>Filer::type($file_path), 'mime'=>Filer::mime($file_path), 'size'=>Filer::size($file_path), 'filename'=>Filer::filename($file_path), 'extension'=>Filer::extension($file_path), 'last_modified'=>Filer::modified($file_path), 'last_accessed'=>Filer::accessed($file_path),'url'=>$new_url, 'uri'=>$new_uri);
                    }
                    
                    
               }
               else
               {
                    exit('No Image found');
               }
               
		}
		
		

		
		if ($file) 
		{
			$return = $this->file = $file;
		}
		
		if (empty($return) || (isset($return['path']) && !is_file($return['path']))) {
			$this->image_notFound = true;
			$return = $this->file = $this->getNotFound();

		}

		return $return;
	}
	public function getNotFound()
	{
		return false;
	}
	public function serverError()
	{
	    show_404();
		/*
return $this->render($this->document->template, array
			(
				'content' => NULL,
				'heading'=>"Wrong File",
				'encoding' => $this->document->encoding,
				'title' => "The file you requested is not the type allowed.",
				'scripts' => $this->document->scripts,
				'styles' => $this->document->styles
			));
*/
	}
	public function badRequest()
	{
	    show_404();
		/*
return $this->render($this->document->template, array
			(
				'content' => NULL,
				'heading'=>"Wrong File",
				'encoding' => $this->document->encoding,
				'title' => "The file you requested is not the type allowed.",
				'scripts' => $this->document->scripts,
				'styles' => $this->document->styles
			));
*/
	}
	public function notAllowedType()
	{
		return $this->render($this->document->template, array
			(
				'content' => NULL,
				'heading'=>"Wrong File",
				'encoding' => $this->document->encoding,
				'title' => "The file you requested is not the type allowed.",
				'scripts' => $this->document->scripts,
				'styles' => $this->document->styles
			));

	}
}