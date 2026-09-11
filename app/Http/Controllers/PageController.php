<?php

namespace App\Http\Controllers;

use App\Services\MarkdownService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Render a static informational page (e.g. About, Terms, Privacy) from a
     * Markdown file stored in `resources/markdown`.
     *
     * The page slug is validated against the route constraint, so only known
     * pages reach this action.
     */
    public function show(string $page): View
    {
        $path = resource_path("markdown/{$page}.md");

        if (! File::exists($path)) {
            abort(404);
        }

        return view('pages.static', [
            'title' => Str::title($page),
            'contentHtml' => MarkdownService::render(File::get($path)),
        ]);
    }
}