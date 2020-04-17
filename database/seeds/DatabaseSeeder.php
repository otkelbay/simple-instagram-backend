<?php

use App\Comment;
use App\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('ru_RU');

        for ($j = 0; $j < 30; $j++) {
            $user = new User();
            $user->login = $faker->userName;
            $user->avatar = 'https://api.adorable.io/avatars/285/' . $j;
            $user->password = \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random());
            $user->about_me = $faker->realText(500);
            $user->api_token = \Illuminate\Support\Str::random(80);
            $user->save();
        }

        for ($i = 0; $i < 1000; $i++) {
            $post = new \App\Post();
            $post->post_text = $faker->realText(300);
            $post->photo_url = "https://i.picsum.photos/id/$i/400/300.jpg";
            $post->author_id = rand(1, 30);
            $post->save();
        }

        for ($z = 1; $z <= 30; $z++) {
            $user = User::find($z);
            for ($w = 1; $w <= 200; $w++) {
                $randomPost = rand(1, 1000);
                $like = DB::table('user_post_likes')
                    ->where('post_id', $randomPost)
                    ->where('user_id', $user->id)
                    ->first();
                if (!$like) {
                    DB::table('user_post_likes')
                        ->insert([
                            'user_id' => $user->id,
                            'post_id' => $randomPost
                        ]);
                }
            }

            $subs = User::where('id', '!=', $user->id)->inRandomOrder()->limit(20)->get();

            foreach ($subs as $sub) {
                DB::table('user_subscribers')
                    ->insert([
                        'user_id' => $user->id,
                        'subscribed_to_id' => $sub->id
                    ]);
            }

            for ($w = 1; $w <= 100; $w++) {
                $randomPost = rand(1, 1000);
                $comment = new Comment(
                    [
                        'post_id' => $randomPost,
                        'user_id' => $user->id,
                        'text' => $faker->realText(100)
                    ]
                );
                $comment->save();
            }
        }

    }
}
