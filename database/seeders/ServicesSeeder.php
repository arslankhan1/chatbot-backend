<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $services = [
            [
                'name' => 'Home Appliance Repair',
                'type' => 'repairment',
                'country' => 'United States',
                'region' => 'New York',
                'provider_name' => 'Quick Fix Solutions',
                'provider_contact' => '+1-555-0101',
                'description' => 'Professional repair services for all home appliances',
                'price' => 75.00,
                'is_active' => true
            ],
            [
                'name' => 'Computer Repair Service',
                'type' => 'repairment',
                'country' => 'United States',
                'region' => 'California',
                'provider_name' => 'Tech Repair Pro',
                'provider_contact' => '+1-555-0102',
                'description' => 'Expert computer and laptop repair services',
                'price' => 120.00,
                'is_active' => true
            ],
            [
                'name' => 'Mobile Phone Repair',
                'type' => 'repairment',
                'country' => 'United Kingdom',
                'region' => 'London',
                'provider_name' => 'Phone Fix UK',
                'provider_contact' => '+44-20-7946-0958',
                'description' => 'Screen replacement and mobile phone repairs',
                'price' => 60.00,
                'is_active' => true
            ],
            [
                'name' => 'Auto Repair Service',
                'type' => 'repairment',
                'country' => 'Canada',
                'region' => 'Toronto',
                'provider_name' => 'Maple Auto Repair',
                'provider_contact' => '+1-416-555-0103',
                'description' => 'Complete automotive repair and maintenance',
                'price' => 150.00,
                'is_active' => true
            ],

            // Product Supply Services
            [
                'name' => 'Office Supplies Delivery',
                'type' => 'product_supply',
                'country' => 'United States',
                'region' => 'New York',
                'provider_name' => 'Office Supply Co',
                'provider_contact' => '+1-555-0201',
                'description' => 'Complete office supplies and stationery delivery',
                'price' => 25.00,
                'is_active' => true
            ],
            [
                'name' => 'Grocery Delivery Service',
                'type' => 'product_supply',
                'country' => 'United States',
                'region' => 'California',
                'provider_name' => 'Fresh Groceries Inc',
                'provider_contact' => '+1-555-0202',
                'description' => 'Fresh groceries delivered to your door',
                'price' => 15.00,
                'is_active' => true
            ],
            [
                'name' => 'Medical Supplies',
                'type' => 'product_supply',
                'country' => 'United Kingdom',
                'region' => 'Manchester',
                'provider_name' => 'Health Supplies Ltd',
                'provider_contact' => '+44-161-496-0040',
                'description' => 'Medical equipment and supplies delivery',
                'price' => 50.00,
                'is_active' => true
            ],
            [
                'name' => 'Electronics Supply',
                'type' => 'product_supply',
                'country' => 'Canada',
                'region' => 'Vancouver',
                'provider_name' => 'Tech Supply Canada',
                'provider_contact' => '+1-604-555-0203',
                'description' => 'Latest electronics and gadgets supply',
                'price' => 200.00,
                'is_active' => true
            ],

            // Car Driver/Chauffeur Services
            [
                'name' => 'Executive Chauffeur Service',
                'type' => 'car_driver',
                'country' => 'United States',
                'region' => 'New York',
                'provider_name' => 'Elite Drivers NYC',
                'provider_contact' => '+1-555-0301',
                'description' => 'Professional chauffeur service for executives',
                'price' => 80.00,
                'is_active' => true
            ],
            [
                'name' => 'Airport Transfer Service',
                'type' => 'car_driver',
                'country' => 'United States',
                'region' => 'California',
                'provider_name' => 'LAX Transfer Pro',
                'provider_contact' => '+1-555-0302',
                'description' => 'Reliable airport pickup and drop-off service',
                'price' => 65.00,
                'is_active' => true
            ],
            [
                'name' => 'Wedding Chauffeur',
                'type' => 'car_driver',
                'country' => 'United Kingdom',
                'region' => 'London',
                'provider_name' => 'Royal Wedding Cars',
                'provider_contact' => '+44-20-7946-0959',
                'description' => 'Luxury wedding transportation service',
                'price' => 300.00,
                'is_active' => true
            ],
            [
                'name' => 'City Tour Driver',
                'type' => 'car_driver',
                'country' => 'Canada',
                'region' => 'Toronto',
                'provider_name' => 'Toronto City Tours',
                'provider_contact' => '+1-416-555-0304',
                'description' => 'Professional city tour guide and driver',
                'price' => 120.00,
                'is_active' => true
            ],

            // Additional services for variety
            [
                'name' => 'Plumbing Repair',
                'type' => 'repairment',
                'country' => 'Australia',
                'region' => 'Sydney',
                'provider_name' => 'Sydney Plumbers',
                'provider_contact' => '+61-2-9876-5432',
                'description' => '24/7 emergency plumbing repair services',
                'price' => 95.00,
                'is_active' => true
            ],
            [
                'name' => 'Food Delivery',
                'type' => 'product_supply',
                'country' => 'Australia',
                'region' => 'Melbourne',
                'provider_name' => 'Melbourne Eats',
                'provider_contact' => '+61-3-9876-5433',
                'description' => 'Restaurant food delivery service',
                'price' => 12.00,
                'is_active' => true
            ],
            [
                'name' => 'Personal Driver',
                'type' => 'car_driver',
                'country' => 'Australia',
                'region' => 'Sydney',
                'provider_name' => 'Sydney Personal Drivers',
                'provider_contact' => '+61-2-9876-5434',
                'description' => 'Personal driver for daily transportation needs',
                'price' => 45.00,
                'is_active' => true
            ]
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
