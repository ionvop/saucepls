<?php

namespace App\Services;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;

class MarkdownService
{
    /**
     * Render a Markdown string to safe HTML.
     *
     * Raw HTML in the input is escaped and unsafe links are rejected, so the
     * output is safe to echo with {!! !!}.
     */
    public static function render(string $markdown): string
    {
        try {
            $converter = new CommonMarkConverter([
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ]);

            return $converter->convert($markdown)->getContent();
        } catch (CommonMarkException) {
            return e($markdown);
        }
    }
}