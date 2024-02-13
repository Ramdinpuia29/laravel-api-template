<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class Permission extends Command
{

    private mixed $config;

    private array $permissions = [];

    private array $policies = [];

    protected $signature = 'permissions:sync
                                {--C|clean}
                                {--P|policies}
                                {--O|oep}
                                {--Y|yes-to-all}';

    protected $description = 'Generates permissions through Models';

    public function __construct()
    {
        parent::__construct();
        $this->config = config('roles-permissions.generator');
    }

    public function handle(): void
    {
        $classes = $this->getAllModels();

        $classes = array_diff($classes, $this->getExcludedModels());

        $this->deleteExistingPermissions();

        $this->prepareClassPermissionsAndPolicies($classes);

        $this->prepareCustomPermissions();

        $permissionModel = config('permission.models.permission');

        foreach ($this->permissions as $permission) {
            $this->comment('Syncing Permission for: ' . $permission['name']);
            $permissionModel::firstOrCreate($permission);
        }
    }

    public function deleteExistingPermissions(): void
    {
        if ($this->option('clean')) {
            if ($this->option('yes-to-all') || $this->confirm('This will delete existing permissions. Do you want to continue?', false)) {
                $this->comment('Deleting Permissions');
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::table(config('permission.table_names.permissions'))->truncate();
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    $this->comment('Deleted Permissions');
                } catch (\Exception $exception) {
                    $this->warn($exception->getMessage());
                }
            }
        }
    }

    public function prepareClassPermissionsAndPolicies($classes): void
    {
        $filesystem = new Filesystem();

        // Ensure the policies folder exists
        File::ensureDirectoryExists(app_path('Policies/'));

        foreach ($classes as $model) {
            $modelName = $model->getShortName();

            $stub = '/stubs/genericPolicy.stub';
            $contents = $filesystem->get(__DIR__ . $stub);

            foreach ($this->permissionAffixes() as $key => $permissionAffix) {
                foreach ($this->guardNames() as $guardName) {
                    $permission = eval($this->config['permission_name']);
                    $this->permissions[] = [
                        'name' => $permission,
                        'guard_name' => $guardName
                    ];

                    if ($this->option('policies')) {
                        $contents = Str::replace('{{ ' . $key . ' }}', $permission, $contents);
                    }
                }
            }

            if ($this->option('policies') || $this->option('yes-to-all')) {
                $policyVariables = [
                    'class' => $modelName . 'Policy',
                    'namespacedModel' => $model->getName(),
                    'namespacedUserModel' => (new ReflectionClass($this->config['user_model']))->getName(),
                    'namespace' => $this->config['policies_namespace'],
                    'user' => 'User',
                    'model' => $modelName,
                    'modelVariable' => $modelName == 'User' ? 'model' : Str::lower($modelName)
                ];

                foreach ($policyVariables as $search => $replace) {
                    if ($modelName == 'User' && $search == 'namespacedModel') {
                        $contents = Str::replace('use {{ namespacedModel }};', '', $contents);
                    } else {
                        $contents = Str::replace('{{ ' . $search . ' }}', $replace, $contents);
                    }
                }

                if ($filesystem->exists(app_path('Policies/' . $modelName . 'Policy.php'))) {
                    if ($this->option('oep')) {
                        $filesystem->put(app_path('Policies/' . $modelName . 'Policy.php'), $contents);
                        $this->comment('Overriding Existing Policy: ' . $modelName);
                    } else {
                        $this->warn('Policy already exists for: ' . $modelName);
                    }
                } else {
                    $filesystem->put(app_path('Policies/' . $modelName . 'Policy.php'), $contents);
                    $this->comment('Creating Policy: ' . $modelName);
                }
            }
        }
    }

    public function prepareCustomPermissions(): void
    {
        foreach ($this->getCustomPermissions() as $customPermission) {
            foreach ($this->guardNames() as $guardName) {
                $this->permissions[] = [
                    'name' => $customPermission,
                    'guard_name' => $guardName
                ];
            }
        }
    }

    public function getModels(): array
    {
        $models = [];

        foreach ($this->config['model_directories'] as $directory) {
            $models = array_merge($models, $this->getClassesInDirectory($directory));
        }

        return $models;
    }

    private function getClassesInDirectory($path): array
    {
        $files = File::files($path);
        $models = [];

        foreach ($files as $file) {
            $namespace = $this->extractNamespace($file);
            $class = $namespace . '\\' . $file->getFilenameWithoutExtension();
            $model = new ReflectionClass($class);
            if (!$model->isAbstract()) {
                $models[] = $model;
            }
        }

        return $models;
    }

    private function permissionAffixes(): array
    {
        return $this->config['permission_affixes'];
    }

    private function guardNames(): array
    {
        return $this->config['guard_names'];
    }

    private function getCustomModels(): array
    {
        return $this->getModelReflections($this->config['custom_models']);
    }

    private function getCustomPermissions(): array
    {
        return $this->config['custom_permissions'];
    }

    private function getExcludedModels(): array
    {
        return $this->getModelReflections($this->config['excluded_models']);
    }

    private function getModelReflections($array): array
    {
        return array_map(function ($classes) {
            return new \ReflectionClass($classes);
        }, $array);
    }

    private function extractNamespace($file): string
    {
        $ns = '';
        $handle = fopen($file, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/namespace\s+([a-zA-Z0-9_\\\\]+);/', $line, $matches)) {
                    $ns = $matches[1];
                    break;
                }
            }
            fclose($handle);
        }
        return $ns;
    }

    public function getAllModels(): array
    {
        $models = $this->getModels();
        $customModels = $this->getCustomModels();

        return array_merge($models, $customModels);
    }
}
