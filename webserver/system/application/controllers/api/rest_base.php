<?php

require APPPATH.'libraries/REST.php';

class Rest_base extends REST {
	public $model_name;
	
	public function search()
	{
		$results = array('results'=>array(),'method'=>'search', 'numResults'=>0, 'filters'=>array());
		// get the various filters
		error_log('============== SEARCHING ==============');
		error_log(print_r($_POST, true));
		$filters = $this->filter_search_criteria($_POST);
		error_log(print_r($filters, true));
		$results['filters'] = $filters;
		foreach($filters as $model=>$filter)
		{
			$model = Core::getModelName($model);
			if($model)
			{
				$q = Doctrine_Query::create()
					->from($model . ' m');
				if(isset($filter['search']))
				{
					$search_term = $filter['search'];
				    unset($filter['search']);
				}
				
				if(count($filter))
				{
					$columns = Doctrine::getTable($model)->getColumns();
					foreach($filter as $k=>$v)
					{
						if(isset($columns[$k]) && !is_array($v))
						{
							$q->andWhere("m.$k = ?", $v);
						}
					}
				}
				if(isset($filter['search']))
				{
					$q = Doctrine::getTable($model)
				    	->search($search_term, $q);
				    
				}
				$items = $q->execute();
				$results['numResults'] = $items->count();
				if($items->count())
				{
					$results['results'] = $items->toArray(true);
					
				}
			}
		}
		error_log(print_r($results, true));
		$this->preProcessResult($results);
	}
	function filter_search_criteria($input) 
	  { 
	    foreach ($input as &$value) 
	    { 
	      if (is_array($value)) 
	      { 
	        $value = $this->filter_search_criteria($value); 
	      } 
	    } 
	    
	    return array_filter($input); 
	  } 
	public function preProcessResult ($query_obj=NULL, $method=NULL)
	{
		$data = array();
		$data['data'] = array();
		if($query_obj && is_object($query_obj))
		{
			$result = $query_obj->execute();
			if($result) $data['data'] = $result->toArray();
			
		}
		elseif($query_obj && is_array($query_obj))
		{
			$data['data'] = $query_obj;
		}
		elseif($query_obj === false)
		{
			$data['method'] = $method->name;
			$this->response($data, 404);
			return;
		}
		if($method)
		{
			$data['method'] = $method->name;
		}
		
		$this->response($data, 200);
		return;
	}
	
	/* request function
      - this is like a _remap function for the API, it's only called if a specific function for the object and method are not found
    */
	function request($object_name, $http_method)
	{
		try
		{
			$method = $this->loadMethod($object_name, $http_method);
		}
		catch(Exception $e)
		{
			error_log('loadMethod problem: ' . $e->getMessage());
		}
		if ($method) 
		{
			$this->executeMethod($method, $object_name, $http_method);
		}
		elseif(is_null($method))
		{
			error_log("API Method not found");
			show_error("API Method not found");
		}
		else 
		{
			error_log("Invalid request.");
			show_error("Invalid request.");
		}
	}


	private function loadMethod($object, $http_method)
	{
		$obj = strtolower($object);
		$query = Doctrine_Query::create()->from('ApiMethod a')->where('doc_type = ?', 'method');
		
		// adds the user's role to the query to make sure the user is allowed to run the API method
		

		
		if ($model_name = Core::getModelName($object)) {
			$this->model_name = $model_name;
			$anotherName = strtolower($http_method . $object . '-method');
			$query_str = 'a.slug = ?';
			$params = $anotherName;
		}
		
		$root = $query->andWhere($query_str, $params)->fetchOne();
		if($root)
		{
			
			$function_query = Doctrine_Query::create()->from('ApiMethod a')
				->where('a.doc_type = ?', 'function')
				->andWhere('a.root_id = ?', $root->root_id)
				->andWhere('a.lft > ? AND a.rgt < ?', array($root->lft, $root->rgt))
				->orderBy('a.lft ASC');
				
			
			// check to see if the function is attached to the logged in user
			
			if (!$this->isBackdoorUser()) {
				
				$id = $this->_user->id;
				$function_query->leftJoin('a.UserMethod u')->andWhere('u.user_id = ' . $id);
			}
			$functions_result = $function_query->execute();
			
			if($functions_result && $functions_result->count())
			{
				return $functions_result->getFirst();
			}
		}
		if(!is_array($params)) error_log('Model not found when trying to look up "' . $object . '" during the loadMethod()');
		if(is_array($params)) show_error("API Method not found: " . $params);
		return NULL;
	}


	private function executeMethod($api_method, $object_name, $http_method)
	{

		if (method_exists($this, $api_method->name)) {
			
			$this->preProcessResult($this->{$api_method->name}($this->getUrlData()), $api_method);
		}
		else {
			// check to see if the class is loaded
			$loaded = false;
			$objname = strtolower($api_method->class_name);
			
			
			
			if (file_exists(APPPATH . 'helpers' . DIRECTORY_SEPARATOR . $objname . '_helper.php')) {
				
				$this->load->helper($objname);
				$loaded = true;
			}

			// if the class is loaded run that method
			if ($loaded and function_exists($api_method->name)) {
				$this->api_method_name = $api_method->name;
				$f = $api_method->name;
				
				$this->preProcessResult($f($this->getUrlData()), $api_method);
			}
			// otherwise if there is a generic HTTP METHOD function and the obj requested is a model run the generic request
			elseif (method_exists($this, $http_method . "_request") && $model_name = Core::getModelName($object_name)) {
				$this->api_method_name = $api_method->name;
				$this->model_name = $model_name;
				error_log('API running generic: ' . $http_method . "_request");
				$this->{$http_method . "_request"}($this->getUrlData());
			}
			else {
				show_error("Method not implemented.");
			}
		}

	}
	protected function request_base($object_name, $method)
	{

		if ($model_name = Core::getModelName($object_name)) {
			$this->model_name = $model_name;
			$this->{$method . "_request"}();
		}
		else {
			$this->response(NULL, 404);
		}
	}
	
	protected function getPageNumber()
	{

		$filters = $this->getFilters();
		if (array_key_exists('page', $filters)) {
			return intval($filters['page']);
		}
		return 1;
	}
	protected function getResultsPerPage()
	{
		$filters = $this->getFilters();
		if (array_key_exists('limit', $filters)) {
			return intval($filters['limit']);
		}
		return NULL;
	}
	public function get_request()
	{
		$findBy = $this->uri->segment(2);
		$find = $this->uri->segment(3);


		$data['method'] = $this->api_method_name;
		if ((strtolower($findBy) == "format" && $this->isSupportedFormat($find)) || ($this->isSupportedFormat($findBy) && !$find)) {

			if ($this->isSupportedFormat($findBy) && !$find) {
				$this->setFormat($findBy);
			}
			$findBy = $find = NULL;
		}

		// if there is no column specified in the 2nd segment and the 2nd segment is a number, assume request is for a specific record in a table
		if (is_numeric($findBy)) {
			$find = $findBy;
			$findBy = Doctrine::getTable($this->model_name)->getIdentifier();

			$result = Doctrine_Query::create()->from("$this->model_name m")->where("$findBy = ?", $find)->fetchOne();
			if ($result) {
				$data['data'] = $result;
				$this->response($data, 200);
			}
			else {
				$this->response(NULL, 404);
			}

		}
		elseif ($findBy && Doctrine::getTable($this->model_name)->hasField($findBy)) {
			if ($find) {
				$result = Doctrine_Query::create()->from("$this->model_name m")->where("$findBy = ?", $find)->execute();
				if ($result && $result->count()) {
					$data['data'] = $result;
					$this->response($data, 200);
				}
				else {
					$this->response(NULL, 404);
				}
			}
			else {
				$this->response(NULL, 404);
			}
		}
		else {
			$query = Doctrine_Query::create()
			->from("$this->model_name m");

			$pager = $this->getPagerObj($query);
			$result = $pager->execute();
			if ($result && $result->count()) {
				
				$data['data'] = $result->toArray();
				$data['pagination'] = $this->extractPaginationData($pager);
				$this->response($data, 200);
			}
			else {
				$this->response(NULL, 404);
			}
		}
	}
	public function put_request()
	{
		$data = array();
		$id = $this->uri->segment(2);
		if (is_numeric($id)) {
			$input = $this->put();
			$record = Doctrine::getTable($this->model_name)->find($id);
			$record->cleanData($input);
			$record->fromArray($input);
			try
			{
				$record->save();
				$resp = true;
			}
			catch(Exception $e)
			{
				error_log($e->getMessage());
				$resp = false;
			}
			
			if ($resp) {
				$data['data'] = $record->toArray();
				$data['method'] = $this->api_method_name;
				error_log(print_r($data, true));
			}
		}
		$this->response($data);
	}
	public function post_request()
	{
		$data = array();

		$input = $this->post();
		$record = new $this->model_name;
		$record->cleanData($input);
		$record->fromArray($input);
		
		try
		{
			$record->save();
			$resp = true;
		}
		catch(Exception $e)
		{
			error_log($e->getMessage());
			$resp = false;
		}
		
		if ($resp) {
			$data['data'] = $record->toArray();
			$data['method'] = $this->api_method_name;
			error_log(print_r($data, true));
		}

		$this->response($data);
	}
	public function delete_request()
	{
		$data = array();
		$id = $this->uri->segment(2);
		if (is_numeric($id)) {
			$input = $this->put();
			$record = Doctrine::getTable($this->model_name)->find($id);
			if ($record) {
				$resp = $record->delete();
			}
			else {
				$resp = true;
			}
			if ($resp) {
				$data['data'] = $resp;
				$data['method'] = $this->api_method_name;
			}
		}
		$this->response($data);
	}
	public function getPagerObj($query)
	{
		$currentPage = $this->getPageNumber();
		$resultsPerPage = $this->getResultsPerPage();
		// Creating pager object
		$pager = new Doctrine_Pager(
			$query,
			$currentPage, // Current page of request
			$resultsPerPage // (Optional) Number of results per page. Default is 25
		);
		return $pager;
	}
	public function extractPaginationData($pager, $data=array())
	{
		// Return the total number of itens found on query search
		$data['num_results'] = $pager->getNumResults();

		// Return the first page (always 1)
		$data['first_page'] = $pager->getFirstPage();

		// Return the total number of pages
		$data['total_pages'] = $pager->getLastPage();

		// Return the current page
		$data['current_page'] = $pager->getPage();

		// Return the next page
		$data['next_page'] = $pager->getNextPage();

		// Return the previous page
		$data['previous_page'] = $pager->getPreviousPage();

		// Return the first indice of current page
		$data['first_indice'] = $pager->getFirstIndice();

		// Return the last indice of current page
		$data['last_indice'] = $pager->getLastIndice();

		// Return true if it's necessary to paginate or false if not
		$data['have_to_paginate'] = $pager->haveToPaginate();

		// Return the maximum number of records per page
		$data['max_per_page'] = $pager->getMaxPerPage();


		// Returns the number of itens in current page
		$data['results_in_page'] = $pager->getResultsInPage();
		return $data;

	}
	protected function models_get()
	{
		$this->setFormat('json');
		$this->response(Columbus::getFriendlyModelNames(), 200);
	}
	function getMethods($className = "")
	{
		$methods = array();
		$reflect = new ReflectionClass($className);

		foreach ($reflect->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectmethod) {
			if ($reflectmethod->getDeclaringClass()->getName() !== __CLASS__) {
				$methods[$reflectmethod->getName()] = array("namespace" => $reflectmethod->getExtensionName());
				foreach ($reflectmethod->getParameters() as $num => $param) {

				}

			}
			else {
				break;
			}
		}
		return $methods;
	}


}

?>