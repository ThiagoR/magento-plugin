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
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_CartController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $utm = array();
        foreach (array('utm_source', 'utm_medium', 'utm_term', 'utm_content', 'utm_campaign') as $k) {
            if (!empty($_GET[$k])) {
                $utm = $k.'='.urlencode($_GET[$k]);
            }
        }
        $utm = join('&', $utm);
            
        $customerSession = Mage::getSingleton('customer/session');
        if (!$customerSession->isLoggedIn()) {
            $customerSession->setBeforeAuthUrl(Mage::getUrl('checkout/cart'));
            $customerSession->addNotice(Mage::helper('foomanjirafe')->__('Please login to continue shopping.'));
            $url = 'customer/account/login';
        } else {
            $url = 'checkout/cart';
        }
        
        $this->_redirect($url.($utm ? '?'.$utm : ''));
    }
}
