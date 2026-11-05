<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class OperatorSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('operator.name'));
        $email = Str::lower(trim((string) config('operator.email')));
        $password = (string) config('operator.password');

        if ($name === '' || $email === '' || $password === '') {
            throw new RuntimeException(
                'Set OPERATOR_NAME, OPERATOR_EMAIL, and OPERATOR_PASSWORD before running the database seeder.'
            );
        }

        if (mb_strlen($name) > 255) {
            throw new RuntimeException('OPERATOR_NAME must be at most 255 characters.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
            throw new RuntimeException('OPERATOR_EMAIL must be a valid email address of at most 255 characters.');
        }

        if (mb_strlen($password) < 12) {
            throw new RuntimeException('OPERATOR_PASSWORD must contain at least 12 characters.');
        }

        User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );
    }
}
