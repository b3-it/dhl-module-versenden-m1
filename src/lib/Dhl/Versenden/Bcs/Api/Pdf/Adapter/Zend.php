<?php
/**
 * Dhl Versenden
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * PHP version 5
 *
 * @category  Dhl
 * @package   Dhl\Versenden\Bcs\Api
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Bcs\Api\Pdf\Adapter;

use Dhl\Versenden\Bcs\Api\Pdf\Adapter;

/**
 * Zend Pdf Adapter
 *
 * @category Dhl
 * @package  Dhl\Versenden\Bcs\Api
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Zend implements Adapter
{
    /**
     * @param string[] $pages
     * @return string
     */
    public function merge($pages)
    {
        $pages = array_filter($pages);
        if (count($pages) === 1) {
            return current($pages);
        }

        $pdfOut = new \Zend_Pdf();

        foreach ($pages as $page) {
            $pdfIn = \Zend_Pdf::parse($page);
            foreach ($pdfIn->pages as $pageIn) {
                $pdfOut->pages[]= clone $pageIn;
            }
        }

        return $pdfOut->render();
    }
}
