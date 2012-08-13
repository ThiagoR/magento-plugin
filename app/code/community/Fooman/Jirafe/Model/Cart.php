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
    public function recover($visitorId)
    {
        $oldQuote = Mage::getModel('sales/quote')->load($visitorId, 'jirafe_visitor_id');
        if ($oldQuote->getId()) {
            Mage::getSingleton('checkout/session')->setQuoteId($oldQuote->getId());
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $quote->setJirafeOrigVisitorId($visitorId)->save();
            $siteId = Mage::helper('foomanjirafe')->getStoreConfig('site_id', $quote->getStoreId());

            $jirafePiwikUrl = 'http://' . Mage::getModel('foomanjirafe/jirafe')->getPiwikBaseUrl();
            $piwikTracker = new Fooman_Jirafe_Model_JirafeTracker($siteId, $jirafePiwikUrl);
            $piwikTracker->doRecoveryEmailUpdate($visitorId, 3);
        }
    }
}