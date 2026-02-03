<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_id_card_front')->nullable()->after('profile_img');
            $table->string('user_id_card_back')->nullable()->after('user_id_card_front');
            $table->string('father_id_card_front')->nullable()->after('user_id_card_back');
            $table->string('father_id_card_back')->nullable()->after('father_id_card_front');
            $table->text('current_location')->nullable()->after('father_id_card_back');
            $table->string('house_photo_front')->nullable()->after('current_location');
            $table->decimal('credit_limit', 15, 2)->nullable()->after('house_photo_front');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_id_card_front', 'user_id_card_back',
                'father_id_card_front', 'father_id_card_back',
                'current_location', 'house_photo_front', 'credit_limit'
            ]);
        });
    }
};
