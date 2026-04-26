<?php

namespace Tests\Feature\Auth;

use App\Models\Satker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression test: guards against the double-hashing bug where
 * UserController manually called Hash::make() AND the User model's
 * 'hashed' cast called Hash::make() a second time, producing a
 * bcrypt-of-bcrypt hash that can never be verified on login.
 */
class PasswordHashingTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_PASSWORD = 'polri2026';

    /**
     * Create a minimal induk Satker to satisfy the admin_satker FK constraint.
     */
    private function createSatker(): Satker
    {
        return Satker::create([
            'nama_satker' => 'SATKER TEST',
            'tipe_satuan' => 'satker',
            'level'       => 'induk',
            'parent_id'   => null,
        ]);
    }

    /** @test */
    public function operator_password_is_stored_as_single_bcrypt_hash(): void
    {
        $satker = $this->createSatker();

        // Simulate super_admin making the request
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        // Hit the UserController@store endpoint exactly as the form would
        $response = $this->post(route('users.store'), [
            'name'                  => 'Operator Test',
            'username'              => 'operator_test',
            'password'              => self::TEST_PASSWORD,
            'password_confirmation' => self::TEST_PASSWORD,
            'role'                  => 'admin_satker',
            'satker_id'             => $satker->id,
            'status'                => 'active',
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::where('username', 'operator_test')->firstOrFail();

        // ── Assert 1: stored hash must verify against the plain-text password ──
        $this->assertTrue(
            Hash::check(self::TEST_PASSWORD, $user->password),
            'Hash::check() failed — password is likely double-hashed.'
        );

        // ── Assert 2: the hash must NOT itself be a valid bcrypt string of another hash ──
        // If double-hashed, Hash::check(Hash::make(plain), stored) would pass.
        // We assert the INVERSE — checking the already-hashed value should FAIL.
        $this->assertFalse(
            Hash::check(Hash::make(self::TEST_PASSWORD), $user->password),
            'Double-hash detected: the stored value verifies against Hash::make(plain) instead of plain.'
        );

        // ── Assert 3: Auth::attempt() must succeed ────────────────────────────
        $authenticated = Auth::attempt([
            'username' => 'operator_test',
            'password' => self::TEST_PASSWORD,
        ]);

        $this->assertTrue(
            $authenticated,
            'Auth::attempt() failed — user cannot log in with the correct password.'
        );
    }

    /** @test */
    public function updating_operator_password_via_controller_produces_single_hash(): void
    {
        $satker = $this->createSatker();

        // Create an operator directly (model cast handles hashing correctly)
        $operator = User::create([
            'name'      => 'Operator Lama',
            'username'  => 'operator_lama',
            'password'  => self::TEST_PASSWORD,
            'role'      => 'admin_satker',
            'satker_id' => $satker->id,
            'status'    => 'active',
        ]);

        $newPassword = 'newpassword2026';

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        $response = $this->put(route('users.update', $operator), [
            'name'                  => 'Operator Lama',
            'username'              => 'operator_lama',
            'password'              => $newPassword,
            'password_confirmation' => $newPassword,
            'role'                  => 'admin_satker',
            'satker_id'             => $satker->id,
            'status'                => 'active',
        ]);

        $response->assertRedirect(route('users.index'));

        $operator->refresh();

        $this->assertTrue(
            Hash::check($newPassword, $operator->password),
            'Updated password hash failed Hash::check() — likely double-hashed on update.'
        );

        $authenticated = Auth::attempt([
            'username' => 'operator_lama',
            'password' => $newPassword,
        ]);

        $this->assertTrue(
            $authenticated,
            'Auth::attempt() failed after password update.'
        );
    }
}
