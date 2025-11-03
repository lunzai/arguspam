<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class PolicyPermissionService
{
    /**
     * Get all policy files and their permissions
     */
    protected function getPolicyPermissions(): Collection
    {
        $policyPath = app_path('Policies');
        $policyFiles = glob($policyPath.'/*Policy.php');

        return collect($policyFiles)->flatMap(function ($file) {
            $policyClass = 'App\\Policies\\'.basename($file, '.php');
            $modelName = Str::before(class_basename($file), 'Policy');
            if (!class_exists($policyClass)) {
                return [];
            }
            return $this->getPermissionsFromPolicy($policyClass, $modelName);
        });
    }

    /**
     * Get permissions from a policy class
     */
    protected function getPermissionsFromPolicy(string $policyClass, string $modelName): Collection
    {
        try {
            $reflection = new ReflectionClass($policyClass);
            return collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
                ->filter(function (ReflectionMethod $method) use ($policyClass) {
                    if ($method->getDeclaringClass()->getName() !== $policyClass) {
                        return false;
                    }
                    if (str_starts_with($method->getName(), '__')) {
                        return false;
                    }
                    return $method->getNumberOfParameters() >= 1;
                })
                ->map(function (ReflectionMethod $method) use ($modelName) {
                    $name = "{$modelName}:{$method->getName()}";
                    return [
                        'name' => strtolower($name),
                        'description' => Str::of($name)
                            ->headline()
                            ->replace(':', ': ')
                            ->title(),
                    ];
                });
        } catch (\ReflectionException $e) {
            return collect();
        }
    }

    /**
     * Get changes that would be made during sync
     */
    public function getChanges(bool $removeOthers = false): array
    {
        $policyPermissions = $this->getPolicyPermissions();
        $existingPermissions = Permission::all()->map(function ($permission) {
            return [
                'name' => $permission->name,
                'description' => $permission->description,
                'id' => $permission->id,
            ];
        });

        $toAdd = $policyPermissions->filter(function ($permission) use ($existingPermissions) {
            return !$existingPermissions->contains('name', $permission['name']);
        });

        $toRemove = $removeOthers ?
            $existingPermissions->filter(function ($permission) use ($policyPermissions) {
                return !$policyPermissions->contains('name', $permission['name']);
            }) :
            collect();

        $unchanged = $existingPermissions->filter(function ($permission) use ($toRemove) {
            return !$toRemove->contains('id', $permission['id']);
        });

        return [
            'to_add' => $toAdd->values(),
            'to_remove' => $toRemove->values(),
            'unchanged' => $unchanged->values(),
        ];
    }

    /**
     * Sync permissions with policy methods
     */
    public function syncPermissions(bool $removeOthers = false): array
    {
        $changes = $this->getChanges($removeOthers);

        if ($changes['to_add']->isNotEmpty()) {
            $now = now();
            $newPermissions = $changes['to_add']->map(function ($permission) use ($now) {
                return [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

            Permission::insert($newPermissions->toArray());
        }

        if ($removeOthers && $changes['to_remove']->isNotEmpty()) {
            Permission::whereIn('id', $changes['to_remove']->pluck('id'))->delete();
        }

        return $changes;
    }
}
