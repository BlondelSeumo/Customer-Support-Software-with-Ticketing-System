<?php

use App\User;
use Common\Auth\Permissions\Permission;
use Common\Auth\Roles\Role;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;

class DemoUserSeeder extends Seeder
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Faker
     */
    private $faker;

    /**
     * @var Role
     */
    private $role;

    /**
     * @param User $user
     * @param Role $role
     * @param Filesystem $fs
     * @param Faker $faker
     */
    public function __construct(User $user, Role $role, Filesystem $fs, Faker $faker)
    {
        $this->fs = $fs;
        $this->user = $user;
        $this->faker = $faker;
        $this->role = $role;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedSpecialDemoUsers();
        $userIds = $this->seedDemoCustomers();
        $this->attachCustomerGroupToUsers($userIds);
    }

    /**
     * Seed special users (agent, admin) for demo site.
     */
    private function seedSpecialDemoUsers()
    {
        $password = Hash::make('demo');

        $adminUser = $this->user->create([
            'email' => 'admin@demo.com',
            'password' => $password,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ]);
        $adminUser->permissions()->sync([Permission::where('name', 'admin')->first()->id]);

        $agentUser = $this->user->create([
            'email' => 'agent@demo.com',
            'password' => $password,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ]);
        $agentGroup = $this->role->where('name', 'agents')->first();
        $agentUser->roles()->attach($agentGroup->id);
    }

    /**
     * @return Collection
     */
    private function seedDemoCustomers()
    {
        $users = collect([]);
        $password = Hash::make('demo');

        for ($i = 0; $i <= 30; $i++) {
            $email = $i === 0 ? 'customer@demo.com' : $this->faker->email;
            $users->push([
                'email' => $email,
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'password' => $password,
            ]);
        }

        $this->user->insert($users->toArray());

        return $this->user->whereIn('email', $users->pluck('email'))->get()->pluck('id');
    }

    /**
     * @param Collection $userIds
     */
    private function attachCustomerGroupToUsers($userIds)
    {
        $customerGroup = $this->role->where('name', 'customers')->first();

        $pivot = $userIds->map(function ($id) use ($customerGroup) {
            return ['user_id' => $id, 'role_id' => $customerGroup->id];
        })->toArray();

        DB::table('user_role')->insert($pivot);
    }
}
