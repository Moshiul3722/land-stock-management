<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Additional (Admin Wing)
        // Id = 1
        Department::create([
            'name' => 'Additional (Admin Wing)',
            'name_bn' => 'অতিরিক্ত (প্রশাসন অনুবিভাগ)',
        ]);

        // Deputy Secretary (Admin Branch)
        // Id = 2
        Department::create([
            'name' => 'Deputy Secretary (Admin Branch)',
            'name_bn' => 'উপসচিব (প্রশাসন অধিশাখা)',
        ]);

        // Service, Receive and Dispatch Section
        // Id = 3
        Department::create([
            'name' => 'Service, Receive and Dispatch Section',
            'name_bn' => 'সেবা, গ্রহণ ও প্রেরণ শাখা',
        ]);

        // Establishment Section
        // Id = 4
        Department::create([
            'name' => 'Establishment Section',
            'name_bn' => 'সংস্থাপন শাখা',
        ]);

        // Discipline-1 Section
        // Id = 5
        Department::create([
            'name' => 'Discipline-1 Section',
            'name_bn' => 'শৃঙ্খলা-1 শাখা',
        ]);

        // Discipline-2 Section
        // Id = 6
        Department::create([
            'name' => 'Discipline-2 Section',
            'name_bn' => 'শৃঙ্খলা-২ শাখা',
        ]);

        // Deputy Secretary (Budget Branch)
        // Id = 7
        Department::create([
            'name' => 'Deputy Secretary (Budget Branch)',
            'name_bn' => 'উপসচিব (বাজেট অধিশাখা)',
        ]);

        // Budget and Audit Section
        // Id = 8
        Department::create([
            'name' => 'Budget and Audit Section',
            'name_bn' => 'বাজেট ও অডিট শাখা',
        ]);

        // Accounts Section
        // Id = 9
        Department::create([
            'name' => 'Accounts Section',
            'name_bn' => 'হিসাব শাখা',
        ]);

        // Library Section
        // Id = 10
        Department::create([
            'name' => 'Library Section',
            'name_bn' => 'লাইব্রেরি শাখা',
        ]);

        // Additional Secretary (Development Wing)
        // Id = 11
        Department::create([
            'name' => 'Additional Secretary (Development Wing)',
            'name_bn' => 'অতিরিক্ত সচিব (উন্নয়ন অনুবিভাগ)',
        ]);

        // Planning Cell
        // Id = 12
        Department::create([
            'name' => 'Planning Cell',
            'name_bn' => 'প্ল্যানিং সেল',
        ]);

        // Deputy Secretary (Planning and Development Branch)
        // Id = 13
        Department::create([
            'name' => 'Deputy Secretary (Planning and Development Branch)',
            'name_bn' => 'উপসচিব (পরিকল্পনা ও উন্নয়ন অধিশাখা)',
        ]);

        // Planning and Development-1 Section
        // Id = 14
        Department::create([
            'name' => 'Planning and Development-1 Section',
            'name_bn' => 'পরিকল্পনা ও উন্নয়ন-১ শাখা',
        ]);

        // Planning and Development-2 Section
        // Id = 15
        Department::create([
            'name' => 'Planning and Development-2 Section',
            'name_bn' => 'পরিকল্পনা ও উন্নয়ন-২ শাখা',
        ]);

        // Planning and Development-3 Section
        // Id = 16
        Department::create([
            'name' => 'Planning and Development-3 Section',
            'name_bn' => 'পরিকল্পনা ও উন্নয়ন-৩ শাখা',
        ]);

        // Joint Secretary (Law Wing)
        // Id = 17
        Department::create([
            'name' => 'Joint Secretary (Law Wing)',
            'name_bn' => 'যুগ্ম সচিব (আইন অনুবিভাগ)',
        ]);

        // Deputy Secretary (Law-1 Branch)
        // Id = 18
        Department::create([
            'name' => 'Deputy Secretary (Law-1 Branch)',
            'name_bn' => 'উপসচিব (আইন-১ অধিশাখা)',
        ]);

        // Law-1 Section
        // Id = 19
        Department::create([
            'name' => 'Law-1 Section',
            'name_bn' => 'আইন-১ শাখা',
        ]);

        // Law-2 Section
        // Id = 20
        Department::create([
            'name' => 'Law-2 Section',
            'name_bn' => 'আইন-২ শাখা',
        ]);

        // Deputy Secretary (Law-2 Branch)
        // Id = 21
        Department::create([
            'name' => 'Deputy Secretary (Law-2 Branch)',
            'name_bn' => 'উপসচিব (আইন-২ অধিশাখা)',
        ]);

        // Law-3 Section
        // Id = 22
        Department::create([
            'name' => 'Law-3 Section',
            'name_bn' => 'আইন-৩ শাখা',
        ]);

        // Law-4 Section
        // Id = 23
        Department::create([
            'name' => 'Law-4 Section',
            'name_bn' => 'আইন-৪ শাখা',
        ]);

        // Additional Secretary (Field Admin Wing)
        // Id = 24
        Department::create([
            'name' => 'Additional Secretary (Field Admin Wing)',
            'name_bn' => 'অতিরিক্ত সচিব (মাঠ প্রশাসন অনুবিভাগ)',
        ]);

        // Deputy Secretary (Field Admin Branch)
        // Id = 25
        Department::create([
            'name' => 'Deputy Secretary (Field Admin Branch)',
            'name_bn' => 'উপসচিব (মাঠ প্রশাসন অধিশাখা)',
        ]);

        // Field Admin-1 Section
        // Id = 26
        Department::create([
            'name' => 'Field Admin-1 Section',
            'name_bn' => 'মাঠ প্রশাসন-১ শাখা',
        ]);

        // Field Admin-2 Section
        // Id = 27
        Department::create([
            'name' => 'Field Admin-2 Section',
            'name_bn' => 'মাঠ প্রশাসন-২ শাখা',
        ]);

        // Field Admin-3 Section
        // Id = 28
        Department::create([
            'name' => 'Field Admin-3 Section',
            'name_bn' => 'মাঠ প্রশাসন-৩ শাখা',
        ]);

        // Joint Secretary (Acquisition & Khas Land Wing)
        // Id = 29
        Department::create([
            'name' => 'Joint Secretary (Acquisition & Khas Land Wing)',
            'name_bn' => 'যুগ্মসচিব (অধিগ্রহণ ও খাসজমি অনুবিভাগ)',
        ]);

        // Deputy Secretary (Acquisition Branch)
        // Id = 30
        Department::create([
            'name' => 'Deputy Secretary (Acquisition Branch)',
            'name_bn' => 'উপসচিব (অধিগ্রহণ অধিশাখা)',
        ]);

        // Acquisition-1 Section
        // Id = 31
        Department::create([
            'name' => 'Acquisition-1 Section',
            'name_bn' => 'অধিগ্রহণ-১ শাখা',
        ]);

        // Acquisition-2 Section
        // Id = 32
        Department::create([
            'name' => 'Acquisition-2 Section',
            'name_bn' => 'অধিগ্রহণ-২ শাখা',
        ]);

        // Deputy Secretary (Khas Land Branch)
        // Id = 33
        Department::create([
            'name' => 'Deputy Secretary (Khas Land Branch)',
            'name_bn' => 'উপসচিব (খামজমি অধিশাখা)',
        ]);

        // Khas Land-1 Section
        // Id = 34
        Department::create([
            'name' => 'Khas Land-1 Section',
            'name_bn' => 'খামজমি-১ শাখা',
        ]);

        // Khas Land-2 Section
        // Id = 35
        Department::create([
            'name' => 'Khas Land-2 Section',
            'name_bn' => 'খামজমি-২ শাখা',
        ]);

        // Khas Land-3 Section
        // Id = 36
        Department::create([
            'name' => 'Khas Land-3 Section',
            'name_bn' => 'খামজমি-৩ শাখা',
        ]);

        // Joint Secretary (Survey & Sairat Wing)
        // Id = 37
        Department::create([
            'name' => 'Joint Secretary (Survey & Sairat Wing)',
            'name_bn' => 'যুগ্মসচিব (জরিপ ও সায়রাত অনুবিভাগ)',
        ]);

        // Deputy Secretary (Sairat Branch)
        // Id = 38
        Department::create([
            'name' => 'Deputy Secretary (Sairat Branch)',
            'name_bn' => 'উপসচিব (সায়রাত অধিশাখা)',
        ]);

        // Sairat-1 Section
        // Id = 39
        Department::create([
            'name' => 'Sairat-1 Section',
            'name_bn' => 'সায়রাত-১ শাখা',
        ]);

        // Sairat-2 Section
        // Id = 40
        Department::create([
            'name' => 'Sairat-2 Section',
            'name_bn' => 'সায়রাত-২ শাখা',
        ]);

        // Deputy Secretary (Survey Branch)
        // Id = 41
        Department::create([
            'name' => 'Deputy Secretary (Survey Branch)',
            'name_bn' => 'উপসচিব (জরিপ অধিশাখা)',
        ]);

        // Survey-1 Section
        // Id = 42
        Department::create([
            'name' => 'Survey-1 Section',
            'name_bn' => 'জরিপ-১ শাখা',
        ]);

        // Survey-2 Section
        // Id = 43
        Department::create([
            'name' => 'Survey-2 Section',
            'name_bn' => 'জরিপ-২ শাখা',
        ]);

        // Joint Secretary (DKMP Wing)
        // Id = 44
        Department::create([
            'name' => 'Joint Secretary (DKMP Wing)',
            'name_bn' => 'যুগ্মসচিব (ডিকেএমপি অনুবিভাগ)',
        ]);

        // Deputy Secretary (Digitization and Service Delivery-DSD Branch)
        // Id = 45
        Department::create([
            'name' => 'Deputy Secretary (Digitization and Service Delivery-DSD Branch)',
            'name_bn' => 'উপসচিব (ডিজিটাইজেশন ও সার্ভিস ডেলিভারি-ডিএসডি অধিশাখা)',
        ]);

        // Citizen Smart Services (CSS) Section
        // Id = 46
        Department::create([
            'name' => 'Citizen Smart Services (CSS) Section',
            'name_bn' => 'সিটিজেন স্মার্ট সার্ভিসেস (সিএসএস) শাখা',
        ]);

        // Internal Smart Services (ISS) Section
        // Id = 47
        Department::create([
            'name' => 'Internal Smart Services (ISS) Section',
            'name_bn' => 'ইন্টারনাল স্মার্ট সার্ভিসেস (আইএসএস) শাখা',
        ]);

        // Deputy Secretary (Knowledge, Policy Reform-KPR Branch)
        // Id = 48
        Department::create([
            'name' => 'Deputy Secretary (Knowledge, Policy Reform-KPR Branch)',
            'name_bn' => 'উপসচিব (নলেজ, পলিসি রিফর্ম-কেপিআর অধিশাখা)',
        ]);

        // Training, Motivation and Promotion (TMP) Section
        // Id = 49
        Department::create([
            'name' => 'Training, Motivation and Promotion (TMP) Section',
            'name_bn' => 'ট্রেনিং, মোটিভেশন ও প্রোমোশন (টিএমপি) শাখা',
        ]);

        // System Implementation and Procurement (SIP) Section
        // Id = 50
        Department::create([
            'name' => 'System Implementation and Procurement (SIP) Section',
            'name_bn' => 'সিস্টেম ইমপ্লিমেন্টেশন ও প্রকিউরমেন্ট (এসআইপি) শাখা',
        ]);

        // System Analyst (Security, Audit and Quality Control)
        // Id = 51
        Department::create([
            'name' => 'System Analyst (Security, Audit and Quality Control)',
            'name_bn' => 'সিস্টেম এনালিস্ট (সিকিউরিটি, অডিট ও কোয়ালিটি কন্ট্রোল)',
        ]);
    }
}