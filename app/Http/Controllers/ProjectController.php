<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ProjectController extends Controller
{
    /** Prompt (ChatGPT GPT-5 mini)
     * "what comes into ProjectController.php to get all points and measurements with their measurement values?"
     */
    public function pointsWithMeasurements(Project $project)
    {
        // get all points including their measurements
        $points = $project->points()->with(['measurementValues' => function ($query) {
            $query->orderBy('measurement_id'); // chronologically?
        }])->get();

        // return response()->json($points);
        
        return response()->json($points->map(function ($point) {
            return [
                'id' => $point->id,
                'name' => $point->name,
                'projection_id' => $point->projection_id,
                'measurement_values' => $point->measurementValues->map(fn($m) => [
                    'x' => $m->x,
                    'y' => $m->y,
                    'z' => $m->z,
                    'lat' => $m->lat,
                    'lon' => $m->lon,
                    'measurement_id' => $m->measurement_id,
                    'date' => $m->measurement->date ?? null
                ])
            ];
        }));
    }
}
