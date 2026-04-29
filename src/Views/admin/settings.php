<?php
/** @var array $settings */

$val = function (string $key, $default = '') use ($settings) {
    return $settings[$key]['value'] ?? $default;
};
?>

<form method="post" action="/admin/comments/settings">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">General</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="allow_guest_comments" value="1" class="form-check-input" <?= $val('allow_guest_comments') ? 'checked' : '' ?>>
                            <span class="form-check-label">Allow guest comments</span>
                        </label>
                        <small class="form-hint">Allow non-logged-in users to post comments.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Default Status</label>
                        <select name="default_status" class="form-select">
                            <option value="pending" <?= $val('default_status', 'pending') === 'pending' ? 'selected' : '' ?>>Pending (require moderation)</option>
                            <option value="approved" <?= $val('default_status', 'pending') === 'approved' ? 'selected' : '' ?>>Approved (auto-publish)</option>
                        </select>
                        <small class="form-hint">Status assigned to new comments.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Max Nesting Depth</label>
                        <input type="number" name="max_nesting_depth" class="form-control" min="1" max="10" step="1" value="<?= (int) $val('max_nesting_depth', 3) ?>">
                        <small class="form-hint">Maximum reply depth for threaded comments.</small>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Captcha</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Provider</label>
                        <select name="captcha_provider" class="form-select">
                            <option value="none" <?= $val('captcha_provider', 'none') === 'none' ? 'selected' : '' ?>>None</option>
                            <option value="hcaptcha" <?= $val('captcha_provider', 'none') === 'hcaptcha' ? 'selected' : '' ?>>hCaptcha</option>
                            <option value="recaptcha" <?= $val('captcha_provider', 'none') === 'recaptcha' ? 'selected' : '' ?>>reCAPTCHA v2</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Site Key</label>
                        <input type="text" name="captcha_site_key" class="form-control" value="<?= htmlspecialchars($val('captcha_site_key')) ?>" placeholder="Public site key">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="password" name="captcha_secret_key" class="form-control" value="<?= htmlspecialchars($val('captcha_secret_key')) ?>" placeholder="Secret key">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-outline-primary">
            <i class="ti ti-device-floppy me-1"></i>Save Settings
        </button>
    </div>
</form>
