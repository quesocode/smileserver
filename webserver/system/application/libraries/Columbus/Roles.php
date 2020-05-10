<?php
include_once('Trees.php');

class Roles extends Trees
{
  
  function getRole($api, $return = array())
  {
    $allow_links = $api->uri->segment(2) == "links" ? true : false;
    $prefix = $api->input->get_post("prefix");
    $options = array(
      "link_prefix" => $prefix,
      "link_branches" => true,
      "model"=>"Role",
      "parent"=>"dev",
      "allow_links"=>$allow_links
    
    );
    
    $return['roles'] = $this->getDescendantTree($options);;  
    $return['method'] = $api->api_method_name;
    $api->response($return, 200);
    
  }
  function findRole($title=NULL)
  {
    return Doctrine::getTable("Role")->findOneByTitle($title);
  }
}
?>