<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class LoginWebTest extends PHPUnit_Extensions_SeleniumTestCase
{

  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("http://localhost/users/login");
  }


  public function testLogin()
  {
  	$this->open("/users/login");
    // $this->assertTitleEquals('Example Web Page');
    $this->type("id=UserEmail", "marcus@texttochange.com");
    $this->type("id=UserPassword", "marcus");
    $this->click("css=input[type=\"submit\"]");
    $this->waitForPageToLoad("30000");
    $this->assertEquals("Login successful.", $this->getText("id=flashMessage"));
  }


  public function testProgramManagerCannotEditProgramSettings()
  {
    $this->open("/users/login");
    $this->type("id=UserEmail", "maureen@texttochange.com");
    $this->type("id=UserPassword", "maureen");
    $this->click("css=input[type=\"submit\"]");
    $this->waitForPageToLoad("30000");
    $this->click("css=div.ttc-program-box");
    $this->waitForPageToLoad("30000");
    $this->click("link=Settings");
    $this->waitForPageToLoad("30000");
    $this->assertTrue((bool)preg_match('/^[\s\S]*\/view$/',$this->getLocation()));
  }


}
