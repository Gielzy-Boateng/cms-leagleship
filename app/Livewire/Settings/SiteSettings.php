<?php

namespace App\Livewire\Settings;

use App\Models\SiteSetting;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Site settings')]
class SiteSettings extends Component
{
    #[Validate(['required', 'string', 'max:255'])]
    public string $orgName = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $contactHeading = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $addressLine1 = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $addressLine2 = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $addressRegion = '';

    #[Validate(['nullable', 'email:rfc', 'max:255'])]
    public string $email = '';

    #[Validate(['nullable', 'string', 'max:50'])]
    public string $phone = '';

    #[Validate(['nullable', 'url:http,https', 'max:255'])]
    public string $facebookUrl = '';

    #[Validate(['nullable', 'url:http,https', 'max:255'])]
    public string $instagramUrl = '';

    #[Validate(['nullable', 'url:http,https', 'max:255'])]
    public string $linkedinUrl = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $contactButtonLabel = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $newsletterHeading = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $newsletterPlaceholder = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $newsletterButtonLabel = '';

    #[Validate(['nullable', 'string', 'max:1000'])]
    public string $newsletterConsent = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $footerNote = '';

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->orgName = $settings->org_name;
        $this->contactHeading = (string) $settings->contact_heading;
        $this->addressLine1 = (string) $settings->address_line1;
        $this->addressLine2 = (string) $settings->address_line2;
        $this->addressRegion = (string) $settings->address_region;
        $this->email = (string) $settings->email;
        $this->phone = (string) $settings->phone;
        $this->facebookUrl = (string) $settings->facebook_url;
        $this->instagramUrl = (string) $settings->instagram_url;
        $this->linkedinUrl = (string) $settings->linkedin_url;
        $this->contactButtonLabel = (string) $settings->contact_button_label;
        $this->newsletterHeading = (string) $settings->newsletter_heading;
        $this->newsletterPlaceholder = (string) $settings->newsletter_placeholder;
        $this->newsletterButtonLabel = (string) $settings->newsletter_button_label;
        $this->newsletterConsent = (string) $settings->newsletter_consent;
        $this->footerNote = (string) $settings->footer_note;
    }

    public function save(): void
    {
        $this->validate();

        SiteSetting::current()->update([
            'org_name' => $this->orgName,
            'contact_heading' => $this->contactHeading ?: null,
            'address_line1' => $this->addressLine1 ?: null,
            'address_line2' => $this->addressLine2 ?: null,
            'address_region' => $this->addressRegion ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'facebook_url' => $this->facebookUrl ?: null,
            'instagram_url' => $this->instagramUrl ?: null,
            'linkedin_url' => $this->linkedinUrl ?: null,
            'contact_button_label' => $this->contactButtonLabel ?: null,
            'newsletter_heading' => $this->newsletterHeading ?: null,
            'newsletter_placeholder' => $this->newsletterPlaceholder ?: null,
            'newsletter_button_label' => $this->newsletterButtonLabel ?: null,
            'newsletter_consent' => $this->newsletterConsent ?: null,
            'footer_note' => $this->footerNote ?: null,
        ]);

        session()->flash('status', 'Site settings saved.');
        $this->redirectRoute('settings', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.settings.site-settings');
    }
}
