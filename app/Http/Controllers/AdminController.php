<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveRegistrationRequest;
use App\Http\Requests\ImportMeasurementsRequest;
use App\Models\Measurement;
use App\Models\Project;
use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\MeasurementImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function __construct(
        protected MeasurementImportService $measurementImportService
    ) {}

    // GET /admin
    public function index(): Response
    {
        return Inertia::render('Admin', [
            'registrationRequests' => RegistrationRequest::query()
                ->orderBy('created_at', 'asc')
                ->get(['id', 'name', 'email', 'note', 'created_at as createdAt']),
            'roles' => Role::query()
                ->orderBy('name', 'asc')
                ->get(['id', 'name']),
        ]);
    }

    // POST /admin/registration-requests/{registrationRequest}
    public function approve(ApproveRegistrationRequest $request, RegistrationRequest $registrationRequest): RedirectResponse
    {
        User::create([
            'name' => $registrationRequest->name,
            'email' => $registrationRequest->email,
            'password' => $registrationRequest->password,
            'role_id' => $request->validated('role_id'),
        ]);

        $registrationRequest->delete();

        return redirect()->route('admin');
    }

    // DELETE /admin/registration-requests/{registrationRequest}
    public function reject(RegistrationRequest $registrationRequest): RedirectResponse
    {
        $registrationRequest->delete();

        return redirect()->route('admin');
    }

    // GET /projects/{project}/measurements/import
    public function createMeasurementImport(Project $project): Response
    {
        $previousMeasurementName = $project->measurements()
            ->latest('measurement_datetime')
            ->value('name');

        return Inertia::render('admin/ImportMeasurements', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'previousMeasurementName' => $previousMeasurementName,
            ],
        ]);
    }

    // POST /projects/{project}/measurements/import
    public function storeMeasurementImport(ImportMeasurementsRequest $request, Project $project): RedirectResponse
    {
        $this->measurementImportService->import(
            $project,
            $request->validated(),
            $request->file('file'),
        );

        return redirect()->route('project', $project);
    }

    // GET /projects/{project}/measurements/export
    public function createMeasurementExport(Project $project): Response
    {
        return Inertia::render('admin/ExportMeasurements', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'measurements' => $project->measurements()
                ->orderBy('measurement_datetime')
                ->get(['id', 'name', 'measurement_datetime as datetime']),
        ]);
    }

    // GET /projects/{project}/measurements/{measurement}/export
    public function downloadMeasurementExport(Project $project, Measurement $measurement): StreamedResponse
    {
        // Does the measurement belong to the project? If not, return a 404 error. (should be handled by scoped bindings, but just in case)
        abort_unless($measurement->project_id === $project->id, 404);

        // Generate a filename based on the project and measurement names, replacing any non-alphanumeric characters with underscores.
        $filename = Str::of("{$project->name}_{$measurement->name}_{$measurement->measurement_datetime->format('Y-m-d')}.csv")
            ->replaceMatches('/[^\pL\pN._-]+/u', '_')
            ->toString();

        // Instead of generating the entire CSV in memory and then returning it, Laravel streams the CSV directly to the client.
        return response()->streamDownload(function () use ($measurement): void {
            $handle = fopen('php://output', 'wb'); // write binary
            // eager load point id and name
            // lazy -> process measurement values in chunks of roughly 500 records
            foreach ($measurement->measurementValues()->with('point:id,name')->orderBy('id')->lazy(500) as $value) {
                fputcsv($handle, [$value->point->name, $value->x, $value->y, $value->z], separator: ';', escape: '', eol: "\n");
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
