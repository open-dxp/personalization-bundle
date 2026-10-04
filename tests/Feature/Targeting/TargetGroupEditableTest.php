<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PersonalizationBundle\Tests\Feature\Targeting;

use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Page;
use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Model\Document\Editable\Input;
use OpenDxp\Test\Factory\DocumentPageFactory;

function targetGroup(string $name): TargetGroup
{
    $targetGroup = new TargetGroup();
    $targetGroup->setName($name);
    $targetGroup->save();

    return TargetGroup::getByName($name);
}

function saveForTargetGroup(Page $page, TargetGroup $targetGroup, string $text): string
{
    $page->setUseTargetGroup($targetGroup->getId());
    $name = $page->getTargetGroupEditableName('headline');
    $page->setRawEditable($name, 'input', $text);
    $page->save();

    return $name;
}

function inputText(Page $page, string $name): string
{
    $editable = $page->getEditable($name);

    return $editable instanceof Input ? $editable->getData() : '';
}

it('keeps a version of an editable per target group', function () {
    $page = Page::getById(DocumentPageFactory::createOne()->getId(), ['force' => true]);
    $page->setRawEditable('headline', 'input', 'for everybody');
    $page->save();

    expect($page)->toBeInstanceOf(Page::class);

    $first = saveForTargetGroup($page, targetGroup('first'), 'for the first group');
    $second = saveForTargetGroup($page, targetGroup('second'), 'for the second group');

    $reloaded = Page::getById($page->getId(), ['force' => true]);

    expect([inputText($page, $first), inputText($page, $second)])->toBe(['for the first group', 'for the second group'])
        ->and([inputText($reloaded, $first), inputText($reloaded, $second)])->toBe(['for the first group', 'for the second group'])
        ->and(inputText($reloaded, 'headline'))->toBe('for everybody');
});
