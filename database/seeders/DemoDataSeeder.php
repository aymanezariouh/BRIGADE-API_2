<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->seedAdmin();
        $customers = $this->seedCustomers();

        $categories = $this->seedCategories($admin);
        $ingredients = $this->seedIngredients();

        $this->seedPlates($admin, $categories, $ingredients);
    }

    private function seedAdmin(): User
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'dietary_tags' => [],
            ]
        );

        $admin->profile()->updateOrCreate(
            ['user_id' => $admin->id],
            ['dietary_tags' => []]
        );

        return $admin;
    }

    private function seedCustomers(): array
    {
        $profiles = [
            'test@example.com' => [
                'name' => 'Gluten Free User',
                'dietary_tags' => ['gluten_free', 'no_lactose'],
            ],
            'vegan@example.com' => [
                'name' => 'Vegan User',
                'dietary_tags' => ['vegan', 'no_cholesterol'],
            ],
            'sugarfree@example.com' => [
                'name' => 'Sugar Free User',
                'dietary_tags' => ['no_sugar'],
            ],
        ];

        $users = [];

        foreach ($profiles as $email => $profile) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $profile['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_CUSTOMER,
                    'dietary_tags' => $profile['dietary_tags'],
                ]
            );

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['dietary_tags' => $profile['dietary_tags']]
            );

            $users[$email] = $user;
        }

        return $users;
    }

    private function seedCategories(User $admin): array
    {
        $categories = [
            'Main Courses' => [
                'description' => 'Hearty plates for lunch and dinner.',
                'color' => '#FF8A00',
                'is_active' => true,
            ],
            'Desserts' => [
                'description' => 'Sweet finishes and bakery items.',
                'color' => '#D94E7A',
                'is_active' => true,
            ],
            'Drinks' => [
                'description' => 'Cold and refreshing beverages.',
                'color' => '#227C9D',
                'is_active' => true,
            ],
            'Secret Menu' => [
                'description' => 'Hidden items to test inactive categories.',
                'color' => '#444444',
                'is_active' => false,
            ],
        ];

        $result = [];

        foreach ($categories as $name => $data) {
            $result[$name] = Category::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $data['description'],
                    'color' => $data['color'],
                    'is_active' => $data['is_active'],
                    'user_id' => $admin->id,
                    'image' => null,
                ]
            );
        }

        return $result;
    }

    private function seedIngredients(): array
    {
        $ingredients = [
            'Flour' => ['contains_gluten'],
            'Cream' => ['contains_lactose'],
            'Sugar' => ['contains_sugar'],
            'Beef' => ['contains_meat', 'contains_cholesterol'],
            'Chicken' => ['contains_meat', 'contains_cholesterol'],
            'Lettuce' => [],
            'Tomato' => [],
            'Olive Oil' => [],
            'Lemon Juice' => [],
            'Orange' => [],
            'Rice' => [],
            'Sparkling Water' => [],
        ];

        $result = [];

        foreach ($ingredients as $name => $tags) {
            $result[$name] = Ingredient::updateOrCreate(
                ['name' => $name],
                ['tags' => $tags]
            );
        }

        return $result;
    }

    private function seedPlates(User $admin, array $categories, array $ingredients): void
    {
        $plates = [
            'Pasta Alfredo' => [
                'description' => 'Creamy pasta with flour-based noodles.',
                'price' => 12.50,
                'is_available' => true,
                'category' => 'Main Courses',
                'ingredients' => ['Flour', 'Cream'],
            ],
            'Garden Salad' => [
                'description' => 'A light salad with no conflict tags.',
                'price' => 8.00,
                'is_available' => true,
                'category' => 'Main Courses',
                'ingredients' => ['Lettuce', 'Tomato', 'Olive Oil', 'Lemon Juice'],
            ],
            'Grilled Steak' => [
                'description' => 'Simple beef plate for vegan and cholesterol tests.',
                'price' => 18.90,
                'is_available' => true,
                'category' => 'Main Courses',
                'ingredients' => ['Beef', 'Olive Oil'],
            ],
            'Chocolate Cake' => [
                'description' => 'Dessert with sugar, gluten, and lactose conflicts.',
                'price' => 7.50,
                'is_available' => true,
                'category' => 'Desserts',
                'ingredients' => ['Flour', 'Cream', 'Sugar'],
            ],
            'Fresh Orange Juice' => [
                'description' => 'Safe drink with no dietary conflict tags.',
                'price' => 4.50,
                'is_available' => true,
                'category' => 'Drinks',
                'ingredients' => ['Orange'],
            ],
            'Hidden Chicken Rice' => [
                'description' => 'Plate inside an inactive category for visibility tests.',
                'price' => 11.00,
                'is_available' => true,
                'category' => 'Secret Menu',
                'ingredients' => ['Chicken', 'Rice'],
            ],
            'Unavailable Sweet Water' => [
                'description' => 'Unavailable drink to test plate availability filters.',
                'price' => 3.00,
                'is_available' => false,
                'category' => 'Drinks',
                'ingredients' => ['Sparkling Water', 'Sugar'],
            ],
        ];

        foreach ($plates as $name => $data) {
            $plate = Plate::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'image' => null,
                    'is_available' => $data['is_available'],
                    'user_id' => $admin->id,
                    'category_id' => $categories[$data['category']]->id,
                ]
            );

            $plate->ingredients()->sync(
                collect($data['ingredients'])
                    ->map(fn (string $ingredientName) => $ingredients[$ingredientName]->id)
                    ->all()
            );
        }
    }
}
