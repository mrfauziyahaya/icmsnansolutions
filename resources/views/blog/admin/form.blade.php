<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">{{ $post->exists ? 'Edit Artikel' : 'Artikel Baru' }}</h2>
    </x-slot>

    {{-- Trix uploads read this endpoint (querySelector finds it anywhere). --}}
    <meta name="blog-attachment-url" content="{{ route('blog-posts.attachment') }}">

    @if($errors->any())
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            Sila semak semula borang — ada medan yang tidak lengkap.
        </div>
    @endif

    <form method="POST"
          action="{{ $post->exists ? route('blog-posts.update', $post) : route('blog-posts.store') }}"
          enctype="multipart/form-data"
          x-data="{ cover: '{{ $post->coverUrl() }}' }"
          class="space-y-6">
        @csrf
        @if($post->exists) @method('PUT') @endif

        <div class="bg-white shadow rounded-lg p-5 sm:p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tajuk <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $post->title) }}" required
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="blog_category_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                        <option value="">— Tiada —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('blog-categories.index') }}" class="mt-1 inline-block text-xs text-orange-600 hover:underline">Urus kategori</a>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Utama</label>
                    <input type="file" name="cover" accept="image/*"
                           @change="cover = URL.createObjectURL($event.target.files[0])"
                           class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-orange-50 file:px-3 file:py-1.5 file:text-orange-700 hover:file:bg-orange-100">
                    @error('cover')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <template x-if="cover">
                        <img :src="cover" class="mt-2 h-28 w-auto rounded-md object-cover border border-gray-200">
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Petikan Ringkas (excerpt)</label>
                <textarea name="excerpt" rows="2" maxlength="500"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">{{ old('excerpt', $post->excerpt) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Kosongkan untuk guna permulaan artikel secara automatik.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kandungan</label>
                <input id="body-input" type="hidden" name="body" value="{{ old('body', $post->body) }}">
                <trix-editor input="body-input"
                             class="trix-content block w-full rounded-md border border-gray-300 bg-white min-h-[22rem] px-3 py-2 text-sm"></trix-editor>
                @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Publish + SEO --}}
        <div class="bg-white shadow rounded-lg p-5 sm:p-6 space-y-5">
            <div class="flex flex-wrap items-center gap-6">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $post->is_published))
                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                    <span class="text-sm font-medium text-gray-700">Terbitkan</span>
                </label>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tarikh terbit (pilihan)</label>
                    <input type="datetime-local" name="published_at"
                           value="{{ old('published_at', $post->published_at?->timezone('Asia/Kuala_Lumpur')->format('Y-m-d\TH:i')) }}"
                           class="rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                </div>
            </div>

            <details class="text-sm">
                <summary class="cursor-pointer font-medium text-gray-700">SEO (pilihan)</summary>
                <div class="mt-3 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="2" maxlength="500"
                                  class="block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('meta_description', $post->meta_description) }}</textarea>
                    </div>
                </div>
            </details>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('blog-posts.index') }}" class="rounded-md bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200">Batal</a>
            <button type="submit" class="rounded-md bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-700">Simpan</button>
        </div>
    </form>
</x-app-layout>
