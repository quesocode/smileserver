<?php
include_once 'base.php';

class Thumbit extends Base
{
	function index()
	{
	    $this->load->helper('url');
	    $data = array();
	    
	    $this->view($this->load->view('thumbit/index.html', $data, true));
	    
	}
	
	
}