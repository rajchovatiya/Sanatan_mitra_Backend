<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/** The first admin is created here; later admins from the panel. */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {name} {--role=super_admin : super_admin or editor}';

    protected $description = 'Create an admin panel user (asks for the password)';

    public function handle(): int
    {
        $data = [
            'email' => $this->argument('email'),
            'name' => $this->argument('name'),
            'role' => $this->option('role'),
            'password' => $this->secret('Password (min 10 characters)'),
        ];

        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'unique:admins,email'],
            'name' => ['required', 'string', 'max:80'],
            'role' => ['required', 'in:'.implode(',', Admin::ROLES)],
            'password' => ['required', 'string', 'min:10'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        Admin::create($data);
        $this->info("Admin {$data['email']} created.");

        return self::SUCCESS;
    }
}
