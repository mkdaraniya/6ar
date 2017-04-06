<?php
class Bss_MultiStoreViewPricing_Model_Resource_CatalogRule_Rule extends Mage_CatalogRule_Model_Resource_Rule
{
    /**
     * Get catalog rules product price for specific date, website and
     * customer group
     *
     * @param int|string $date
     * @param int $wId
     * @param int $gId
     * @param int $pId
     *
     * @return float|bool
     */
    public function getRulePrice($date, $wId, $gId, $pId, $storeId = null)
    {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return parent::getRulePrice($date, $wId, $gId, $pId);

        $data = $this->getRulePrices($date, $wId, $gId, array($pId), $storeId);
        if (isset($data[$pId])) {
            return $data[$pId];
        }

        return false;
    }

	/**
     * Retrieve product prices by catalog rule for specific date, website and customer group
     * Collect data with  product Id => price pairs
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param array $productIds
     *
     * @return array
     */
    public function getRulePrices($date, $websiteId, $customerGroupId, $productIds, $storeId = null)
    {	
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return parent::getRulePrices($date, $websiteId, $customerGroupId, $productIds);

        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('multistoreviewpricing/rule_product_price'), array('product_id', 'rule_price'))
            ->where('rule_date = ?', $this->formatDate($date, false))
            ->where('store_id = ?', $storeId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id IN(?)', $productIds);
        return $adapter->fetchPairs($select);
    }

    /**
     * Delete old price rules data
     *
     * @param string $date
     * @param int|null $productId
     *
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    public function deleteOldData($date, $productId = null)
    {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return parent::deleteOldData($date, $productId);

        $write = $this->_getWriteAdapter();
        $conds = array();
        $conds[] = $write->quoteInto('rule_date<?', $this->formatDate($date));
        if (!is_null($productId)) {
            $conds[] = $write->quoteInto('product_id=?', $productId);
        }
        $write->delete($this->getTable('multistoreviewpricing/rule_product_price'), $conds);
        return $this;
    }

    /**
     * Remove catalog rules product prices for specified date range and product
     *
     * @param int|string $fromDate
     * @param int|string $toDate
     * @param int|null $productId
     *
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    public function removeCatalogPricesForDateRange($fromDate, $toDate, $productId = null)
    {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return parent::removeCatalogPricesForDateRange($fromDate, $toDate, $productId);

        $write = $this->_getWriteAdapter();
        $conds = array();
        $cond = $write->quoteInto('rule_date between ?', $this->formatDate($fromDate));
        $cond = $write->quoteInto($cond.' and ?', $this->formatDate($toDate));
        $conds[] = $cond;
        if (!is_null($productId)) {
            $conds[] = $write->quoteInto('product_id=?', $productId);
        }

        /**
         * Add information about affected products
         * It can be used in processes which related with product price (like catalog index)
         */
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('multistoreviewpricing/rule_product_price'), 'product_id')
            ->where(implode(' AND ', $conds))
            ->group('product_id');

        $replace = $write->insertFromSelect(
            $select,
            $this->getTable('catalogrule/affected_product'),
            array('product_id'),
            true
        );
        $write->query($replace);
        $write->delete($this->getTable('multistoreviewpricing/rule_product_price'), $conds);
        return $this;
    }

    /**
     * Apply catalog rule to product
     *
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param Mage_Catalog_Model_Product $product
     * @param array $websiteIds
     *
     * @throws Exception
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    public function applyToProduct($rule, $product, $websiteIds)
    {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return parent::applyToProduct($rule, $product, $websiteIds);

        if (!$rule->getIsActive()) {
            return $this;
        }

        $ruleId    = $rule->getId();
        $productId = $product->getId();

        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        if ($this->_isProductMatchedRule($ruleId, $product)) {
            $this->cleanProductData($ruleId, array($productId));
        }
        if ($this->validateProduct($rule, $product, $websiteIds)) {
            try {
                $this->insertRuleData($rule, $websiteIds, array(
                    $productId => array_combine(array_values($websiteIds), array_values($websiteIds)))
                );
            } catch (Exception $e) {
                $write->rollback();
                throw $e;
            }
        } else {
            $write->delete($this->getTable('multistoreviewpricing/rule_product_price'), array(
                $write->quoteInto('product_id = ?', $productId),
            ));
        }

        $write->commit();
        return $this;
    }

    /**
     * Run reindex
     *
     * @param int|Mage_Catalog_Model_Product $product
     */
    protected function _reindexCatalogRule($product = null)
    {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return parent::_reindexCatalogRule($product);
        
        $indexerCode = 'multistoreviewpricing/action_index_refresh';
        $value = null;
        if ($product) {
            $value = $product instanceof Mage_Catalog_Model_Product ? $product->getId() : $product;
            $indexerCode = 'multistoreviewpricing/action_index_refresh_row';
        }

        /** @var $indexer Mage_CatalogRule_Model_Action_Index_Refresh */
        $indexer = Mage::getModel(
            $indexerCode,
            array(
                'connection' => $this->_getWriteAdapter(),
                'factory'    => Mage::getModel('core/factory'),
                'resource'   => $this,
                'app'        => Mage::app(),
                'value'      => $value
            )
        );
        $indexer->execute();
    }
}
