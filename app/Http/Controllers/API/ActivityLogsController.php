<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Helper\ValidationHelper;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogsController extends Controller
{
    public function createLog(Request $request)
    {
        $rules = [
            'description' => 'required|string|max:255',
            'module' => 'required|string|max:255',
        ];

        $validationError = ValidationHelper::validate($request, $rules);
        if ($validationError) return $validationError;

        try {
            $log = ActivityLog::create([
                'account_id' => auth()->id(),
                'description' => $request->description,
                'module' => $request->module
            ]);
            return response()->json(['message' => 'Activity log created successfully', 'data' => $log], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating activity log', 'error' => $e->getMessage()], 500);
        }
    }

    public function getLogs(Request $request)
    {
        $rules = [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];

        $validationError = ValidationHelper::validate($request, $rules);
        if ($validationError) return $validationError;

        try {
            $query = ActivityLog::query()
                ->join('accounts', 'activity_logs.account_id', '=', 'accounts.id')
                ->select('activity_logs.*',
                        DB::raw("CONCAT(accounts.first_name, ' ', accounts.last_name) as account_name"));

            // Get query parameters
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $search = $request->query('search');
            $perPage = $request->query('per_page', 15);

            if ($startDate) {
                $query->whereDate('activity_logs.created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('activity_logs.created_at', '<=', $endDate);
            }

            if ($search) {
                $searchLower = strtolower($search);
                $query->where(function($q) use ($searchLower) {
                    $q->whereRaw('LOWER(accounts.first_name) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(accounts.last_name) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(activity_logs.description) LIKE ?', ["%{$searchLower}%"]);
                });
            }

            $logs = $query->orderBy('activity_logs.created_at', 'desc')
                         ->paginate($perPage);

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving activity logs', 'error' => $e->getMessage()], 500);
        }
    }
}
