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
                'plan' => Tenant::PLAN_DIGITAL,
                'is_platform' => false,
                'domains' => ['localhost', '127.0.0.1', 'btriadjiujitsu.com.br'],
                'primary_domain' => 'btriadjiujitsu.com.br',
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
                'name' => 'Tatameiro',
                'slug' => 'tatameiro',
                'plan' => Tenant::PLAN_DIGITAL,
                'is_platform' => true,
                'domains' => ['tatameiro.com.br', 'tatameiro.test'],
                'primary_domain' => 'tatameiro.com.br',
                'site' => [
                    'academy_name' => 'Tatameiro',
                    'page_title' => 'Tatameiro | Gestão completa para academias de Jiu-Jitsu',
                    'hero_title' => 'Tatameiro',
                    'hero_subtitle' => 'Gestão completa para academias de Jiu-Jitsu',
                    'primary_color' => '#e52521',
                    'header_color' => '#0a0a0a',
                    'background_color' => '#0a0a0a',
                    'trial_button_color' => '#e52521',
                    'portal_button_color' => '#ffffff',
                    'app_primary_color' => '#111827',
                    'app_header_color' => '#0a0a0a',
                    'app_background_color' => '#f8fafc',
                    'app_login_background_color' => '#0a0a0a',
                    'logo_path' => 'tatameiro-logo.png',
                    'carousel_images' => [],
                    'schedule' => [],
                ],
            ],
            [
                'name' => 'AP Jiu-Jitsu',
                'slug' => 'apjiujitsu',
                'plan' => Tenant::PLAN_DIGITAL,
                'is_platform' => false,
                'domains' => ['apjiujitsu.com.br'],
                'primary_domain' => 'apjiujitsu.com.br',
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
                [
                    'name' => $data['name'],
                    'plan' => $data['plan'],
                    'is_platform' => $data['is_platform'],
                    'primary_domain' => $data['primary_domain'] ?? null,
                ],
            );

            foreach ($data['domains'] as $domain) {
                Domain::updateOrCreate(
                    ['domain' => $domain],
                    ['tenant_id' => $tenant->id],
                );
            }

            TenantSite::firstOrCreate(
                ['tenant_id' => $tenant->id],
                array_merge($data['site'], [
                    'active' => $tenant->hasPublicSite(),
                ]),
            );
        }

        $tatameiroTenant = Tenant::where('slug', 'tatameiro')->firstOrFail();

        User::withoutGlobalScope('tenant')->updateOrCreate(
            ['username' => 'tatameiro'],
            [
                'tenant_id' => $tatameiroTenant->id,
                'name' => 'Tatameiro Admin',
                'role' => 'admin',
                'password' => Hash::make('tatameiro@32145'),
                'active' => true,
            ],
        );

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
