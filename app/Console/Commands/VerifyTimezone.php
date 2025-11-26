<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VerifyTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timezone:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify timezone configuration for SmartPlants IoT system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌏 SmartPlants IoT - Timezone Verification');
        $this->newLine();

        // 1. Check PHP timezone
        $this->line('📌 <fg=cyan>PHP Configuration:</>');
        $phpTimezone = date_default_timezone_get();
        $this->line("   System Timezone: <fg=yellow>{$phpTimezone}</>");
        $this->line("   PHP date(): <fg=yellow>" . date('Y-m-d H:i:s T') . "</>");
        $this->newLine();

        // 2. Check Laravel app timezone
        $this->line('📌 <fg=cyan>Laravel Configuration:</>');
        $appTimezone = config('app.timezone');
        $this->line("   App Timezone: <fg=yellow>{$appTimezone}</>");
        
        $now = now();
        $this->line("   now(): <fg=yellow>{$now->format('Y-m-d H:i:s T (P)')}</>");
        $this->line("   Carbon::now(): <fg=yellow>" . Carbon::now()->format('Y-m-d H:i:s T (P)') . "</>");
        $this->newLine();

        // 3. Check MySQL/MariaDB timezone
        $this->line('📌 <fg=cyan>Database Configuration:</>');
        try {
            $mysqlTimezone = DB::selectOne('SELECT @@session.time_zone as tz, NOW() as db_time');
            $this->line("   MySQL Session Timezone: <fg=yellow>{$mysqlTimezone->tz}</>");
            $this->line("   MySQL NOW(): <fg=yellow>{$mysqlTimezone->db_time}</>");
        } catch (\Exception $e) {
            $this->error("   ❌ Could not query database: {$e->getMessage()}");
        }
        $this->newLine();

        // 4. Expected values for WIB
        $this->line('📌 <fg=cyan>Expected Values for WIB (Asia/Jakarta):</>');
        $this->line("   ✓ App Timezone: <fg=green>Asia/Jakarta</>");
        $this->line("   ✓ MySQL Timezone: <fg=green>+07:00</>");
        $this->line("   ✓ Offset from UTC: <fg=green>+07:00</>");
        $this->newLine();

        // 5. Validation
        $this->line('📌 <fg=cyan>Validation:</>');
        $allGood = true;

        if ($appTimezone !== 'Asia/Jakarta') {
            $this->error("   ❌ App timezone is '{$appTimezone}', should be 'Asia/Jakarta'");
            $this->warn("      Fix: Update config/app.php → 'timezone' => 'Asia/Jakarta'");
            $allGood = false;
        } else {
            $this->info('   ✓ App timezone correctly set to Asia/Jakarta');
        }

        try {
            $dbTz = DB::selectOne('SELECT @@session.time_zone as tz')->tz;
            if ($dbTz !== '+07:00') {
                $this->error("   ❌ Database timezone is '{$dbTz}', should be '+07:00'");
                $this->warn("      Fix: Update config/database.php → Add PDO::MYSQL_ATTR_INIT_COMMAND");
                $allGood = false;
            } else {
                $this->info('   ✓ Database timezone correctly set to +07:00');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Could not verify database timezone');
            $allGood = false;
        }

        // Check if offset matches
        $offset = $now->format('P');
        if ($offset !== '+07:00') {
            $this->error("   ❌ Carbon offset is '{$offset}', should be '+07:00'");
            $allGood = false;
        } else {
            $this->info('   ✓ Carbon timezone offset correct (+07:00)');
        }

        $this->newLine();

        if ($allGood) {
            $this->info('🎉 <fg=green>All timezone checks passed! System is using WIB (Asia/Jakarta)</fg=green>');
        } else {
            $this->error('⚠️  <fg=red>Some timezone checks failed. Please review the configuration.</fg=red>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
