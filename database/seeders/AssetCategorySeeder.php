<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetCategory;

class AssetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Laptop / PC', 'type' => 'Hardware', 'description' => 'Personal computing devices'],
            ['name' => 'Server', 'type' => 'Hardware', 'description' => 'Physical or virtual servers'],
            ['name' => 'Networking Equipment', 'type' => 'Hardware', 'description' => 'Routers, Switches, Firewalls'],
            ['name' => 'Mobile Device', 'type' => 'Hardware', 'description' => 'Smartphones, Tablets'],
            ['name' => 'Peripheral', 'type' => 'Hardware', 'description' => 'Monitors, Keyboards, Mice'],

            ['name' => 'Operating System', 'type' => 'Software', 'description' => 'Windows, Linux, macOS'],
            ['name' => 'Productivity Software', 'type' => 'Software', 'description' => 'Office, Adobe, etc.'],
            ['name' => 'Development Tools', 'type' => 'Software', 'description' => 'IDE, Compilers'],

            ['name' => 'Customer Database', 'type' => 'Data', 'description' => 'Records of customers'],
            ['name' => 'Financial Records', 'type' => 'Data', 'description' => 'Invoices, Payroll, Tax'],
            ['name' => 'Source Code', 'type' => 'Data', 'description' => 'Proprietary application code'],

            ['name' => 'Employee', 'type' => 'People', 'description' => 'Staff members'],

            ['name' => 'Office Building', 'type' => 'Other', 'description' => 'Physical premises'],
            ['name' => 'Server Room', 'type' => 'Other', 'description' => 'Secure IT facility'],
        ];

        foreach ($categories as $category) {
            AssetCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
