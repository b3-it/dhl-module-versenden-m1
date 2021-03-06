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
 * @package   Dhl_Versenden
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
use \Dhl\Versenden\Bcs\Api\Product;
/**
 * Dhl_Versenden_Model_Adminhtml_System_Config_Source_Procedure
 *
 * @category Dhl
 * @package  Dhl_Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Dhl_Versenden_Model_Adminhtml_System_Config_Source_Procedure
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();

        $options = $this->toArray();
        foreach ($options as $value => $label) {
            $optionArray[]= array('value' => $value, 'label' => $label);
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $helper = Mage::helper('dhl_versenden/data');
        return array(
            // DHL Paket procedures
            Product::PROCEDURE_PAKET_NATIONAL => $helper->__('DHL Paket: V01PAK'),
            Product::PROCEDURE_WARENPOST_NATIONAL => $helper->__('DHL Warenpost National: V62WP'),
            Product::PROCEDURE_WELTPAKET => $helper->__('DHL Paket International: V53WPAK'),
            Product::PROCEDURE_RETURNSHIPMENT_NATIONAL => $helper->__('Retoure DHL Paket: V01PAK'),

            // Legacy DHL AT procedures
            '82' => $helper->__('[AT] V82PARCEL (deleted)'),
            '86' => $helper->__('[AT] V86PARCEL (deleted)'),
            '87' => $helper->__('[AT] V87PARCEL (deleted)'),
            '83' => $helper->__('[AT] Retoure V86PARCEL (deleted)'),
            '85' => $helper->__('[AT] Retoure V87PARCEL (deleted)'),
        );
    }
}
