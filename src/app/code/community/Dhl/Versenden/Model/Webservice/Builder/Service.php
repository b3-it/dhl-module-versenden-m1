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
use \Dhl\Versenden\Webservice\RequestData\ShipmentOrder\ServiceSelection;
use \Dhl\Versenden\Shipment\Service;
/**
 * Dhl_Versenden_Model_Webservice_Builder_Service
 *
 * @category Dhl
 * @package  Dhl_Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Dhl_Versenden_Model_Webservice_Builder_Service
{
    /** @var Dhl_Versenden_Model_Config_Shipper */
    protected $shipperConfig;

    /** @var Dhl_Versenden_Model_Config_Shipment */
    protected $shipmentConfig;

    /**
     * Dhl_Versenden_Model_Webservice_Builder_Service constructor.
     * @param stdClass[] $args
     * @throws Mage_Core_Exception
     */
    public function __construct($args)
    {
        $argName = 'shipper_config';
        if (!isset($args[$argName])) {
            throw new Mage_Core_Exception("required argument missing: $argName");
        }
        if (!$args[$argName] instanceof Dhl_Versenden_Model_Config_Shipper) {
            throw new Mage_Core_Exception("invalid argument: $argName");
        }
        $this->shipperConfig = $args[$argName];

        $argName = 'shipment_config';
        if (!isset($args[$argName])) {
            throw new Mage_Core_Exception("required argument missing: $argName");
        }
        if (!$args[$argName] instanceof Dhl_Versenden_Model_Config_Shipment) {
            throw new Mage_Core_Exception("invalid argument: $argName");
        }
        $this->shipmentConfig = $args[$argName];
    }

    /**
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $salesEntity
     * @param mixed[] $serviceInfo
     * @return ServiceSelection
     */
    public function getServiceSelection(Mage_Core_Model_Abstract $salesEntity, array $serviceInfo)
    {
        $selectedServices = $serviceInfo['shipment_service'];
        $serviceDetails = $serviceInfo['service_setting'];

        $dayOfDelivery = isset($selectedServices[Service\DayOfDelivery::CODE])
            ? $serviceDetails[Service\DayOfDelivery::CODE]
            : false;

        $deliveryTimeFrame = isset($selectedServices[Service\DeliveryTimeFrame::CODE])
            ? $serviceDetails[Service\DeliveryTimeFrame::CODE]
            : false;

        $preferredLocation = isset($selectedServices[Service\PreferredLocation::CODE])
            ? $serviceDetails[Service\PreferredLocation::CODE]
            : false;

        $preferredNeighbour = isset($selectedServices[Service\PreferredNeighbour::CODE])
            ? $serviceDetails[Service\PreferredNeighbour::CODE]
            : false;

        $parcelAnnouncement = isset($selectedServices[Service\ParcelAnnouncement::CODE])
            ? (bool)$selectedServices[Service\ParcelAnnouncement::CODE]
            : false;

        $visualCheckOfAge = isset($selectedServices[Service\VisualCheckOfAge::CODE])
            ? $serviceDetails[Service\VisualCheckOfAge::CODE]
            : false;

        $returnShipment = isset($selectedServices[Service\ReturnShipment::CODE])
            ? (bool)$selectedServices[Service\ReturnShipment::CODE]
            : false;

        $insurance = isset($selectedServices[Service\Insurance::CODE])
            ? number_format($salesEntity->getBaseGrandTotal(), 2)
            : false;

        $bulkyGoods = isset($selectedServices[Service\BulkyGoods::CODE])
            ? (bool)$selectedServices[Service\BulkyGoods::CODE]
            : false;

        $paymentMethod = $salesEntity->getPayment()->getMethod();
        $cod = $this->shipmentConfig->isCodPaymentMethod($paymentMethod, $salesEntity->getStoreId())
            ? number_format($salesEntity->getBaseGrandTotal(), 2)
            : false;

        $printOnlyIfCodeable = isset($selectedServices[Service\PrintOnlyIfCodeable::CODE])
            ? (bool)$selectedServices[Service\PrintOnlyIfCodeable::CODE]
            : false;

        return new ServiceSelection(
            $dayOfDelivery,
            $deliveryTimeFrame,
            $preferredLocation,
            $preferredNeighbour,
            $parcelAnnouncement,
            $visualCheckOfAge,
            $returnShipment,
            $insurance,
            $bulkyGoods,
            $cod,
            $printOnlyIfCodeable
        );
    }
}
