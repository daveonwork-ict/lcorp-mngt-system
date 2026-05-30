<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class EmployeeScheduleSpreadsheetFeatureTest extends TestCase
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

    public function test_branch_manager_can_download_schedule_template_with_branch_employees(): void
    {
        [$branch, $manager] = $this->makeBranchManagerWithSchedulePermissions();

        $employee = User::factory()->create([
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'username' => 'template.employee',
            'full_name' => 'Template Employee',
            'name' => 'Template Employee',
        ]);
        $employee->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        $response = $this->actingAs($manager)
            ->get(route('hr.schedules.template', ['branch_id' => $branch->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'schedule-template-').'.xlsx';
        file_put_contents($tempPath, $content);

        $spreadsheet = IOFactory::load($tempPath);
        $this->assertNotNull($spreadsheet->getSheetByName('Instructions'));
        $this->assertNotNull($spreadsheet->getSheetByName('Template'));

        $employeesSheet = $spreadsheet->getSheetByName('Branch Employees');
        $this->assertNotNull($employeesSheet);

        $found = false;
        foreach ($employeesSheet->toArray() as $row) {
            if (($row[2] ?? null) === 'template.employee') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Template should include current branch employees.');

        @unlink($tempPath);
    }

    public function test_branch_manager_can_import_schedule_from_excel_template(): void
    {
        [$branch, $manager] = $this->makeBranchManagerWithSchedulePermissions();

        $employee = User::factory()->create([
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'username' => 'excel.employee',
            'full_name' => 'Excel Employee',
            'name' => 'Excel Employee',
        ]);
        $employee->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');
        $sheet->fromArray([
            ['employee_username', 'schedule_date', 'schedule_type', 'time_in', 'time_out', 'break_start', 'break_end', 'is_rest_day', 'branch_code'],
            ['excel.employee', '2026-06-12', 'fixed', '08:00', '17:00', '12:00', '13:00', '0', (string) ($branch->code ?? $branch->branch_code)],
        ], null, 'A1');

        $tempPath = tempnam(sys_get_temp_dir(), 'schedule-import-').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $upload = new UploadedFile(
            $tempPath,
            'schedule-import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($manager)
            ->post(route('hr.schedules.import'), [
                'branch_id' => $branch->id,
                'file' => $upload,
            ])
            ->assertRedirect(route('hr.schedules.index', ['branch_id' => $branch->id]));

        $this->assertDatabaseHas('employee_schedules', [
            'user_id' => $employee->id,
            'branch_id' => $branch->id,
            'schedule_date' => '2026-06-12 00:00:00',
            'schedule_type' => 'fixed',
            'time_in' => '08:00',
            'time_out' => '17:00',
            'is_rest_day' => 0,
        ]);

        $this->assertSame(1, EmployeeSchedule::query()->where('user_id', $employee->id)->count());

        @unlink($tempPath);
    }

    private function makeBranchManagerWithSchedulePermissions(): array
    {
        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $role = Role::query()->where('code', 'branch_manager')->firstOrFail();

        $permissionIds = Permission::query()
            ->whereIn('code', ['view_schedules', 'manage_schedules'])
            ->pluck('id')
            ->all();

        $role->permissions()->syncWithoutDetaching($permissionIds);

        $manager = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'username' => 'branch.manager.schedule',
            'full_name' => 'Branch Manager Schedule',
            'name' => 'Branch Manager Schedule',
        ]);

        $manager->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        return [$branch, $manager];
    }
}
