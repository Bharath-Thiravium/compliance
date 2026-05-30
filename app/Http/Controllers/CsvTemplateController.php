<?php

namespace App\Http\Controllers;

use App\Services\Compliance\CsvTemplateService;

class CsvTemplateController extends Controller
{
    public function download(string $type, CsvTemplateService $service)
    {
        $response = $service->downloadResponse($type);

        if (! $response) {
            abort(404, "Unknown CSV template type: {$type}");
        }

        return $response;
    }

    public function index(CsvTemplateService $service)
    {
        return response()->json([
            'templates' => collect($service->allMetadata())->map(fn($m, $type) => [
                'type'     => $type,
                'filename' => $m['filename'],
                'version'  => $m['version'],
                'forms'    => $m['forms'],
                'required' => $m['required'],
                'url'      => route('csv.template', $type),
            ])->values(),
        ]);
    }
}
