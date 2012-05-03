<?php
class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://localhost/");
  }

  public function testFlashMessageDisappear()
  {
    $this->open("/users/login");
    $this->type("id=UserEmail", "marcus@texttochange.com");
    $this->type("id=UserPassword", "marcus");
    $this->click("css=input[type=\"submit\"]");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isElementPresent("id=flashMessage"));
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if (!$this->isVisible("id=flashMessage")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

  }
}
?>