<?php
    /**
     * NOTICE OF LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     *
     * @package     Fooman_Jirafe
     * @copyright   Copyright (c) 2012 Jirafe Inc (http://www.jirafe.com)
     * @copyright   Copyright (c) 2012 Fooman Limited (http://www.fooman.co.nz)
     * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     */

class Fooman_Jirafe_Model_Cart extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'foomanjirafe_cart';
    protected $_eventObject = 'jirafecart';

    protected function _construct ()
    {
        $this->_init('foomanjirafe/cart');
    }

    public function recover($visitorIdMd5)
    {
        $recoverInfo = $this->loadByVisitorMd5($visitorIdMd5);
        if($recoverInfo->getId()){
            $oldQuote = Mage::getModel('sales/quote')->load($recoverInfo->getQuoteId());
            if ($oldQuote->getId()) {

                if($this->hasCartBeenPurchased($oldQuote)) {
                    Mage::throwException((Mage::helper('foomanjirafe')->__('This cart has already been ordered.')));
                }
                if ($oldQuote->getCustomerId()) {
                    //don't auto login cart that belongs to a user
                    return false;
                    /*
                    $customer = Mage::getModel('customer/customer')->load($oldQuote->getCustomerId());
                    if ($customerSession->isLoggedIn()) {
                        $customerSession->logout();
                    }
                    $customerSession->setCustomerAsLoggedIn($customer);
                    */
                }
                Mage::getSingleton('checkout/session')->replaceQuote($oldQuote);
                return true;
            }
        }
        return false;
    }

    public function saveRecoveryInformation($email, $visitorId, $quoteId)
    {
        $cart = $this->loadByVisitorMd5(md5($visitorId));
        $cart->setEmailAddress($email)
            ->setJirafeVisitorId($visitorId)
            ->setJirafeVisitorIdMd5(md5($visitorId))
            ->setQuoteId($quoteId)
            ->save();
    }

    public function periodicClean()
    {
        //delete after 90 days
        $deleteAfter = 60*60*24*90;
        $collection = $this->getCollection()->addFieldToFiler();
        $collection->addFieldToFilter('updated_at', array('to'=>date("Y-m-d", time()-$deleteAfter)));
        $collection->walk('delete');
    }

    public function loadByVisitorMd5($visitorIdMd5)
    {
        $collection = $this->getCollection();
        $collection
            ->addFieldToFilter('jirafe_visitor_id_md5', $visitorIdMd5)
            ->setOrder('created_at ', 'desc')
            ->load();
        if (count($collection)) {
            return $collection->getFirstItem();
        } else {
            //no cart recovery info saved yet - create a new one
            return Mage::getModel('foomanjirafe/cart');
        }
    }

    public function hasCartBeenPurchased($quote)
    {
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addAttributeToFilter('quote_id', $quote->getId())->load();
        return count($orderCollection) > 0;
    }

}