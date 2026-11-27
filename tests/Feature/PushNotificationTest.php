<?php

namespace Tests\Feature;

use App\Http\Controllers\ReservationController;
use App\Models\Config;
use App\Models\House;
use App\Models\Reservation;
use App\Models\ReservationWaitlist;
use App\Models\User;
use App\Notifications\ReservationPushNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'webpush.vapid.public_key' => 'test-public-key',
            'webpush.vapid.private_key' => 'test-private-key',
            'webpush.vapid.subject' => 'mailto:test@versallesmarsella.com',
        ]);
    }

    public function test_authenticated_user_can_subscribe_and_unsubscribe_push(): void
    {
        $user = User::factory()->create();

        $endpoint = 'https://fcm.googleapis.com/fcm/send/fake-endpoint-123';

        $this->actingAs($user)
            ->postJson('/api/push-subscriptions', [
                'endpoint' => $endpoint,
                'keys' => [
                    'p256dh' => 'fake-p256dh-key',
                    'auth' => 'fake-auth-token',
                ],
                'contentEncoding' => 'aes128gcm',
            ])
            ->assertOk();

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $user->id,
            'endpoint' => $endpoint,
        ]);

        $this->actingAs($user)
            ->postJson('/api/push-subscriptions/unsubscribe', [
                'endpoint' => $endpoint,
            ])
            ->assertOk();

        $this->assertDatabaseMissing('push_subscriptions', [
            'subscribable_id' => $user->id,
            'endpoint' => $endpoint,
        ]);
    }

    public function test_push_notifications_are_sent_only_to_subscribed_email_recipients_across_controllers_and_cron(): void
    {
        Mail::fake();
        Notification::fake();

        $house1 = House::create(['street_id' => 1, 'house_number' => '101']);
        $house2 = House::create(['street_id' => 1, 'house_number' => '102']);
        $house3 = House::create(['street_id' => 1, 'house_number' => '103']);

        $adminSubscribed = User::factory()->create(['role_id' => 1, 'house_id' => $house1->id]);
        $adminSubscribed->updatePushSubscription('https://push.test/admin1', 'key1', 'auth1');

        $adminUnsubscribed = User::factory()->create(['role_id' => 1, 'house_id' => $house1->id]);

        $resident = User::factory()->create(['role_id' => 2, 'house_id' => $house2->id]);
        $resident->updatePushSubscription('https://push.test/res1', 'key2', 'auth2');

        $otherResident = User::factory()->create(['role_id' => 2, 'house_id' => $house3->id]);
        $otherResident->updatePushSubscription('https://push.test/res2', 'key3', 'auth3');

        // 1. Resident creates reservation -> only subscribed admins receive push
        $futureDate = now()->addDays(15)->format('Y-m-d');
        $this->actingAs($resident)
            ->postJson('/api/reservations', [
                'reservation_date' => $futureDate,
                'acceptTerms' => true,
            ])
            ->assertOk();

        Notification::assertSentTo(
            $adminSubscribed,
            ReservationPushNotification::class,
            fn (ReservationPushNotification $n) => $n->type === 'created'
        );
        Notification::assertNotSentTo($adminUnsubscribed, ReservationPushNotification::class);
        Notification::assertNotSentTo($resident, ReservationPushNotification::class);

        $reservation = Reservation::first();

        // 2. Admin approves reservation -> only the reservation owner receives push
        Notification::fake();
        $this->actingAs($adminSubscribed)
            ->putJson('/api/reservations/approval-requests', [
                'id' => $reservation->id,
                'is_approved' => 1,
            ])
            ->assertOk();

        Notification::assertSentTo(
            $resident,
            ReservationPushNotification::class,
            fn (ReservationPushNotification $n) => $n->type === 'approved'
        );
        Notification::assertNotSentTo($otherResident, ReservationPushNotification::class);

        // 3. Waitlist release -> only users on the waitlist receive push
        ReservationWaitlist::create([
            'reservation_id' => $reservation->id,
            'user_id' => $otherResident->id,
        ]);

        Notification::fake();
        $this->actingAs($adminSubscribed)
            ->deleteJson("/api/reservations/admin/{$reservation->id}")
            ->assertOk();

        Notification::assertSentTo(
            $otherResident,
            ReservationPushNotification::class,
            fn (ReservationPushNotification $n) => $n->type === 'waitlist_released'
        );
        Notification::assertNotSentTo($resident, ReservationPushNotification::class);

        // 4. Cron job: payment reminder (24h window) & expired reservation cancellation
        Config::where('slug', 'mdtpr')->update(['setting' => '7']);

        $expiredRes = Reservation::create([
            'house_id' => $house2->id,
            'user_id' => $resident->id,
            'is_signed' => 1,
            'is_approved' => 0,
            'reservation_date' => now()->addDays(20)->format('Y-m-d 12:00:00'),
        ]);
        $expiredRes->created_at = now()->subDays(8);
        $expiredRes->save();

        $reminderRes = Reservation::create([
            'house_id' => $house3->id,
            'user_id' => $otherResident->id,
            'is_signed' => 1,
            'is_approved' => 0,
            'reservation_date' => now()->addDays(25)->format('Y-m-d 12:00:00'),
        ]);
        // 7 days deadline minus 24h = created 6 days ago
        $reminderRes->created_at = now()->subDays(6);
        $reminderRes->save();

        Notification::fake();
        (new ReservationController())->cancelExpiredReservations();

        Notification::assertSentTo(
            $resident,
            ReservationPushNotification::class,
            fn (ReservationPushNotification $n) => $n->type === 'rejected'
        );
        Notification::assertSentTo(
            $otherResident,
            ReservationPushNotification::class,
            fn (ReservationPushNotification $n) => $n->type === 'payment_reminder'
        );
    }
}
