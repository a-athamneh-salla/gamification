<?php

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamification_events_log';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'event_name',
        'event_payload',
        'processed',
        'processed_at',
        'result',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_payload' => 'json',
        'result' => 'json',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'processed_at',
        'created_at',
    ];

    /**
     * Get the store that owns the event.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        $storeModel = config('gamification.store_model');
        return $this->belongsTo($storeModel, 'store_id');
    }

    /**
     * Mark the event as processed.
     * 
     * @param array $result
     * @return bool
     */
    public function markAsProcessed(array $result = []): bool
    {
        $this->processed = true;
        $this->processed_at = now();
        $this->result = $result;
        
        return $this->save();
    }
}