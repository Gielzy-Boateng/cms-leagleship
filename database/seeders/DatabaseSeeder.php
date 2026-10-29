<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Models\Event;
use App\Models\Manifesto;
use App\Models\Page;
use App\Models\Program;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedAdmin();

        Event::query()->firstOrCreate(
            ['slug' => 'community-launch-meetup'],
            [
                'title' => 'Community Launch Meetup',
                'description' => 'Join us to celebrate the launch of our new community platform.',
                'location' => 'Nairobi Convention Centre',
                'starts_at' => now()->addWeeks(2)->setTime(18, 0),
                'ends_at' => now()->addWeeks(2)->setTime(21, 0),
                'status' => PublicationStatus::Published,
            ],
        );

        Event::query()->firstOrCreate(
            ['slug' => 'volunteer-training-day'],
            [
                'title' => 'Volunteer Training Day',
                'description' => 'A hands-on training session for new volunteers.',
                'location' => 'Head Office',
                'starts_at' => now()->addMonth()->setTime(9, 0),
                'ends_at' => now()->addMonth()->setTime(17, 0),
                'status' => PublicationStatus::Draft,
            ],
        );

        Program::query()->firstOrCreate(
            ['slug' => 'youth-mentorship-program'],
            [
                'title' => 'Youth Mentorship Program',
                'description' => 'One-year mentorship pairing young people with experienced professionals.',
                'tags' => ['Courage', 'Vision', 'Stewardship', 'Excellence', 'Innovation', 'Identity'],
                'link' => 'https://example.com/mentorship',
                'status' => PublicationStatus::Published,
            ],
        );

        Program::query()->firstOrCreate(
            ['slug' => 'digital-skills-bootcamp'],
            [
                'title' => 'Digital Skills Bootcamp',
                'description' => 'Free six-week bootcamp covering basic digital literacy.',
                'link' => null,
                'status' => PublicationStatus::Draft,
            ],
        );

        Manifesto::query()->firstOrCreate(
            ['slug' => 'we-lead-by-serving'],
            [
                'title' => 'We lead by serving',
                'excerpt' => 'Leadership is measured by what it gives away, not what it accumulates.',
                'body' => '<p>Every programme we run starts from one question: who is being left out?</p><p>We would rather walk beside a community than in front of it. That means <strong>listening before planning</strong>, sharing credit publicly, and handing over real decision-making power to the people closest to the problem.</p>',
                'sort_order' => 1,
                'status' => PublicationStatus::Published,
            ],
        );

        Manifesto::query()->firstOrCreate(
            ['slug' => 'promise-over-performance'],
            [
                'title' => 'Promise over performance',
                'excerpt' => 'We publish our commitments and hold ourselves to them in public.',
                'body' => '<p>Announcements are easy. Delivery is the part that counts.</p><p>When we commit to something we write it down, attach a date, and report back on it — including when we fall short. A track record is only useful if it can be checked.</p>',
                'sort_order' => 2,
                'status' => PublicationStatus::Published,
            ],
        );

        Manifesto::query()->firstOrCreate(
            ['slug' => 'grow-the-next-cohort'],
            [
                'title' => 'Grow the next cohort',
                'excerpt' => 'A generation of leaders is only real if it keeps replacing the one before it.',
                'body' => '<p>We judge our success by how quickly we are no longer needed in a given role.</p><p>That is why mentorship sits at the centre of everything here: every beneficiary today is expected to be a mentor within two years.</p>',
                'sort_order' => 3,
                'status' => PublicationStatus::Draft,
            ],
        );

        Page::query()->firstOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'status' => PublicationStatus::Draft,
                'content' => [
                    ['type' => 'heading', 'text' => 'Leadership that gives the way back', 'level' => 2],
                    ['type' => 'paragraph', 'text' => 'Diba Leagleship Institute trains ordinary people to lead where they already live — in their street, their school, their workplace.'],
                    ['type' => 'stat', 'value' => '1,200', 'label' => 'Leaders mentored'],
                    ['type' => 'stat', 'value' => '38', 'label' => 'Community cohorts'],
                    ['type' => 'rich_text', 'html' => '<p>See who is <a href="/events">coming up next</a>, or read the <a href="/manifestos">commitments we publish</a>.</p>'],
                    ['type' => 'quote', 'text' => 'We would rather walk beside a community than in front of it.', 'attribution' => 'Our founding promise'],
                ],
            ],
        );

        Page::query()->firstOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'About',
                'status' => PublicationStatus::Draft,
                'content' => [
                    ['type' => 'heading', 'text' => 'Who we are', 'level' => 2],
                    ['type' => 'paragraph', 'text' => 'We started with one cohort and one question: who is being left out of the conversations that decide their lives?'],
                    ['type' => 'paragraph', 'text' => 'Today the institute runs mentorship circles, skills bootcamps and volunteer training across Accra. Every programme is co-designed with the community it serves, and every graduate is expected to mentor within two years.'],
                    ['type' => 'heading', 'text' => 'What we do', 'level' => 3],
                    ['type' => 'rich_text', 'html' => '<ul><li>Mentorship circles pairing young people with working professionals</li><li>Digital skills bootcamps, free to attend</li><li>Volunteer training for community organisations</li></ul>'],
                ],
            ],
        );

        Page::query()->firstOrCreate(
            ['slug' => 'philosophy'],
            [
                'title' => 'Philosophy',
                'status' => PublicationStatus::Draft,
                'content' => [
                    ['type' => 'heading', 'text' => 'Our leadership philosophy', 'level' => 2],
                    ['type' => 'paragraph', 'text' => 'Leadership is a practice, not a title. It is built by people who are willing to be accountable in public, and who measure success by how quickly they are no longer needed in a given role.'],
                    ['type' => 'quote', 'text' => 'A generation of leaders is only real if it keeps replacing the one before it.', 'attribution' => 'Grow the next cohort'],
                    ['type' => 'heading', 'text' => 'How we hold ourselves to it', 'level' => 3],
                    ['type' => 'rich_text', 'html' => '<ol><li>We write every commitment down and attach a date to it.</li><li>We report back in public, including when we fall short.</li><li>We hand decision-making power to the people closest to the problem.</li></ol>'],
                ],
            ],
        );

        Page::query()->firstOrCreate(
            ['slug' => 'founder'],
            [
                'title' => 'Founder',
                'status' => PublicationStatus::Draft,
                'content' => [
                    ['type' => 'heading', 'text' => 'The founder', 'level' => 2],
                    ['type' => 'paragraph', 'text' => 'After a decade spent coordinating youth programmes that kept the same faces in the same rooms, our founder started the institute on a simple bet: the people closest to a problem already have the judgment to solve it, and only need the platform.'],
                    ['type' => 'paragraph', 'text' => 'They still open every cohort, and still sit in the back of the room for the closing review.'],
                    ['type' => 'quote', 'text' => 'Announcements are easy. Delivery is the part that counts.', 'attribution' => 'Promise over performance'],
                ],
            ],
        );

        if (SiteSetting::query()->doesntExist()) {
            SiteSetting::create([
                'org_name' => 'Diba Leagleship Institute',
                'contact_heading' => 'Contact Us',
                'address_line1' => 'No. 6 Nii Kotei Baale Ave,',
                'address_line2' => 'East Legon, Accra,',
                'address_region' => 'Greater Accra Region (GA-414-1928)',
                'email' => 'hello@dibaleagleship.org',
                'phone' => '(233) 24 5678 567',
                'contact_button_label' => 'Get in Touch',
                'newsletter_heading' => 'Sign up for Updates',
                'newsletter_placeholder' => 'Enter your Email',
                'newsletter_button_label' => 'Sign Up Now',
                'newsletter_consent' => 'By subscribing, you provide consent to receive updates from Diba Leagleship.',
                'footer_note' => '© '.now()->year.' Diba Leagleship Institute. All rights reserved.',
            ]);
        }
    }

    /**
     * The CMS sign-in, driven by the environment so credentials stay out of the repository.
     */
    protected function seedAdmin(): void
    {
        $password = config('admin.password');

        if (blank($password)) {
            if (! app()->isLocal()) {
                throw new RuntimeException('Set ADMIN_PASSWORD before seeding the CMS administrator.');
            }

            $password = 'password';
        }

        User::query()->updateOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => config('admin.name'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );
    }
}
