<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * @see https://exercism.org/docs/building/tooling/analyzers/interface#h-type-optional
 */
enum CommentType: string {
    /**
     * Comment that soft-block a student until they have addressed it.
     */
    case Essential = 'essential';
    /**
     * Any comment that gives a specific instruction to a user to improve their solution.
     */
    case Actionable = 'actionable';
    /**
     * Comments that give information, but do not necessarily expect students to use it. For example, in Ruby, if someone uses String Concatenation in TwoFer, we also tell them about String Formatting, but don't suggest that it is a better option.
     */
    case Informative = 'informative';
    /**
     * Comments that tell users they've done something right, either as a general comment on the solution, or on a technique.
     */
    case Celebratory = 'celebratory';
}