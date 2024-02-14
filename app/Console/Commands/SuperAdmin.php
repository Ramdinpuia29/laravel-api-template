<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class SuperAdmin extends Command
{

    protected $signature = 'role:super-admin
        {--user= : ID of user to be made super admin.}
    ';

    protected $description = 'Creates Super Admin';

    protected Authenticatable $superAdmin;

    public function handle()
    {
        $superAdminName = config('roles-permissions.super_admin_name');

        $roleModel = config('permission.models.role');

        $superAdminRoleExists = $roleModel::where('name', $superAdminName)->exists();

        if (!$superAdminRoleExists) {
            $guardNames = config('roles-permissions.generator.guard_names');

            foreach ($guardNames as $guardName) {
                $roleModel::create([
                    'name' => $superAdminName,
                    'guard' => $guardName
                ]);
            }
        }

        $userModel = config('roles-permissions.generator.user_model');

        $usersCount = $userModel::count();

        if ($this->option('user')) {
            $this->superAdmin = $userModel::findOrFail($this->option('user'));
        } elseif ($usersCount === 1) {
            $this->superAdmin = $userModel::first();
        } elseif ($usersCount > 1) {
            $this->table(
                ['ID', 'Name', 'Email', 'Roles'],
                $userModel::with('roles')->get()->map(function (Authenticatable $user) {
                    return [
                        'id' => $user->getAttribute('id'),
                        'name' => $user->getAttribute('name'),
                        'email' => $user->getAttribute('email'),
                        'roles' => implode(',', $user->roles->pluck('name')->toArray())
                    ];
                })
            );

            $superAdminId = text(
                label: 'Please provide the `UserId` to be set as `Super admin`',
                required: 'true'
            );

            $this->superAdmin = $userModel::findOrFail($superAdminId);
        } else {
            $this->superAdmin = $this->createSuperAdmin();
        }

        $this->superAdmin->assignRole($superAdminName);

        $this->components->info("Success! Super admin role assigned to {$this->superAdmin->email}");
    }

    protected function createSuperAdmin(): Authenticatable
    {
        $userModel = config('roles-permissions.generator.user_model');
        return $userModel::create([
            'name' => text(label: 'Name', required: true),
            'email' => text(label: 'Email', required: true, validate: fn (string $email): ?string => match (true) {
                !filter_var($email, FILTER_VALIDATE_EMAIL) => 'The email address must be valid.',
                $userModel::where('email', $email)->exists() => 'A user with this email already exists',
                default => null
            }),
            'password' => Hash::make(password(
                label: 'Password',
                required: true,
                validate: fn (string $value) => match (true) {
                    strlen($value) < 8 => 'The password must be at least 8 characters.',
                    default => null
                }
            ))
        ]);
    }
}
