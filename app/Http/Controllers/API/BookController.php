<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helper\ValidationHelper;
use App\Models\Book;
use App\Models\BookContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    // Book Functions
    public function store(Request $request): JsonResponse
    {
        $validationError = ValidationHelper::validate($request, [
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required'
        ]);

        if ($validationError) return $validationError;

        $book = Book::create([
            'title' => $request->title,
            'description' => $request->description,
            'account_id' => Auth::id(),
            'status' => $request->status
        ]);

        return response()->json(['message' => 'Book has been successfully created', 'data' => $book], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        if ($book->account_id !== Auth::id()) {
            return response()->json(['message' => 'Access denied: Insufficient permissions'], 403);
        }

        $validationError = ValidationHelper::validate($request, [
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validationError) return $validationError;

        $book->update($request->all());

        return response()->json(['message' => 'Book has been successfully updated', 'data' => $book]);
    }

    public function destroy($id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $book->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Book has been successfully marked as deleted']);
    }

    public function show($id): JsonResponse
    {
        $book = Book::with('contents')
                ->with('account')
                ->findOrFail($id);
        return response()->json(['data' => $book]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Book::with('account')
            ->withCount('contents')
            ->whereNull('deleted_at'); // Exclude soft-deleted records

        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        $books = $query->orderBy('updated_at', 'desc')->paginate($request->get('per_page', 10));

        return response()->json(['data' => $books]);
    }

    // Content Functions
    public function storeContent(Request $request, $bookId): JsonResponse
    {
        $book = Book::findOrFail($bookId);

        if ($book->account_id !== Auth::id()) {
            return response()->json(['message' => 'Access denied: Insufficient permissions'], 403);
        }

        $validationError = ValidationHelper::validate($request, [
            'contents' => 'required|array',
            'contents.*.content' => 'required|string',
            'contents.*.title' => 'nullable|string',
            'contents.*.page_number' => 'required|string'
        ]);

        if ($validationError) return $validationError;

        $contents = collect($request->contents)->map(function($content) use ($bookId) {
            return BookContent::create([
                'book_id' => $bookId,
                'content' => $content['content'],
                'title' => $content['title'] ?? null,
                'page_number' => $content['page_number']
            ]);
        });

        return response()->json([
            'message' => 'Book contents have been successfully created',
            'data' => $contents
        ], 201);
    }

    public function updateContent(Request $request): JsonResponse
    {
        $validationError = ValidationHelper::validate($request, [
            'contents' => 'required|array',
            'contents.*.id' => 'required|exists:book_contents,id',
            'contents.*.content' => 'required|string',
            'contents.*.title' => 'nullable|string',
            'contents.*.page_number' => 'required|string'
        ]);

        if ($validationError) return $validationError;

        $updatedContents = collect($request->contents)->map(function($contentData) {
            $content = BookContent::findOrFail($contentData['id']);

            if ($content->book->account_id !== Auth::id()) {
                return ['error' => 'Unauthorized', 'content_id' => $contentData['id']];
            }

            $content->update([
                'content' => $contentData['content'],
                'title' => $contentData['title'] ?? null,
                'page_number' => $contentData['page_number']
            ]);
            return $content;
        });

        $errors = $updatedContents->where('error', 'Unauthorized');
        if ($errors->isNotEmpty()) {
            return response()->json([
                'message' => 'Operation partially completed: Some contents could not be updated due to insufficient permissions',
                'errors' => $errors,
                'updated' => $updatedContents->whereNotIn('error', ['Unauthorized'])
            ], 403);
        }

        return response()->json([
            'message' => 'Book contents have been successfully updated',
            'data' => $updatedContents
        ]);
    }

    public function destroyContent($contentId): JsonResponse
    {
        $content = BookContent::findOrFail($contentId);
        $content->delete();

        return response()->json(['message' => 'Book content has been successfully deleted']);
    }
}
