# Lost & Found Hub - OTP login, separate Admin area, Myanmar profiles

## Install
1. Copy these folders into your project root (next to `index.html`, `api/`, `uploads/`):
   `admin/`, `user/`, `includes/`, `storage/`, and the new `config/mail.php`.
   Your existing `config/database.php` is NOT changed.
2. Database
   - New database: import `database/lost_and_found_hub.sql`.
   - Already imported: run `database/migration_otp_profiles.sql` once.
3. Edit `config/mail.php`:
   - `MAIL_DRIVER = 'log'` writes OTP emails to `storage/mail.log` (testing, no email sent).
   - `MAIL_DRIVER = 'smtp'` + SMTP settings (Gmail needs an App Password) sends real email.
   - Change `OTP_PEPPER` to a long random string.
4. Make sure `storage/` is writable by PHP. Set a real email for each account that must sign in.

## URLs
- Users: `/user/register.php`, `/user/login.php`
- Admin: `/admin/login.php` (accounts with `role = 'admin'` only)
- Demo accounts come from the seed data (`admin`, `sarah`, `marcus`, `elena`; see the comment above the INSERT in the SQL).
  Their emails are fake, so update them or use the `log` driver and read the code in `storage/mail.log`.

## Login flow
password check -> 6-digit OTP emailed (hashed in DB, 5 min expiry, 5 attempts, 60 s resend wait)
-> OTP verified -> session created (new session id).
