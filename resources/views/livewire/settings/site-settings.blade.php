<div>
<x-page-header title="Site settings"
               :breadcrumbs="['Site' => null, 'Settings' => null]" />

<form wire:submit="save" class="max-w-3xl space-y-8 rounded-2xl border border-dashed border-yellow-300 bg-white p-6 shadow-sm sm:p-8">
    <section class="space-y-5">
        <h2 class="text-xs font-medium uppercase tracking-wider text-gray-400">Organisation</h2>

        <div>
            <label for="org_name" class="mb-1.5 block text-sm font-medium text-gray-700">Institute name</label>
            <input id="org_name" type="text" wire:model="orgName" required
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('orgName') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
    </section>

    <section class="space-y-5 border-t border-dashed border-yellow-300 pt-6">
        <h2 class="text-xs font-medium uppercase tracking-wider text-gray-400">Contact block</h2>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="contact_heading" class="mb-1.5 block text-sm font-medium text-gray-700">Heading</label>
                <input id="contact_heading" type="text" wire:model="contactHeading"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('contactHeading') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="contact_button_label" class="mb-1.5 block text-sm font-medium text-gray-700">Button label</label>
                <input id="contact_button_label" type="text" wire:model="contactButtonLabel"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('contactButtonLabel') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address_line1" class="mb-1.5 block text-sm font-medium text-gray-700">Address line 1</label>
                <input id="address_line1" type="text" wire:model="addressLine1"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('addressLine1') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address_line2" class="mb-1.5 block text-sm font-medium text-gray-700">Address line 2</label>
                <input id="address_line2" type="text" wire:model="addressLine2"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('addressLine2') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address_region" class="mb-1.5 block text-sm font-medium text-gray-700">Region / postal code</label>
                <input id="address_region" type="text" wire:model="addressRegion"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('addressRegion') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" wire:model="email"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('email') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-700">Phone</label>
                <input id="phone" type="text" wire:model="phone"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('phone') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
    </section>

    <section class="space-y-5 border-t border-dashed border-yellow-300 pt-6">
        <h2 class="text-xs font-medium uppercase tracking-wider text-gray-400">Social links</h2>
        <p class="-mt-2 text-xs text-gray-400">Leave a field empty to hide that link on the website.</p>

        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label for="facebook_url" class="mb-1.5 block text-sm font-medium text-gray-700">Facebook</label>
                <input id="facebook_url" type="url" wire:model="facebookUrl" placeholder="https://facebook.com/…"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('facebookUrl') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="instagram_url" class="mb-1.5 block text-sm font-medium text-gray-700">Instagram</label>
                <input id="instagram_url" type="url" wire:model="instagramUrl" placeholder="https://instagram.com/…"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('instagramUrl') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="linkedin_url" class="mb-1.5 block text-sm font-medium text-gray-700">LinkedIn</label>
                <input id="linkedin_url" type="url" wire:model="linkedinUrl" placeholder="https://linkedin.com/company/…"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('linkedinUrl') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
    </section>

    <section class="space-y-5 border-t border-dashed border-yellow-300 pt-6">
        <h2 class="text-xs font-medium uppercase tracking-wider text-gray-400">Newsletter block</h2>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="newsletter_heading" class="mb-1.5 block text-sm font-medium text-gray-700">Heading</label>
                <input id="newsletter_heading" type="text" wire:model="newsletterHeading"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('newsletterHeading') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="newsletter_placeholder" class="mb-1.5 block text-sm font-medium text-gray-700">Input placeholder</label>
                <input id="newsletter_placeholder" type="text" wire:model="newsletterPlaceholder"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('newsletterPlaceholder') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="newsletter_button_label" class="mb-1.5 block text-sm font-medium text-gray-700">Button label</label>
                <input id="newsletter_button_label" type="text" wire:model="newsletterButtonLabel"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('newsletterButtonLabel') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="newsletter_consent" class="mb-1.5 block text-sm font-medium text-gray-700">Consent line</label>
                <textarea id="newsletter_consent" wire:model="newsletterConsent" rows="2"
                          class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm leading-relaxed text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500"></textarea>
                @error('newsletterConsent') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
    </section>

    <section class="space-y-5 border-t border-dashed border-yellow-300 pt-6">
        <h2 class="text-xs font-medium uppercase tracking-wider text-gray-400">Footer</h2>

        <div>
            <label for="footer_note" class="mb-1.5 block text-sm font-medium text-gray-700">Copyright note</label>
            <input id="footer_note" type="text" wire:model="footerNote"
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('footerNote') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
    </section>

    <div class="flex items-center justify-between border-t border-dashed border-yellow-300 pt-5">
        <a href="/api/site" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-navy-800">
            <x-icon name="code-bracket" class="text-[16px]" /> /api/site
        </a>
        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex items-center gap-2 rounded-lg bg-gold-500 px-5 py-2 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-600 hover:text-white disabled:cursor-wait disabled:opacity-70">
            <x-icon name="progress_activity" wire:loading wire:target="save" style="display: none" class="text-[16px]! animate-spin" />
            <span wire:loading.remove wire:target="save">Save settings</span>
            <span wire:loading wire:target="save" style="display: none">Saving…</span>
        </button>
    </div>
</form>
</div>
