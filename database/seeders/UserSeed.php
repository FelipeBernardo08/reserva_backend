<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeed extends Seeder
{
    private $userModel;

    public function __construct(
        User $user
    ) {
        $this->userModel = $user;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->userModel->createUserSeed('admin@email.com', 'Admin', '1234');
    }
}
