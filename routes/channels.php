<?php

use Illuminate\Support\Facades\Broadcast;

// Websocket broadcast channel
Broadcast::channel('csv-status.{guestId}', function () {
    return true;
});
