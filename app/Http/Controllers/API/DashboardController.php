<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Book;
use App\Models\Minigame;
use App\Models\MinigameHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminDashboard(Request $request)
    {
        // Optional date range filter
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get counts of each user type
        $userCounts = $this->getUserTypeCounts($startDate, $endDate);

        // Get book counts (with optional account filter)
        $accountId = $request->input('account_id');
        $bookCounts = $this->getBookCounts($accountId, $startDate, $endDate);

        // Get minigame counts (with optional account filter)
        $minigameCounts = $this->getMinigameCounts($accountId, $startDate, $endDate);

        // Get average percentage score from minigame histories (optional teacher filter)
        $teacherId = $request->input('teacher_id');
        $averageScore = $this->getAverageMinigameScore($teacherId, $startDate, $endDate);

        return response()->json([
            'user_counts' => $userCounts,
            'book_counts' => $bookCounts,
            'minigame_counts' => $minigameCounts,
            'average_minigame_score' => $averageScore,
        ]);
    }

    /**
     * Get counts of each user type (all, active, inactive)
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function getUserTypeCounts($startDate = null, $endDate = null)
    {
        $query = Account::query();

        // Apply date filter if provided
        if ($startDate && $endDate) {
            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }

        $totalUsers = $query->count();
        $activeUsers = $query->where('status', 'active')->count();
        $inactiveUsers = $query->where('status', 'inactive')->count();

        $adminCount = $query->where('user_role', 'admin')->count();
        $teacherCount = $query->where('user_role', 'teacher')->count();
        $studentCount = $query->where('user_role', 'student')->count();

        $activeAdminCount = $query->where('user_role', 'admin')->where('status', 'active')->count();
        $activeTeacherCount = $query->where('user_role', 'teacher')->where('status', 'active')->count();
        $activeStudentCount = $query->where('user_role', 'student')->where('status', 'active')->count();

        $inactiveAdminCount = $query->where('user_role', 'admin')->where('status', 'inactive')->count();
        $inactiveTeacherCount = $query->where('user_role', 'teacher')->where('status', 'inactive')->count();
        $inactiveStudentCount = $query->where('user_role', 'student')->where('status', 'inactive')->count();

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $inactiveUsers,
            'by_role' => [
                'admin' => [
                    'total' => $adminCount,
                    'active' => $activeAdminCount,
                    'inactive' => $inactiveAdminCount,
                ],
                'teacher' => [
                    'total' => $teacherCount,
                    'active' => $activeTeacherCount,
                    'inactive' => $inactiveTeacherCount,
                ],
                'student' => [
                    'total' => $studentCount,
                    'active' => $activeStudentCount,
                    'inactive' => $inactiveStudentCount,
                ],
            ],
        ];
    }

    /**
     * Get book counts (all, active, inactive)
     *
     * @param int|null $accountId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function getBookCounts($accountId = null, $startDate = null, $endDate = null)
    {
        $query = Book::query();

        // Filter by account if provided
        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        // Apply date filter if provided
        if ($startDate && $endDate) {
            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }

        // Exclude soft deleted books
        $query->whereNull('deleted_at');

        $totalBooks = $query->count();
        $activeBooks = $query->where('status', 'active')->count();
        $inactiveBooks = $query->where('status', 'inactive')->count();

        return [
            'total' => $totalBooks,
            'active' => $activeBooks,
            'inactive' => $inactiveBooks,
        ];
    }

    /**
     * Get minigame counts (all, completed, upcoming)
     *
     * @param int|null $accountId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function getMinigameCounts($accountId = null, $startDate = null, $endDate = null)
    {
        $query = Minigame::query();
        $now = Carbon::now();

        // Filter by account if provided
        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        // Apply date filter if provided
        if ($startDate && $endDate) {
            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }

        // Exclude soft deleted minigames
        $query->whereNull('deleted_at');

        $totalMinigames = $query->count();
        $completedMinigames = (clone $query)->where('starts_at', '<', $now)->count();
        $upcomingMinigames = (clone $query)->where('starts_at', '>=', $now)->count();

        return [
            'total' => $totalMinigames,
            'completed' => $completedMinigames,
            'upcoming' => $upcomingMinigames,
        ];
    }

    /**
     * Get average percentage score from minigame histories
     *
     * @param int|null $teacherId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float|null
     */
    private function getAverageMinigameScore($teacherId = null, $startDate = null, $endDate = null)
    {
        $query = MinigameHistory::query()
            ->join('accounts', 'minigame_histories.account_id', '=', 'accounts.id');

        // Filter by teacher if provided
        if ($teacherId) {
            $query->where('accounts.teacher_id', $teacherId);
        }

        // Apply date filter if provided
        if ($startDate && $endDate) {
            $query->whereBetween('minigame_histories.updated_at', [$startDate, $endDate]);
        }

        // Calculate average score percentage
        $result = $query->selectRaw('
            AVG((minigame_histories.correct_count * 100.0) /
                NULLIF((minigame_histories.correct_count + minigame_histories.incorrect_count), 0)) as average_percentage
        ')->first();

        return $result ? round($result->average_percentage, 2) : null;
    }
}
