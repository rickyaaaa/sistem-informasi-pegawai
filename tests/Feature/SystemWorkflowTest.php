<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Satker;
use App\Models\PegawaiRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SystemWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat satker dummy
        $this->indukSatker = Satker::create(['nama_satker' => 'POLDA', 'level' => 'induk', 'tipe_satuan' => 'satker']);
        $this->subSatker = Satker::create(['nama_satker' => 'BAG SUMDA', 'level' => 'sub', 'parent_id' => $this->indukSatker->id, 'tipe_satuan' => 'satker']);

        // Buat Super Admin
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'status' => 'active'
        ]);

        // Buat Operator
        $this->operator = User::create([
            'name' => 'Operator Polda',
            'username' => 'operator_polda',
            'password' => bcrypt('password'),
            'role' => 'admin_satker',
            'satker_id' => $this->indukSatker->id,
            'status' => 'active'
        ]);
    }

    #[Test]
    public function alur_login_berhasil_untuk_user_aktif()
    {
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    #[Test]
    public function cek_akses_rbac_super_admin_vs_operator()
    {
        // Operator mencoba akses halaman Satker (Hanya untuk super_admin)
        $responseOperator = $this->actingAs($this->operator)->get('/satker');
        $responseOperator->assertStatus(403);

        // Super Admin mencoba akses halaman Satker
        $responseAdmin = $this->actingAs($this->superAdmin)->get('/satker');
        $responseAdmin->assertStatus(200);
    }

    #[Test]
    public function approval_workflow_berjalan_semestinya()
    {
        // 1. Operator membuat request tambah pegawai
        $this->actingAs($this->operator)->post('/pegawai', [
            'nama' => 'PNS Baru',
            'nik' => '1234567890123456',
            'jenis_kelamin' => 'Pria',
            'pendidikan' => 'S1',
            'satker_id' => $this->subSatker->id,
            'status' => 'aktif',
            'status_k2' => 'Non K-II',
        ]);

        // Pastikan request masuk ke database
        $this->assertDatabaseHas('pegawai_requests', [
            'requested_by' => $this->operator->id,
            'status' => 'pending',
            'action_type' => 'create'
        ]);

        $request = PegawaiRequest::where('status', 'pending')->first();

        // 2. Super Admin mendarat di halaman approval dan menyetujui
        $response = $this->actingAs($this->superAdmin)->post("/approvals/{$request->id}/approve");

        $response->assertSessionHas('success');

        // Pastikan status request menjadi approved
        $this->assertDatabaseHas('pegawai_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'approved_by' => $this->superAdmin->id,
        ]);

        // Pastikan data riil pegawai benar-benar dibuat
        $this->assertDatabaseHas('pegawais', [
            'nama' => 'PNS Baru',
            'nik' => '1234567890123456',
        ]);
    }
}
