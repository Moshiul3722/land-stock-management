<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FiscalYear;

class FiscalYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Id = 1
        FiscalYear::create([
            'name' => '2010-11',
            'name_bn' => '২০১০-১১',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 2
        FiscalYear::create([
            'name' => '2011-12',
            'name_bn' => '২০১১-১২',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 3
        FiscalYear::create([
            'name' => '2012-13',
            'name_bn' => '২০১২-১৩',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 4
        FiscalYear::create([
            'name' => '2013-14',
            'name_bn' => '২০১৩-১৪',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 5
        FiscalYear::create([
            'name' => '2014-15',
            'name_bn' => '২০১৪-১৫',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 6
        FiscalYear::create([
            'name' => '2015-16',
            'name_bn' => '২০১৫-১৬',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 7
        FiscalYear::create([
            'name' => '2016-17',
            'name_bn' => '২০১৬-১৭',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 8
        FiscalYear::create([
            'name' => '2017-18',
            'name_bn' => '২০১৭-১৮',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 9
        FiscalYear::create([
            'name' => '2018-19',
            'name_bn' => '২০১৮-১৯',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 10
        FiscalYear::create([
            'name' => '2019-20',
            'name_bn' => '২০১৯-২০',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 11
        FiscalYear::create([
            'name' => '2020-21',
            'name_bn' => '২০২০-২১',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 12
        FiscalYear::create([
            'name' => '2021-22',
            'name_bn' => '২০২১-২২',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 13
        FiscalYear::create([
            'name' => '2022-23',
            'name_bn' => '২০২২-২৩',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 14
        FiscalYear::create([
            'name' => '2023-24',
            'name_bn' => '২০২৩-২৪',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 15
        FiscalYear::create([
            'name' => '2024-25',
            'name_bn' => '২০২৪-২৫',
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
