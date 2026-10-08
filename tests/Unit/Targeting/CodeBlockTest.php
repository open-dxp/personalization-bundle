<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PersonalizationBundle\Tests\Unit\Targeting;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Code\CodeBlock;

const PARTS = [
    'foo;',
    'bar?',
    "bazinga!\n!!!",
];
const JOINED = "foo;\nbar?\nbazinga!\n!!!";

it('joins its parts by lines', function () {
    $block = new CodeBlock(PARTS);

    expect($block->asString())
        ->toBe(JOINED)
        ->and((string) $block)
        ->toBe(JOINED);
});

it('starts without parts', function () {
    $block = new CodeBlock();

    expect($block->getParts())->toBe([]);
});

it('takes its parts later', function () {
    $block = new CodeBlock();

    $block->setParts(PARTS);

    expect($block->getParts())
        ->toBe(PARTS)
        ->and($block->asString())
        ->toBe(JOINED);
});

it('appends a part or several parts', function () {
    $block = new CodeBlock(PARTS);
    $block->append('foofoo');

    $block->append([
        '123',
        '456',
    ]);

    expect($block->asString())->toBe(JOINED . "\nfoofoo\n123\n456");
});

it('prepends a part or several parts', function () {
    $block = new CodeBlock(PARTS);
    $block->prepend('barbar');

    $block->prepend([
        '654',
        '321',
    ]);

    expect($block->asString())->toBe("654\n321\nbarbar\n" . JOINED);
});
