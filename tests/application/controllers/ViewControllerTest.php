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
            $image = new Unsee_Image();
            $image->hash = $hash->key;
            $image->setFile(TEST_DATA_PATH . '/images/good/1mb.jpg');
        }

        return $hash;
    }

    public function testViewOwner($numImages = 1)
    {
        $hash = $this->upload($numImages);
        $this->dispatch('/view/index/hash/' . $hash->key . '/');

        $this->assertResponseCode(200);
        $this->assertController('view');
        $this->assertXpathCount('//img[contains(@src,"/image/")]', $numImages);

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

    public function testImageOutput()
    {
        $hash = $this->testViewAnon();
        $this->dispatch('/view/index/hash/' . $hash->key . '/');
        $this->assertResponseCode(310);
        $this->assertController('view');
    }

    public function testNoExif()
    {
        // TOOD: Implement
        // die(1);
    }
}
