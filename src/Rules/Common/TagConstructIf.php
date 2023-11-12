<?php

namespace App\Rules\Common;

use App\Rules\TagRule;
use PhpParser\Node;
use PHPStan\Analyser\Scope;

class TagConstructIf extends TagRule
{
    public function getTag(): string
    {
        return 'construct:if';
    }

    public function getNodeType(): string
    {
        return Node\Stmt\If_::class;
    }

    public function hasTag(Node $node, Scope $scope): bool
    {
        return true;
    }
}
