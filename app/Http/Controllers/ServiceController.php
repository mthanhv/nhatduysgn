<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (! config('site.services_page')) {
            return redirect()->to(lroute('home'));
        }

        return view('pages.services', [
            'services' => Service::query()->published()->ordered()->get(),
            'projects' => Project::query()->published()->ordered()->limit(3)->get(),
        ]);
    }
}
