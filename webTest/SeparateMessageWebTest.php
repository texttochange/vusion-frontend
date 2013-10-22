<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class RequestWebTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("http://vusion-test.texttochange.org/");
  }

  public function testRequestAddDelet()
  {
    $this->windowMaximize();
    $this->open("/users/login");
    $this->type("id=UserEmail", "marcus@texttochange.com");
    $this->type("id=UserPassword", "marcus");
    $this->click("css=input[type=\"submit\"]");
    $this->waitForPageToLoad("30000");
    $this->click("link=Programs Management");
    $this->waitForPageToLoad("30000");
    $this->click("css=div.ttc-program-box");
    $this->waitForPageToLoad("30000");
    $this->click("link=New Message");
    $this->waitForPageToLoad("30000");
    $this->type("id=name", "Test Announcement");
    $this->click("id=UnattachedMessageSend-to-typeAll");
    $this->type("id=unattached-content", "Hello everyone");
    $this->click("id=UnattachedMessageType-scheduleImmediately");
    $this->click("id=UnattachedMessageType-scheduleFixedTime");
    $this->click("id=fixed-time");
    $this->click("xpath=(//button[@type='button'])[2]");
    $this->click("id=UnattachedMessageType-scheduleImmediately");
    $this->click("css=input[type=\"submit\"]");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Test Announcement"));
    $this->click("link=Delete");
    $this->assertTrue((bool)preg_match('/^Are you sure you want to delete the separate message "Test Announcement" [\s\S]$/',$this->getConfirmation()));
    $this->chooseOkOnNextConfirmation();
    $this->waitForPageToLoad("6000");
    $this->assertFalse($this->isTextPresent("Test Announcement"));
  }
}
?>