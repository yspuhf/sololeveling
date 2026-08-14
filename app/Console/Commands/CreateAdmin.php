<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Super Admin account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment('testing')) {
            $name = 'Test Admin';
            $email = 'admin@test.com';
            $password = 'password123';
        } else {
            $name = $this->ask('Enter Admin Name');
            $email = $this->ask('Enter Admin Email');
            $password = $this->secret('Enter Admin Password');
        }

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // Fetch or create super_admin role
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        // Create Admin user
        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Attach role
        $admin->roles()->attach($role->id);

        $this->info("Super Admin account successfully created for {$email}!");
        return 0;
    }
}
