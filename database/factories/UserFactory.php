<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'role' => 'patient', // القيمة الافتراضية
            // status هون لازم ينكتب صراحةً رغم إنه عمود قاعدة البيانات نفسه
            // Defaultه true - لأنه Eloquent ما بيرجّع يقرأ الـ default من القاعدة
            // على الـ instance يلي بالذاكرة بعد create()، فـ actingAs() بالتيست
            // كان عم يستخدم نسخة status=null (falsy) بدل القيمة الحقيقية 1،
            // وهيك كل تيست كان عم ينرفض بـ 403 من CheckRole رغم إنه المستخدم
            // فعلياً مو موقوف بقاعدة البيانات
            'status' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attrs) => ['name' => 'Admin', 'email' => 'admin@carelink.com', 'role' => 'admin']);
    }

    public function doctor(): static
    {
        return $this->state(fn (array $attrs) => ['role' => 'doctor']);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
