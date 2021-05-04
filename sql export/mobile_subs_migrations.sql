insert into mobile_subs.migrations (id, migration, batch)
values  (1, '2014_10_12_000000_create_users_table', 1),
        (2, '2014_10_12_100000_create_password_resets_table', 1),
        (3, '2019_08_19_000000_create_failed_jobs_table', 1),
        (4, '2021_05_01_214212_create_devices_table', 1),
        (6, '2021_05_01_232450_create_subscriptions_table', 2),
        (16, '2021_05_04_143439_create_jobs_table', 3),
        (17, '2021_05_04_153417_add_callback_url_to_devices_table', 3);