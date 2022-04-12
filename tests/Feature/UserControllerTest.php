<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    /**
     * Successfull Registration
     * This test is to check user Registered Successfully or not
     * @test
     */
    public function successfulRegistrationTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/register', [
                "first_name" => "Arafath",
                "last_name" => "Baig",
                "email" => "arafathbaig@gamil.com",
                "password" => "abcdefghij",
                "password_confirmation" => "abcdefghij"
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'User Successfully Registered']);
    }

    /**
     * Test to check the user is already registered
     * @test
     */
    public function userisAlreadyRegisteredTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/register', [
                "first_name" => "Arafath",
                "last_name" => "Baig",
                "email" => "arafathbaig@gamil.com",
                "password" => "abcdefghij",
                "password_confirmation" => "abcdefghij"
            ]);
        $response->assertStatus(401)->assertJson(['message' => 'The email has already been taken.']);
    }

    /**
     * Test for successful Login
     * @test
     */

    public function successfulLoginTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/login',
            [
                "email" => "arafathbaig@gamil.com",
                "password" => "abcdefghij"
            ]
        );
        $response->assertStatus(201)->assertJson(['success' => 'Login Successful']);
    }

    /**
     * Test for Unsuccessfull Login
     * @test
     */

    public function unSuccessfulLoginTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/login',
            [
                "email" => "arafathbaig@gamil.com",
                "password" => "abcdefghi"
            ]
        );
        $response->assertStatus(402)->assertJson(['message' => 'Wrong Password']);
    }

    /**
     * Test for Successfull Forgot Password
     * @test
     */
    public function successfulForgotPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/forgotPassword', [
                "email" => "arafathbaig1997@gmail.com"
            ]);

            $response->assertStatus(200)->assertJson(['message' => 'Reset Password Token Sent to your Email']);
        }
    }

    /**
     * Test for Successfull Reset Password
     * @test
     */
    public function successfulResetPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/resetPassword', [
                "new_password" => "arafath1234",
                "password_confirmation" => "arafath1234",
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9mb3Jnb3RQYXNzd29yZCIsImlhdCI6MTY0OTY1NTUyMCwiZXhwIjoxNjQ5NjU5MTIwLCJuYmYiOjE2NDk2NTU1MjAsImp0aSI6IlVLUTFMaWpIN2I0ZUFmR0wiLCJzdWIiOjYsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.MmcwniPLKMFgt5YSIBkCLCd6D8_RH6-6qhRb8SNp0W4'
            ]);

            $response->assertStatus(201)->assertJson(['message' => 'Password Reset Successful']);
        }
    }
}
