<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateOperator extends Command
{
    protected $signature = 'users:create {name : Nama petugas} {email : Email untuk login}';

    protected $description = 'Buat akun petugas gudang dengan kata sandi tersembunyi';

    public function handle(): int
    {
        $name = trim($this->argument('name'));
        $email = Str::lower(trim($this->argument('email')));

        if ($name === '' || mb_strlen($name) > 255) {
            $this->error('Nama wajib diisi dan maksimal 255 karakter.');

            return self::FAILURE;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
            $this->error('Masukkan alamat email yang valid (maksimal 255 karakter).');

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('Email tersebut sudah terdaftar.');

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi (minimal 12 karakter)');

        if (! is_string($password) || mb_strlen($password) < 12) {
            $this->error('Kata sandi harus memiliki minimal 12 karakter.');

            return self::FAILURE;
        }

        if (! hash_equals($password, (string) $this->secret('Ulangi kata sandi'))) {
            $this->error('Konfirmasi kata sandi tidak cocok.');

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info("Akun petugas {$email} berhasil dibuat.");

        return self::SUCCESS;
    }
}
