<?php

use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create some products
        $product_type = ProductType::find(1);

        $products = factory(Product::class, 10)->create(['product_type_id' => $product_type->id]);

        $products->map(function ($product) {
            $product->price()->create([
                'price' => 10,
                'discount' => 2
            ]);
        });

        // create an admin user
        factory(\App\Models\User::class)->create([
            'name'    => 'Admin Rajesh',
            'email'    => 'admin.rajesh@gmail.com',
            'password' => Hash::make('admin@1234'),
            'is_admin' => true
        ]);

        //create a regular user
        factory(\App\Models\User::class)->create([
            'name'    => 'User Rajesh',
            'email'    => 'user.rajesh@gmail.com',
            'password' => Hash::make('user@1234')
        ]);
    }
}
