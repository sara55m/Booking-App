<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('bookings', function ($user) {
    return true;
});
