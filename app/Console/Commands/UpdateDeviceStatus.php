<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;

class UpdateDeviceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update device online/offline status based on last_seen timestamp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating device statuses...');
        
        $devices = Device::all();
        $updated = 0;
        
        foreach ($devices as $device) {
            $oldStatus = $device->status;
            
            if ($device->last_seen) {
                $isOnline = $device->last_seen->diffInMinutes(now()) < 5;
                $newStatus = $isOnline ? 'online' : 'offline';
            } else {
                $newStatus = 'offline';
            }
            
            if ($oldStatus !== $newStatus) {
                $device->update(['status' => $newStatus]);
                $this->line("Device {$device->name} ({$device->id}): {$oldStatus} → {$newStatus}");
                $updated++;
            }
        }
        
        $this->info("Updated {$updated} device(s).");
        return 0;
    }
}
