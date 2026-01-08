<?php
namespace App\Services;

class GuidService {
    public static function generate(): string {
        return bin2hex(random_bytes(16));
    }
}
