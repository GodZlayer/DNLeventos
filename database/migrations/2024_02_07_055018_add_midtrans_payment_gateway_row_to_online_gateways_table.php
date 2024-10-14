<?php

use App\Models\PaymentGateway\OnlineGateway;
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
        Schema::table('online_gateways', function (Blueprint $table) {
            $data = OnlineGateway::where('keyword', 'midtrans')->first();
            if (empty($data)) {
                $information = [
                    'server_key' => null,
                    'is_production' => 0
                ];
                $data = [
                    'name' => 'Midtrans',
                    'keyword' => 'midtrans',
                    'information' => json_encode($information, true),
                    'status' => 0
                ];
                OnlineGateway::create($data);
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
        Schema::table('online_gateways', function (Blueprint $table) {
            $data = OnlineGateway::where('keyword', 'midtrans')->first();
            if ($data) {
                $data->delete();
            }
        });
    }
};
