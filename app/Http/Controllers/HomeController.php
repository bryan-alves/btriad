<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SiteReview;
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
                ->where('status', SiteReview::STATUS_APPROVED)
                ->orderBy('sort_order')
                ->orderByDesc('id'),
        ]);

        $classes = SchoolClass::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $schedule = SiteScheduleFromClasses::build($classes);

        $view = view()->exists("tenants.{$tenant->slug}.index")
            ? "tenants.{$tenant->slug}.index"
            : 'tenants.index';

        return view($view, compact('tenant', 'schedule'));
    }
}
