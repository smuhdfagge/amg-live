<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;
use App\Models\VolunteerApplication;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'unreadMessages' => ContactMessage::whereNull('read_at')->count(),
                'newVolunteers' => VolunteerApplication::whereNull('read_at')->count(),
                'subscribers' => Subscriber::count(),
                'posts' => Post::count(),
                'projects' => Project::count(),
            ],
            'messages' => ContactMessage::latest()->take(5)->get(),
            'volunteers' => VolunteerApplication::latest()->take(5)->get(),
        ]);
    }
}
