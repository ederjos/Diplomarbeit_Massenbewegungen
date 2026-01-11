<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Measurement;
use Inertia\Inertia;
use Clickbar\Magellan\Database\PostgisFunctions\ST;

class ProjectController extends Controller
{
    public function index()
    {
        /* Prompt (Gemini 3 Pro)
         * "What comes into ProjectController.php to get all projects with their last measurement date and next measurement date based on period?"
         * "Update the controller of index to correctly and simply return the requested data. no errors should appear when working with timezones +2 or +1. just return the date in the same form as you get it from measurement_datetime without timezone: 2025-06-04 00:00:00"
         * "i dont want to have to do str replacement. interval from postrgresql should just work here. no silly workarounds."
         */

        // We use raw SQL subqueries here to leverage PostgreSQL's native interval arithmetic (e.g. adding the 'period' interval to the datetime).
        // This allows us to calculate the 'next_measurement' date directly on the database part.
        $projects = Project::query()
            ->select('projects.*')
            ->addSelect([
                'last_measurement' => Measurement::select('measurement_datetime')
                    ->whereColumn('project_id', 'projects.id')
                    ->latest('measurement_datetime')
                    ->limit(1),
                'next_measurement' => Measurement::selectRaw('measurement_datetime + projects.period')
                    ->whereColumn('project_id', 'projects.id')
                    ->latest('measurement_datetime')
                    ->limit(1)
            ])
            ->get();

        return Inertia::render('Home', [
            'projects' => $projects->map(function($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'isActive' => $project->is_active,
                    'lastMeasurement' => $project->last_measurement,
                    'nextMeasurement' => $project->is_active ? $project->next_measurement : null,
                ];
            })
        ]);
    }

    public function show(Project $project)
    {
        return Inertia::render('Project', [
            'project' => $project,
            'points' => $this->points($project),
            'measurements' => $this->measurements($project)
        ]);
    }

    /** Prompt (ChatGPT GPT-5 mini)
     * "what comes into ProjectController.php to get all points and measurements with their measurement values?"
     * "Fix the issue in README.md to load measurement values chronologically"
     */
    public function points(Project $project)
    {
        // ensures measurementValues is loaded for each point and measurement for each value
        // CAN GET VERY LARGE IF MANY MEASUREMENTS EXIST -> Nice2Have: Implement Level of Detail (LOD)
        $points = $project->points()->with([
            'measurementValues' => function ($query) {
                $query->select('measurement_values.*') // select all fields from measurement_values but none from measurements (joined only for ordering)
                    // Order by datetime here (on the db directly) to have this not done in Vue later
                    ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
                    ->orderBy('measurements.measurement_datetime')
                    // with selects here and not in the model, we prevent queries for each measurement value
                    ->addSelect(ST::y(ST::transform('measurement_values.geom', 4326))->as('lat'))
                    ->addSelect(ST::x(ST::transform('measurement_values.geom', 4326))->as('lon'));
            },
            'measurementValues.measurement' // Only temporary (so long as ProjectTimeline needs the datetime)
        ])->get();
        /** "Eager loading"
         * What is "Eager Loading"?
         * Simply explained: Instead of going to the database separately for each single item to fetch its measurements (which would happen hundreds of times), you fetch everything at once in just three large batches.
         */

        return $points->map(function ($point) {
            return [
                'id' => $point->id,
                'name' => $point->name,
                'projectionId' => $point->projection_id,
                'measurementValues' => $point->measurementValues->map(function ($m) {
                    return [
                        'x' => $m->x,
                        'y' => $m->y,
                        'z' => $m->z,
                        'lat' => $m->lat,
                        'lon' => $m->lon,
                        'measurementId' => $m->measurement_id,
                        'datetime' => $m->measurement->measurement_datetime // only temporary
                    ];
                })
            ];
        });
    }

    public function measurements(Project $project)
    {
        return $project->measurements()->orderBy('measurement_datetime')->get()->map(function ($measurement) {
            return [
                'id' => $measurement->id,
                'name' => $measurement->name,
                'datetime' => $measurement->measurement_datetime
            ];
        });
    }
}
