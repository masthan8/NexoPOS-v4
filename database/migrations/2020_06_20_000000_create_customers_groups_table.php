<?php
/**
 * Table Migration
 * @package  5.0
**/

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        if ( ! Schema::hasTable( 'nexopos_customers_groups' ) ) {
            Schema::create( 'nexopos_customers_groups', function( Blueprint $table ) {
                $table->bigIncrements('id');
                $table->string( 'name' );
                $table->text( 'description' )->nullable();
                $table->integer( 'reward_system_id' )->default(0);
                $table->integer( 'author' );
                $table->string( 'uuid' )->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        if ( Schema::hasTable( 'nexopos_customers_groups' ) ) {
            Schema::drop( 'nexopos_customers_groups' );
        }
    }
}

