<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\TenantSite;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'Equipe B-Triad Jiu-Jitsu',
                'slug' => 'btriad',
                'domains' => ['localhost', '127.0.0.1', 'btriadjiujitsu.com.br'],
                'site' => [
                    'academy_name' => 'Equipe B-Triad Jiu-Jitsu',
                    'page_title' => 'B-Triad Jiu-Jitsu | Aulas de jiu-jitsu para crianças e adultos',
                    'hero_title' => 'Estamos no aquecimento!',
                    'hero_subtitle' => 'Em breve, o site oficial da Equipe B-Triad Jiu-Jitsu. Fique ligado para novidades sobre nossas aulas, horários e eventos!',
                    'primary_color' => '#c41e3a',
                    'header_color' => '#1b1b18',
                    'background_color' => '#3d3d3d',
                    'trial_button_color' => '#c41e3a',
                    'portal_button_color' => '#2563eb',
                    'app_primary_color' => '#111827',
                    'app_header_color' => '#1b1b18',
                    'app_background_color' => '#f8fafc',
                    'app_login_background_color' => '#333333',
                    'logo_path' => 'logo.png',
                    'carousel_images' => [],
                    'schedule' => [
                        ['day' => 'Segunda-feira', 'kids_time' => '18h - 19h', 'adults_time' => '19h - 20h'],
                        ['day' => 'Quarta-feira', 'kids_time' => '18h - 19h', 'adults_time' => '19h - 20h'],
                        ['day' => 'Sexta-feira', 'kids_time' => '18h - 19h', 'adults_time' => '19h - 20h'],
                    ],
                ],
            ],
            [
                'name' => 'AP Jiu-Jitsu',
                'slug' => 'apjiujitsu',
                'domains' => ['apjiujitsu.com.br'],
                'site' => [
                    'academy_name' => 'AP Jiu-Jitsu',
                    'page_title' => 'AP Jiu-Jitsu',
                    'hero_title' => 'AP Jiu-Jitsu',
                    'hero_subtitle' => 'Aulas de jiu-jitsu para crianças e adultos.',
                    'primary_color' => '#c97716',
                    'header_color' => '#111827',
                    'background_color' => '#111827',
                    'trial_button_color' => '#c97716',
                    'portal_button_color' => '#2563eb',
                    'app_primary_color' => '#111827',
                    'app_header_color' => '#111827',
                    'app_background_color' => '#f8fafc',
                    'app_login_background_color' => '#1f2937',
                    'logo_path' => 'apjiujitsu-logo.png',
                    'carousel_images' => [],
                    'schedule' => [],
                ],
            ],
        ];

        $domains = collect($tenants)->flatMap(fn (array $tenant) => $tenant['domains']);

        Domain::whereNotIn('domain', $domains)->delete();

        foreach ($tenants as $data) {
            $tenant = Tenant::updateOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name']],
            );

            foreach ($data['domains'] as $domain) {
                Domain::updateOrCreate(
                    ['domain' => $domain],
                    ['tenant_id' => $tenant->id],
                );
            }

            TenantSite::updateOrCreate(
                ['tenant_id' => $tenant->id],
                $data['site'],
            );
        }

        $apTenant = Tenant::where('slug', 'apjiujitsu')->firstOrFail();

        User::withoutGlobalScope('tenant')->updateOrCreate(
            ['username' => 'petterin'],
            [
                'tenant_id' => $apTenant->id,
                'name' => 'Andre Petterin',
                'role' => 'admin',
                'password' => Hash::make('petterin@32145'),
                'active' => true,
            ],
        );
    }
}
