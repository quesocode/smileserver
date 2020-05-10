<?php

class Users
{
  function deleteUserRole($api)
  {
    $user_id = $api->uri->segment(2);
    
    $role_id = $api->uri->segment(4);
    
    if($user_id && $role_id)
    {
      $result = Doctrine_Query::create()->delete("UserRole u")->where("user_id = ?", $user_id)->andWhere('role_id = ?', $role_id)->execute();
      if($result && $result->count())
      {
        $data['__result'] = true;
        $data['deleted'] = $result->count();
        $api->response($data);
      }
    }
    $data['__result'] = false;
    $api->response($data, 500);
  }
  function getUser($api)
  {
  	
    $id = $api->uri->segment(2);
    if($id && is_numeric($id))
    {
      $user = Doctrine_Query::create()->from("User u")->leftJoin('u.Roles r')->where("id = ?", $id)->fetchOne();
      if($user)
      {
        $data['__result'] = $user;
        $api->response($data, 200);
      }
    }
    else
    {
      $api->get_request();
    }
    
  }
  function postUserRole($api)
  {
    $post = $_POST;
    $role = new UserRole();
    $role->cleanData($post);
    $role->fromArray($post);
    $resp = $role->trySave();
    if($resp)
    {
      $data['__result'] = $role;
      $api->response($data, 200);
    }
    else
    {
      $api->response($data, 500);
    }
    
  }
  function validate($api)
  {
    $email = $api->input->get_post('email');
    $password = $api->input->get_post('password');
    $user = Doctrine_Query::create()->from('User u')->where('u.email = ?', $email)->andWhere('u.password = ?', $password)->leftJoin('u.Roles r')->fetchOne();
    //var_dump($user->toArray());
    $api->response($user->toArray());
  }
  
  function getUserInventory($api)
  {
    $data = array();
    $id = $api->uri->segment(2);
    $query = Doctrine_Query::create()->from('Lot l');
    $query->leftJoin('l.User u');
    $query->where('u.id = ?', $id);
    $pager = $api->getPagerObj($query);
    $lots = $pager->execute();
    $data['__result'] = $lots;
    $data['pagination'] = $api->extractPaginationData($pager);
    
    $api->response($data);
  }
  function putUser($api)
  {
    $api->put_request();
    
  }
  function postUser($api)
  {
    $api->post_request();
  }
}