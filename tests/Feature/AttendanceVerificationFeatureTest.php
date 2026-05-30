<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceVerificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesSeeder::class,
            PermissionsSeeder::class,
            BranchSeeder::class,
        ]);
    }

    public function test_attendance_detail_preview_and_reverify_work_for_authorized_user(): void
    {
        Storage::fake();

        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $role = Role::query()->where('code', 'branch_manager')->firstOrFail();
        $this->grantRolePermissions($role, ['view_attendance']);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'full_name' => 'Verifier User',
            'name' => 'Verifier User',
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        $filePath = 'hr/attendance-selfies/attendance-test-in.jpg';
        $contents = 'attendance-selfie-binary';
        Storage::put($filePath, $contents);

        $payload = [
            'capture_type' => 'in',
            'user_id' => $user->id,
            'user_name' => 'Verifier User',
            'branch_id' => $branch->id,
            'branch_name' => $branch->branch_name ?? $branch->name,
            'attendance_date' => now()->toDateString(),
            'captured_at' => now()->toIso8601String(),
            'gps_latitude' => '15.4812365',
            'gps_longitude' => '120.5971123',
            'device_info_raw' => 'FeatureTest Browser',
            'ip_address' => '127.0.0.1',
            'file_path' => $filePath,
            'image_sha256' => hash('sha256', $contents),
            'recorded_at' => now()->toIso8601String(),
        ];

        $attendance = AttendanceLog::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'attendance_date' => now()->toDateString(),
            'time_in' => now()->toDateTimeString(),
            'selfie_time_in_path' => $filePath,
            'gps_latitude_in' => 15.4812365,
            'gps_longitude_in' => 120.5971123,
            'device_info_in' => ['raw' => 'FeatureTest Browser'],
            'capture_metadata_in' => [
                'algorithm' => 'hmac-sha256',
                'signature' => $this->signPayload($payload),
                'payload' => $payload,
            ],
            'ip_address_in' => '127.0.0.1',
            'attendance_status' => 'present',
        ]);

        $this->actingAs($user)
            ->get(route('hr.attendance.show', $attendance))
            ->assertOk()
            ->assertSee('Attendance Details')
            ->assertSee('Verified');

        $this->actingAs($user)
            ->get(route('hr.attendance.selfies.preview', ['attendance' => $attendance, 'captureType' => 'in']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');

        $this->actingAs($user)
            ->post(route('hr.attendance.reverify', $attendance))
            ->assertRedirect(route('hr.attendance.show', $attendance))
            ->assertSessionHas('status');
    }

    private function grantRolePermissions(Role $role, array $codes): void
    {
        $permissionIds = Permission::query()
            ->whereIn('code', $codes)
            ->pluck('id')
            ->all();

        $role->permissions()->syncWithoutDetaching($permissionIds);
    }

    private function signPayload(array $payload): string
    {
        $key = (string) config('app.key', 'attendance-capture-key');
        $key = str_starts_with($key, 'base64:')
            ? (string) base64_decode(substr($key, 7), true)
            : $key;

        return hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES), $key);
    }
}