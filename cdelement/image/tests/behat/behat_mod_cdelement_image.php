<?php
use Behat\MinkExtension\Context\MinkContext;

class behat_mod_cdelement_image extends behat_base {

    /**
     * @Then /^"(?P<locator_string>(?:[^"]|\\")*)" "xpath_element" should exist (?P<count>\d+) times$/
     */
    public function xpath_element_should_exist_times($locator, $count) {
        $nodes = $this->getSession()->getPage()->findAll('xpath', $locator);
        if (count($nodes) != $count) {
            throw new \Exception("Expected $count elements, found ".count($nodes));
        }
    }
}

?>
