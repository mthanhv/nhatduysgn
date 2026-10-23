<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'topic' => 'product',
            'name' => 'Nguyễn Văn A',
            'company' => 'Nhà máy Đạm Phú Mỹ',
            'email' => 'nguyenvana@example.com',
            'phone' => '0901 234 567',
            'subject' => 'Tư vấn đầu xiết thủy lực CX-5',
            'message' => 'Cần báo thông tin kỹ thuật cho bu lông M36.',
            'consent' => '1',
            ...$overrides,
        ];
    }

    public function test_message_is_stored_and_sales_is_notified(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), $this->validPayload())
            ->assertRedirect(route('contact'))
            ->assertSessionHas('contact_sent', true);

        $message = ContactMessage::sole();
        $this->assertSame('Tư vấn đầu xiết thủy lực CX-5', $message->subject);
        $this->assertNull($message->read_at);

        Mail::assertSent(ContactMessageReceived::class, fn (ContactMessageReceived $mail) => $mail->hasTo(config('site.contact_notify_email'))
            && $mail->hasReplyTo('nguyenvana@example.com'));
    }

    public function test_invalid_submission_shows_vietnamese_errors(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.store'), $this->validPayload(['email' => 'khong-hop-le', 'message' => 'ngắn', 'consent' => null]))
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors(['email', 'message', 'consent']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_honeypot_submissions_are_silently_discarded(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), $this->validPayload(['website' => 'http://spam.example']))
            ->assertRedirect(route('contact'));

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_form_is_rate_limited_per_ip(): void
    {
        Mail::fake();

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('contact.store'), $this->validPayload())->assertRedirect();
        }

        $this->post(route('contact.store'), $this->validPayload())->assertStatus(429);
        $this->assertDatabaseCount('contact_messages', 3);
    }

    public function test_message_is_kept_when_mail_server_fails(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->post(route('contact.store'), $this->validPayload())->assertRedirect(route('contact'));

        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_contact_page_prefills_topic_and_subject(): void
    {
        $this->get(route('contact', ['chu-de' => 'service', 'tieu-de' => 'Dịch vụ xiết bu lông']))
            ->assertOk()
            ->assertSee('value="Dịch vụ xiết bu lông"', false)
            ->assertSee('value="service" checked', false);
    }
}
