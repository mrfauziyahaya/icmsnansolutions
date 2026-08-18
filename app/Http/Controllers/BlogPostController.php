<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $path = $this->storeOptimised($request->file('file'), 'blog/inline');

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
            $data['cover_image'] = $this->storeOptimised($request->file('cover'), 'blog/covers');
        }

        return $data;
    }

    /**
     * Store an upload at a sane size. Phone and camera images arrive several
     * thousand pixels wide, far more than the blog renders, so they are capped
     * on the long edge.
     *
     * Format is chosen by measuring both encodings rather than by rule: JPEG
     * usually wins for photographs, but a screenshot or flat graphic often
     * encodes smaller as PNG, and forcing that to JPEG would inflate the file
     * and soften it. If neither beats the original and no resize was needed,
     * the upload is stored untouched, as is anything GD cannot read.
     */
    private function storeOptimised(UploadedFile $file, string $directory, int $max = 1600): string
    {
        $original = (string) file_get_contents($file->getRealPath());
        $image    = @imagecreatefromstring($original);

        if (! $image) {
            return $file->store($directory, 'public');
        }

        $width   = imagesx($image);
        $height  = imagesy($image);
        $scale   = min($max / $width, $max / $height, 1);   // never enlarge
        $resized = $scale < 1;

        if ($resized) {
            $smaller = imagecreatetruecolor((int) round($width * $scale), (int) round($height * $scale));
            imagealphablending($smaller, false);
            imagesavealpha($smaller, true);
            imagecopyresampled(
                $smaller, $image, 0, 0, 0, 0,
                imagesx($smaller), imagesy($smaller), $width, $height
            );
            imagedestroy($image);
            $image = $smaller;
        }

        $candidates = ['png' => $this->encodePng($image)];
        if (! $this->hasTransparency($image)) {
            $candidates['jpg'] = $this->encodeJpeg($image);
        }
        imagedestroy($image);

        // Compare by length: min() on binary strings compares bytewise, and a
        // PNG (0x89...) always sorts below a JPEG (0xFF...) whatever its size.
        // Compare by length: min() on binary strings compares bytewise, and a
        // PNG (0x89...) always sorts below a JPEG (0xFF...) whatever its size.
        $extension = 'png';
        if (isset($candidates['jpg']) && strlen($candidates['jpg']) < strlen($candidates['png'])) {
            $extension = 'jpg';
        }
        $binary = $candidates[$extension];

        // Re-encoding can beat nothing on an already-optimised file.
        if (! $resized && strlen($binary) >= strlen($original)) {
            return $file->store($directory, 'public');
        }

        $path = $directory . '/' . Str::random(40) . '.' . $extension;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /** @param \GdImage $image */
    private function encodePng($image): string
    {
        imagesavealpha($image, true);
        ob_start();
        // Level 6, not 9: on a 1254px cover, 9 cost 4.3s to beat 6 by 3%.
        imagepng($image, null, 6);

        return (string) ob_get_clean();
    }

    /**
     * JPEG has no alpha, so the image is composited onto white first. Without
     * that, GD renders every transparent pixel black.
     *
     * @param \GdImage $image
     */
    private function encodeJpeg($image): string
    {
        $flat = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefilledrectangle($flat, 0, 0, imagesx($flat), imagesy($flat), imagecolorallocate($flat, 255, 255, 255));
        imagealphablending($flat, true);
        imagecopy($flat, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        ob_start();
        imagejpeg($flat, null, 82);
        $binary = (string) ob_get_clean();
        imagedestroy($flat);

        return $binary;
    }

    /**
     * Whether any pixel is actually see-through. The PNG header is no use:
     * editors routinely export fully opaque images as RGBA, which would rule out
     * JPEG for the very photographs that gain most from it — one 1.9MB cover was
     * colour type 6 with not a single transparent pixel, and JPEG took it to
     * 262KB. Sampled on a stride so the scan stays cheap on a large image.
     *
     * @param \GdImage $image
     */
    private function hasTransparency($image): bool
    {
        $width  = imagesx($image);
        $height = imagesy($image);
        $step   = max(1, (int) sqrt($width * $height / 40000));

        for ($x = 0; $x < $width; $x += $step) {
            for ($y = 0; $y < $height; $y += $step) {
                if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
