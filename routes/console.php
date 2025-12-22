<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:send-weekly-summaries')->weekly()->mondays()->at('09:00');
