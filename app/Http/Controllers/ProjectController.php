<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $industry = $request->query('nganh');
        $projects = Project::query()->published()->ordered()->get();

        return view('projects.index', [
            'industries' => $projects->pluck('industry')->filter()->unique()->values(),
            'currentIndustry' => $industry,
            'projects' => $industry ? $projects->where('industry', $industry)->values() : $projects,
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->is_published, 404);

        return view('projects.show', [
            'project' => $project,
            'others' => Project::query()->published()->whereKeyNot($project->id)->ordered()->limit(3)->get(),
        ]);
    }
}
