<?php
function getRoleMethods($role_id = NULL)
{
  $return = array();
  if (is_numeric($role_id))
  {
    return Doctrine_Query::create()->from('Role r')->leftJoin('r.Methods m')->where('r.id = ?', $role_id)->execute();
  }
  else
  {
    return $return;
  } 
}
?>