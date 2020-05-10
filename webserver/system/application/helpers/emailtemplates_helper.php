<?php
function getEmailTemplates($community_id = NULL, $currentPage = 1, $resultsPerPage = 5)
{
  $pagerLayout = new Doctrine_Pager_Layout(
      new Doctrine_Pager(
            Doctrine_Query::create()
                  ->select( 't.*, a.first, a.last, e.first, e.last' )
                  ->from( 'Emailtemplate t' )
                  ->leftJoin( 't.Author a' )
                  ->leftJoin( 't.Editor e' )
                  ->where( 't.community_id = ?', $community_id )
                  ->orderby( 't.title ASC' ),
            $currentPage,
            $resultsPerPage
      ),
      new Doctrine_Pager_Range_Sliding(array(
            'chunk' => 5
      )),
      '#/settings/neighborhood/'.$community_id.'/emails/page/{%page_number}'
  );

  $pagerLayout->setTemplate('<li><a class="changeState" title="neighborhood_emails" rel="Opened Neighborhood Emails Page {%page}" href="{%url}">{%page}</a></li>');
  $pagerLayout->setSelectedTemplate('<li class="current">{%page}</li>');
  
  // Retrieving Doctrine_Pager instance
  return $pagerLayout;  
}

function getEmailVars()
{
  $emailvars = Doctrine_Query::create()
                      ->select( 'v.*' )
                      ->from( 'EmailVariable v' )
                      ->execute();
  return $emailvars;
}

?>