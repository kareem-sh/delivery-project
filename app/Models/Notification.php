<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // Fields that can be mass-assigned
    protected $fillable = ['notifiable_id', 'notifiable_type', 'type', 'data', 'is_read'];

    // Accessor for 'data' to cast JSON
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Define the polymorphic relation for the 'notifiable' entity (could be User, Order, etc.)
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}
