<?php
function getAccounts($currentPage = 1, $resultsPerPage = 5)
{
  $pagerLayout = new Doctrine_Pager_Layout(
      new Doctrine_Pager(
            Doctrine_Query::create()
                  ->select( 'a.*' )
                  ->from( 'Account a' )
                  ->orderby( 'a.name ASC' ),
            $currentPage,
            $resultsPerPage
      ),
      new Doctrine_Pager_Range_Sliding(array(
            'chunk' => 5
      )),
      '#/settings/accounts/page/{%page_number}'
  );

  $pagerLayout->setTemplate('<li><a class="changeState" title="accounts" rel="Opened Accounts Page {%page}" href="{%url}">{%page}</a></li>');
  $pagerLayout->setSelectedTemplate('<li class="current">{%page}</li>');
  
  // Retrieving Doctrine_Pager instance
  return $pagerLayout;  
}

function getCommunityCount($account_id = NULL)
{
  if (is_numeric($account_id))
  {
    $count = Doctrine_Query::create()->select('COUNT(c.id) AS community_count')->from('Community c')->where('c.account_id = ?', $account_id)->fetchArray();
    return $count[0]['community_count'];
  }
  else
  {
    return 0;
  }
}

?>