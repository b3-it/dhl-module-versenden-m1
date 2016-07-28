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
 * @package   Dhl\Versenden\Webservice\Parser
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Webservice\Parser\Soap;
use Dhl\Bcs\Api as VersendenApi;
use \Dhl\Versenden\Webservice\Parser;
use Dhl\Versenden\Webservice\ResponseData;

/**
 * ShipmentParser
 *
 * @category Dhl
 * @package  Dhl\Versenden\Webservice\Parser
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
abstract class Shipment implements Parser
{
    /**
     * @param \stdClass $response
     * @return \stdClass
     */
    abstract public function parse($response);

    /**
     * @param VersendenApi\Statusinformation $statusInfo
     * @return ResponseData\Status
     */
    protected function parseResponseStatus(VersendenApi\Statusinformation $statusInfo)
    {
        $status = new ResponseData\Status(
            $statusInfo->getStatusCode(),
            $statusInfo->getStatusText(),
            $statusInfo->getStatusMessage()
        );
        return $status;
    }
}
