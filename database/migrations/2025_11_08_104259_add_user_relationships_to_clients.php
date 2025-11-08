<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AddUserRelationshipsToClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::create('client_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['client_id', 'user_id']);
        });

        // Get existing users
        $existingUsers = User::all();

        // Create dummy users if they don't exist
        $dummyUsers = collect([
            ['name' => 'John Doe', 'email' => 'john@example.com', 'password' => Hash::make('demo')],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => Hash::make('demo')],
            ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'password' => Hash::make('demo')],
        ])->map(fn ($data) => User::firstOrCreate(['email' => $data['email']], $data));

        // Combine all users (existing + dummy)
        $allUsers = $existingUsers->merge($dummyUsers)->unique('id');
        $userIds = $allUsers->pluck('id')->all();

        // Randomly assign existing clients to users as owners (only if clients exist)
        $allClients = collect(DB::table('clients')->get());

        if ($allClients->isNotEmpty()) {

            $allClients->each(function ($client) use ($userIds) {
                DB::table('clients')
                    ->where('id', $client->id)
                    ->update(['user_id' => $userIds[array_rand($userIds)]]);
            });

            // Make user_id not nullable
            DB::statement('ALTER TABLE clients ALTER COLUMN user_id SET NOT NULL');

            // Randomly assign clients to users (each user gets 5-7 assigned clients)
            $allUsers->each(function ($user) use ($allClients) {
                $numAssignments = rand(5, min(7, $allClients->count()));

                if ($numAssignments > 0) {
                    $allClients->random($numAssignments)->each(fn ($client) => DB::table('client_user')->insert([
                        'client_id' => $client->id,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_user');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}
