<?php

use App\Livewire\Settings\SiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can update the site settings from the dashboard', function () {
    Livewire::test(SiteSettings::class)
        ->set('orgName', 'Diba Leagleship Institute')
        ->set('addressLine1', 'No. 6 Nii Kotei Baale Ave,')
        ->set('email', 'hello@dibaleagleship.org')
        ->set('phone', '(233) 24 5678 567')
        ->set('facebookUrl', 'https://facebook.com/dibaleagleship')
        ->set('footerNote', '© 2026 Diba Leagleship Institute. All rights reserved.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('settings'));

    $settings = SiteSetting::sole();

    expect($settings->email)->toBe('hello@dibaleagleship.org')
        ->and($settings->address_line1)->toBe('No. 6 Nii Kotei Baale Ave,')
        ->and($settings->facebook_url)->toBe('https://facebook.com/dibaleagleship')
        ->and($settings->footer_note)->toBe('© 2026 Diba Leagleship Institute. All rights reserved.');
});

test('the institute name is required', function () {
    Livewire::test(SiteSettings::class)
        ->set('orgName', '')
        ->call('save')
        ->assertHasErrors(['orgName' => 'required']);
});

test('contact email and social urls are validated', function () {
    Livewire::test(SiteSettings::class)
        ->set('email', 'not-an-email')
        ->set('instagramUrl', 'javascript:alert(1)')
        ->call('save')
        ->assertHasErrors(['email' => 'email', 'instagramUrl' => 'url']);

    expect(SiteSetting::query()->value('instagram_url'))->toBeNull();
});

test('guests cannot open the settings page', function () {
    auth()->logout();

    $this->get('/settings')->assertRedirect('/login');
});

test('site api returns the live settings', function () {
    SiteSetting::create(array_merge(SiteSetting::defaults(), [
        'address_line1' => 'No. 6 Nii Kotei Baale Ave,',
        'address_line2' => 'East Legon, Accra,',
        'address_region' => 'Greater Accra Region (GA-414-1928)',
        'email' => 'hello@dibaleagleship.org',
        'phone' => '(233) 24 5678 567',
        'linkedin_url' => 'https://linkedin.com/company/dibaleagleship',
    ]));

    $this->getJson('/api/site')
        ->assertOk()
        ->assertJsonPath('data.org_name', 'Diba Leagleship Institute')
        ->assertJsonPath('data.contact.email', 'hello@dibaleagleship.org')
        ->assertJsonPath('data.contact.address', [
            'No. 6 Nii Kotei Baale Ave,',
            'East Legon, Accra,',
            'Greater Accra Region (GA-414-1928)',
        ])
        ->assertJsonPath('data.social_links', [
            ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/dibaleagleship'],
        ])
        ->assertJsonPath('data.newsletter.button_label', 'Sign Up Now')
        ->assertJsonStructure([
            'data' => [
                'org_name',
                'contact' => ['heading', 'address', 'email', 'phone', 'button_label'],
                'social_links',
                'newsletter' => ['heading', 'placeholder', 'button_label', 'consent'],
                'footer_note',
                'updated_at',
            ],
        ]);
});

test('site api omits blank contact details instead of returning empty strings', function () {
    SiteSetting::create(['org_name' => 'Diba Leagleship Institute']);

    $this->getJson('/api/site')
        ->assertOk()
        ->assertJsonPath('data.social_links', [])
        ->assertJsonPath('data.contact.address', [])
        ->assertJsonPath('data.contact.email', null);
});
