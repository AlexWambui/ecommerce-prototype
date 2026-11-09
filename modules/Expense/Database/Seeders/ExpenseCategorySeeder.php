<?php

namespace Modules\Expense\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Expense\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Rent'],
            ['name' => 'Water'],
            ['name' => 'Electricity'],
            ['name' => 'Internet'],
            ['name' => 'Airtime'],
            ['name' => 'Packaging'],
            ['name' => 'Transport'],
            ['name' => 'Salaries & Wages'],
            ['name' => 'Marketing & Advertising'],
            ['name' => 'Software Subscriptions'],
            ['name' => 'Payment Processing Fees'],
            ['name' => 'Shipping & Logistics'],
            ['name' => 'Maintenance & Repairs'],
            ['name' => 'Miscellaneous'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
            ]);
        }
    }
}
