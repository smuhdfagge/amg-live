<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Subscriber;
use App\Models\VolunteerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class FormController extends Controller
{
    public function contact(Request $request): RedirectResponse
    {
        if ($this->isBot($request)) {
            return redirect(route('contact').'#message')->with('status', 'Thank you. Your message has been sent.');
        }

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(ContactMessage::TYPES))],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'organisation' => ['nullable', 'string', 'max:160'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = ContactMessage::create($data);

        $this->notify(
            'New '.strtolower($message->typeLabel()).' message from '.$message->name,
            "From: {$message->name} <{$message->email}>\nPhone: {$message->phone}\nOrganisation: {$message->organisation}\nSubject: {$message->subject}\n\n{$message->message}",
            $message->email,
        );

        return redirect(route('contact').'#message')->with('status', 'Thank you, '.$message->name.'. We have received your message and will reply within three working days.');
    }

    public function volunteer(Request $request): RedirectResponse
    {
        if ($this->isBot($request)) {
            return redirect(route('involved').'#volunteer')->with('volunteer_status', 'Thank you. Your application has been received.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:120'],
            'interest' => ['required', Rule::in(array_keys(VolunteerApplication::INTERESTS))],
            'availability' => ['nullable', Rule::in(array_keys(VolunteerApplication::AVAILABILITY))],
            'skills' => ['nullable', 'string', 'max:2000'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        $application = VolunteerApplication::create($data);

        $this->notify(
            'New volunteer application from '.$application->name,
            "Name: {$application->name}\nEmail: {$application->email}\nPhone: {$application->phone}\nLocation: {$application->location}\nInterest: ".VolunteerApplication::INTERESTS[$application->interest]."\n\nSkills:\n{$application->skills}\n\nMessage:\n{$application->message}",
            $application->email,
        );

        return redirect(route('involved').'#volunteer')
            ->with('volunteer_status', 'Thank you, '.$application->name.'. Your application has been received. Our volunteer coordinator will call you.');
    }

    public function subscribe(Request $request): RedirectResponse
    {
        if ($this->isBot($request)) {
            return back()->with('subscribe_status', 'You are on the list.');
        }

        $data = $request->validateWithBag('subscribe', [
            'email' => ['required', 'email', 'max:160'],
        ]);

        Subscriber::firstOrCreate(['email' => strtolower($data['email'])]);

        return back()->with('subscribe_status', 'You are on the list. We will send you our updates.');
    }

    /** Hidden field that people never see but simple bots fill in. */
    private function isBot(Request $request): bool
    {
        return filled($request->input('website'));
    }

    private function notify(string $subject, string $body, string $replyTo): void
    {
        $to = config('swatch.notify_email');

        if (blank($to)) {
            return;
        }

        try {
            Mail::raw($body, function ($mail) use ($to, $subject, $replyTo) {
                $mail->to($to)->replyTo($replyTo)->subject('[Website] '.$subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Website form notification could not be sent: '.$e->getMessage());
        }
    }
}
