<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.services', [
            'services' => Service::query()->published()->ordered()->get(),
            'projects' => Project::query()->published()->ordered()->limit(3)->get(),
        ]);
    }
}
