<?php

/**
 * See LICENSE.md for license details.
 */

class Dhl_Versenden_Test_Model_Observer_AutoloaderTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @singleton dhl_versenden/autoloader
     */
    public function registerAutoload()
    {
        $configMock = $this->getModelMock('dhl_versenden/config', array('isAutoloadEnabled'));
        $configMock
            ->expects($this->once())
            ->method('isAutoloadEnabled')
            ->willReturn(true);
        $this->replaceByMock('model', 'dhl_versenden/config', $configMock);

        $autoloaderMock = $this->getHelperMock('dhl_versenden/autoloader', array('register'));
        $autoloaderMock
            ->expects($this->once())
            ->method('register');
        $this->replaceByMock('helper', 'dhl_versenden/autoloader', $autoloaderMock);

        $observer = new Dhl_Versenden_Model_Observer_Autoload();
        $observer->registerAutoload();
    }

    /**
     * @test
     * @singleton dhl_versenden/autoloader
     */
    public function registerAutoloadOff()
    {
        $configMock = $this->getModelMock('dhl_versenden/config', array('isAutoloadEnabled'));
        $configMock
            ->expects($this->once())
            ->method('isAutoloadEnabled')
            ->willReturn(false);
        $this->replaceByMock('model', 'dhl_versenden/config', $configMock);

        $autoloaderMock = $this->getHelperMock('dhl_versenden/autoloader', array('register'));
        $autoloaderMock
            ->expects($this->never())
            ->method('register');
        $this->replaceByMock('helper', 'dhl_versenden/autoloader', $autoloaderMock);

        $observer = new Dhl_Versenden_Model_Observer_Autoload();
        $observer->registerAutoload();
    }
}
