<?php

namespace Eaw;

use Exception;

abstract class Init
{
    public static function init()
    {
        if (static::auth() === null) {
            static::onboarding();
        }
    }

    /**
     * @return bool|null True on success, false on failure, or null if no credentials were provided.
     */
    public static function auth()
    {
        if (eaw()->isAuthenticated()) {
            return true;
        }

        if ((null !== $username = env('eaw_username')) && (null !== $password = env('eaw_password'))) {
            return eaw()->userAuth($username, $password);
        }

        if ((null !== $clientId = env('eaw_client_id')) && (null !== $clientSecret = env('eaw_client_secret'))) {
            return eaw()->clientAuth($clientId, $clientSecret);
        }

        return null;
    }

    public static function onboarding()
    {
        logger()->info('Looks like you\'re new here. Let\'s get you set up!');

        $authType = multiple_choice('How would you like to authenticate?', [
            'u' => 'User with email address and password.',
            'c' => 'Client with client ID and secret.',
        ]);

        switch ($authType) {
            case 'c':
                static::onboardingClient();
                break;

            case 'u':
                static::onboardingUser();
                break;
        }
    }

    protected static function onboardingClient()
    {
        do {
            $clientId = readline('Client ID: ');
            $clientSecret = readline_secret('Client Secret: ');

            try {
                eaw()->clientAuth($clientId, $clientSecret);
            } catch (Exception $exception) {
                logger()->error('Invalid client ID or secret. Please try again.');
            }
        } while (!eaw()->isAuthenticated());

        logger()->info('Thank you! We hope you enjoy your stay :)');
    }

    protected static function onboardingUser()
    {
        do {
            $email = readline('Email: ');
            $password = readline_secret('Password: ');

            try {
                eaw()->userAuth($email, $password);
            } catch (Exception $exception) {
                logger()->error('Invalid email or password. Please try again.');
            }
        } while (!eaw()->isAuthenticated());

        $user = eaw()->read("/users/{$email}");

        logger()->info("Welcome, {$user['first_name']}! We hope you enjoy your stay :)");
    }
}
