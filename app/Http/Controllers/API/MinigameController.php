<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helper\ValidationHelper;
use App\Models\Minigame;
use App\Models\MinigameContent;
use App\Models\MinigameHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MinigameController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'default_timer' => 'required|numeric|min:0',
            'default_points' => 'required|integer|min:0',
            'starts_at' => 'required|date',
        ];

        $validationError = ValidationHelper::validate($request, $rules);
        if ($validationError) return $validationError;

        try {
            $minigame = Minigame::create([
                'account_id' => auth()->id(),
                'title' => $request->title,
                'default_timer' => $request->default_timer,
                'default_points' => $request->default_points,
                'starts_at' => $request->starts_at,
            ]);

            return response()->json(['message' => 'Minigame created successfully', 'data' => $minigame], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating minigame', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'default_timer' => 'required|numeric|min:0',
            'default_points' => 'required|integer|min:0',
            'starts_at' => 'required|date',
        ];

        $validationError = ValidationHelper::validate($request, $rules);
        if ($validationError) return $validationError;

        try {
            $minigame = Minigame::where('id', $id)
                               ->where('account_id', auth()->id())
                               ->firstOrFail();

            $minigame->update($request->all());
            return response()->json(['message' => 'Minigame updated successfully', 'data' => $minigame]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating minigame', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $minigame = Minigame::where('id', $id)
                               ->where('account_id', auth()->id())
                               ->firstOrFail();

            $minigame->delete();
            return response()->json(['message' => 'Minigame deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting minigame', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $rules = [
            'status' => 'nullable|in:upcoming,ongoing,completed',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
            'account_id' => 'nullable|exists:accounts,id',
        ];

        $validationError = ValidationHelper::validate($request, $rules);
        if ($validationError) return $validationError;

        try {
            $query = Minigame::with('account:id,first_name,last_name')
                             ->withCount('contents')
                             ->withSum('contents', 'points');

            if ($request->has('status')) {
                $now = now();
                switch ($request->status) {
                    case 'upcoming':
                        $query->where('starts_at', '>', $now);
                        break;
                    case 'ongoing':
                        $query->where('starts_at', '<=', $now);
                        break;
                    case 'completed':
                        $query->where('starts_at', '<', $now);
                        break;
                }
            }

            if ($request->has('search')) {
                $search = strtolower($request->search);
                $query->where('title', 'LIKE', "%{$search}%");
            }

            if ($request->has('account_id')) {
                $query->where('account_id', $request->account_id);
            }

            $perPage = $request->input('per_page', 30);
            $minigames = $query->orderBy('starts_at', 'desc')->paginate($perPage);

            return response()->json($minigames);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving minigames', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $minigame = Minigame::with(['contents', 'account:id,first_name,last_name'])
                               ->findOrFail($id);

            return response()->json($minigame);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving minigame', 'error' => $e->getMessage()], 500);
        }
    }

    public function storeContent(Request $request, $minigameId)
    {
        $rules = [
            'contents' => 'required|array',
            'contents.*.question' => 'required|string',
            'contents.*.correct_answer' => 'required|integer|between:1,4',
            'contents.*.option_1' => 'required|string',
            'contents.*.option_2' => 'required|string',
            'contents.*.option_3' => 'required|string',
            'contents.*.option_4' => 'required|string',
            'contents.*.points' => 'required|integer|min:0',
            'contents.*.timer' => 'required|numeric|min:0',
        ];

        $validationError = ValidationHelper::validate($request, $rules);
        if ($validationError) return $validationError;

        try {
            $minigame = Minigame::where('id', $minigameId)
                               ->where('account_id', auth()->id())
                               ->firstOrFail();

            $contents = collect($request->contents)->map(function ($content) use ($minigameId) {
                return array_merge($content, [
                    'minigame_id' => $minigameId,
                    'account_id' => auth()->id(),
                ]);
            });

            MinigameContent::insert($contents->toArray());

            return response()->json(['message' => 'Minigame contents created successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating minigame contents', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateContent(Request $request)
    {
        $rules = [
            'contents' => 'required|array',
            'contents.*.id' => 'required|exists:minigame_contents,id',
            'contents.*.question' => 'required|string',
            'contents.*.correct_answer' => 'required|integer|between:1,4',
            'contents.*.option_1' => 'required|string',
            'contents.*.option_2' => 'required|string',
            'contents.*.option_3' => 'required|string',
            'contents.*.option_4' => 'required|string',
            'contents.*.points' => 'required|integer|min:0',
            'contents.*.timer' => 'required|numeric|min:0',
        ];

        $validationError = ValidationHelper::validate($request, $rules);
        if ($validationError) return $validationError;

        try {
            foreach ($request->contents as $content) {
                MinigameContent::where('id', $content['id'])
                    ->where('account_id', auth()->id())
                    ->update([
                        'question' => $content['question'],
                        'correct_answer' => $content['correct_answer'],
                        'option_1' => $content['option_1'],
                        'option_2' => $content['option_2'],
                        'option_3' => $content['option_3'],
                        'option_4' => $content['option_4'],
                        'points' => $content['points'],
                        'timer' => $content['timer'],
                    ]);
            }

            return response()->json(['message' => 'Minigame contents updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating minigame contents', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyContent($contentId)
    {
        try {
            $content = MinigameContent::where('id', $contentId)
                                    ->where('account_id', auth()->id())
                                    ->firstOrFail();

            $content->delete();
            return response()->json(['message' => 'Minigame content deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting minigame content', 'error' => $e->getMessage()], 500);
        }
    }

    public function getContents($minigameId)
    {
        try {
            $contents = MinigameContent::where('minigame_id', $minigameId)
                                     ->get();

            return response()->json($contents);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving minigame contents', 'error' => $e->getMessage()], 500);
        }
    }

    public function storeHistory(Request $request)
    {
        $rules = [
            'minigame_id' => 'required|exists:minigames,id',
            'total_score' => 'required|integer|min:0',
            'correct_count' => 'required|integer|min:0',
            'incorrect_count' => 'required|integer|min:0',
        ];

        $validationError = ValidationHelper::validate($request, $rules);
        if ($validationError) return $validationError;

        try {
            $history = MinigameHistory::create([
                'minigame_id' => $request->minigame_id,
                'account_id' => auth()->id(),
                'total_score' => $request->total_score,
                'correct_count' => $request->correct_count,
                'incorrect_count' => $request->incorrect_count,
            ]);

            return response()->json(['message' => 'History created successfully', 'data' => $history], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating history', 'error' => $e->getMessage()], 500);
        }
    }

    public function getHistory($minigameId, $studentId = null)
    {
        try {
            $query = MinigameHistory::with('account')
                                  ->where('minigame_id', $minigameId);

            if ($studentId) {
                $query->where('account_id', $studentId);
            }

            $history = $query->get();

            return response()->json($history);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving history', 'error' => $e->getMessage()], 500);
        }
    }
}
