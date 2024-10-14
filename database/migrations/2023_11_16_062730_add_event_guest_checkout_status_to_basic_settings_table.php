<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings', 'event_guest_checkout_status')) {
                $table->integer('event_guest_checkout_status')->default(0)->nullable()->comment('0=deactive, 1=active');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (Schema::hasColumn('basic_settings', 'event_guest_checkout_status')) {
                $table->dropColumn('event_guest_checkout_status');
            }
        });
    }
};
