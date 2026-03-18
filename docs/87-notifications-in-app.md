# 87 — In-App Notification System

Bell icon in the Navbar with a red unread badge, dropdown panel showing notifications, 60-second polling. Backed by Laravel's database notification channel.

---

## What Was Built

### Backend

#### `InAppNotification` (abstract base)
`app/Notifications/InAppNotification.php`

All notification classes extend this. It locks down `via()` (always `database`) and `toArray()` as `final` — subclasses only implement `toData(): array`.

#### `NotificationType` enum
`app/Enums/Notification/NotificationType.php`

Typed constants for every notification type. Adding a new type means adding a case here + a `label()` entry.

```php
enum NotificationType: string
{
    case AccommodationApproved = 'accommodation_approved';
    case BookingReceived       = 'booking_received';
    case BookingConfirmed      = 'booking_confirmed';
    case BookingCancelled      = 'booking_cancelled';
}
```

#### Notification classes
Each is ~15 lines — constructor injection + `toData()` only.

| Class | Dispatched from | Recipient |
|---|---|---|
| `AccommodationApprovedNotification` | `CreateAccommodation` job | Host |
| `BookingReceivedNotification` | `SendBookingCreatedNotifications` | Host (always) |
| `BookingConfirmedNotification` | `SendBookingCreatedNotifications` (instant) + `SendBookingConfirmedNotifications` (request approval) | Guest |
| `BookingCancelledNotification` | `SendBookingCancelledNotifications` | Both guest and host |

#### `NotificationController`
`app/Http/Controllers/NotificationController.php`

| Method | Route | Description |
|---|---|---|
| `index` | `GET /api/notifications` | Last 50 notifications + unread count |
| `markAsRead` | `POST /api/notifications/{id}/read` | Mark one as read |
| `markAllRead` | `POST /api/notifications/read-all` | Mark all as read |

Response shape from `index`:
```json
{
  "data": [
    {
      "id": "uuid",
      "type": "booking_received",
      "data": { "...payload..." },
      "read": false,
      "created_at": "2026-03-18T10:00:00+00:00"
    }
  ],
  "unread_count": 3
}
```

Note: returns raw `response()->json()`, not wrapped in `ApiResponse`.

#### Notification data payloads

```
accommodation_approved  → { type, accommodation_id, title }
booking_received        → { type, booking_id, accommodation_id, accommodation_title, guest_name, check_in, check_out, is_instant }
booking_confirmed       → { type, booking_id, accommodation_id, accommodation_title, check_in, check_out }
booking_cancelled       → { type, booking_id, accommodation_id, accommodation_title, check_in, check_out }
```

#### Storage
Uses Laravel's built-in `notifications` table (database channel). Migration ran 2026-03-18. `User` model already had the `Notifiable` trait.

---

### Frontend

#### Vuex module — `notifications`
`resources/js/store/modules/notifications/`

Standard 6-file module structure. Registered in `store/index.js`.

- **State:** `notifications[]`, `unreadCount`
- **Getters:** `unreadBadge` (returns number or `"99+"`), `hasUnread`
- **Actions:** `fetchNotifications`, `markAsRead(id)`, `markAllRead`

apiClient URL mapping:
```js
apiClient.notifications.get()            // GET  /api/notifications
apiClient.notifications[uuid].read.post() // POST /api/notifications/{uuid}/read
apiClient.notifications.readAll.post()   // POST /api/notifications/read-all
```

Reading the response (raw json, not ApiResponse envelope):
```js
commit(types.SET_NOTIFICATIONS, response.data.data);
commit(types.SET_UNREAD_COUNT, response.data.unread_count);
```

#### `NotificationDropdown.vue`
`resources/js/src/components/NotificationDropdown.vue`

- Bell SVG icon with red badge (shows `unreadBadge`, hidden when zero)
- Click-outside dismissal via `document` listener + `ref="dropdownRef"`
- Dropdown lists last 50 notifications; unread rows have green tint + green dot
- Per-type inline SVG icons and colors
- Clicking a notification marks it read then navigates:
  - `accommodation_approved` / `booking_received` → hosting home
  - `booking_confirmed` / `booking_cancelled` → bookings list
- "Mark all as read" button appears when `hasUnread`

#### Navbar polling
`resources/js/src/components/Navbar.vue`

- `startPolling()` fetches immediately then every **60 seconds** via `setInterval`
- `stopPolling()` clears the timer on logout / `beforeUnmount`
- Watcher on `isLoggedIn` starts/stops polling automatically

---

## How to Add a New Notification Type

1. Add a case to `App\Enums\Notification\NotificationType` with a `label()`
2. Create `app/Notifications/YourNotification.php` extending `InAppNotification`, implement `toData()`
3. Call `$user->notify(new YourNotification($model))` in the relevant listener or job
4. In `NotificationDropdown.vue` add entries to `titleFor`, `subtitleFor`, `iconPathFor`, `colorFor`, and `navigateTo`

---

## Switching to WebSockets

When WebSockets are ready, replace `startPolling()` / `stopPolling()` in `Navbar.vue` with a socket subscription that dispatches `fetchNotifications` (or directly commits the payload). The Vuex store and `NotificationDropdown` require no changes.
