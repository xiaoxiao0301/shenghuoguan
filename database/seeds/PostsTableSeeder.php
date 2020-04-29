<?php

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Post::truncate(); // 清除表数据
        factory(Post::class, 20)->create(); // 一次性填充20篇文章
    }
}
