<?php

class ApiMethods
{
  function getApiMethod($api)
  {
    $data = array();
    $query = Doctrine_Query::create()->from("ApiMethod a")->leftJoin("a.ApiVersions v")->leftJoin("a.Roles r")->orderBy("a.class_name ASC, a.name ASC");
    
    $pager = $api->getPagerObj($query);
    $methods = $pager->execute();
    $data['__result'] = $methods;
    $data['pagination'] = $api->extractPaginationData($pager);
    
    $api->response($data);
  }
}