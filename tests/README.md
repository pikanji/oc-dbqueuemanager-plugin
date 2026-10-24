# Job model regression tests

Run with an installed Winter CMS project and its PHPUnit 9.6 dependencies:

```sh
WINTER_CMS_PATH=/absolute/path/to/winter /absolute/path/to/winter/vendor/bin/phpunit -c phpunit.xml
```

When installed at `plugins/pikanji/dbqueuemanager`, WINTER_CMS_PATH can be omitted.
The bootstrap explicitly loads the Job model from this checkout, not another copy
installed in the host CMS. It does not require Goodbet.Gbc or SpyceTek.Utility.

The six cases cover reading JSON and Unix dates, saving an edited queue, writing
available_at as Unix seconds without changing created_at, deleting one selected
job, worker reservation/deletion, and delayed availability. The jobs table has no
updated_at, matching Winter's queue schema. Job timestamps are managed by the queue
worker, not automatic Eloquent created_at/updated_at handling.

Only SQLite :memory: and temporary local files are used. Tests do not boot the
application kernel, load .env, run migrations, connect to MySQL or AWS, or execute
job payloads. They do not cover MySQL locking or backend permissions/UI.

Verified with PHP 8.0.28, Winter 1.2.1, Laravel 9.52.16 and PHPUnit 9.6.15.
PHP 8.5 and other October/Winter versions require separate verification.
