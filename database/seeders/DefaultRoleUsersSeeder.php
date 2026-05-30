<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DefaultRoleUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $headOfficeCode = env('RMS_DEFAULT_BRANCH_CODE', 'MAIN');
        $headOfficeBranch = Branch::query()->where('code', $headOfficeCode)->first();
        $defaultPassword = env('RMS_DEFAULT_ROLE_PASSWORD', 'ChangeMe123!');
        $branchDefaultEmailPrefix = env('RMS_BRANCH_DEFAULT_USER_EMAIL_PREFIX', 'default');
        $branchDefaultEmailDomain = env('RMS_BRANCH_DEFAULT_USER_EMAIL_DOMAIN', 'rcstore.local');
        $branchDefaultUsernamePrefix = env('RMS_BRANCH_DEFAULT_USER_USERNAME_PREFIX', 'default_');
        $branchDefaultNameSuffix = env('RMS_BRANCH_DEFAULT_USER_NAME_SUFFIX', 'Default User');
        $defaultBranchRoleCode = Str::lower((string) env('RMS_BRANCH_DEFAULT_USER_ROLE_CODE', 'staff_user'));
        $rawBranchRoleMap = json_decode((string) env('RMS_BRANCH_DEFAULT_USER_ROLE_MAP', '[]'), true);

        $branchRoleMap = [];

        if (is_array($rawBranchRoleMap)) {
            foreach ($rawBranchRoleMap as $branchCode => $roleCode) {
                $normalizedBranchCode = Str::upper((string) $branchCode);
                $normalizedRoleCode = Str::lower(trim((string) $roleCode));

                if ($normalizedBranchCode !== '' && $normalizedRoleCode !== '') {
                    $branchRoleMap[$normalizedBranchCode] = $normalizedRoleCode;
                }
            }
        }

        $headOfficeRole = Role::query()->where('code', 'super_admin')->first();

        if ($headOfficeRole) {
            $headOfficeName = env('RMS_SUPER_ADMIN_NAME', 'Head Office Administrator');
            $headOfficeFirstName = env('RMS_SUPER_ADMIN_FIRST_NAME', Str::of($headOfficeName)->before(' ')->toString());
            $headOfficeLastName = env('RMS_SUPER_ADMIN_LAST_NAME', Str::of($headOfficeName)->afterLast(' ')->toString());

            $headOfficeUser = User::query()->updateOrCreate(
                ['email' => env('RMS_SUPER_ADMIN_EMAIL', 'superadmin@rcstore.local')],
                [
                    'name' => $headOfficeName,
                    'employee_code' => env('RMS_SUPER_ADMIN_EMPLOYEE_CODE', 'EMP-SUPERADMIN-001'),
                    'first_name' => $headOfficeFirstName,
                    'last_name' => $headOfficeLastName,
                    'full_name' => $headOfficeName,
                    'username' => env('RMS_SUPER_ADMIN_USERNAME', 'superadmin'),
                    'password' => Hash::make(env('RMS_SUPER_ADMIN_PASSWORD', $defaultPassword)),
                    'role_id' => $headOfficeRole->id,
                    'primary_branch_id' => $headOfficeBranch?->id,
                    'status' => 'active',
                    'is_active' => true,
                ]
            );

            if ($headOfficeBranch) {
                $headOfficeUser->branches()->syncWithoutDetaching([
                    $headOfficeBranch->id => ['is_primary' => true],
                ]);
            }
        }

        $roleCodes = collect($branchRoleMap)
            ->values()
            ->push($defaultBranchRoleCode)
            ->push('staff_user')
            ->unique()
            ->values()
            ->all();

        $rolesByCode = Role::query()
            ->whereIn('code', $roleCodes)
            ->get()
            ->keyBy('code');

        $defaultBranchRole = $rolesByCode->get($defaultBranchRoleCode) ?? $rolesByCode->get('staff_user');

        if (! $defaultBranchRole) {
            return;
        }

        $branches = Branch::query()
            ->when($headOfficeBranch, fn ($query) => $query->where('id', '!=', $headOfficeBranch->id))
            ->get();

        foreach ($branches as $branch) {
            $branchCode = Str::upper((string) ($branch->code ?? $branch->branch_code ?? 'BR'.$branch->id));
            $branchToken = Str::of($branchCode)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
            $branchToken = $branchToken !== '' ? $branchToken : 'branch'.$branch->id;

            $displayName = (string) ($branch->name ?? $branch->branch_name ?? 'Branch '.$branch->id);
            $defaultUserName = trim($displayName.' '.$branchDefaultNameSuffix);
            $branchDefaultEmail = trim($branchDefaultEmailPrefix) !== ''
                ? trim($branchDefaultEmailPrefix).'.'.$branchToken.'@'.$branchDefaultEmailDomain
                : $branchToken.'@'.$branchDefaultEmailDomain;
            $branchDefaultUsername = trim($branchDefaultUsernamePrefix) !== ''
                ? trim($branchDefaultUsernamePrefix).$branchToken
                : $branchToken;
            $assignedRoleCode = $branchRoleMap[$branchCode] ?? $defaultBranchRole->code;
            $assignedRole = $rolesByCode->get($assignedRoleCode) ?? $defaultBranchRole;

            $user = User::query()->updateOrCreate(
                ['email' => $branchDefaultEmail],
                [
                    'name' => $defaultUserName,
                    'employee_code' => 'EMP-'.$branchCode.'-001',
                    'first_name' => 'Default',
                    'last_name' => 'User',
                    'full_name' => $defaultUserName,
                    'username' => $branchDefaultUsername,
                    'password' => Hash::make($defaultPassword),
                    'role_id' => $assignedRole->id,
                    'primary_branch_id' => $branch->id,
                    'status' => 'active',
                    'is_active' => true,
                ]
            );

            $user->branches()->syncWithoutDetaching([
                $branch->id => ['is_primary' => true],
            ]);
        }
    }
}
