<?php

// database/factories/LawyerFactory.php

namespace Database\Factories;

use App\Models\Lawyer;
use Illuminate\Database\Eloquent\Factories\Factory;

class LawyerFactory extends Factory
{
    protected $model = Lawyer::class;

    public function definition(): array
    {
        $specializations = $this->faker->randomElements(
            Lawyer::getSpecializationOptions(),
            $this->faker->numberBetween(1, 4)
        );

        $languages = $this->faker->randomElements(
            Lawyer::getLanguageOptions(),
            $this->faker->numberBetween(1, 3)
        );

        $consultationMethods = $this->faker->randomElements(
            Lawyer::getConsultationMethodOptions(),
            $this->faker->numberBetween(1, 3)
        );

        $courts = $this->faker->randomElements(
            Lawyer::getCourtsOptions(),
            $this->faker->numberBetween(1, 3)
        );

        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $firmName = $this->faker->company() . ' Law';

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+27 ' . $this->faker->numerify('## ### ####'),
            'mobile' => '+27 ' . $this->faker->numerify('## ### ####'),
            'fax' => $this->faker->boolean(30) ? '+27 ' . $this->faker->numerify('## ### ####') : null,
            'license_number' => 'LSA-' . strtoupper($this->faker->lexify('???')) . '-' . $this->faker->year() . '-' . $this->faker->numerify('###'),
            'firm_name' => $firmName,
            'bio' => $this->faker->paragraph(3),
            'specializations' => $specializations,
            'languages' => $languages,
            'years_experience' => $this->faker->numberBetween(1, 25),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->randomElement([
                'Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth',
                'Bloemfontein', 'East London', 'Polokwane', 'Rustenburg', 'Nelspruit'
            ]),
            'province' => $this->faker->randomElement(Lawyer::getProvinceOptions()),
            'postal_code' => $this->faker->numerify('####'),
            'latitude' => $this->faker->latitude(-35, -22),
            'longitude' => $this->faker->longitude(16, 33),
            'admission_date' => $this->faker->date('Y-m-d', '-5 years'),
            'law_society' => 'Law Society of South Africa',
            'courts_admitted' => $courts,
            'accepts_new_clients' => $this->faker->boolean(80),
            'consultation_methods' => $consultationMethods,
            'consultation_fee' => $this->faker->randomFloat(2, 400, 1500),
            'website' => $this->faker->boolean(60) ? $this->faker->url() : null,
            'social_media' => $this->faker->boolean(40) ? [
                'LinkedIn' => $this->faker->url(),
                'Twitter' => '@' . $this->faker->userName(),
            ] : null,
            'business_hours' => [
                'Monday' => '08:00 - 17:00',
                'Tuesday' => '08:00 - 17:00',
                'Wednesday' => '08:00 - 17:00',
                'Thursday' => '08:00 - 17:00',
                'Friday' => '08:00 - 16:00',
            ],
            'notes' => $this->faker->boolean(20) ? $this->faker->sentence() : null,
            'is_verified' => $this->faker->boolean(70),
            'is_active' => $this->faker->boolean(90),
            'verified_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-1 year') : null,
            'verified_by' => $this->faker->boolean(70) ? $this->faker->name() : null,
            'keywords' => $this->faker->words(5),
            'rating' => $this->faker->randomFloat(2, 2.0, 5.0),
            'review_count' => $this->faker->numberBetween(0, 50),
        ];
    }

    /**
     * Indicate that the lawyer is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'verified_at' => $this->faker->dateTimeBetween('-1 year'),
            'verified_by' => $this->faker->name(),
        ]);
    }

    /**
     * Indicate that the lawyer is not verified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }

    /**
     * Indicate that the lawyer is highly rated.
     */
    public function highlyRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->randomFloat(2, 4.5, 5.0),
            'review_count' => $this->faker->numberBetween(20, 100),
        ]);
    }

    /**
     * Indicate that the lawyer accepts new clients.
     */
    public function acceptingClients(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepts_new_clients' => true,
        ]);
    }
}