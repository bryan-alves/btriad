<?php

namespace App\Http\Controllers;

use App\Support\CurrentTenant;

class HomeController extends Controller
{
    public function index()
    {
        $tenant = CurrentTenant::get();
        $view = "tenants.{$tenant->slug}.index";

        if (! view()->exists($view)) {
            abort(404, 'Página inicial não configurada para este tenant.');
        }

        return view($view, compact('tenant'));
    }
}
