# pubvana/comments

Nested, moderated comments for Pubvana with hCaptcha and reCAPTCHA support.

## Features

- Polymorphic comments on posts and pages
- Nested/threaded replies (configurable max depth, default 3)
- Guest comments (admin-configurable)
- Moderation workflow: auto-approve or pending queue (admin-configurable)
- Captcha support: hCaptcha, reCAPTCHA v2, or none
- HTMLPurifier sanitization on comment body
- Headless service: `$app->comments()`

## Configuration

In `app/config/config.php`:

```php
'pubvana/comments' => [
    'enabled'              => true,
    'priority'             => 55,
],
```

## Service API

```php
$comments = $app->comments();

$comments->findForContent('post', $postId);        // Threaded tree for display
$comments->create($data);                           // Create comment (validates depth, captcha, purifies)
$comments->approve($id);                            // Set status to approved
$comments->reject($id);                             // Set status to rejected
$comments->delete($id);                             // Hard delete
$comments->list($page, $perPage, $status);          // Admin listing
$comments->countByStatus($status);                  // Count by status
$comments->verifyCaptcha($token, $remoteIp);        // Manual captcha check
```

## Admin

Moderation panel at `/admin/comments` with status filters, approve/reject/delete actions, and comment detail view.

## Permissions

- `comments.moderate` - Approve, reject, and delete comments

## Requirements

- PHP ^8.1
- enlivenapp/flight-school ^0.2
- pubvana/admin
- enlivenapp/flight-shield
- enlivenapp/migrations
- flightphp/active-record ^0.7

## License

MIT
