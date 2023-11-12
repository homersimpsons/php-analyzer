<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * @see https://exercism.org/docs/building/tooling/analyzers/interface#h-output-format
 */
class AnalysisComment implements \JsonSerializable
{
    public function __construct(
        private string $comment,
        private array $params,
        private CommentType $type = CommentType::Informative,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'comment' => $this->comment,
            'params' => $this->params ?: new \stdClass(), // Serialize to an empty object
            'type' => $this->type,
        ];
    }
}
