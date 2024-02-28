<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      $username = env('SUPER_ADMIN_USERNAME');
      $email = env('SUPER_ADMIN_EMAIL');
      if(!$email || !$username){
        $this->command->error('Set the needed keys in .env for super admin');
      }
      else if(User::where('username',$username)->first()){
        $this->command->error('Super admin already present');
      }
      else{
        $password = Str::random(18);
        $userCreated = User::create([
          "username" => $username,
          "email" => $email,
          "levelAdmin" => 2,
          "password" =>  Hash::make(hash("sha256",$password), ["rounds" => 14]),
          "nameDatabaseConnection" => null,
          "companyName" => 'XNOOVA'
        ]);
        DB::connection(config('database.default'))->table('roles_users')->insert([
          "userId" => $userCreated->id,
          "clientsFilter" => null,
          "scoreFilter" => null,
          "modFilter" => null,
          "eventTypeFilter" => null,
        ]);
        if($userCreated)
          $this->command->info("User saved correctly. store somewhere safe your password: $password");
        else $this->command->error('Error in storing super admin');
      }
    }
}
