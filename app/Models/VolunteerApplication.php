<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolunteerApplication extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'location', 'interest', 'availability', 'skills', 'message', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public const INTERESTS = [
        'field' => 'Community and field work',
        'health' => 'Health outreach',
        'education' => 'Education and mentoring',
        'data' => 'Data, IT and digital',
        'admin' => 'Office and administration',
        'advocacy' => 'Advocacy and communications',
    ];

    public const AVAILABILITY = [
        'weekdays' => 'Weekdays',
        'weekends' => 'Weekends',
        'flexible' => 'Flexible',
    ];
}
