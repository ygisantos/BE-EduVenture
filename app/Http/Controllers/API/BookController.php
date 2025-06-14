<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helper\ValidationHelper;
use App\Models\Book;
use App\Models\BookContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    // Book Functions
    public function store(Request $request): JsonResponse
    {
        $validationRules = [
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required',
            'book_type' => 'required|in:manual,pdf',
            'pdf_file' => 'required_if:book_type,pdf|file|mimes:pdf'
        ];

        $validationError = ValidationHelper::validate($request, $validationRules);

        if ($validationError) return $validationError;

        $filePath = null;
        if ($request->book_type === 'pdf' && $request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $fileName = time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.pdf';
            $filePath = $file->storeAs('books', $fileName, 'public');
            $filePath = 'storage/' . $filePath;
        }

        $book = Book::create([
            'title' => $request->title,
            'description' => $request->description,
            'account_id' => Auth::id(),
            'status' => $request->status,
            'book_type' => $request->book_type,
            'file_path' => $filePath
        ]);

        return response()->json(['message' => 'Book has been successfully created', 'data' => $book], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $book = Book::findOrFail($id);

            $validationRules = [
                'title' => 'required|string',
                'description' => 'required|string',
                'status' => 'required|in:active,inactive',
                'book_type' => 'in:manual,pdf',
                'pdf_file' => 'sometimes|required_if:book_type,pdf|file|mimes:pdf'
            ];

            $validationError = ValidationHelper::validate($request, $validationRules);

            if ($validationError) return $validationError;

            $data = $request->all();

            // Handle PDF file update
            if ($request->book_type === 'pdf' && $request->hasFile('pdf_file')) {
                // Delete old file if exists
                if ($book->file_path) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $book->file_path));
                }

                $file = $request->file('pdf_file');
                $fileName = time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.pdf';
                $filePath = $file->storeAs('books', $fileName, 'public');
                $data['file_path'] = 'storage/' . $filePath;
            } else if ($request->book_type === 'manual') {
                // If switching to manual type, remove file
                if ($book->file_path) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $book->file_path));
                }
                $data['file_path'] = null;
            }

            $book->update($data);

            return response()->json(['message' => 'Book has been successfully updated', 'data' => $book]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $book->update(['deleted_at' => now()]);

        return response()->json([
            'message' => 'Book has been successfully marked as deleted',
            'book' => $book
        ]);
    }

    public function show($id): JsonResponse
    {
        $book = Book::with(['contents' => function($query) {
                    $query->orderBy('page_number', 'asc');
                }])
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
        try {
            $book = Book::findOrFail($bookId);

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
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateContent(Request $request): JsonResponse
    {
        try {
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

                $content->update([
                    'content' => $contentData['content'],
                    'title' => $contentData['title'] ?? null,
                    'page_number' => $contentData['page_number']
                ]);
                return $content;
            });

            return response()->json([
                'message' => 'Book contents have been successfully updated',
                'data' => $updatedContents
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyContent($contentId): JsonResponse
    {
        $content = BookContent::findOrFail($contentId);
        $content->delete();

        return response()->json([
            'message' => 'Book content has been successfully deleted',
            'content' => $content
        ]);
    }
}
