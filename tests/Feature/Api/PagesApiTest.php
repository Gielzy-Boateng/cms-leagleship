<?php

use App\Models\Page;
use Illuminate\Support\Facades\Storage;

test('pages api returns only published pages', function () {
    Page::factory()->create(['title' => 'Draft Page']);
    Page::factory()->published()->create(['title' => 'About']);
    Page::factory()->published()->create(['title' => 'Home']);

    $this->getJson('/api/pages')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.title', 'About')
        ->assertJsonPath('data.1.title', 'Home')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'blocks', 'html', 'body_text', 'updated_at'],
            ],
        ]);
});

test('pages api returns blocks in order with rendered html and plain text', function () {
    Page::factory()->published()->create([
        'slug' => 'philosophy',
        'content' => [
            ['type' => 'heading', 'text' => 'Our leadership philosophy', 'level' => 2],
            ['type' => 'paragraph', 'text' => "Line one.\nLine two."],
            ['type' => 'stat', 'value' => '1,200', 'label' => 'Leaders mentored'],
        ],
    ]);

    $response = $this->getJson('/api/pages/philosophy')->assertOk();

    expect($response->json('data.blocks.0.type'))->toBe('heading')
        ->and($response->json('data.blocks.2.type'))->toBe('stat')
        ->and($response->json('data.html'))
        ->toContain('<h2>Our leadership philosophy</h2>')
        ->toContain("<p>Line one.<br>\nLine two.</p>")
        ->toContain('<strong>1,200</strong>')
        ->and($response->json('data.body_text'))->toContain('Leaders mentored');
});

test('pages api escapes text blocks and sanitises rich text blocks', function () {
    Page::factory()->published()->create([
        'slug' => 'unsafe',
        'content' => [
            ['type' => 'heading', 'text' => '<script>alert("boom")</script>', 'level' => 2],
            ['type' => 'rich_text', 'html' => '<p>We lead.</p><script>alert("boom")</script><a href="https://example.com" onclick="track()">read</a>'],
        ],
    ]);

    $html = $this->getJson('/api/pages/unsafe')->assertOk()->json('data.html');

    expect($html)->not->toContain('<script>')
        ->and($html)->not->toContain('onclick')
        ->and($html)->toContain('&lt;script&gt;')
        ->and($html)->toContain('<p>We lead.</p>')
        ->and($html)->toContain('<a href="https://example.com">read</a>');
});

test('pages api resolves block image paths to public urls', function () {
    Storage::fake('public');
    Storage::disk('public')->put('pages/cohort.jpg', 'bytes');

    Page::factory()->published()->create([
        'slug' => 'founder',
        'content' => [
            ['type' => 'image', 'src' => 'pages/cohort.jpg', 'caption' => 'Opening cohort', 'alt' => 'Volunteers in a circle'],
        ],
    ]);

    $response = $this->getJson('/api/pages/founder')->assertOk();

    expect($response->json('data.blocks.0.src'))->toBe(Storage::disk('public')->url('pages/cohort.jpg'))
        ->and($response->json('data.html'))
        ->toContain('<figure><img src="'.Storage::disk('public')->url('pages/cohort.jpg').'" alt="Volunteers in a circle"')
        ->toContain('<figcaption>Opening cohort</figcaption>');
});

test('pages api show accepts an id', function () {
    $page = Page::factory()->published()->create(['slug' => 'home']);

    $this->getJson('/api/pages/'.$page->id)
        ->assertOk()
        ->assertJsonPath('data.slug', 'home');
});

test('pages api show hides draft pages', function () {
    Page::factory()->create(['slug' => 'secret']);

    $this->getJson('/api/pages/secret')->assertNotFound();
});
