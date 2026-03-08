<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = ApiLog::query()
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('api-logs/Index', [
            'logs' => $logs,
        ]);
    }
}
