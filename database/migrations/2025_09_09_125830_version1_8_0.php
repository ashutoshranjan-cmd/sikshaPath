<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {

            // Insert system settings for version 2.0.0
            SystemSetting::upsert([
                ["name" => "database_root_user", "data" => 0, "type" => "integer"],
                ["name" => "laravel_queue_setup", "data" => 0, "type" => "integer"],
                ["name" => "wildcard_domain", "data" => 0, "type" => "integer"],
                ["name" => "web_socket_setup", "data" => 0, "type" => "integer"],
                ["name" => "notification_settings", "data" => 0, "type" => "integer"],
            ], ['name'], ['data', 'type']);

            // Update decimal precision for amount fields
            Schema::table('addons', function (Blueprint $table) {
                $table->decimal('price', 64, 2)->change();
            });

            Schema::table('addon_subscriptions', function (Blueprint $table) {
                $table->decimal('price', 64, 2)->change();
            });

            Schema::table('subscriptions', function (Blueprint $table) {
                $table->decimal('student_charge', 64, 2)->change();
                $table->decimal('staff_charge', 64, 2)->change();
                $table->decimal('charges', 64, 2)->change();
            });

            Schema::table('subscription_bills', function (Blueprint $table) {
                $table->decimal('amount', 64, 2)->change();
            });
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // Remove system settings
         SystemSetting::whereIn('name', [
            'database_root_user',
            'laravel_queue_setup',
            'wildcard_domain',
            'web_socket_setup',
            'notification_settings'
        ])->delete();

        // Revert decimal precision changes (assuming original precision was 10,2)
        Schema::table('addons', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('addon_subscriptions', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('student_charge', 10, 2)->change();
            $table->decimal('staff_charge', 10, 2)->change();
            $table->decimal('charges', 10, 2)->change();
        });

        Schema::table('subscription_bills', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });
    }
};
