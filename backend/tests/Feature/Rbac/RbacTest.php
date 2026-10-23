<?php

declare(strict_types=1);

namespace Tests\Feature\Rbac;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberTokenService;
use Modules\Rbac\Database\Seeders\RbacSeeder;
use Modules\Rbac\Support\RbacCatalog;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RbacTest extends TestCase
{
    use RefreshDatabase;

    private const DEMO = '/api/v1/rbac-demo/products';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seed(RbacSeeder::class);
    }

    /** Creates a verified member, optionally with a role */
    private function member(?string $role = null): Member
    {
        $member = Member::create([
            'full_name' => 'Test User',
            'email' => Str::random(8) . '@example.com',
            'password' => 'Secret123',
        ]);
        $member->forceFill(['email_verified_at' => now()])->save();

        if ($role !== null) {
            $member->assignRole(Role::findByName($role, RbacCatalog::GUARD));
        }

        return $member;
    }

    /** Builds the Authorization header with a real token from our existing token service */
    private function authFor(Member $member): array
    {
        $token = app(MemberTokenService::class)->issue($member, false)['access_token'];

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_seeder_creates_all_four_roles(): void
    {
        $this->assertSame(4, Role::where('guard_name', RbacCatalog::GUARD)->count());
    }

    public function test_request_without_token_returns_401(): void
    {
        $this->getJson(self::DEMO)
            ->assertStatus(401)
            ->assertJsonPath('type', 'UNAUTHENTICATED');
    }

    public function test_member_without_any_role_is_denied(): void
    {
        $this->withHeaders($this->authFor($this->member()))
            ->getJson(self::DEMO)
            ->assertStatus(403)
            ->assertJsonPath('type', 'FORBIDDEN');
    }

    public function test_sales_rep_can_view_products_but_not_delete_them(): void
    {
        $sales = $this->member(RbacCatalog::SALES_REPRESENTATIVE);

        $this->withHeaders($this->authFor($sales))
            ->getJson(self::DEMO)
            ->assertStatus(200);

        $this->withHeaders($this->authFor($sales))
            ->deleteJson(self::DEMO . '/1')
            ->assertStatus(403)
            ->assertJsonPath('type', 'FORBIDDEN');
    }

    public function test_inventory_manager_can_delete_products(): void
    {
        $manager = $this->member(RbacCatalog::INVENTORY_MANAGER);

        $this->withHeaders($this->authFor($manager))
            ->deleteJson(self::DEMO . '/1')
            ->assertStatus(200);
    }

    public function test_sales_rep_cannot_assign_roles(): void
    {
        $sales = $this->member(RbacCatalog::SALES_REPRESENTATIVE);
        $target = $this->member();

        $this->withHeaders($this->authFor($sales))
            ->postJson("/api/v1/members/{$target->id}/role", ['role' => 'Admin'])
            ->assertStatus(403);
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = $this->member(RbacCatalog::ADMIN);

        $this->withHeaders($this->authFor($admin))
            ->postJson("/api/v1/members/{$admin->id}/role", ['role' => 'Accountant'])
            ->assertStatus(403)
            ->assertJsonPath('type', 'CANNOT_CHANGE_OWN_ROLE');
    }

    public function test_delegate_cannot_grant_admin(): void
    {
        // A custom role that can assign roles but is not Admin
        $delegateRole = Role::create(['name' => 'HR Delegate', 'guard_name' => RbacCatalog::GUARD]);
        $delegateRole->givePermissionTo('members.roles.assign');

        $delegate = $this->member();
        $delegate->assignRole($delegateRole);
        $target = $this->member();

        $this->withHeaders($this->authFor($delegate))
            ->postJson("/api/v1/members/{$target->id}/role", ['role' => 'Admin'])
            ->assertStatus(403)
            ->assertJsonPath('type', 'ROLE_ESCALATION_DENIED');
    }

    public function test_admin_can_assign_a_role_to_another_member(): void
    {
        $admin = $this->member(RbacCatalog::ADMIN);
        $target = $this->member();

        $this->withHeaders($this->authFor($admin))
            ->postJson("/api/v1/members/{$target->id}/role", ['role' => 'Accountant'])
            ->assertStatus(200);

        $this->assertTrue($target->fresh()->hasRole(RbacCatalog::ACCOUNTANT, RbacCatalog::GUARD));
    }

    public function test_unknown_role_name_returns_422(): void
    {
        $admin = $this->member(RbacCatalog::ADMIN);
        $target = $this->member();

        $this->withHeaders($this->authFor($admin))
            ->postJson("/api/v1/members/{$target->id}/role", ['role' => 'Superuser'])
            ->assertStatus(422)
            ->assertJsonStructure(['data' => ['errors' => ['role']]]);
    }
}
