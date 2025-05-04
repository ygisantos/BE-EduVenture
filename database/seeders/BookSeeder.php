<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run()
    {
        Book::insert([
            [
                'title' => 'Book One',
                'description' => 'Description for Book One',
                'status' => 'published',
                'account_id' => 1,
                'deleted_at' => null,
            ],
            [
                'title' => 'Book Two',
                'description' => 'Description for Book Two',
                'status' => 'draft',
                'account_id' => 2,
                'deleted_at' => null,
            ],
            [
                'title' => 'Book Three',
                'description' => 'Description for Book Three',
                'status' => 'archived',
                'account_id' => 1,
                'deleted_at' => null,
            ],
            [
                'title' => 'Book Four',
                'description' => 'Description for Book Four',
                'status' => 'published',
                'account_id' => 3,
                'deleted_at' => null,
            ],
            [
                'title' => 'Book Five',
                'description' => 'Description for Book Five',
                'status' => 'draft',
                'account_id' => 2,
                'deleted_at' => null,
            ],
        ]);
    }
}
