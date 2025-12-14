<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@admin.com')->firstOrFail();

        $categories = [
            ['name' => 'Alimentação', 'color' => '#f87171', 'icon' => 'heroicon-o-cake'],
            ['name' => 'Supermercado', 'color' => '#fb923c', 'icon' => 'heroicon-o-shopping-cart'],
            ['name' => 'Transporte', 'color' => '#facc15', 'icon' => 'heroicon-o-truck'],
            ['name' => 'Moradia', 'color' => '#4ade80', 'icon' => 'heroicon-o-home'],
            ['name' => 'Saúde', 'color' => '#2dd4bf', 'icon' => 'heroicon-o-heart'],
            ['name' => 'Educação', 'color' => '#60a5fa', 'icon' => 'heroicon-o-academic-cap'],
            ['name' => 'Lazer', 'color' => '#a78bfa', 'icon' => 'heroicon-o-face-smile'],
            ['name' => 'Assinaturas', 'color' => '#f472b6', 'icon' => 'heroicon-o-film'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name'], 'user_id' => $user->id],
                ['color' => $cat['color'], 'icon' => $cat['icon']]
            );
        }
    }
}
