<?php
class Trees
{

  private $rootColumnName;
  private $tree;
  function createNode($options=array())
  {
    extract($options);
    $this->tree = $tree = Doctrine::getTable($model)->getTree();
    
    $leaf = new $model;
    $leaf->{$column} = $value;
    
    $extra = parse_str($extra, $extra_arr);
    $leaf->cleanData($extra_arr);
    $leaf->fromArray($extra_arr);
    $leaf->save();
    
    if($ref)
    {
      $ref = Doctrine::getTable($model)->find($ref);
    }
    else
    {
      $roots = $tree->fetchRoots();
      $ref = reset($roots);
    
    }
    $child = $sibling = $leaf;
    $success = false;
    switch($type)
    {
      case "after":
        if($ref && $ref->getNode()->isRoot())
        {
          $tree->createRoot($leaf);
          $leaf->moveAfter($ref);
        }
        elseif($ref)
        {
          if($leaf)
          {
            $leaf->getNode()->insertAsNextSiblingOf($ref);
            $success = true;
          }
        }
        elseif(!$ref)
        {
          $tree->createRoot($leaf);
        }
        break;
      case "before":
        if($ref && $ref->getNode()->isRoot())
        {
          $tree->createRoot($leaf);
          $leaf->moveBefore($ref);
        }
        elseif($ref)
        {
          if($sibling)
          {
            if(!$leaf->getNode()->isEqualTo($ref))
            {
               $leaf->getNode()->insertAsPrevSiblingOf($ref);
            }
            $success = true;
          }
        }
        elseif(!$ref)
        {
          $tree->createRoot($leaf);
        }
        
        break;
      case "top":
        if($child && $ref)
        {
          $child->getNode()->insertAsFirstChildOf($ref);
          $success = true;
        }
        break;
      case "bottom":
      case "inside":

        if($child && $ref)
        {
          $child->getNode()->insertAsLastChildOf($ref);
          $success = true;
        }
        break;
    }
    
    
    
     $json = json_encode($leaf->toArray());
    return $json;
    
  }
  function renameNode($options=array())
  {
    $json = "failed";
    extract($options);

    
    $leaf = Doctrine::getTable($model)->find($id);
    if($leaf)
    {
      $leaf->{$column} = $value;
      $leaf->slug = NULL;
  
  
      $leaf->save();
  
      
      
      $json = json_encode($leaf->toArray());
    }
    return $json;
    //throw new Controller_Http_Response(200, $json);
    
  }
  function deleteNode($options=array())
  {
    extract($options);
    $node = Doctrine::getTable($model)->find($id);
    if($node)
    {
      $node->getNode()->delete();
      $json = json_encode(true);
      return $json;
    }
    else
    {
      $json = json_encode(false);
      return $json;
    }
  }
  
  /** MOVE NODE **/
  
  
  function moveNode($options=array())
  {
    
    extract($options);
    $this->tree = $tree = Doctrine::getTable($model)->getTree();
    
    
    if($ref)
    {
      $ref = Doctrine::getTable($model)->find($ref);
    }
    else
    {
      $roots = $tree->fetchRoots();
      $ref = reset($roots);
    
    }
    $child = $sibling = $leaf =  Doctrine::getTable($model)->find(intval($node));
    
    $success = false;
    switch($type)
    {
      case "after":
        if($ref && $ref->getNode()->isRoot())
        {
          if($leaf->getNode()->isLeaf())
          {
            $leaf->getNode()->makeRoot();
            $leaf->moveAfter($ref);
            $success = true;
          }
          else
          {
            $leaf->moveAfter($ref);
            $success = true;
          }
          
        }
        elseif($ref)
        {
          if($sibling)
          {
            $sibling->getNode()->moveAsNextSiblingOf($ref);
            $success = true;
          }
        }
        elseif(!$ref)
        {
          $leaf->getNode()->makeRoot();
          $success = true;
        }
        break;
      case "before":
        if($ref && $ref->getNode()->isRoot())
        {
          if(!$leaf->getNode()->isRoot())
          {
            $leaf->getNode()->makeRoot($leaf->id);
            //$tree->createRoot($leaf);
            //$leaf->getNode()->createRoot();
            $leaf->moveBefore($ref);
            $success = true;
          }
          else
          {
            $leaf->moveBefore($ref);
            $success = true;
          }
          
        }
        elseif($ref)
        {
          if($sibling)
          {
            if(!$leaf->getNode()->isEqualTo($ref))
            {
              $sibling->getNode()->moveAsPrevSiblingOf($ref);
            }
            $success = true;
          }
        }
        elseif(!$ref)
        {
          $leaf->getNode()->makeRoot();
          $success = true;
        }
        break;
      case "top":
        if($child && $ref)
        {
          $child->getNode()->moveAsFirstChildOf($ref);
          $success = true;
        }
        break;
      case "bottom":
      case "inside":
        
        if($child && $ref)
        {
         // if($ref->getNode()->isLeaf()) $ref->getNode()->makeRoot();
          $child->getNode()->moveAsLastChildOf($ref);
          $success = true;
        }
        break;
    }

    $json = $success ? json_encode(true) : json_encode(false);
    return $json;
  }
  
  
  /* html tree construction */
  
  
  
   
  function getTreeHtml($options)
  {
    if(!isset($options['title_column'])) $options['title_column'] =  'title';
    if(!isset($options['id_column'])) $options['id_column'] =  'id';
    if(!isset($options['forcelink'])) $options['forcelink'] =  false;
    if(!isset($options['append_link_suffix'])) $options['append_link_suffix'] =  false;
    if(!isset($options['leaf_linked'])) $options['leaf_linked'] =  false;
    if(!isset($options['leaf_linked_value'])) $options['leaf_linked_value'] =  NULL;
    if(!isset($options['allow_links'])) $options['allow_links'] =  true;
    if(!isset($options['link_suffix'])) $options['link_suffix'] =  NULL;
    if(!isset($options['link_branches'])) $options['link_branches'] =  true;
    if(!isset($options['link_prefix'])) $options['link_prefix'] =  NULL;
    if(!isset($options['link_column'])) $options['link_column'] =  false;
    if(!isset($options['include_data'])) $options['include_data'] =  true;
    return $this->makeTreeMenu($this->getTree($options), $options);
  }
  
  
  
  
  
  function getDescendantTree($options)
  {
    if(!isset($options['title_column'])) $options['title_column'] =  'title';
    if(!isset($options['id_column'])) $options['id_column'] =  'id';
    if(!isset($options['forcelink'])) $options['forcelink'] =  false;
    if(!isset($options['append_link_suffix'])) $options['append_link_suffix'] =  false;
    if(!isset($options['leaf_linked'])) $options['leaf_linked'] =  false;
    if(!isset($options['leaf_linked_value'])) $options['leaf_linked_value'] =  NULL;
    if(!isset($options['link_suffix'])) $options['link_suffix'] =  NULL;
    if(!isset($options['allow_links'])) $options['allow_links'] =  true;
    if(!isset($options['link_branches'])) $options['link_branches'] =  false;
    if(!isset($options['link_prefix'])) $options['link_prefix'] =  NULL;
    if(!isset($options['link_column'])) $options['link_column'] =  false;
    if(!isset($options['include_data'])) $options['include_data'] =  true;
    if(!isset($options['include_root'])) $options['include_root'] =  false;
    $branches = $this->getDescendants($options);
    extract($options);
    $count = count($branches);
    if($count)
    {
      if($include_root)
      {
        $branches = array($branches);
        $root_branch = reset($branches);
        $this->root_branch = $root_branch;
        unset($root_branch['branches']);
      }
      else
      {
        $root_branch = $branches;
        unset($root_branch['branches']);
        if(isset($branches['branches']))
        {
          $branches = $branches['branches'];
        }
        else
        {
          $branches = array();
        }
      }
    }
    return $this->makeTreeMenu($branches, $options);
  }
  function getDescendants($options)
  {
    $model = "Category";
    $levels = 10;
    $parent = NULL;
    $children = array();
    extract($options);
    $this->maxlevel = $levels;
    $q = Doctrine_Query::create()->from($model . ' c')->select("c.*");
    $q->orderBy('c.lft DESC');
    if($parent)
    {
      if(is_numeric($parent))
      {
        $q->andWhere("c.id = ?", $parent);
      }
      elseif(is_string($parent))
      {
        $q->andWhere("c.slug = ?", $parent);
      }
      $q->limit(1);
      $root = $q->fetchOne();
      if($root)
      {
        $children = $this->cycleTree($root);
      }
    }
    else
    {
      $treeObject = Doctrine::getTable($model)->getTree();
      if(isset($q)) $treeObject->setBaseQuery($q);
      $this->treeObject = $treeObject;
      foreach ($treeObject->fetchRoots() as $root) 
      {
        $children[] = $this->cycleTree($root);
      }
      
    }
    return $children;
  }
  
  
  function makeTreeMenu($branches, $options)
  {
    extract($options);
    $tree = array();
    foreach($branches as $branch)
    {
      $leaf = $include_data ? $branch : array();
      if(!empty($branch['branches']))
      {
        if(isset($branch[$id_column])) $leaf[$id_column] = $branch[$id_column];
        if($allow_links)
        {
            /* build a leaf link */
            if($link_column && $link_branches && ((!isset($leaf['link']) || is_null($leaf['link'])) || $forcelink)) $leaf['link'] = $link_prefix . $branch[$link_column] . $link_suffix;
            
            /* build a branch link */
            if(isset($branch['slug']) && $link_branches && !$link_column && (!$leaf_linked || $leaf[$leaf_linked] == $leaf_linked_value) && ((!isset($leaf['link']) || is_null($leaf['link'])) || $forcelink)) $leaf['link'] = $link_prefix . $branch['slug'] . $link_suffix;
        }
        if($append_link_suffix)
        {
          $newoptions = $options;
          $newoptions['link_prefix'] = $link_prefix . $branch['slug'] . $link_suffix;
          $leaf['branches'] = $this->makeTreeMenu($branch['branches'], $newoptions); 
        }
        else
        {
          $leaf['branches'] = $this->makeTreeMenu($branch['branches'], $options); 
        }
      }
      else
      {
        if($allow_links)
        {
            /* build a leaf link */
            if($link_column && ((!isset($leaf['link']) || is_null($leaf['link'])) || $forcelink)) $leaf['link'] = $link_prefix .  $branch[$link_column] . $link_suffix;
            /* build a branch link */
            if(isset($branch['slug']) && !$link_column && (!$leaf_linked || ($leaf[$leaf_linked] == $leaf_linked_value)) && ((!isset($leaf['link']) || is_null($leaf['link'])) || $forcelink)) $leaf['link'] = $link_prefix . $branch['slug'] . $link_suffix;
        }
        if(isset($branch[$id_column])) $leaf[$id_column] = $branch[$id_column];
      }
      
      $leaf['text'] = isset($title_column) ? $branch[$title_column] : $branch['name'];
      $leaf['value'] = isset($leaf[$id_column]) ? $leaf[$id_column] : NULL;
      unset($leaf['lft']);
      unset($leaf['rgt']);
      unset($leaf['level']);
      $tree[$branch[$id_column]] = $leaf;
    }
    return $tree;
  }
  
  
  function getTree($options)
  {
    $model = "Category";
    $onemptycreatebranch = false;
    $levels = 10;
    $order = true;
    $find = false;
    extract($options);
    $q = isset($options['query']) ? $options['query'] : Doctrine_Query::create()->from($model . ' c')->select("c.*");
    if($find && $findby)
    {
        $q->andWhere("c." . $findby . " = ?", $find);
    }
    if($order)
    {
      
    }
    $treeObject = Doctrine::getTable($model)->getTree();
    if(isset($q)) $treeObject->setBaseQuery($q);
    $this->treeObject = $treeObject;
    $rootColumnName = $treeObject->getAttribute('rootColumnName');
    
    $this->maxlevel = $levels;
    $branches = array();
    foreach ($treeObject->fetchRoots() as $root) {
      
      $branches[] = $this->cycleTree($root);
      
    }
    if(empty($branches) && $onemptycreatebranch)
    {
      
      $record = new $model;
      $record->cleanData($onemptycreatebranch);
      $record->fromArray($onemptycreatebranch);
      $record->save();
      
      
      $treeObject->createRoot($record);
      $branches[] = $this->cycleTree($record);
      
      
    }
    return $branches;
  }
  
  
  /* builds a multilevel drop down select */
  function makeIndentedOptions ($options)
  {
    $opt = array();
    if(isset($this->treeObject))
    {
      $rootColumnName = $this->treeObject->getAttribute('rootColumnName');
      foreach($this->treeObject->fetchRoots() as $root)
      {
        $options = array(
          'root_id' => $root->$rootColumnName
        );
        foreach($this->treeObject->fetchTree($options) as $node) {
          $opt[$node['id']] = str_repeat('--', $node['level']) . $node['name'] . "\n";
        }
      }
    }
    return $opt;
    
  }
  
  
  private function cycleTree($node, $level=0)
  {
    if($node && $level < $this->maxlevel)
    {
      
      
      $root = $node->toArray();
      
    	if($node->getNode()->hasChildren() && $level+1 < $this->maxlevel)
    	{
    	 $root["branches"] = array();
    		$children = $node->getNode()->getDescendants(true);
    		
    		if($children && $children->count())
    		{
    			foreach($children as $child)
    			{
    				$root["branches"][] = $this->cycleTree($child, $level+1);
    			}
    		}
    	}
    	return $root;
  	}
  	
  }
  
  
}