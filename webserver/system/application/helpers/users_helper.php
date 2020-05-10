<?php
function validate($email=NULL, $password=NULL)
{
  return Doctrine_Query::create()->from('User u')->where('u.email = ?', $email)->andWhere('u.password = ?', $password)->leftJoin('u.Role r')->fetchOne();
}

function getUsers($currentPage = 1, $resultsPerPage = 5)
{
  $pagerLayout = new Doctrine_Pager_Layout(
      new Doctrine_Pager(
            Doctrine_Query::create()
                  ->from( 'User u' )
                  
                  ->orderby( 'u.last, u.first ASC' ),
            $currentPage,
            $resultsPerPage
      ),
      new Doctrine_Pager_Range_Sliding(array(
            'chunk' => 5
      )),
      '#/settings/users/page/{%page_number}'
  );

  $pagerLayout->setTemplate('<li><a class="changeState" rel="Opened Users Page {%page}" title="users" href="{%url}">{%page}</a></li>');
  $pagerLayout->setSelectedTemplate('<li class="current">{%page}</li>');
  
  // Retrieving Doctrine_Pager instance
  return $pagerLayout;  
}

function getUsersAsAdmin($currentPage = 1, $resultsPerPage = 5)
{
  $pagerLayout = new Doctrine_Pager_Layout(
      new Doctrine_Pager(
            Doctrine_Query::create()
                  ->from( 'User u' )
                  ->where('u.first = ?', 'travis')
                  ->orderby( 'u.first ASC, u.last ASC' ),
            $currentPage,
            $resultsPerPage
      ),
      new Doctrine_Pager_Range_Sliding(array(
            'chunk' => 5
      )),
      '#/settings/users/page/{%page_number}'
  );

  $pagerLayout->setTemplate('<li><a class="changeState" rel="Opened Users Page {%page}" title="users" href="{%url}">{%page}</a></li>');
  $pagerLayout->setSelectedTemplate('<li class="current">{%page}</li>');
  
  // Retrieving Doctrine_Pager instance
  return $pagerLayout;  
}

function getUser($user_identifier = NULL)
{
  $return = array();
  if (is_numeric($user_identifier))
  {
    return Doctrine_Query::create()->from('User u')->where('u.id = ?', $user_identifier)->fetchOne();
  }
  elseif (is_string($user_identifier))
  {
    return Doctrine_Query::create()->from('User u')->where('u.username = ?', $user_identifier)->fetchOne();
  }
  else
  {
    return $return;
  }
}

function getUserRole($role_id = NULL)
{
  $return = array();
  if (is_numeric($role_id))
  {
    return Doctrine_Query::create()->from('Role r')->where('r.id = ?', $role_id)->fetchOne();
  }
  else
  {
    return $return;
  }
}

function getUserMethods($user_id = NULL)
{
  $return['user'] = array();
  if (!isset($user_id) || is_null($user_id))
  {
    if (isset($_SESSION['user']))
    {
      $user_id = $_SESSION['user']['id'];
      return Doctrine_Query::create()->select('u.id, m.id, m.name, m.description')->from('User u')->leftJoin('u.Methods m')->where('u.id = ?', $user_id)->execute();
    }
  }
  else if (is_numeric($user_id))
  {
    return Doctrine_Query::create()->select('u.id, m.id, m.name, m.description')->from('User u')->leftJoin('u.Methods m')->where('u.id = ?', $user_id)->execute();
  }
  else
  {
    return $return;
  }
}

function getUserInventory($user_id = NULL, $logged_in_user_id = NULL)
{
  //return a list of lots based on $logged_in_user_id, and mark which ones are assigned to $user_id within that list

  $availLots = array();
  $assignedLots = array();
  
  if (!isset($logged_in_user_id) && !is_numeric($logged_in_user_id) && isset($_SESSION['user']))
  {
    $logged_in_user_id = $_SESSION['user']['id'];
  }
  if (is_numeric($user_id))
  {
    $user_lots = Doctrine_Query::create()->from('User u')->leftJoin('u.Lots l')->where('u.id = ?', $user_id)->orWhere('u.id = ?', $logged_in_user_id)->execute();
  }
  elseif(is_array($user_id) && !empty($user_id))
  {
  	$user_lots = Doctrine_Query::create()->from('User u')->leftJoin('u.Lots l')->whereIn('u.id', $user_id)->orWhere('u.id = ?', $logged_in_user_id)->execute();
  }
  elseif(is_null($user_id))
  {
  	$user_lots = Doctrine_Query::create()->from('User u')->leftJoin('u.Lots l')->where('u.id = ?', $logged_in_user_id)->execute();
  }
  
    if($user_lots)
    {
    	$user_lots = $user_lots->toArray();
    }
    else
    {
    	$user_lots = array();
    }
   
    foreach ($user_lots as $user)
    {
    	foreach($user['Lots'] as $i=>$l) $user['Lots'][$i]['user_id'] = $user['id'];
    	// while we loop thru the users, we need to figure out which array to assign this user to, either available lots (for the logged in user) or assigned lots (for the user(s) looked up)
    	
      if (is_numeric($user_id) && $user['id'] == $user_id)                                                                // <-- if we only looked up one user, and the current user in the foreach loop is that user
      {
        $assignedLots = $user['Lots'];
      }
      elseif (is_array($user_id) && in_array($user['id'], $user_id) && $user['id'] != $logged_in_user_id)                 // <-- if we looked up more than one user, and the current user is one of them
      {
      	//$assignedLots = array_merge($assignedLots, $user['Lots']);
        $assignedLots += $user['Lots'];
      }
      elseif (is_array($user_id) && in_array($user['id'], $user_id) && $user['id'] == $logged_in_user_id)                 // <-- if we looked up more than one user, but this is the logged in user
      {
        //$availLots = array_merge($availLots, $user['Lots']);
        $availLots += $user['Lots'];
      }
      elseif ($user['id'] == $logged_in_user_id)                                                                          // <-- if this is the logged in user
      {
        //$availLots = array_merge($availLots, $user['Lots']);
        $availLots += $user['Lots'];
      }
    }    
    foreach($availLots as $key=>$lot)
    {
      $lot_id = $lot['id'];
      foreach($assignedLots as $alot)
      {
        $alot_id = $alot['id'];
        if ($lot_id == $alot_id)
        {
          $availLots[$key]['assigned'] = true;
        }
        else
        {
          if (isset($availLots[$key]['assigned']) && $availLots[$key]['assigned'])
          {
            $availLots[$key]['assigned'] = true;
          }
          else
          {
            $availLots[$key]['assigned'] = false;
          }
        }
      }
    }
    return $availLots;
}

?>