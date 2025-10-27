<?php

namespace App\Helpers;

class CacheKeys {
    // every lock should start with "lock:" so they can be easily identified
    const RUNTIME_CONSTANTS = 'lock:RUNTIME_CONSTANTS';
}
