<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_admin_can_publish_a_post_with_a_cover(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('blog-posts.store'), [
            'title'        => 'Tips Renew Roadtax',
            'excerpt'      => 'Panduan ringkas.',
            'body'         => '<div>Kandungan penuh artikel.</div>',
            'cover'        => UploadedFile::fake()->image('cover.jpg'),
            'is_published' => '1',
        ])->assertRedirect(route('blog-posts.index'));

        $post = BlogPost::sole();
        $this->assertSame('tips-renew-roadtax', $post->slug);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
        $this->assertNotNull($post->cover_image);
        Storage::disk('public')->assertExists($post->cover_image);
    }

    public function test_slugs_are_unique(): void
    {
        $admin = $this->admin();
        foreach (['Roadtax', 'Roadtax'] as $title) {
            $this->actingAs($admin)->post(route('blog-posts.store'), ['title' => $title, 'body' => 'x']);
        }

        $slugs = BlogPost::pluck('slug')->all();
        $this->assertSame(['roadtax', 'roadtax-2'], $slugs);
    }

    public function test_public_index_shows_published_but_hides_drafts_and_future(): void
    {
        $cat = BlogCategory::create(['name' => 'Insurans']);

        BlogPost::create(['title' => 'Live', 'slug' => 'live', 'body' => 'x', 'is_published' => true, 'published_at' => now()->subDay(), 'blog_category_id' => $cat->id]);
        BlogPost::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'x', 'is_published' => false]);
        BlogPost::create(['title' => 'Scheduled', 'slug' => 'sched', 'body' => 'x', 'is_published' => true, 'published_at' => now()->addWeek()]);

        $html = $this->get(route('blog.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Live', $html);
        $this->assertStringNotContainsString('>Draft<', $html);
        $this->assertStringNotContainsString('Scheduled', $html);
    }

    public function test_public_post_404s_until_published(): void
    {
        $draft = BlogPost::create(['title' => 'Secret', 'slug' => 'secret', 'body' => 'x', 'is_published' => false]);
        $this->get(route('blog.show', $draft))->assertNotFound();

        $draft->update(['is_published' => true, 'published_at' => now()->subMinute()]);
        $this->get(route('blog.show', $draft))->assertOk()->assertSee('Secret');
    }

    public function test_category_filter_narrows_the_listing(): void
    {
        $a = BlogCategory::create(['name' => 'Kereta']);
        $b = BlogCategory::create(['name' => 'Motor']);
        BlogPost::create(['title' => 'Kereta Post', 'slug' => 'kp', 'body' => 'x', 'is_published' => true, 'published_at' => now()->subDay(), 'blog_category_id' => $a->id]);
        BlogPost::create(['title' => 'Motor Post', 'slug' => 'mp', 'body' => 'x', 'is_published' => true, 'published_at' => now()->subDay(), 'blog_category_id' => $b->id]);

        $html = $this->get(route('blog.index', ['kategori' => 'kereta']))->assertOk()->getContent();
        $this->assertStringContainsString('Kereta Post', $html);
        $this->assertStringNotContainsString('Motor Post', $html);
    }

    public function test_trix_attachment_upload_stores_and_returns_a_url(): void
    {
        Storage::fake('public');

        $res = $this->actingAs($this->admin())->post(route('blog-posts.attachment'), [
            'file' => UploadedFile::fake()->image('inline.png'),
        ])->assertOk();

        $this->assertStringContainsString('/storage/blog/inline/', $res->json('url'));
    }

    public function test_the_public_blog_is_guarded_from_guests_only_for_admin_routes(): void
    {
        // Public read is open…
        $this->get(route('blog.index'))->assertOk();
        // …but the admin area is not.
        $this->get(route('blog-posts.index'))->assertRedirect();
    }

    /**
     * Uploads arrive straight off a phone or camera at several thousand pixels
     * wide — one 1.9MB cover came through untouched — so they are capped and
     * re-encoded on the way in.
     */
    public function test_an_oversized_cover_is_shrunk_on_upload(): void
    {
        $source = $this->photograph(3000, 2200);

        $this->actingAs($this->admin())->post(route('blog-posts.store'), [
            'title' => 'Big Cover',
            'body'  => 'x',
            'cover' => new UploadedFile($source, 'huge.jpg', 'image/jpeg', null, true),
        ])->assertRedirect();

        $stored = BlogPost::sole()->cover_image;
        Storage::disk('public')->assertExists($stored);

        [$width, $height] = getimagesize(Storage::disk('public')->path($stored));
        $this->assertLessThanOrEqual(1600, max($width, $height), 'cover was not resized');

        // Aspect ratio preserved.
        $this->assertEqualsWithDelta(3000 / 2200, $width / $height, 0.01);

        $this->assertLessThan(
            filesize($source),
            Storage::disk('public')->size($stored),
            'stored cover is not smaller than the upload'
        );

        @unlink($source);
    }

    /** Flattening a transparent image to JPEG would black out its background. */
    public function test_a_transparent_upload_stays_png(): void
    {
        $image = imagecreatetruecolor(400, 400);
        imagesavealpha($image, true);
        imagealphablending($image, false);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 255, 0, 0, 90));
        $source = tempnam(sys_get_temp_dir(), 'alpha') . '.png';
        imagepng($image, $source);
        imagedestroy($image);

        $this->actingAs($this->admin())->post(route('blog-posts.store'), [
            'title' => 'Transparent',
            'body'  => 'x',
            'cover' => new UploadedFile($source, 'logo.png', 'image/png', null, true),
        ])->assertRedirect();

        $this->assertSame('png', pathinfo(BlogPost::sole()->cover_image, PATHINFO_EXTENSION));

        @unlink($source);
    }

    /** A JPEG with detail, which is what a camera actually produces. */
    private function photograph(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        for ($x = 0; $x < $width; $x += 2) {
            for ($y = 0; $y < $height; $y += 2) {
                imagefilledrectangle($image, $x, $y, $x + 2, $y + 2, imagecolorallocate(
                    $image, ($x * 7 + $y) % 255, ($y * 11) % 255, ($x + $y * 3) % 255
                ));
            }
        }
        $path = tempnam(sys_get_temp_dir(), 'photo') . '.jpg';
        imagejpeg($image, $path, 95);
        imagedestroy($image);

        return $path;
    }
}
