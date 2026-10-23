<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Certificate;
use App\Models\Client;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.about', [
            'brands' => Brand::query()->published()->orderByDesc('is_featured')->ordered()->get(),
            'clients' => Client::query()->published()->ordered()->get(),
            'certificates' => Certificate::query()->published()->ordered()->get(),
        ]);
    }
}
