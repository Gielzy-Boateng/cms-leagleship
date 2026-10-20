<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $org_name
 * @property string|null $contact_heading
 * @property string|null $address_line1
 * @property string|null $address_line2
 * @property string|null $address_region
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $facebook_url
 * @property string|null $instagram_url
 * @property string|null $linkedin_url
 * @property string|null $contact_button_label
 * @property string|null $newsletter_heading
 * @property string|null $newsletter_placeholder
 * @property string|null $newsletter_button_label
 * @property string|null $newsletter_consent
 * @property string|null $footer_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'org_name', 'contact_heading', 'address_line1', 'address_line2', 'address_region',
    'email', 'phone', 'facebook_url', 'instagram_url', 'linkedin_url', 'contact_button_label',
    'newsletter_heading', 'newsletter_placeholder', 'newsletter_button_label', 'newsletter_consent', 'footer_note',
])]
class SiteSetting extends Model
{
    /**
     * The single editable settings record, created with defaults on first use.
     */
    public static function current(): static
    {
        return static::query()->firstOrCreate([], static::defaults());
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'org_name' => 'Diba Leagleship Institute',
            'contact_heading' => 'Contact Us',
            'contact_button_label' => 'Get in Touch',
            'newsletter_heading' => 'Sign up for Updates',
            'newsletter_placeholder' => 'Enter your Email',
            'newsletter_button_label' => 'Sign Up Now',
            'newsletter_consent' => 'By subscribing, you provide consent to receive updates from Diba Leagleship.',
            'footer_note' => '© '.now()->year.' Diba Leagleship Institute. All rights reserved.',
        ];
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public function socialLinks(): array
    {
        $networks = [
            'Facebook' => $this->facebook_url,
            'Instagram' => $this->instagram_url,
            'LinkedIn' => $this->linkedin_url,
        ];

        $links = [];

        foreach ($networks as $label => $url) {
            if (filled($url)) {
                $links[] = ['label' => $label, 'url' => $url];
            }
        }

        return $links;
    }

    /**
     * @return list<string>
     */
    public function addressLines(): array
    {
        return array_values(array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->address_region,
        ], fn (?string $line) => filled($line)));
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiResponse(): array
    {
        return [
            'org_name' => $this->org_name,
            'contact' => [
                'heading' => $this->contact_heading,
                'address' => $this->addressLines(),
                'email' => $this->email,
                'phone' => $this->phone,
                'button_label' => $this->contact_button_label,
            ],
            'social_links' => $this->socialLinks(),
            'newsletter' => [
                'heading' => $this->newsletter_heading,
                'placeholder' => $this->newsletter_placeholder,
                'button_label' => $this->newsletter_button_label,
                'consent' => $this->newsletter_consent,
            ],
            'footer_note' => $this->footer_note,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
