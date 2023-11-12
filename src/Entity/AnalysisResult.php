<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * @see https://exercism.org/docs/building/tooling/analyzers/interface#h-output-format
 */
class AnalysisResult
{
    // FIXME: Automatically generate a summary from comments
    private ?string $summary = null;
    /** @var AnalysisComment[] */
    private array $comments = [];
    /** @var array<string>  */
    private array $tags = [];

    public static function fromPhpstanJson(string $phpstanOutput): self
    {
        $json = json_decode($phpstanOutput, true); // FIXME: Handle error

        $result = new self();

        // Parse tags
        foreach ($json['files'] as $file => $fileData) {
            foreach ($fileData['messages'] as $message) {
                if (str_starts_with($message['message'], 'TAG:')) {
                    $result->tags[] = substr($message['message'], 4);
                }
            }
        }

        // Parse comments
        // @TODO

        return $result;
    }

    public function addComment(AnalysisComment $comment)
    {
        $this->comments[] = $comment;
    }

    public function analysisJsonSerialize(): array
    {
        $result = [];
        if ($this->summary) {
            $result['summary'] = $this->summary;
        }
        $result['comments'] = $this->comments;

        return $result;
    }

    public function tagsJsonSerialize(): array
    {
        return ['tags' => $this->tags];
    }
}
