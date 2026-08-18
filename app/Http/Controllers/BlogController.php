<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;

/**
 * Public blog — the reader-facing listing and single-post pages.
 */
class BlogController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->filled('kategori')
            ? BlogCategory::where('slug', $request->query('kategori'))->first()
            : null;

        $posts = BlogPost::published()
            ->with('category')
            ->when($category, fn ($q) => $q->where('blog_category_id', $category->id))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', [
            'posts'      => $posts,
            'categories' => BlogCategory::has('posts')->orderBy('name')->get(),
            'active'     => $category,
        ]);
    }

    public function show(BlogPost $post)
    {
        abort_unless($post->is_published && $post->published_at && $post->published_at->isPast(), 404);

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
