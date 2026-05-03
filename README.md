[![Stable? Not Quite Yet](https://img.shields.io/badge/stable%3F-not%20quite%20yet-blue?style=for-the-badge)](https://packagist.org/packages/pubvana/comments)
[![License](https://img.shields.io/packagist/l/pubvana/comments?style=for-the-badge)](https://packagist.org/packages/pubvana/comments)
[![PHP Version](https://img.shields.io/packagist/php-v/pubvana/comments?style=for-the-badge)](https://packagist.org/packages/pubvana/comments)
[![Monthly Downloads](https://img.shields.io/packagist/dm/pubvana/comments?style=for-the-badge)](https://packagist.org/packages/pubvana/comments)
[![Total Downloads](https://img.shields.io/packagist/dt/pubvana/comments?style=for-the-badge)](https://packagist.org/packages/pubvana/comments)
[![GitHub Issues](https://img.shields.io/github/issues/Pubvana-CMS/comments?style=for-the-badge)](https://github.com/Pubvana-CMS/comments/issues)
[![Contributors](https://img.shields.io/github/contributors/Pubvana-CMS/comments?style=for-the-badge)](https://github.com/Pubvana-CMS/comments/graphs/contributors)
[![Latest Release](https://img.shields.io/github/v/release/Pubvana-CMS/comments?style=for-the-badge)](https://github.com/Pubvana-CMS/comments/releases)
[![Contributions Welcome](https://img.shields.io/badge/contributions-welcome-blue?style=for-the-badge)](https://github.com/Pubvana-CMS/comments/pulls)

# pubvana/comments

**I noticed folks downloading some of these packages. I'm super grateful, Thank You!  I would like to let folks know until this notice disappears I'm doing a lot of breaking changes without worrying about them.  Once versions are up around 0.5.x things should settle down.**

Nested, moderated comments for Pubvana with hCaptcha and reCAPTCHA support.

## Features

- Polymorphic comments on posts and pages
- Nested/threaded replies (configurable max depth, default 3)
- Guest comments (admin-configurable)
- Moderation workflow: auto-approve or pending queue (admin-configurable)
- Captcha support: hCaptcha, reCAPTCHA v2, or none
- HTMLPurifier sanitization on comment body
- Comment service: `$app->comments()`

## Configuration

In `app/config/config.php`:

```php
'plugins' => [
    'pubvana/comments' => [
        'enabled'  => true,
        'priority' => 55,
    ],
],
```

This package uses Flight School's return-array config format. `src/Config/Config.php` returns the package defaults as an array, Flight School stores that array under `pubvana.comments` on `$app`, and the current public route prefix is defined there with `'routePrepend' => 'comments'`.

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
- enlivenapp/flight-settings
- enlivenapp/flight-shield
- enlivenapp/migrations
- ezyang/htmlpurifier
- flightphp/active-record ^0.7

## License

MIT
