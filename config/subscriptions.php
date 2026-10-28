<?php

return [
    'trial_days' => (int) env('SUBSCRIPTION_TRIAL_DAYS', 7),
    'grace_period_days' => (int) env('SUBSCRIPTION_GRACE_PERIOD_DAYS', 3),
    'renewal_notice_days' => (int) env('SUBSCRIPTION_RENEWAL_NOTICE_DAYS', 3),
];
