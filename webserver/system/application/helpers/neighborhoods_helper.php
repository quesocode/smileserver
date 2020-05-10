<?php
function getNeighborhoods($currentPage = 1, $resultsPerPage = 5)
{
  $pagerLayout = new Doctrine_Pager_Layout(
      new Doctrine_Pager(
            Doctrine_Query::create()
                  ->select( 'c.*, a.name, COUNT(u.id) AS user_count' )
                  ->from( 'Community c' )
                  ->leftJoin( 'c.Account a' )
                  ->leftJoin( 'c.CommunityUser u' )
                  ->groupBy( 'c.id' )
                  ->orderby( 'c.name ASC' ),
            $currentPage,
            $resultsPerPage
      ),
      new Doctrine_Pager_Range_Sliding(array(
            'chunk' => 5
      )),
      '#/settings/neighborhoods/page/{%page_number}'
  );

  $pagerLayout->setTemplate('<li><a class="changeState" title="neighborhoods" rel="Opened Neighborhoods Page {%page}" href="{%url}">{%page}</a></li>');
  $pagerLayout->setSelectedTemplate('<li class="current">{%page}</li>');
  
  // Retrieving Doctrine_Pager instance
  return $pagerLayout;  
}

function getNeighborhoodsWithThumbs($currentPage = 1, $resultsPerPage = 5)
{
  $pagerLayout = new Doctrine_Pager_Layout(
      new Doctrine_Pager(
            Doctrine_Query::create()
                  ->select( 'c.*, t.file_url' )
                  ->from( 'Community c' )
                  ->leftJoin( 'c.Thumb t' )
                  ->orderby( 'c.name ASC' ),
            $currentPage,
            $resultsPerPage
      ),
      new Doctrine_Pager_Range_Sliding(array(
            'chunk' => 5
      )),
      '#/settings/pickneighborhood/page/{%page_number}'
  );

  $pagerLayout->setTemplate('<li><a class="changeState" title="neighborhoodpicker" rel="Opened Neighborhoods Page {%page}" href="{%url}">{%page}</a></li>');
  $pagerLayout->setSelectedTemplate('<li class="current">{%page}</li>');
  
  // Retrieving Doctrine_Pager instance
  return $pagerLayout;  
}

function getLotCount($neighborhood_id = NULL)
{
  if (is_numeric($neighborhood_id))
  {
    $count = Doctrine_Query::create()->select('COUNT(l.id) AS lot_count')->from('Lot l')->where('l.community_id = ?', $neighborhood_id)->fetchArray();
    return $count[0]['lot_count'];
  }
  else
  {
    return 0;
  }
}

function getBranding($neighborhood_id = NULL)
{
  $return = array();
  if (is_numeric($neighborhood_id))
  {
    $neighborhood = Doctrine_Query::create()->select('n.id, n.logo_id, n.thumb_id, n.icon_id, l.*, t.*, i.*')->from('Community n')->leftJoin('n.Logo l')->leftJoin('n.Thumb t')->leftJoin('n.Icon i')->where('n.id = ?', $neighborhood_id)->fetchOne();
    if ($neighborhood->count())
    {
      return $neighborhood;
    }
    else
    {
      return $return;
    }
  }
  else
  {
    return $return;
  }
}
?>