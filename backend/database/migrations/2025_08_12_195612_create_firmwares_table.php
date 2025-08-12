<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('firmwares', function (Blueprint $table) {
        $table->id();
        $table->string('filename');
        $table->string('filepath');
        $table->string('version')->nullable();
        $table->text('description')->nullable();
        $table->bigInteger('filesize');
        $table->string('uploaded_by')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('firmwares');
}

};
