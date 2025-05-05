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
                'status' => 'active',
                'account_id' => 2,
                'deleted_at' => null,
            ],
            [
                'title' => 'Book Two',
                'description' => 'Description for Book Two',
                'status' => 'active',
                'account_id' => 2,
                'deleted_at' => null,
            ],
            [
                'title' => 'Book Three',
                'description' => 'Description for Book Three',
                'status' => 'inactive',
                'account_id' => 2,
                'deleted_at' => null,
            ],
            [
                'title' => 'Book Four',
                'description' => 'Description for Book Four',
                'status' => 'active',
                'account_id' => 2,
                'deleted_at' => null,
            ],
            [
                'title' => 'Book Five',
                'description' => 'Description for Book Five',
                'status' => 'active',
                'account_id' => 2,
                'deleted_at' => null,
            ],
        ]);
    }
}
