<?php

class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

    public function testMainPage()
    {
        $this->dispatch('/');
        $this->assertController('index');
        $this->assertAction('index');
    }

    public function testDefaultLang()
    {
        $this->setUp();
        $this->dispatch('/');
        $this->assertHeader('Content-Language', 'en');
    }

    public function testLang()
    {
        $locale = new Zend_Locale();
        $locale->setLocale('ru_RU');
        Zend_Registry::set('Zend_Locale', $locale);

        $this->setUp();
        $this->dispatch('/');
        $this->assertHeader('Content-Language', 'ru');
    }

    public function testBadLang()
    {
        $locale = new Zend_Locale();
        $locale->setLocale('qwerty');
        Zend_Registry::set('Zend_Locale', $locale);

        $this->setUp();
        $this->dispatch('/');
        $this->assertHeader('Content-Language', 'en');
    }

    public function testNoDnt()
    {
        $this->dispatch('/');
        $this->assertXpathContentContains('//script/@src', 'track.js');
    }

    public function testDnt()
    {
        $this->getRequest()->setHeader('DNT', 1);
        $this->dispatch('/');
        $this->assertNotXpathContentContains('//script/@src', 'track.js');
    }
}
