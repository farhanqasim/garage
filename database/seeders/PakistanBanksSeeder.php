<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PakistanBanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            // Major Commercial Banks
            ['name' => 'Habib Bank Limited', 'short_name' => 'HBL', 'api_enabled' => false, 'status' => true],
            ['name' => 'United Bank Limited', 'short_name' => 'UBL', 'api_enabled' => false, 'status' => true],
            ['name' => 'MCB Bank Limited', 'short_name' => 'MCB', 'api_enabled' => false, 'status' => true],
            ['name' => 'Allied Bank Limited', 'short_name' => 'ABL', 'api_enabled' => false, 'status' => true],
            ['name' => 'Bank Alfalah Limited', 'short_name' => 'BAFL', 'api_enabled' => false, 'status' => true],
            ['name' => 'Meezan Bank Limited', 'short_name' => 'Meezan', 'api_enabled' => false, 'status' => true],
            ['name' => 'Faysal Bank Limited', 'short_name' => 'FBL', 'api_enabled' => false, 'status' => true],
            ['name' => 'Bank of Punjab', 'short_name' => 'BOP', 'api_enabled' => false, 'status' => true],
            ['name' => 'Askari Bank Limited', 'short_name' => 'AKBL', 'api_enabled' => false, 'status' => true],
            ['name' => 'JS Bank Limited', 'short_name' => 'JS Bank', 'api_enabled' => false, 'status' => true],
            ['name' => 'Soneri Bank Limited', 'short_name' => 'Soneri', 'api_enabled' => false, 'status' => true],
            ['name' => 'Bank Islami Pakistan Limited', 'short_name' => 'Bank Islami', 'api_enabled' => false, 'status' => true],
            ['name' => 'Al Baraka Bank Pakistan Limited', 'short_name' => 'Al Baraka', 'api_enabled' => false, 'status' => true],
            ['name' => 'Dubai Islamic Bank Pakistan Limited', 'short_name' => 'DIB Pakistan', 'api_enabled' => false, 'status' => true],
            ['name' => 'Silkbank Limited', 'short_name' => 'Silkbank', 'api_enabled' => false, 'status' => true],
            ['name' => 'Summit Bank Limited', 'short_name' => 'Summit', 'api_enabled' => false, 'status' => true],
            ['name' => 'Standard Chartered Bank Pakistan', 'short_name' => 'SCB', 'api_enabled' => false, 'status' => true],
            
            // Government Banks
            ['name' => 'National Bank of Pakistan', 'short_name' => 'NBP', 'api_enabled' => false, 'status' => true],
            ['name' => 'State Bank of Pakistan', 'short_name' => 'SBP', 'api_enabled' => false, 'status' => true],
            ['name' => 'First Women Bank Limited', 'short_name' => 'FWBL', 'api_enabled' => false, 'status' => true],
            ['name' => 'Zarai Taraqiati Bank Limited', 'short_name' => 'ZTBL', 'api_enabled' => false, 'status' => true],
            ['name' => 'Bank of Khyber', 'short_name' => 'BOK', 'api_enabled' => false, 'status' => true],
            ['name' => 'Sindh Bank Limited', 'short_name' => 'Sindh Bank', 'api_enabled' => false, 'status' => true],
            ['name' => 'Bank of Azad Jammu & Kashmir', 'short_name' => 'BAJK', 'api_enabled' => false, 'status' => true],
            
            // Microfinance Banks
            ['name' => 'Khushhali Microfinance Bank Limited', 'short_name' => 'Khushhali', 'api_enabled' => false, 'status' => true],
            ['name' => 'First Microfinance Bank Limited', 'short_name' => 'FMFB', 'api_enabled' => false, 'status' => true],
            ['name' => 'Telenor Microfinance Bank', 'short_name' => 'Telenor Bank', 'api_enabled' => false, 'status' => true],
            ['name' => 'U Microfinance Bank Limited', 'short_name' => 'U Bank', 'api_enabled' => false, 'status' => true],
            ['name' => 'Mobilink Microfinance Bank', 'short_name' => 'Mobilink Bank', 'api_enabled' => false, 'status' => true],
            ['name' => 'NRSP Microfinance Bank', 'short_name' => 'NRSP', 'api_enabled' => false, 'status' => true],
            ['name' => 'Pak Oman Microfinance Bank', 'short_name' => 'Pak Oman', 'api_enabled' => false, 'status' => true],
            ['name' => 'Apna Microfinance Bank', 'short_name' => 'Apna Bank', 'api_enabled' => false, 'status' => true],
            
            // Development Banks
            ['name' => 'Industrial Development Bank of Pakistan', 'short_name' => 'IDBP', 'api_enabled' => false, 'status' => true],
            ['name' => 'Punjab Provincial Cooperative Bank', 'short_name' => 'PPCB', 'api_enabled' => false, 'status' => true],
            
            // International Banks
            ['name' => 'Industrial and Commercial Bank of China', 'short_name' => 'ICBC', 'api_enabled' => false, 'status' => true],
            ['name' => 'Citibank N.A. Pakistan', 'short_name' => 'Citibank', 'api_enabled' => false, 'status' => true],
            ['name' => 'Deutsche Bank AG', 'short_name' => 'Deutsche Bank', 'api_enabled' => false, 'status' => true],
        ];

        // Remove duplicates based on short_name
        $uniqueBanks = [];
        $seenShortNames = [];
        
        foreach ($banks as $bank) {
            if (!in_array($bank['short_name'], $seenShortNames)) {
                $uniqueBanks[] = $bank;
                $seenShortNames[] = $bank['short_name'];
            }
        }

        // Check if banks already exist, if not then insert
        foreach ($uniqueBanks as $bank) {
            $exists = DB::table('banks')->where('short_name', $bank['short_name'])->exists();
            
            if (!$exists) {
                DB::table('banks')->insert([
                    'name' => $bank['name'],
                    'short_name' => $bank['short_name'],
                    'api_enabled' => $bank['api_enabled'],
                    'status' => $bank['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
