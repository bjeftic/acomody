<?php

use App\Enums\Booking\BookingStatus;
use App\Enums\Ical\IcalSource;
use App\Jobs\SyncIcalCalendarJob;
use App\Models\AvailabilityPeriod;
use App\Models\IcalCalendar;
use App\Services\IcalGeneratorService;
use App\Services\IcalParserService;
use App\Services\IcalSyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

// ============================================================
// IcalParserService
// ============================================================

describe('IcalParserService', function () {
    it('parses all-day events with VALUE=DATE', function () {
        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:test-uid-1@airbnb.com',
            'DTSTART;VALUE=DATE:20240601',
            'DTEND;VALUE=DATE:20240605',
            'SUMMARY:Reserved',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        $events = (new IcalParserService)->parse($ics);

        expect($events)->toHaveCount(1)
            ->and($events[0]['uid'])->toBe('test-uid-1@airbnb.com')
            ->and($events[0]['dtstart']->toDateString())->toBe('2024-06-01')
            ->and($events[0]['dtend']->toDateString())->toBe('2024-06-05')
            ->and($events[0]['summary'])->toBe('Reserved');
    });

    it('parses UTC datetime events', function () {
        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'BEGIN:VEVENT',
            'UID:test-uid-2@booking.com',
            'DTSTART:20240601T120000Z',
            'DTEND:20240605T120000Z',
            'SUMMARY:Booking',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        $events = (new IcalParserService)->parse($ics);

        expect($events)->toHaveCount(1)
            ->and($events[0]['uid'])->toBe('test-uid-2@booking.com')
            ->and($events[0]['dtstart']->toDateString())->toBe('2024-06-01');
    });

    it('skips events without uid or dates', function () {
        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'BEGIN:VEVENT',
            'DTSTART;VALUE=DATE:20240601',
            'DTEND;VALUE=DATE:20240605',
            'SUMMARY:No UID',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        expect((new IcalParserService)->parse($ics))->toBeEmpty();
    });

    it('unfolds long lines', function () {
        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'BEGIN:VEVENT',
            'UID:folded-uid',
            'DTSTART;VALUE=DATE:20240601',
            'DTEND;VALUE=DATE:20240602',
            'SUMMARY:A very long summ',
            ' ary that is folded',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        $events = (new IcalParserService)->parse($ics);

        expect($events[0]['summary'])->toBe('A very long summary that is folded');
    });
});

// ============================================================
// IcalGeneratorService
// ============================================================

describe('IcalGeneratorService', function () {
    it('generates valid ICS with VCALENDAR wrapper', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host, ['title' => 'My Villa']);

        $ics = (new IcalGeneratorService)->generate($accommodation);

        expect($ics)
            ->toContain('BEGIN:VCALENDAR')
            ->toContain('END:VCALENDAR')
            ->toContain('VERSION:2.0')
            ->toContain('X-WR-CALNAME:My Villa');
    });

    it('includes confirmed bookings as events', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);

        createBooking(null, $host, [
            'accommodation_id' => $accommodation->id,
            'host_user_id' => $host->id,
            'status' => BookingStatus::CONFIRMED,
            'check_in' => '2024-07-01',
            'check_out' => '2024-07-05',
        ]);

        $ics = (new IcalGeneratorService)->generate($accommodation->fresh());

        expect($ics)
            ->toContain('DTSTART;VALUE=DATE:20240701')
            ->toContain('DTEND;VALUE=DATE:20240705');
    });

    it('includes manually blocked periods', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);

        $accommodation->availabilityPeriods()->create([
            'status' => 'blocked',
            'reason' => 'owner_blocked',
            'start_date' => '2024-08-01',
            'end_date' => '2024-08-03',
        ]);

        $ics = (new IcalGeneratorService)->generate($accommodation->fresh());

        expect($ics)
            ->toContain('DTSTART;VALUE=DATE:20240801')
            ->toContain('DTEND;VALUE=DATE:20240804'); // exclusive end
    });

    it('excludes imported periods to prevent sync loops', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);

        $calendar = $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/calendar.ics',
        ]);

        $accommodation->availabilityPeriods()->create([
            'status' => 'blocked',
            'reason' => 'external_booking',
            'start_date' => '2024-09-01',
            'end_date' => '2024-09-05',
            'external_uid' => 'airbnb-uid@airbnb.com',
            'ical_calendar_id' => $calendar->id,
        ]);

        $ics = (new IcalGeneratorService)->generate($accommodation->fresh());

        expect($ics)->not->toContain('20240901');
    });
});

// ============================================================
// iCal Export endpoint (public)
// ============================================================

describe('GET /api/{accommodationId}/ical/{token}', function () {
    it('returns ICS file for valid token', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        $accommodation->update(['ical_export_active' => true]);

        $this->get("/api/{$accommodation->id}/ical/{$accommodation->ical_token}")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    });

    it('returns 404 for wrong token', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);

        $this->get("/api/{$accommodation->id}/ical/invalid-token")
            ->assertStatus(404);
    });
});

// ============================================================
// Host iCal calendar CRUD
// ============================================================

describe('Host iCal calendar management', function () {
    it('lists ical calendars for own accommodation', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);

        $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/calendar.ics',
        ]);

        Auth::login($host);

        $this->getJson("/api/host/accommodations/{$accommodation->id}/ical-calendars")
            ->assertOk()
            ->assertJsonPath('data.0.source', 'airbnb');
    });

    it('returns 403 when listing calendars of another host', function () {
        $other = authenticatedUser();
        $accommodation = createAccommodation($other);

        $host = authenticatedUser();
        Auth::login($host);

        $this->getJson("/api/host/accommodations/{$accommodation->id}/ical-calendars")
            ->assertForbidden();
    });

    it('stores a new ical calendar', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        Auth::login($host);

        $this->postJson("/api/host/accommodations/{$accommodation->id}/ical-calendars", [
            'source' => 'booking',
            'ical_url' => 'https://booking.com/calendar.ics',
            'name' => 'Booking.com',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.source', 'booking')
            ->assertJsonPath('data.source_label', 'Booking.com');

        expect(IcalCalendar::where('accommodation_id', $accommodation->id)->count())->toBe(1);
    });

    it('validates required fields on store', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);

        $this->postJson("/api/host/accommodations/{$accommodation->id}/ical-calendars", [])
            ->assertStatus(422)
            ->assertJsonFragment(['field' => 'source'])
            ->assertJsonFragment(['field' => 'ical_url']);
    });

    it('updates an ical calendar', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        Auth::login($host);
        $calendar = $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/old.ics',
        ]);

        $this->putJson("/api/host/accommodations/{$accommodation->id}/ical-calendars/{$calendar->id}", [
            'ical_url' => 'https://airbnb.com/new.ics',
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.ical_url', 'https://airbnb.com/new.ics')
            ->assertJsonPath('data.is_active', false);
    });

    it('deletes calendar and its imported availability periods', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        Auth::login($host);
        $calendar = $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/calendar.ics',
        ]);

        $accommodation->availabilityPeriods()->create([
            'status' => 'blocked',
            'reason' => 'external_booking',
            'start_date' => '2024-10-01',
            'end_date' => '2024-10-05',
            'external_uid' => 'uid@airbnb.com',
            'ical_calendar_id' => $calendar->id,
        ]);

        $this->deleteJson("/api/host/accommodations/{$accommodation->id}/ical-calendars/{$calendar->id}")
            ->assertOk();

        expect(IcalCalendar::find($calendar->id))->toBeNull();
        expect(AvailabilityPeriod::where('ical_calendar_id', $calendar->id)->count())->toBe(0);
    });

    it('regenerates ical token', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        Auth::login($host);
        $oldToken = $accommodation->ical_token;

        $this->postJson("/api/host/accommodations/{$accommodation->id}/ical-token/regenerate")
            ->assertOk()
            ->assertJsonPath('meta.ical_token', fn ($token) => $token !== $oldToken);

        expect($accommodation->fresh()->ical_token)->not->toBe($oldToken);
    });
});

// ============================================================
// SyncIcalCalendarJob / ical:sync command
// ============================================================

describe('ical:sync command', function () {
    it('dispatches jobs for unsynced active calendars', function () {
        Queue::fake();

        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/calendar.ics',
            'is_active' => true,
            'last_synced_at' => null,
        ]);

        $this->artisan('ical:sync')->assertSuccessful();

        Queue::assertPushed(SyncIcalCalendarJob::class);
    });

    it('skips recently synced calendars', function () {
        Queue::fake();

        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/calendar.ics',
            'is_active' => true,
            'last_synced_at' => now()->subMinutes(2),
        ]);

        $this->artisan('ical:sync')->assertSuccessful();

        Queue::assertNotPushed(SyncIcalCalendarJob::class);
    });
});

describe('SyncIcalCalendarJob', function () {
    it('creates availability periods from fetched feed', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        $calendar = $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/calendar.ics',
        ]);

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:airbnb-123@airbnb.com',
            'DTSTART;VALUE=DATE:20241001',
            'DTEND;VALUE=DATE:20241005',
            'SUMMARY:Reserved',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        Http::fake(['*' => Http::response($ics, 200)]);

        (new SyncIcalCalendarJob($calendar))->handle(app(IcalSyncService::class));

        expect(
            AvailabilityPeriod::where('ical_calendar_id', $calendar->id)
                ->where('external_uid', 'airbnb-123@airbnb.com')
                ->exists()
        )->toBeTrue();

        // DTEND 20241005 is exclusive → stored end_date = 2024-10-04
        expect(
            AvailabilityPeriod::where('external_uid', 'airbnb-123@airbnb.com')
                ->first()->end_date->toDateString()
        )->toBe('2024-10-04');

        expect($calendar->fresh()->last_synced_at)->not->toBeNull();
    });

    it('updates existing period when uid already exists', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        $calendar = $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/calendar.ics',
        ]);

        $accommodation->availabilityPeriods()->create([
            'status' => 'blocked',
            'reason' => 'external_booking',
            'start_date' => '2024-10-01',
            'end_date' => '2024-10-04',
            'external_uid' => 'airbnb-123@airbnb.com',
            'ical_calendar_id' => $calendar->id,
        ]);

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'BEGIN:VEVENT',
            'UID:airbnb-123@airbnb.com',
            'DTSTART;VALUE=DATE:20241010',
            'DTEND;VALUE=DATE:20241015',
            'SUMMARY:Reserved',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        Http::fake(['*' => Http::response($ics, 200)]);

        (new SyncIcalCalendarJob($calendar))->handle(app(IcalSyncService::class));

        expect(AvailabilityPeriod::where('ical_calendar_id', $calendar->id)->count())->toBe(1);
        expect(
            AvailabilityPeriod::where('external_uid', 'airbnb-123@airbnb.com')
                ->first()->start_date->toDateString()
        )->toBe('2024-10-10');
    });

    it('removes stale periods no longer in the feed', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        $calendar = $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/calendar.ics',
        ]);

        $accommodation->availabilityPeriods()->create([
            'status' => 'blocked',
            'reason' => 'external_booking',
            'start_date' => '2024-11-01',
            'end_date' => '2024-11-03',
            'external_uid' => 'old-uid@airbnb.com',
            'ical_calendar_id' => $calendar->id,
        ]);

        Http::fake(['*' => Http::response("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nEND:VCALENDAR", 200)]);

        (new SyncIcalCalendarJob($calendar))->handle(app(IcalSyncService::class));

        expect(AvailabilityPeriod::where('ical_calendar_id', $calendar->id)->count())->toBe(0);
    });

    it('handles http fetch failure gracefully', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        $calendar = $accommodation->icalCalendars()->create([
            'source' => IcalSource::Airbnb->value,
            'ical_url' => 'https://airbnb.com/calendar.ics',
        ]);

        Http::fake(['*' => Http::response('', 500)]);

        (new SyncIcalCalendarJob($calendar))->handle(app(IcalSyncService::class));

        // last_synced_at should not be updated on failure
        expect($calendar->fresh()->last_synced_at)->toBeNull();
    });
});

// ============================================================
// Accommodation ical_token generation
// ============================================================

describe('Accommodation ical_token', function () {
    it('is auto-generated on creation', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);

        expect($accommodation->ical_token)
            ->not->toBeNull()
            ->toHaveLength(64);
    });
});
