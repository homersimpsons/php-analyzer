<?php

namespace App\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

abstract class TagRule implements Rule
{
    public const TAG_PREFIX = 'TAG:';

    protected bool $hasTag = false;

    /**
     * @inheritDoc
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->hasTag) {
            $this->hasTag = $this->hasTag($node, $scope);
            if ($this->hasTag) {
                return [RuleErrorBuilder::message(self::TAG_PREFIX . 'construct:if')->build()];
            }
        }

        return [];
    }

    /**
     * @returns string The tag that this rule implements
     */
    abstract public function getTag(): string;

    /**
     * @return bool Whether the tag is present or not
     */
    abstract public function hasTag(Node $node, Scope $scope): bool;
}
