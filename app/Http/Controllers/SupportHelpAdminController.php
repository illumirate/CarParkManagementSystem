<?php

namespace App\Http\Controllers;

use App\Models\HelpArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupportHelpAdminController extends Controller
{
    public function index(): View
    {
        $articles = HelpArticle::query()
            ->with(['createdBy', 'updatedBy'])
            ->orderByDesc('is_published')
            ->orderBy('category')
            ->orderBy('title')
            ->paginate(20);

        return view('admin.support.help.index', [
            'articles' => $articles,
        ]);
    }

    public function create(): View
    {
        return view('admin.support.help.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_published' => ['nullable', 'boolean'],
            'content' => ['required', 'string', 'max:20000'],
        ]);

        $slugBase = trim((string) ($validated['slug'] ?? ''));
        $slugBase = $slugBase !== '' ? $slugBase : $validated['title'];
        $slug = Str::slug($slugBase);
        $slug = $slug !== '' ? $slug : Str::random(8);

        $slug = $this->ensureUniqueSlug($slug);

        $article = HelpArticle::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'category' => $validated['category'] ?? null,
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'content' => $validated['content'],
            'created_by_user_id' => Auth::id(),
            'updated_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.support.help.edit', $article)
            ->with('success', 'Help article created.');
    }

    public function edit(HelpArticle $article): View
    {
        return view('admin.support.help.edit', [
            'article' => $article,
        ]);
    }

    public function update(Request $request, HelpArticle $article): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_published' => ['nullable', 'boolean'],
            'content' => ['required', 'string', 'max:20000'],
        ]);

        $slugBase = trim((string) ($validated['slug'] ?? ''));
        if ($slugBase === '') {
            $slugBase = $validated['title'];
        }
        $slug = Str::slug($slugBase);
        $slug = $slug !== '' ? $slug : Str::random(8);

        if ($slug !== $article->slug) {
            $slug = $this->ensureUniqueSlug($slug, $article->id);
        }

        $article->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'category' => $validated['category'] ?? null,
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'content' => $validated['content'],
            'updated_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.support.help.edit', $article)
            ->with('success', 'Help article updated.');
    }

    public function destroy(HelpArticle $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.support.help.index')
            ->with('success', 'Help article deleted.');
    }

    public function togglePublish(HelpArticle $article): RedirectResponse
    {
        $article->update([
            'is_published' => !$article->is_published,
            'updated_by_user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Publish status updated.');
    }

    private function ensureUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i = 2;

        while (HelpArticle::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
