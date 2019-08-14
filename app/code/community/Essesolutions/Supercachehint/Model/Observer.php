<?php

/**
 * ESSE SOLUTIONS srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@essesolutions.it so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category   Essesolutions
 * @package    Essesolutions_Supercachehint
 * @copyright  Copyright (c) 2011-2013 ESSE SOLUTIONS srl (http://www.essesolutions.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Marco Chinchio <marco.chinchio@essesolutions.it>
 */

/** 
 * Block After Render Observer
 *
 * @category   Essesolutions
 * @package    Essesolutions_Supercachehint
 * @author     Marco Chinchio <marco.chinchio@essesolutions.it>
 */
class Essesolutions_Supercachehint_Model_Observer extends Mage_Core_Block_Template {

  const XML_PATH_DEBUG_CACHE_HINTS = 'supercachehints/debug/template_hints';

  protected static $_showCacheHints;

  /**
   * Event core_block_abstract_to_html_after
   *
   * @param Varien_Event_Observer $params
   * @return void
   * @author Fabrizio Branca
   * @since 2011-01-24
   */
  public function core_block_abstract_to_html_after(Varien_Event_Observer $params) {

    if (!$this->getShowCacheHints()) {
      return;
    }

    $block = $params->getBlock();
    $transport = $params->getTransport();

    $wrappedHtml = $transport->getHtml();

    $cacheLifeTime = $block->getCacheLifetime();

    $info['cache-status'] = 'notcached';
    if (!is_null($cacheLifeTime)) {
      $info['cache-lifetime'] = (intval($cacheLifeTime) == 0) ? 'forever' : intval($cacheLifeTime) . ' sec';
      $info['cache-key'] = $block->getCacheKey();
      $info['tags'] = implode(',', $block->getCacheTags());
      $info['cache-status'] = 'cached';
    } elseif ($this->isWithinCachedBlock($block)) {
      $info['cache-status'] = 'implicitly cached';
    }

    $wrappedHtml = '<div style="position:relative; border:1px dotted green; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;">
<div style="position:absolute; left:0; top:0; padding:2px 5px; background:green; color:white; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'"
onmouseout="this.style.zIndex=\'998\'" title="'.$info['cache-status'].'">status = '.$info['cache-status'].($info['cache-status'] == 'cached' ? ' lifetime = ' . $info['cache-lifetime'] . ' key = ' . $info['cache-key'] . ' tags = ' .$info['tags'] : '').'</div>' . $wrappedHtml . '</div>';

    $transport->setHtml($wrappedHtml);
  }

  protected function isWithinCachedBlock(Mage_Core_Block_Abstract $block) {
    $step = $block;
    while ($step instanceof Mage_Core_Block_Abstract) {
      if (!is_null($step->getCacheLifetime())) {
        return true;
      }
      $step = $step->getParentBlock();
    }
    return false;
  }

  protected function getShowCacheHints() {
    if (is_null(self::$_showCacheHints)) {
      self::$_showCacheHints = Mage::getStoreConfig(self::XML_PATH_DEBUG_CACHE_HINTS) && Mage::helper('core')->isDevAllowed();
    }
    return self::$_showCacheHints;
  }

}
