<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','name','location','api_key','status','last_seen'];
    protected $casts = [
        'last_seen' => 'datetime',
    ];

    public function sensors() { return $this->hasMany(Sensor::class); }
    public function commands(){ return $this->hasMany(Command::class); }
    
    /**
     * Accessor untuk cek apakah device online (last_seen < 5 menit)
     */
    public function getIsOnlineAttribute()
    {
        if (!$this->last_seen) {
            return false;
        }
        return $this->last_seen->diffInMinutes(now()) < 5;
    }
}

