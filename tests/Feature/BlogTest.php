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
}
