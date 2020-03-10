<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        //20 customers generation
        $faker  =  Faker\Factory::create('fr_FR');

        for ($i = 1; $i <= 20; $i++) {
            $customer = new Customer();
            $customer->setName($faker->name);
            $customer->setLogo("https://placekitten.com/200/300");
            $manager->persist($customer);
        }
        $manager->flush();
    }
}
