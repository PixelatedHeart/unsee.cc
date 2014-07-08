<?php

class ViewControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

    private function upload($imagesNum = 1)
    {
        $hash = new Unsee_Hash();

        for ($x = 1; $x <= $imagesNum; $x++) {
            $image = new Unsee_Image($hash->key . '_' . uniqid());
            $image->setFile(TEST_DATA_PATH . '/images/good/1mb.jpg');
        }

        $hash->expireAt(time() + 100);

        return $hash;
    }

    public function testViewOwner($numImages = 1)
    {
        $hash = $this->upload($numImages);
        $this->dispatch('/view/index/hash/' . $hash->key . '/');

        $this->assertResponseCode(200);
        $this->assertController('view');

        $html = $this->getResponse()->getBody();

        $pos = strpos($html, "a=[['");
        $this->assertGreaterThan(0, $pos);

        $html = substr($html, $pos);

        $num = substr_count($html, $hash->key . '_');
        $this->assertEquals($num, $numImages);

        return $hash;
    }

    public function testViewOwnerMulti()
    {
        return $this->testViewOwner(3);
    }

    public function testViewAnon()
    {
        $hash = $this->testViewOwner();

        $this->setUp();
        $_SERVER['HTTP_USER_AGENT'] = 'anonymous';

        $this->dispatch('/view/index/hash/' . $hash->key . '/');
        $this->assertController('view');
        $this->assertResponseCode(200);
        return $hash;
    }

    public function testDeleted()
    {
        $hash = $this->testViewAnon();
        $this->dispatch('/view/index/hash/' . $hash->key . '/');
        $this->assertResponseCode(310);
        $this->assertController('view');
    }

    public function testTtlHour()
    {
        $hash = $this->upload();
        $hash->ttl = 'hour';
        $hash->max_views = 0;

        $this->dispatch('/view/index/hash/' . $hash->key . '/');
        $this->assertResponseCode(200);
        $this->assertController('view');

        $body = $this->getResponse()->getBody();
        $this->assertContains('This page will be deleted in 1 minute', $body);
    }

    public function testNoExif()
    {
        // TOOD: Implement
        // die(1);
    }
}
