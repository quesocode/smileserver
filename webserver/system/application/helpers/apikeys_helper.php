<?php
function getApiKeys($currentPage = 1, $resultsPerPage = 5)
{
  $pagerLayout = new Doctrine_Pager_Layout(
      new Doctrine_Pager(
            Doctrine_Query::create()
                  ->select( 'a.*, c.name, u.first, u.last, u2.first, u2.last, u3.first, u3.last' )
                  ->from( 'ApiKey a' )
                  ->leftJoin( 'a.Account c' )
                  ->leftJoin( 'a.Owner u' )
                  ->leftJoin( 'a.Creator u2' )
                  ->leftJoin( 'a.Editor u3' )
                  ->orderby( 'a.domain ASC' ),
            $currentPage,
            $resultsPerPage
      ),
      new Doctrine_Pager_Range_Sliding(array(
            'chunk' => 5
      )),
      '#/settings/apikeys/page/{%page_number}'
  );

  $pagerLayout->setTemplate('<li><a class="changeState" title="apikeys" rel="Opened ApiKeys Page {%page}" href="{%url}">{%page}</a></li>');
  $pagerLayout->setSelectedTemplate('<li class="current">{%page}</li>');
  
  // Retrieving Doctrine_Pager instance
  return $pagerLayout;  
}
?>