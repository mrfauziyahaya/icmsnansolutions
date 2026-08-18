<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    public function index()
    {
        return view('blog.admin.categories', [
            'categories' => BlogCategory::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:blog_categories,name']);

        BlogCategory::create($data);

        return back()->with('status', 'Kategori ditambah.');
    }

    public function destroy(BlogCategory $blogCategory)
    {
        // Posts keep existing; their category is nulled by the FK constraint.
        $blogCategory->delete();

        return back()->with('status', 'Kategori dipadam.');
    }
}
