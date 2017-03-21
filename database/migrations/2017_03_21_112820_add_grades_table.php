<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('factory')->create('grades', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('title')->notNull();
        });

        DB::connection('factory')->table('grades')->insert([
            ['id' => 1, 'title' => '1 класс'],
            ['id' => 2, 'title' => '2 класс'],
            ['id' => 3, 'title' => '3 класс'],
            ['id' => 4, 'title' => '4 класс'],
            ['id' => 5, 'title' => '5 класс'],
            ['id' => 6, 'title' => '6 класс'],
            ['id' => 7, 'title' => '7 класс'],
            ['id' => 8, 'title' => '8 класс'],
            ['id' => 9, 'title' => '9 класс'],
            ['id' => 10, 'title' => '10 класс'],
            ['id' => 11, 'title' => '11 класс'],
            ['id' => 12, 'title' => 'студенты'],
            ['id' => 13, 'title' => 'остальные'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('factory')->dropIfExists('grades');
    }
}
