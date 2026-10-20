<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin {email? : Email administrator} {--name=Administrator : Nama tampilan}';

    protected $description = 'Membuat akun administrator atau mengatur ulang kata sandinya';

    public function handle(): int
    {
        $email = Str::lower((string) ($this->argument('email') ?: $this->ask('Email administrator')));
        $name = (string) ($this->option('name') ?: 'Administrator');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name],
            [
                'email' => ['required', 'email'],
                'name' => ['required', 'string', 'max:255'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi (minimal 12 karakter)');
        $confirmation = $this->secret('Ulangi kata sandi');

        if (! is_string($password) || mb_strlen($password) < 12) {
            $this->error('Kata sandi minimal 12 karakter.');

            return self::FAILURE;
        }

        if ($password !== $confirmation) {
            $this->error('Konfirmasi kata sandi tidak cocok.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        $this->info(
            ($user->wasRecentlyCreated ? 'Akun administrator dibuat: ' : 'Kata sandi diperbarui: ').$email
        );

        return self::SUCCESS;
    }
}
