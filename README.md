**Tech Stack**
php, laravel

**Instructions**

1. Fill in .env using .env.example as template
2. Run `php artisan db:seed` to seed fake data initially for testing.
3. Run `php artisan queue:work` for asynchronous email sending (use queue_connection = sync otherwise).
4. Run `php artisan schedule:work` for testing the weekly email summary. Can also trigger using `php artisan app:send-weekly-summaries` with no scheduler daemon.

**My thoughts**

1. For feature tests, I covered almost all API endpoints except the last few like upload and download resume due to time constraints. I also did not completely test all request validation for POST APIs. I assumed that should be tested in unit tests rather than feature tests.
2. For the upload and download resume features, I did not implement presigned URLs due to time constraints. I used the Laravel server as a proxy server, which is not ideal.
3. For notifications, I implemented polling from the frontend instead of WebSockets due to time constraints.
4. Email verification should be done asynchronously, but I implemented it synchronously here.
5. In terms of database design, several aspects could be improved:
    - `salary_range` in job_listings should be split into two numeric columns to prevent nonsense values. Currently it's just a string.
    - For job_listing, more logic should govern status changes. For example, users shouldn't be able to change status or details once published to prevent fraudulent advertisements.
    - In the users table, I included `resume_path` and `can_upload` fields. The idea is that not all users can upload resumes because the initial plan used my personal R2 bucket. I would manually change the flag during demonstrations. For `resume_path`, it would be better as a separate table with a one-to-many relationship from users. This trades higher storage costs for the benefit of maintaining user resume history.
    - For the applications table, more logic should govern status changes. For example, rejected applications should not be able to be shortlisted again. A status change log would be beneficial so users can see timestamps of status transitions.
6. Reset password feature should be implemented as well.
