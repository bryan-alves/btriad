<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Support\CurrentTenant;
use App\Support\SiteScheduleFromClasses;

class HomeController extends Controller
{
    public function index()
    {
        $tenant = CurrentTenant::get()->load([
            'site',
            'reviews' => fn ($query) => $query
                ->where('active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id'),
        ]);

        $classes = SchoolClass::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $schedule = SiteScheduleFromClasses::build($classes);

        return view('tenants.index', compact('tenant', 'schedule'));
    }
}
