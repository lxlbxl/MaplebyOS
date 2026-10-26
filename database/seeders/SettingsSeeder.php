<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Entities\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            // General
            ['key' => 'app_name', 'value' => 'Shipping and Logistics OS', 'type' => 'string', 'group' => 'general'],
            ['key' => 'support_email', 'value' => 'support@example.com', 'type' => 'string', 'group' => 'general'],
            ['key' => 'default_currency', 'value' => 'USD', 'type' => 'string', 'group' => 'general'],
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'general'],

            // Email
            ['key' => 'smtp_host', 'value' => '', 'type' => 'string', 'group' => 'email'],
            ['key' => 'smtp_port', 'value' => '587', 'type' => 'integer', 'group' => 'email'],
            ['key' => 'smtp_encryption', 'value' => 'tls', 'type' => 'string', 'group' => 'email'],
            ['key' => 'smtp_username', 'value' => '', 'type' => 'string', 'group' => 'email'],
            ['key' => 'smtp_password', 'value' => '', 'type' => 'string', 'group' => 'email'],

            // Finance
            ['key' => 'vat_rate', 'value' => '20', 'type' => 'float', 'group' => 'finance'],
            ['key' => 'handling_fee', 'value' => '0', 'type' => 'float', 'group' => 'finance'],

            // Security
            ['key' => 'strict_password_policy', 'value' => 'true', 'type' => 'boolean', 'group' => 'security'],
            ['key' => 'session_timeout', 'value' => 'true', 'type' => 'boolean', 'group' => 'security'],
        ];

        foreach ($defaults as $setting) {
            if (!Setting::where('key', $setting['key'])->exists()) {
                Setting::create($setting);
            }
        }
    }
}
