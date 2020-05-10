<?php
function getLots ()
{
	$data=array();
	$community_id = NULL;
	$controller = Core::get('controller');
	$user_id = $controller->_user->id;
	
	$q = Doctrine_Query::create()->from('Lot l')->leftJoin('l.User u')->leftJoin('l.Media m')->where('u.id = ?', $user_id);
	if($community_id)
	{
		$q->andWhere('l.community_id = ?', $community_id);
	}
	$lots = $q->execute();
	$data['data'] = array();

	if($lots->count())
	{
		$data = $lots->toArray(true);
	}
	return $data;
}


function postLots ()
{
	$data=array();
	$lot = isset($_POST['lot']) ? $_POST['lot'] : NULL;
	if($lot)
	{

		try
		{
			$record = new Lot();
			$cleaned = $record->cleanData($lot);
			$record->fromArray($lot);
			$record->save();
			$saved = true;
			
			if($saved)
			{

				$controller = Core::get('controller');
				
				$user_id = $controller->_user->id;

				$user_lot = new LotUser();
					$user_lot->user_id = $user_id;
					$user_lot->lot_id = $record->id;
					$user_lot->save();
				
				
				
				/*
				// get the user's parents
				$parents = $controller->_user->getParentIds();
				foreach($parents as $id)
				{
					$user_parent_lot = new UserLot();
					$user_parent_lot->user_d = $id;
					$user_parent_lot->lot_id = $record->id;
					$user_parent_lot->save();
					$user_parent_lot->free(true);
				}
				*/
				
				if(isset($cleaned['include_children']) && $cleaned['include_children'])
				{
					/*
					// get the user's children
					$children = $controller->_user->getChildrenIds();
					foreach($children as $id)
					{
						$user_child_lot = new UserLot();
						$user_child_lot->user_d = $id;
						$user_child_lot->lot_id = $record->id;
						$user_child_lot->save();
						$user_child_lot->free(true);
					}
					*/
						
				}
				
				if(isset($cleaned['share_users_ids']) && is_array($cleaned['share_users_ids']))
				{
					/*
					foreach($cleaned['share_users_ids'] as $id)
					{
						$user_child_lot = new UserLot();
						$user_child_lot->user_d = $id;
						$user_child_lot->lot_id = $record->id;
						$user_child_lot->save();
						$user_child_lot->free(true);
					}
					*/
						
				}
				
			}
			$data['data'] = $record->toArray();
			$record->free(true);
		}
		catch (Exception $e)
		{
			error_log('Problem: ' . $e->getMessage());
		}
	}

	return $data;
	
}

function putLot($data=array())
{
	$data = array();
	$controller = Core::get('controller');
	$lot = isset($_POST['lot']) ? $_POST['lot'] : NULL;
	if(!$lot)
	{
		$lot = isset($controller->_args['lot']) ? $controller->_args['lot'] : NULL;
	}
	error_log('======== PUT LOT ====================');

	
	error_log('POST:');
	error_log(print_r($_POST, true));
	
	
	
	if(count($lot) == 1 && is_array(reset($lot))) $lot = reset($lot);
	if($lot && is_array($lot))
	{
		$data['data'] = false;
		$record = Doctrine::getTable('Lot')->find(intval($controller->getRecordId()));
		if($record)
		{
			$lot['lot_price_to_builder'] = (isset($lot['lot_price_to_builder']) && is_null($lot['lot_price_to_builder'])) ? 0.00 : floatval($lot['lot_price_to_builder']);
			$lot['price'] = (isset($lot['price']) && is_null($lot['price'])) ? 0.00 : floatval($lot['price']);
			$cleaned = $record->cleanData($lot);
			$record->fromArray($lot);
			try
			{
				$record->save();
				$saved = true;
			}
			catch (Exception $e)
			{
				$saved = false;
				error_log('Record Error: ' . $e->getMessage());
			}
			if($saved)
			{
				$data['data'] = $record->toArray(true);
			}
			
			$record->free(true);
		}
	}
	//error_log('======== RETURN');
	//error_log(print_r($data, true));
	error_log('======== END PUT LOT ====================');
	return $data;
}

function deleteLot ($data=array())
{
	$return = false;
	$controller = Core::get('controller');
	$lot_id = $controller->getRecordId();
	
	$query = Doctrine_Query::create()->delete('LotUser lu')->where('lu.lot_id = ?', $lot_id);
	$user_rows = $query->execute();
	
	$query = Doctrine_Query::create()->delete('LotCustomer lc')->where('lc.lot_id = ?', $lot_id);
	$customer_rows = $query->execute();
	
	$query = Doctrine_Query::create()->delete('LotMedia lm')->where('lm.lot_id = ?', $lot_id);
	$media_rows = $query->execute();
	
	$query = Doctrine_Query::create()->delete('Lot l')->where('l.id = ?', $lot_id);
	$lot_rows = $query->execute();
	
	
	if($lot_rows)
	{
		$return = array();
		$return['deleted'] = array('rows'=>$lot_rows,'lots'=>$lot_rows, 'lotmedia'=>$media_rows, 'lotcustomers'=>$customer_rows, 'lotusers'=>$user_rows);
	}
	return $return;
}
function deleteLotMedia ()
{
	$controller = Core::get('controller');
	$data = $controller->getUrlData();
	
	$q = Doctrine_Query::create()->delete('LotMedia lm')->where('lm.lot_id = ?', $data['lot_id'])->andWhere('lm.media_id = ?', $data['media_id']);
	$num = $q->execute();
	return $num;
	
}
function postLotMedia ()
{
	
	$data = array('data'=>1);
	try
	{
		$input = $_POST;
		$controller = Core::get('controller');
		$upload = $controller->uploads('file');
		$media = new Media();
		if($upload)
		{
			$input = $input + $upload;
			$file = pathinfo($upload['path']);
			$input['uri'] = $controller->_user->getUploadUri() . $file['basename'];
			$input['url'] = $controller->default_media_server . $input['uri'];

		}
		
		$extras = $media->cleanData($input);
		$media->fromArray($input);
		$media->owner_id = $controller->_user->id;
		
		$media->save();
		
		$lotmedia = new LotMedia();
		$lotmedia->lot_id = $extras['lot_id'];
		$lotmedia->media_id = $media->id;
		$lotmedia->save();
		$data = $media->toArray();
		$data['raw_html'] = $controller->formatHtml($data['raw_html']);
		
	}
	catch(Exception $e)
	{
		error_log('Error saving lot media: ' . $e->getMessage() . '\rPOST:'.print_r($_POST, true) .'\rUPLOAD:' . print_r($upload, true));
	}
	return $data;
	
	
	
}
?>