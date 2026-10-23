<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function show(Request $request): View
    {
        $topic = $request->query('chu-de');

        return view('pages.contact', [
            'topics' => ContactMessage::TOPICS,
            'selectedTopic' => array_key_exists((string) $topic, ContactMessage::TOPICS) ? $topic : 'product',
            'subject' => (string) $request->query('tieu-de', ''),
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        // Honeypot: bots fill the hidden "website" field; pretend success without storing anything.
        if (filled($request->input('website'))) {
            return to_route('contact')->with('contact_sent', true);
        }

        $message = ContactMessage::create([
            ...$request->safe()->except(['website', 'consent']),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        try {
            Mail::to(setting('contact_notify_email'))->send(new ContactMessageReceived($message));
        } catch (Throwable $exception) {
            // The message is already saved and visible in the admin; a mail outage must not lose it.
            Log::warning('Contact notification email failed', ['message_id' => $message->id, 'error' => $exception->getMessage()]);
        }

        return to_route('contact')->with('contact_sent', true);
    }
}
