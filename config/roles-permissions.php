<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return [
  'super_admin_name' => 'Super admin',

  'generator' => [

    'guard_names' => [
      'web',
    ],

    'permission_affixes' => [

      /*
             * Permissions Aligned with Policies.
             * DO NOT change the keys unless the genericPolicy.stub is published and altered accordingly
             */
      'viewAnyPermission' => 'view-any',
      'viewPermission' => 'view',
      'createPermission' => 'create',
      'updatePermission' => 'update',
      'deletePermission' => 'delete',
      'restorePermission' => 'restore',
      'forceDeletePermission' => 'force-delete',
    ],

    /*
         * returns the "name" for the permission.
         *
         * $permission which is an iteration of [permission_affixes] ,
         * $model The model to which the $permission will be concatenated
         *
         * Eg: 'permission_name' => 'return $permissionAffix . ' ' . Str::kebab($modelName),
         *
         * Note: If you are changing the "permission_name" , It's recommended to run with --clean to avoid duplications
         */
    'permission_name' => 'return $permissionAffix . \' \' . $modelName;',

    /*
         * Include directories which consists of models.
         */
    'model_directories' => [
      app_path('Models'),
      //app_path('Domains/Forum')
    ],

    /*
         * Define custom_models
         */
    'custom_models' => [
      // Role::class,
      // Permission::class,
    ],

    /*
         * Define excluded_models
         */
    'excluded_models' => [
      //
    ],

    'excluded_policy_models' => [
      //
    ],

    /*
         * Define any other permission that should be synced with the DB
         */
    'custom_permissions' => [
      //'view-log'
    ],

    'user_model' => \App\Models\User::class,

    'policies_namespace' => 'App\Policies',
  ],
];
