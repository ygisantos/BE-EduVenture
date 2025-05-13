<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Minigame;
use App\Models\MinigameContent;
use App\Models\MinigameHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MinigameSeeder extends Seeder
{
    public function run(): void
    {
        // Get some teacher accounts to assign as creators
        $teacherAccounts = Account::where('user_role', 'teacher')->take(3)->get();

        // Get all student accounts (20 students)
        $studentAccounts = Account::where('user_role', 'student')
            ->take(20)
            ->get();

        // If we don't have 20 students, we'll create additional ones
        $existingCount = $studentAccounts->count();
        if ($existingCount < 20) {
            // Get the last account number to continue the sequence
            $lastAccount = Account::orderBy('id', 'desc')->first();
            $nextAccountNumber = 2000001; // Default starting number

            if ($lastAccount && $lastAccount->account_number) {
                $nextAccountNumber = intval($lastAccount->account_number) + 1;
            }

            for ($i = $existingCount + 1; $i <= 20; $i++) {
                Account::create([
                    'email' => "student{$i}@example.com",
                    'password' => bcrypt('password123'),
                    'first_name' => "Student{$i}",
                    'last_name' => "Last{$i}",
                    'user_role' => 'student',
                    'status' => 'active',
                    'account_number' => (string)$nextAccountNumber++
                ]);
            }

            // Refresh our student collection
            $studentAccounts = Account::where('user_role', 'student')
                ->take(20)
                ->get();
        }

        foreach ($teacherAccounts as $teacher) {
            // Create 2 minigames per teacher
            for ($i = 1; $i <= 2; $i++) {
                $minigame = Minigame::create([
                    'account_id' => $teacher->id,
                    'title' => "Sample Quiz {$i} by {$teacher->first_name}",
                    'default_timer' => 30, // 30 seconds per question
                    'default_points' => 10, // 10 points per question
                    'starts_at' => Carbon::now()->addDays(rand(1, 30)), // Random start date within next 30 days
                ]);

                // Create 5 questions for each minigame
                $questions = [
                    [
                        'question' => 'What is the capital of France?',
                        'page_number' => 1,
                        'correct_answer' => 1,
                        'option_1' => 'Paris',
                        'option_2' => 'London',
                        'option_3' => 'Berlin',
                        'option_4' => 'Madrid',
                    ],
                    [
                        'question' => 'Which planet is known as the Red Planet?',
                        'page_number' => 2,
                        'correct_answer' => 3,
                        'option_1' => 'Venus',
                        'option_2' => 'Jupiter',
                        'option_3' => 'Mars',
                        'option_4' => 'Saturn',
                    ],
                    [
                        'question' => 'Who painted the Mona Lisa?',
                        'page_number' => 3,
                        'correct_answer' => 2,
                        'option_1' => 'Michelangelo',
                        'option_2' => 'Leonardo da Vinci',
                        'option_3' => 'Vincent van Gogh',
                        'option_4' => 'Pablo Picasso',
                    ],
                    [
                        'question' => 'What is the chemical symbol for gold?',
                        'page_number' => 4,
                        'correct_answer' => 4,
                        'option_1' => 'Fe',
                        'option_2' => 'Ag',
                        'option_3' => 'Cu',
                        'option_4' => 'Au',
                    ],
                    [
                        'question' => 'Which is the largest ocean on Earth?',
                        'page_number' => 5,
                        'correct_answer' => 1,
                        'option_1' => 'Pacific Ocean',
                        'option_2' => 'Atlantic Ocean',
                        'option_3' => 'Indian Ocean',
                        'option_4' => 'Arctic Ocean',
                    ],
                ];

                foreach ($questions as $question) {
                    MinigameContent::create([
                        'minigame_id' => $minigame->id,
                        'question' => $question['question'],
                        'page_number' => $question['page_number'],
                        'correct_answer' => $question['correct_answer'],
                        'option_1' => $question['option_1'],
                        'option_2' => $question['option_2'],
                        'option_3' => $question['option_3'],
                        'option_4' => $question['option_4'],
                        'points' => $minigame->default_points,
                        'timer' => $minigame->default_timer,
                    ]);
                }
            }
        }

        // Create one completed minigame with history records for 10 students
        if ($teacherAccounts->isNotEmpty()) {
            $teacher = $teacherAccounts->first();

            $completedMinigame = Minigame::create([
                'account_id' => $teacher->id,
                'title' => 'Completed Quiz by ' . $teacher->first_name,
                'default_timer' => 45, // 45 seconds per question
                'default_points' => 20, // 20 points per question
                'starts_at' => Carbon::now()->subDays(15), // Past date (15 days ago)
            ]);

            // Create questions for the completed minigame
            $completedQuestions = [
                [
                    'question' => 'Which element has the chemical symbol "H"?',
                    'page_number' => 1,
                    'correct_answer' => 1,
                    'option_1' => 'Hydrogen',
                    'option_2' => 'Helium',
                    'option_3' => 'Hafnium',
                    'option_4' => 'Hassium',
                ],
                [
                    'question' => 'Who wrote "Romeo and Juliet"?',
                    'page_number' => 2,
                    'correct_answer' => 3,
                    'option_1' => 'Charles Dickens',
                    'option_2' => 'Jane Austen',
                    'option_3' => 'William Shakespeare',
                    'option_4' => 'Mark Twain',
                ],
                [
                    'question' => 'What is the capital of Japan?',
                    'page_number' => 3,
                    'correct_answer' => 2,
                    'option_1' => 'Seoul',
                    'option_2' => 'Tokyo',
                    'option_3' => 'Beijing',
                    'option_4' => 'Bangkok',
                ],
            ];

            foreach ($completedQuestions as $question) {
                MinigameContent::create([
                    'minigame_id' => $completedMinigame->id,
                    'question' => $question['question'],
                    'page_number' => $question['page_number'],
                    'correct_answer' => $question['correct_answer'],
                    'option_1' => $question['option_1'],
                    'option_2' => $question['option_2'],
                    'option_3' => $question['option_3'],
                    'option_4' => $question['option_4'],
                    'points' => $completedMinigame->default_points,
                    'timer' => $completedMinigame->default_timer,
                ]);
            }

            // Create history records for only first 10 students
            $first10Students = $studentAccounts->take(10);
            foreach ($first10Students as $student) {
                // Randomize the scores to make it more realistic
                $correctCount = rand(0, count($completedQuestions));
                $incorrectCount = count($completedQuestions) - $correctCount;
                $totalScore = $correctCount * $completedMinigame->default_points;

                MinigameHistory::create([
                    'minigame_id' => $completedMinigame->id,
                    'account_id' => $student->id,
                    'total_score' => $totalScore,
                    'correct_count' => $correctCount,
                    'incorrect_count' => $incorrectCount,
                ]);
            }
        }

        // Create another completed minigame with history records for all 20 students
        if ($teacherAccounts->count() >= 2) {
            $teacher = $teacherAccounts->get(1); // Use second teacher if available

            $completedMinigame2 = Minigame::create([
                'account_id' => $teacher->id,
                'title' => 'Math Challenge Quiz by ' . $teacher->first_name,
                'default_timer' => 60, // 60 seconds per question
                'default_points' => 15, // 15 points per question
                'starts_at' => Carbon::now()->subDays(5), // More recent past date (5 days ago)
            ]);

            // Create math questions for the second completed minigame
            $mathQuestions = [
                [
                    'question' => 'What is the square root of 144?',
                    'page_number' => 1,
                    'correct_answer' => 2,
                    'option_1' => '10',
                    'option_2' => '12',
                    'option_3' => '14',
                    'option_4' => '16',
                ],
                [
                    'question' => 'If 3x + 7 = 22, what is the value of x?',
                    'page_number' => 2,
                    'correct_answer' => 1,
                    'option_1' => '5',
                    'option_2' => '6',
                    'option_3' => '7',
                    'option_4' => '8',
                ],
                [
                    'question' => 'What is the area of a circle with radius 5?',
                    'page_number' => 3,
                    'correct_answer' => 3,
                    'option_1' => '25π',
                    'option_2' => '10π',
                    'option_3' => '25π',
                    'option_4' => '5π',
                ],
                [
                    'question' => 'What is 15% of 80?',
                    'page_number' => 4,
                    'correct_answer' => 2,
                    'option_1' => '10',
                    'option_2' => '12',
                    'option_3' => '15',
                    'option_4' => '18',
                ],
            ];

            foreach ($mathQuestions as $question) {
                MinigameContent::create([
                    'minigame_id' => $completedMinigame2->id,
                    'question' => $question['question'],
                    'page_number' => $question['page_number'],
                    'correct_answer' => $question['correct_answer'],
                    'option_1' => $question['option_1'],
                    'option_2' => $question['option_2'],
                    'option_3' => $question['option_3'],
                    'option_4' => $question['option_4'],
                    'points' => $completedMinigame2->default_points,
                    'timer' => $completedMinigame2->default_timer,
                ]);
            }

            // Create history records for all 20 students
            foreach ($studentAccounts as $student) {
                // Randomize the scores to make it more realistic
                $correctCount = rand(0, count($mathQuestions));
                $incorrectCount = count($mathQuestions) - $correctCount;
                $totalScore = $correctCount * $completedMinigame2->default_points;

                MinigameHistory::create([
                    'minigame_id' => $completedMinigame2->id,
                    'account_id' => $student->id,
                    'total_score' => $totalScore,
                    'correct_count' => $correctCount,
                    'incorrect_count' => $incorrectCount,
                ]);
            }
        }
    }
}
