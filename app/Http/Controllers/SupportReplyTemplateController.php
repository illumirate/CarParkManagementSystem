<?php

namespace App\Http\Controllers;

use App\Models\SupportReplyTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportReplyTemplateController extends Controller
{
    public function index(): View
    {
        $templates = SupportReplyTemplate::query()
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('title')
            ->paginate(20);

        return view('admin.support.templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create(): View
    {
        return view('admin.support.templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $template = SupportReplyTemplate::create([
            'title' => $validated['title'],
            'category' => $validated['category'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'content' => $validated['content'],
            'created_by_user_id' => Auth::id(),
            'updated_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.support.templates.edit', $template)
            ->with('success', 'Template created.');
    }

    public function edit(SupportReplyTemplate $template): View
    {
        return view('admin.support.templates.edit', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, SupportReplyTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $template->update([
            'title' => $validated['title'],
            'category' => $validated['category'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'content' => $validated['content'],
            'updated_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.support.templates.edit', $template)
            ->with('success', 'Template updated.');
    }

    public function destroy(SupportReplyTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('admin.support.templates.index')
            ->with('success', 'Template deleted.');
    }

    public function toggle(SupportReplyTemplate $template): RedirectResponse
    {
        $template->update([
            'is_active' => !$template->is_active,
            'updated_by_user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Template status updated.');
    }
}
