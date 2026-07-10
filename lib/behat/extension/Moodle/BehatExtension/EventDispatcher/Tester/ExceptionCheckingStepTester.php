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

namespace Moodle\BehatExtension\EventDispatcher\Tester;

use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\SkippedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\Result\UndefinedStepResult;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Moodle\BehatExtension\Exception\SkippedException;

// phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod

/**
 * Override step tester to check for exceptions and debugging messages after each step.
 *
 * After every executed step, the internal 'I look for exceptions' step definition
 * (see behat_hooks::i_look_for_exceptions) is run so that Moodle exceptions,
 * debugging() calls and PHP notices caught during the step fail the scenario.
 *
 * It also converts steps throwing SkippedException into skipped step results.
 *
 * @package    core
 * @copyright  2016 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ExceptionCheckingStepTester implements StepTester {
    /**
     * The text of the step to look for exceptions / debugging messages.
     */
    const EXCEPTIONS_STEP_TEXT = 'I look for exceptions';

    /**
     * @var StepTester Base step tester.
     */
    private $singlesteptester;

    /**
     * Constructor.
     *
     * @param StepTester $steptester single step tester.
     */
    public function __construct(StepTester $steptester) {
        $this->singlesteptester = $steptester;
    }

    /**
     * Sets up step for a test.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param bool     $skip
     *
     * @return Setup
     */
    public function setUp(Environment $env, FeatureNode $feature, StepNode $step, $skip) {
        return $this->singlesteptester->setUp($env, $feature, $step, $skip);
    }

    /**
     * Tests step.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param bool     $skip
     * @return StepResult
     */
    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip) {
        $result = $this->checkSkipResult($this->singlesteptester->test($env, $feature, $step, $skip));

        // If undefined step then there is nothing to check for.
        if ($result instanceof UndefinedStepResult) {
            return $result;
        }

        // If exception caught, then the step already failed.
        if (($result instanceof ExecutedStepResult) && $result->hasException()) {
            return $result;
        }

        // If step is skipped, then no need to look for exceptions.
        if ($result instanceof SkippedStepResult) {
            return $result;
        }

        // Check for exceptions.
        // Extra step, looking for a moodle exception, a debugging() message or a PHP debug message.
        $checkingstep = new StepNode('Given', self::EXCEPTIONS_STEP_TEXT, [], $step->getLine());
        $afterexceptioncheckingevent = $this->singlesteptester->test($env, $feature, $checkingstep, $skip);
        $exceptioncheckresult = $this->checkSkipResult($afterexceptioncheckingevent);

        if (!$exceptioncheckresult->isPassed()) {
            return $exceptioncheckresult;
        }

        return $result;
    }

    /**
     * Tears down step after a test.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param bool     $skip
     * @param StepResult  $result
     * @return Teardown
     */
    public function tearDown(Environment $env, FeatureNode $feature, StepNode $step, $skip, StepResult $result) {
        return $this->singlesteptester->tearDown($env, $feature, $step, $skip, $result);
    }

    /**
     * Handle skip exception.
     *
     * @param StepResult $result
     *
     * @return ExecutedStepResult|SkippedStepResult
     */
    private function checkSkipResult(StepResult $result) {
        if ((method_exists($result, 'getException')) && ($result->getException() instanceof SkippedException)) {
            return new SkippedStepResult($result->getSearchResult());
        } else {
            return $result;
        }
    }
}
