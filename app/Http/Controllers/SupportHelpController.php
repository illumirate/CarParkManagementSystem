<?php

namespace App\Http\Controllers;

use App\Models\HelpArticle;
use Illuminate\View\View;

class SupportHelpController extends Controller
{
    /**
     * Help documentation & parking guidelines.
     */
    public function index(): View
    {
        $articles = HelpArticle::query()
            ->where('is_published', true)
            ->orderBy('category')
            ->orderBy('title')
            ->get();

        return view('support.help', [
            'articles' => $articles,
        ]);
    }
}
