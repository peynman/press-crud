<?php

namespace Larapress\CRUD\Tests;

use Larapress\CRUD\Models\Role;
use Larapress\Notifications\Models\SMSGatewayData;
use Larapress\Profiles\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;
use Larapress\ECommerce\IECommerceUser;
use Larapress\Profiles\Flags\UserDomainFlags;
use Larapress\Profiles\Models\EmailAddress;
use Larapress\Profiles\Models\PhoneNumber;

class CustomerTestApplication extends PackageTestApplication
{
    /**
     * Setup User Registration requirements
     */
    protected function setUp(): void
    {
        parent::setUp();

        SMSGatewayData::factory()->create();
        Domain::factory()->create([
            'domain' => ''
        ]);
        Role::factory()->create();
        Role::factory()->create();
    }

    protected function createVerifiedCustomer($name, $password, $phone = null, $email = null) : IECommerceUser
    {
        $userClass = config('larapress.crud.user.class');
        /** @var Factory */
        $factory = call_user_func([$userClass, 'factory']);
        /** @var IECommerceUser */
        $customer = $factory->create([
            'name' => $name,
            'password' => $password
        ]);
        $customer->roles()->attach(config('larapress.profiles.customer_role_id'));
        $customer->domains()->attach(config('larapress.auth.signup.default_domain'), [
            'flags' => UserDomainFlags::DEFAULT_DOMAIN | UserDomainFlags::REGISTRATION_DOMAIN | UserDomainFlags::MEMBERSHIP_DOMAIN,
        ]);
        if (!is_null($phone)) {
            $customer->phones()->save(PhoneNumber::factory()->create([
                'number' => $phone,
                'flags' => PhoneNumber::FLAGS_VERIFIED,
            ]));
        }
        if (!is_null($email)) {
            $customer->emails()->save(EmailAddress::factory()->create([
                'email' => $email,
                'flags' => EmailAddress::FLAGS_VERIFIED,
            ]));
        }

        return $customer;
    }
}
