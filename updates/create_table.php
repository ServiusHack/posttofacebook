<?php namespace ServiusHack\PostToFacebook\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateFbPostIdsTable extends Migration
{
  public function up()
  {
    Schema::create('posttofacebook_posts', function ($table)
    {
      $table->engine = 'InnoDB';
      $table->string('fb_post_id');
      $table->integer('blog_post_id')->index();
    });

  }

  public function down()
  {
    Schema::drop('posttofacebook_posts');
  }
}
