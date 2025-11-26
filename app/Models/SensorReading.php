<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = ['sensor_id', 'value', 'recorded_at'];
    
    /**
     * Casts for attributes
     * 
     * Using 'immutable_datetime' ensures thread-safety and prevents accidental mutations.
     * The timezone used for casting is controlled by config('app.timezone') = Asia/Jakarta.
     * 
     * When serialized to JSON:
     * - Default behavior: Converts to UTC (e.g., "2025-11-26T07:30:00.000000Z")
     * - To preserve WIB in API responses, use serializeDate() override below
     */
    protected $casts = [
        'recorded_at' => 'immutable_datetime',
    ];

    /**
     * Customize JSON serialization format for datetime attributes
     * 
     * By default, Laravel converts datetimes to UTC when serializing.
     * This override preserves the application timezone (Asia/Jakarta) in API responses.
     * 
     * Output format: "2025-11-26T14:30:00+07:00" (ISO 8601 with WIB offset)
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        // Return ISO 8601 format with timezone offset (e.g., +07:00 for WIB)
        // This ensures API consumers see the actual local time, not UTC
        return $date->format('Y-m-d\TH:i:sP');
    }

    public function sensor() 
    { 
        return $this->belongsTo(Sensor::class); 
    }
}
