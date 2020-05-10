<?php
function getRecord($record_id = NULL, $model = NULL)
{
  $return = array();
  if (is_numeric($record_id) && is_string($model))
  {
    return Doctrine_Query::create()->from($model . ' m')->where('m.id = ?', $record_id)->fetchOne();
  }
  else
  {
    return $return;
  }
}
?>