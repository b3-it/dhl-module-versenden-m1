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

/**
 * Dhl_Versenden_Test_Block_Adminhtml_Sales_Order_Shipment_ServiceTest
 *
 * @category Dhl
 * @package  Dhl_Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Dhl_Versenden_Test_Block_Adminhtml_Sales_Order_Shipment_ServiceTest
    extends EcomDev_PHPUnit_Test_Case
{
    protected function setUp()
    {
        $this->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        parent::setUp();
    }

    /**
     * @test
     * @loadFixture Model_ConfigTest
     */
    public function renderView()
    {
        $blockType = 'dhl_versenden/adminhtml_sales_order_shipment_service';
        $blockHtml = 'foo';

        $orderOne = new Mage_Sales_Model_Order();
        $orderOne->setShippingMethod('flatrate_flatrate');
        $shipmentOne = new Mage_Sales_Model_Order_Shipment();
        $shipmentOne->setOrder($orderOne);

        $orderTwo = new Mage_Sales_Model_Order();
        $orderTwo->setShippingMethod('dhlversenden_flatrate');
        $shipmentTwo = new Mage_Sales_Model_Order_Shipment();
        $shipmentTwo->setOrder($orderTwo);

        $blockMock = $this->getBlockMock($blockType, array('getShipment', 'fetchView'));
        $blockMock
            ->expects($this->exactly(2))
            ->method('getShipment')
            ->willReturnOnConsecutiveCalls($shipmentOne, $shipmentTwo);
        $blockMock
            ->expects($this->exactly(1))
            ->method('fetchView')
            ->willReturn($blockHtml);
        $this->replaceByMock('block', $blockType, $blockMock);

        /** @var Dhl_Versenden_Block_Adminhtml_Sales_Order_Shipment_Service $block */
        $block = Mage::app()->getLayout()->createBlock($blockType);

        $this->assertEmpty($block->renderView());
        $this->assertEquals($blockHtml, $block->renderView());
    }
}
