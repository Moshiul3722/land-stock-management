<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\YearSeeder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\CnfGenderSeeder;
use Database\Seeders\SubModuleSeeder;
use Database\Seeders\FiscalYearSeeder;
use Database\Seeders\PermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DepartmentSeeder::class,
            UserSeeder::class,
            ModuleSeeder::class,
            SubModuleSeeder::class,
            MenuSeeder::class,
            PermissionSeeder::class,
            YearSeeder::class,
            PurchaseTypeSeeder::class,
            CategorySeeder::class,
            ApprovalEntitySeeder::class,
            ApprovalProcessSeeder::class,
            TestSeeder::class,
        ]);
    }
}
