<?php

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.ch)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\PersonalizationBundle\Tests\Unit\Targeting\Document;

use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Page;
use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Tests\Support\Helper\Document\TestDataHelper;
use OpenDxp\Tests\Support\Test\ModelTestCase;
use OpenDxp\Tests\Support\Util\TestHelper;

class TargetGroupEditableTest extends ModelTestCase
{
    protected int $seed = 1;

    protected Page $testPage;

    protected TestDataHelper $testDataHelper;

    public function _inject(TestDataHelper $testData): void
    {
        $this->testDataHelper = $testData;
    }

    public function testTargetGroupsEditable(): void
    {
        $defaultEditableName = 'inputEditable';
        $defaultEditableData = $this->seed;

        $this->createTestPage();
        $this->testDataHelper->fillInput($this->testPage, $defaultEditableName, $defaultEditableData);
        $this->testPage->save();

        $targetGroup1 = 'testGroup1';
        $targetGroup2 = 'testGroup2';

        // Create 2 different Target Groups
        $this->createTargetGroup($targetGroup1);
        $this->createTargetGroup($targetGroup2);

        $targetGroup1 = TargetGroup::getByName($targetGroup1);
        $targetGroup1EditableSeed = $this->seed + 1;
        $targetGroupEditableName1 = $this->saveTargetGroupEditable($targetGroup1, $defaultEditableName, $targetGroup1EditableSeed);

        $this->testDataHelper->assertInput($this->testPage, $targetGroupEditableName1, $targetGroup1EditableSeed);

        $targetGroup2 = TargetGroup::getByName($targetGroup2);
        $targetGroup2EditableSeed = $this->seed + 2;
        $targetGroupEditableName2 = $this->saveTargetGroupEditable($targetGroup2, $defaultEditableName, $targetGroup2EditableSeed);

        $this->testDataHelper->assertInput($this->testPage, $targetGroupEditableName2, $targetGroup2EditableSeed);

        //Test the value of first target group editable again
        $this->testDataHelper->assertInput($this->testPage, $targetGroupEditableName1, $targetGroup1EditableSeed);

        $this->reloadPage();

        // Test after reloading
        $this->testDataHelper->assertInput($this->testPage, $targetGroupEditableName1, $targetGroup1EditableSeed);
        $this->testDataHelper->assertInput($this->testPage, $targetGroupEditableName2, $targetGroup2EditableSeed);
    }

    protected function createTestPage(): void
    {
        /** @var Page $testPage */
        $testPage = TestHelper::createEmptyDocument(type: Page::class);
        $this->testPage = $testPage;
    }

    // Save the editable using the target specific prefix
    protected function saveTargetGroupEditable(TargetGroup $targetGroup, string $editableName, int $targetGroupSeed): string
    {
        $targetGroupData = 'content' . $targetGroupSeed;
        $this->testPage->setUseTargetGroup($targetGroup->getId());
        $targetGroupEditableName = $this->testPage->getTargetGroupEditableName($editableName);

        $this->testPage->setRawEditable($targetGroupEditableName, 'input', $targetGroupData);
        $this->testPage->save();

        return $targetGroupEditableName;
    }

    public function reloadPage(): void
    {
        $this->testPage = Page::getById($this->testPage->getId(), ['force' => true]);
    }

    // Create Target Group
    public function createTargetGroup(string $name): void
    {
        /** @var TargetGroup|TargetGroup\Dao $targetGroup */
        $targetGroup = new TargetGroup();
        $targetGroup->setName($name);
        $targetGroup->save();
    }
}
