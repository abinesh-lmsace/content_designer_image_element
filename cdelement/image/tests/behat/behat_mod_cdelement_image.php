<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Behat custom steps for image cdelement.
 *
 * @package    cdelement_image
 * @category   test
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');


 /**
  * Behat custom steps for image cdelement.
  */
class behat_cdelement_image extends behat_base {

    /**
     * Checks if the element at the specified XPath exists the expected number of times.
     *
     * @Then /^"(?P<locator_string>(?:[^"]|\\")*)" "xpath_element" should exist (?P<count>\d+) times$/
     *
     * @param string $locator The XPath locator of the element.
     * @param int $count The expected number of times the element should exist.
     */
    public function xpath_element_should_exist_times($locator, $count) {
        $nodes = $this->getSession()->getPage()->findAll('xpath', $locator);
        if (count($nodes) != $count) {
            throw new \Exception("Expected $count elements, found ".count($nodes));
        }
    }
}
