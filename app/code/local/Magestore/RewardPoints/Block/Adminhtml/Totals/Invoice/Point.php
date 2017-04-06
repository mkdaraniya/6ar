<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Rewardpoints
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Rewardpoints Block
 * 
 * @category    
 * @package     Rewardpoints
 * @author      Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Totals_Invoice_Point extends Mage_Adminhtml_Block_Sales_Order_Totals_Item
{
    /**
     * add points value into invoice total
     *     
     */
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $invoice = $totalsBlock->getInvoice();
        if ($invoice->getRewardpointsEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'rewardpoints_earn_label',
                'label' => $this->__('Earn Points'),
                'value' => $invoice->getRewardpointsEarn(),
//                'strong'        => true,
                'is_formated'   => true,
            )), 'subtotal');
        }
        if ($invoice->getRewardpointsDiscount()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'rewardpoints',
                'label' => $this->__('Point Discount'),
                'value' => -$invoice->getRewardpointsDiscount(),
            )), 'subtotal');
        }
    }
}
