<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 選挙名（例：第49回衆議院議員総選挙）
            $table->enum('type', ['house_of_representatives', 'house_of_councillors']); // 衆議院/参議院
            $table->date('election_date'); // 選挙日
            $table->date('announcement_date')->nullable(); // 公示日
            $table->integer('total_seats'); // 総議席数
            $table->integer('single_seat_districts')->nullable(); // 小選挙区数
            $table->integer('proportional_seats')->nullable(); // 比例代表議席数
            $table->decimal('voter_turnout', 5, 2)->nullable(); // 投票率（%）
            $table->bigInteger('total_voters')->nullable(); // 有権者総数
            $table->bigInteger('total_votes')->nullable(); // 総投票数
            $table->text('notes')->nullable(); // 備考
            $table->timestamps();

            $table->index('type');
            $table->index('election_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
