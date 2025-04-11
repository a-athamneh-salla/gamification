<?php

namespace Salla\Gamification\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email'];
    
    /**
     * Create the test table for Store model.
     */
    public static function createTable()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function () {
            if (!Schema::hasTable('stores')) {
                self::createTable();
            }
        });
    }
}