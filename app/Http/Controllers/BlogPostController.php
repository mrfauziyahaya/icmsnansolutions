<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $posts = BlogPost::with('category')
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('blog.admin.index', compact('posts', 'search'));
    }

    public function create()
    {
        return view('blog.admin.form', [
            'post'       => new BlogPost(['is_published' => false]),
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['slug']    = BlogPost::uniqueSlug($data['title']);

        $post = BlogPost::create($this->withCover($request, $data));

        return redirect()->route('blog-posts.index')
            ->with('status', "Artikel “{$post->title}” disimpan.");
    }

    public function edit(BlogPost $blogPost)
    {
        return view('blog.admin.form', [
            'post'       => $blogPost,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $data = $this->validated($request);
        $data['slug'] = BlogPost::uniqueSlug($data['title'], $blogPost->id);

        $blogPost->update($this->withCover($request, $data, $blogPost));

        return redirect()->route('blog-posts.index')
            ->with('status', "Artikel “{$blogPost->title}” dikemaskini.");
    }

    public function destroy(BlogPost $blogPost)
    {
        if ($blogPost->cover_image) {
            Storage::disk('public')->delete($blogPost->cover_image);
        }
        $blogPost->delete();

        return redirect()->route('blog-posts.index')->with('status', 'Artikel dipadam.');
    }

    /**
     * Trix inline-image upload. Stores the file and returns its public URL,
     * which Trix drops into the post body as an attachment.
     */
    public function attachment(Request $request)
    {
        $request->validate(['file' => 'required|image|max:5120']);

        $path = $request->file('file')->store('blog/inline', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'            => 'required|string|max:255',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'excerpt'          => 'nullable|string|max:500',
            'body'             => 'nullable|string',
            'cover'            => 'nullable|image|max:5120',
            'is_published'     => 'nullable|boolean',
            'published_at'     => 'nullable|date',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);
    }

    /**
     * Fold the uploaded cover (if any) and the publish state into the data. A
     * post being published without a date gets "now".
     */
    private function withCover(Request $request, array $data, ?BlogPost $existing = null): array
    {
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published']
            ? ($request->filled('published_at') ? $request->date('published_at') : ($existing?->published_at ?? now()))
            : null;

        unset($data['cover']);

        if ($request->hasFile('cover')) {
            if ($existing?->cover_image) {
                Storage::disk('public')->delete($existing->cover_image);
            }
            $data['cover_image'] = $request->file('cover')->store('blog/covers', 'public');
        }

        return $data;
    }
}
