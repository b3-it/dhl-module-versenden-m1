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
use Dhl\Versenden\Webservice\RequestData\ShipmentOrder\Receiver\PostalFacility;

/**
 * Dhl_Versenden_Model_Observer
 *
 * @category Dhl
 * @package  Dhl_Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Dhl_Versenden_Model_Observer
{
    /**
     * Register autoloader in order to locate the extension libraries.
     */
    public function registerAutoload()
    {
        if (!Mage::getModel('dhl_versenden/config')->isAutoloadEnabled()) {
            return;
        }

        /** @var Dhl_Versenden_Helper_Autoloader $autoloader */
        $autoloader = Mage::helper('dhl_versenden/autoloader');

        $dhlLibs = array('Versenden', 'Bcs');
        array_walk($dhlLibs,
            function($libDir) use ($autoloader) {
                $autoloader->addNamespace(
                    "Dhl\\$libDir\\", // prefix
                    sprintf('%s/Dhl/%s/', Mage::getBaseDir('lib'), $libDir) // baseDir
                );
            }
        );

        $externalLibs = array('Psr');
        array_walk($externalLibs,
            function($libDir) use ($autoloader) {
                $autoloader->addNamespace(
                    "$libDir\\", // prefix
                    sprintf('%s/Netresearch/%s/', Mage::getBaseDir('lib'), $libDir) // baseDir
                );
            }
        );

        $autoloader->register();
    }

    /**
     * Append the service selection form elements to the opc shipping method form.
     * - event: core_block_abstract_to_html_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function appendServices(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if (!$block instanceof Mage_Checkout_Block_Onepage_Shipping_Method_Available) {
            return;
        }

        $serviceBlock = Mage::app()->getLayout()->createBlock(
            'dhl_versenden/checkout_onepage_shipping_method_service',
            'dhl_versenden_service',
            array(
                'template'    => 'dhl_versenden/checkout/shipping_services.phtml',
                'module_name' => 'Dhl_Versenden',
            )
        );

        $transport = $observer->getTransport();
        $html      = $transport->getHtml() . $serviceBlock->toHtml();
        $transport->setHtml($html);
    }

    /**
     * When the customer submits shipping method in OPC, then
     * - persist service settings
     * - process shipping address
     * Event:
     * - checkout_controller_onepage_save_shipping_method
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveShippingSettings(Varien_Event_Observer $observer)
    {
        //TODO(nr): rework DHL Versenden Info handling

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        /** @var Dhl_Versenden_Model_Config_Shipment $shipmentConfig */
        $shipmentConfig = Mage::getModel('dhl_versenden/config_shipment');

        if (!$shipmentConfig->canProcessMethod($shippingAddress->getShippingMethod())) {
            // customer selected a shipping method not to be processed via DHL Versenden
            return;
        }

        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $observer->getRequest();

        /** @var Dhl_Versenden_Model_Webservice_Builder_Receiver $receiverBuilder */
        $args = array(
            'country_directory' => Mage::getModel('directory/country'),
            'helper'            => Mage::helper('dhl_versenden/address'),
        );
        $receiverBuilder = Mage::getModel('dhl_versenden/webservice_builder_receiver', $args);
        $receiver = $receiverBuilder->getReceiver($shippingAddress);

        /** @var Dhl_Versenden_Model_Webservice_Builder_Service $serviceBuilder */
        $args = array(
            'shipper_config'  => Mage::getModel('dhl_versenden/config_shipper'),
            'shipment_config' => $shipmentConfig,
        );

        $services = $request->getPost('shipment_service', array());
        $settings = $request->getPost('service_setting', array());

        $services[\Dhl\Versenden\Shipment\Service\PrintOnlyIfCodeable::CODE] =
            $shipmentConfig->getSettings($quote->getStoreId())->isPrintOnlyIfCodeable();
        $services[\Dhl\Versenden\Shipment\Service\ParcelAnnouncement::CODE] =
            Mage::getModel('dhl_versenden/config_service')
                ->getServices($quote->getStoreId())
                ->getItem(\Dhl\Versenden\Shipment\Service\ParcelAnnouncement::CODE)
                ->isEnabled();

        $serviceBuilder = Mage::getModel('dhl_versenden/webservice_builder_service', $args);
        $serviceSettings = $serviceBuilder->getServiceSelection(
            $quote,
            array(
                'shipment_service' => $services,
                'service_setting'  => $settings,
            )
        );

        $shippingInfo = new \Dhl\Versenden\Webservice\RequestData\ShippingInfo($receiver, $serviceSettings);
        $shippingAddress->setData('dhl_versenden_info', json_encode($shippingInfo, JSON_FORCE_OBJECT));
    }

    /**
     * When a new order is placed, set the DHL Versenden carrier if applicable.
     * Event:
     * - sales_order_place_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateCarrier(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order          = $observer->getOrder();
        $shippingMethod = $order->getShippingMethod();
        /** @var Dhl_Versenden_Model_Config_Shipment $config */
        $config         = Mage::getModel('dhl_versenden/config_shipment');

        if ($config->canProcessMethod($shippingMethod)) {
            $parts          = explode('_', $shippingMethod);
            $parts[0]       = Dhl_Versenden_Model_Shipping_Carrier_Versenden::CODE;
            $shippingMethod = implode('_', $parts);
            $order->setShippingMethod($shippingMethod);
        }
    }

    /**
     * Read postal facility data from quote address.
     *
     * Dhl_Versenden comes with very basic facilities support, check out the
     * separate Dhl_LocationFinder extension for better integration.
     *
     * Facility properties:
     * - shop_type: [packStation|postOffice|parcelShop]
     * - shop_number: int(3)
     * - post_number: text(10)
     *
     * Event:
     * - dhl_versenden_set_postal_facility
     *
     * @param Varien_Event_Observer $observer
     */
    public function preparePostalFacility(Varien_Event_Observer $observer)
    {
        /** @var Varien_Object $facility */
        $facility = $observer->getPostalFacility();
        if ($facility->hasData()) {
            // someone else set a facility, we assume they know what they did.
            return;
        }

        /** @var Mage_Sales_Model_Quote_Address $address */
        $address    = $observer->getQuoteAddress();
        $station    = $address->getStreetFull();
        $postNumber = $address->getCompany();

        if ($postNumber != '' && !is_numeric($postNumber)) {
            // not a valid DHL account number
            return;
        }

        if (strpos($station, 'Packstation') === 0) {
            $facility->setData(
                array(
                    'shop_type'   => PostalFacility::TYPE_PACKSTATION,
                    'shop_number' => preg_filter('/^.*([\d]{3})$/', '$1', $station),
                    'post_number' => $postNumber,
                )
            );
        } elseif (strpos($station, 'Postfiliale') === 0) {
            $facility->setData(
                array(
                    'shop_type'   => PostalFacility::TYPE_POSTFILIALE,
                    'shop_number' => preg_filter('/^.*([\d]{3})$/', '$1', $station),
                    'post_number' => $postNumber,
                )
            );
        }
    }

    /**
     * Disable COD in case it is not available for the current destination.
     *
     * @param Varien_Event_Observer $observer
     */
    public function disableCodPayment(Varien_Event_Observer $observer)
    {
        $checkResult = $observer->getData('result');
        if (!$checkResult->isAvailable) {
            // payment method not available anyway
            return;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getData('quote');
        if (!$quote) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        if (!$quote) {
            // no quote, cannot check whether cod is allowed or not.
            return;
        }

        /** @var Mage_Payment_Model_Method_Abstract $methodInstance */
        $methodInstance = $observer->getData('method_instance');

        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $paymentMethod  = $methodInstance->getCode();

        /** @var Dhl_Versenden_Model_Config_Shipment $config */
        $config = Mage::getModel('dhl_versenden/config_shipment');
        if (!$config->canProcessMethod($shippingMethod, $quote->getStoreId())) {
            // no dhl shipping method
            return;
        }

        if (!$config->isCodPaymentMethod($paymentMethod, $quote->getStoreId())) {
            // no cod payment method
            return;
        }

        // obtain possible dhl products (national, weltpaket, …) and check if
        // the filter allows cod for these them
        $shipperCountry = Mage::getStoreConfig(
            Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
            $quote->getStoreId()
        );
        $recipientCountry = $quote->getShippingAddress()->getCountryId();
        $euCountries = Mage::getStoreConfig(Mage_Core_Helper_Data::XML_PATH_EU_COUNTRIES_LIST, $quote->getStoreId());
        $euCountries = explode(',', $euCountries);

        $availableProducts = \Dhl\Versenden\Product::getCodesByCountry(
            $shipperCountry,
            $recipientCountry,
            $euCountries
        );

        $filter = new \Dhl\Versenden\Shipment\Service\Filter($availableProducts, false, false);
        $codService = $filter->filterService(new \Dhl\Versenden\Shipment\Service\Cod('cod', true, true));
        if ($codService === null) {
            $checkResult->isAvailable = false;
        }
    }

    /**
     * Cancel the shipping label via DHL Business Customer Shipping API.
     * The track will not be deleted if shipping label deletion fails.
     * - event: sales_order_shipment_track_delete_before
     *
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Exception
     */
    public function deleteShippingLabel(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        $track = $observer->getData('track');
        if ($track->getCarrierCode() !== Dhl_Versenden_Model_Shipping_Carrier_Versenden::CODE) {
            return;
        }

        $gateway = Mage::getModel('dhl_versenden/webservice_gateway_soap');
        $shipmentNumbers = array($track->getData('track_number'));
        $response = $gateway->deleteShipmentOrder($shipmentNumbers);
        if ($response->getStatus()->isError()) {
            throw new Mage_Core_Exception($response->getStatus()->getStatusText());
        }

        //FIXME(nr): shipping label is still available after delete
        $track->getShipment()->unsetData('shipping_label');
        $track->getShipment()->save();
    }
}
