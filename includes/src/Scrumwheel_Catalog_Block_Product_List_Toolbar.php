<?php
/**
 * Created by PhpStorm.
 * User: Scrumwheel
 * Date: 12/28/2016
 * Time: 3:34 PM
 */
class Scrumwheel_Catalog_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    protected $_direction           = 'desc';

    public function setCollection($collection)
    {
        parent::setCollection($collection);

        if ($this->getCurrentOrder()) {
            if($this->getCurrentOrder() == 'availability') {
                $dir = $this->getCurrentDirection();
                $this->_collection->getSelect()->
                joinLeft(
                    array('_inventory_table'=>'mg_cataloginventory_stock_item'),
                    "_inventory_table.product_id = e.entity_id ",
                    array('qty')
                )->order("_inventory_table.qty $dir");
            } elseif ($this->getCurrentOrder() == 'popularity') {
                $dir = $this->getCurrentDirection();
                $this->_collection->getSelect()->
                joinLeft('mg_report_event AS _table_views',
                    ' _table_views.object_id = e.entity_id')->joinInner('mg_cataloginventory_stock_item AS _inventory_table',
                    "_inventory_table.product_id = e.entity_id ",'COUNT(_table_views.object_id)+_inventory_table.qty AS views')->
                    group('e.entity_id')->order("views $dir");
                    
            } else {
                $this->getCollection()
                    ->setOrder($this->getCurrentOrder(), $this->getCurrentDirection())->getSelect();
            }
        }

        return $this;
    }
    
    public function setDefaultOrder($field)
    {
        if (isset($this->_availableOrder['popularity'])) {
            $this->_orderField = 'popularity';
        }
        return $this;
    }
}