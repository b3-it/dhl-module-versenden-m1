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
 * @package   Dhl\Versenden\Service
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Shipment\Service\Type;

/**
 * Date
 *
 * @category Dhl
 * @package  Dhl\Versenden\Service
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
abstract class Radio extends Text
{
    protected $frontendInputType = 'radio';

    /**
     * List of possible service values.
     * @see GenericWithDetails::$value
     * Format:
     *   key => localized value
     * @var string[]
     */
    protected $options;

    /**
     * Generic service constructor.
     *
     * @param string $name
     * @param bool $isEnabled
     * @param bool $isSelected
     * @param string[] $options
     */
    public function __construct($name, $isEnabled, $isSelected, $options)
    {
        $this->options = $options;

        parent::__construct($name, $isEnabled, $isSelected, '');
    }

    /**
     * @return \string[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getValueHtml()
    {
        $options = $this->getOptions();
        $values = array_keys($options);

        $optionsHtml = array_reduce(
            $values,
            function ($carry, $value) use ($options) {
                $checked = ($value == $this->getValue()) ? 'checked="checked"' : '';
                $carry .= sprintf(
                    '<input type="radio" name="service_setting[%s]" id="shipment_service_%sDetails" %s value="%s"> %s',
                    $this->getCode(),
                    $this->getCode(),
                    $checked,
                    $value,
                    $options[$value]
                );
                return $carry;
            }
        );

        return $optionsHtml;
    }
}
