<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VolunteerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VolunteerController extends Controller
{
    public function index(): View
    {
        return view('admin.volunteers.index', ['volunteers' => VolunteerApplication::latest()->paginate(25)]);
    }

    public function show(VolunteerApplication $volunteer): View
    {
        if (! $volunteer->read_at) {
            $volunteer->update(['read_at' => now()]);
        }

        return view('admin.volunteers.show', ['volunteer' => $volunteer]);
    }

    public function destroy(VolunteerApplication $volunteer): RedirectResponse
    {
        $volunteer->delete();

        return redirect()->route('admin.volunteers.index')->with('status', 'The application was deleted.');
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Name', 'Email', 'Phone', 'Location', 'Interest', 'Availability', 'Skills', 'Message']);
            VolunteerApplication::latest()->each(function ($v) use ($out) {
                fputcsv($out, [
                    $v->created_at->format('Y-m-d'), $v->name, $v->email, $v->phone, $v->location,
                    VolunteerApplication::INTERESTS[$v->interest] ?? $v->interest,
                    VolunteerApplication::AVAILABILITY[$v->availability] ?? $v->availability,
                    $v->skills, $v->message,
                ]);
            });
            fclose($out);
        }, 'swatch-volunteers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
