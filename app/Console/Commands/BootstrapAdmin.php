<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Creates the first admin from FIRST_ADMIN_EMAIL / FIRST_ADMIN_NAME /
 * FIRST_ADMIN_PASSWORD when there is no admin yet. Run on every container
 * start (docker/start.sh), so a hosted server without a shell gets its first
 * login. Does nothing once any admin exists; remove the password variable then.
 */
class BootstrapAdmin extends Command
{
    protected $signature = 'admin:bootstrap';

    protected $description = 'Create the first admin from FIRST_ADMIN_* variables if none exists';

    public function handle(): int
    {
        if (Admin::query()->exists()) {
            $this->line('Admins already exist; nothing to do.');

            return self::SUCCESS;
        }

        $data = [
            'email' => (string) env('FIRST_ADMIN_EMAIL', ''),
            'name' => (string) env('FIRST_ADMIN_NAME', 'Admin'),
            'role' => 'super_admin',
            'password' => (string) env('FIRST_ADMIN_PASSWORD', ''),
        ];

        if ($data['email'] === '' || $data['password'] === '') {
            $this->line('No admin yet; set FIRST_ADMIN_EMAIL and FIRST_ADMIN_PASSWORD to create one.');

            return self::SUCCESS;
        }

        $validator = Validator::make($data, [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'min:10'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        Admin::create($data);
        $this->info("First admin {$data['email']} created.");

        return self::SUCCESS;
    }
}
