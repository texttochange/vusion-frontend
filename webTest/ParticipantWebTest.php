<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class ParticipantWebTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("http://vusion-test.texttochange.org/");
  }

  public function testParticipantAddDelete()
  {
    $this->open("/users/login");
    $this->type("id=UserEmail", "marcus@texttochange.com");
    $this->type("id=UserPassword", "marcus");
    $this->click("css=input[type=\"submit\"]");
    $this->waitForPageToLoad("30000");
    $this->click("link=Programs Management");
    ## Issue with the program index that is very slow to load until we introduce AJAX
    $this->waitForPageToLoad("60000");
    $this->click("css=div.ttc-program-box");
    $this->waitForPageToLoad("30000");
    $this->click("link=Participants »");
    $this->waitForPageToLoad("30000");
    $this->click("link=Add");
    $this->waitForPageToLoad("30000");
    $this->type("id=ParticipantPhone", "256783255632");
    $this->click("css=input[type=\"submit\"]");
    $this->waitForPageToLoad("30000");
    $this->verifyTextPresent("+256783255632");
    $this->click("xpath=(//a[contains(text(),'Delete')])[4]");
    $this->waitForPageToLoad("30000");
    $this->assertTrue((bool)preg_match('/^Are you sure you want to delete participant \+\d* [\s\S]$/',$this->getConfirmation()));
    $this->chooseOkOnNextConfirmation();
    try {
        $this->assertFalse($this->isTextPresent("+256783255632"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>