<?php
/**
 * Created by PhpStorm.
 * User: Scrumwheel
 * Date: 12/28/2016
 * Time: 3:37 PM
 */
class Scrumwheel_Catalog_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = (is_null($select)) ?
            $this->_getClearSelect() :
            $this->_buildClearSelect($select);

        if(count($countSelect->getPart(Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(Zend_Db_Select::GROUP);
        }

        $countSelect->columns('COUNT(DISTINCT e.entity_id)');
        if ($resetLeftJoins) {
            $countSelect->resetJoinLeft();
        }
        return $countSelect;
    }
}