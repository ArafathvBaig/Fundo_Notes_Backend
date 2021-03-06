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
     * by using first_name, last_name, email and password as credentials
     * 
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
                "email" => "arafath@gamil.com",
                "password" => "arafath",
                "password_confirmation" => "arafath"
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'User Successfully Registered']);
    }

    /**
     * Test to check the user is already registered
     * by using first_name, last_name, email and password as credentials
     * The email used is a registered email for this test
     * 
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
     * Login the user by using the email and password as credentials
     * 
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
        $response->assertStatus(200)->assertJson(['success' => 'Login Successful']);
    }

    /**
     * Test for Unsuccessfull Login
     * Login the user by email and password
     * Wrong password for this test
     * 
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
     * Send a mail for forgot password of a registered user
     * 
     * @test
     */
    public function successfulForgotPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/forgotPassword', [
                "email" => "arafathbaig1997@gmail.com"
            ]);

            $response->assertStatus(201)->assertJson(['message' => 'Reset Password Token Sent to your Email']);
        }
    }

    /**
     * Test for UnSuccessfull Forgot Password
     * Send a mail for forgot password of a registered user
     * Non-Registered email for this test
     * 
     * @test
     */
    public function unsuccessfulForgotPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/forgotPassword', [
                "email" => "arafathbaig123@gmail.com"
            ]);

            $response->assertStatus(404)->assertJson(['message' => 'Not a Registered Email']);
        }
    }

    /**
     * Test for Successfull Reset Password
     * Reset password using the token and 
     * setting the new password to be the password
     * 
     * @test
     */
    public function successfulResetPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/resetPassword', [
                "new_password" => "arafath1234",
                "password_confirmation" => "arafath1234",
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2FwaVwvZm9yZ290UGFzc3dvcmQiLCJpYXQiOjE2NDk4MjgzMDgsImV4cCI6MTY0OTgzMTkwOCwibmJmIjoxNjQ5ODI4MzA4LCJqdGkiOiI1SURSMzdCY2lIR2VNak41Iiwic3ViIjozLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.d93SpbOT1aL1qAOH9qjWfP2OdqfeB27vMV2fkx1hShA'
            ]);

            $response->assertStatus(201)->assertJson(['message' => 'Password Reset Successful']);
        }
    }

    /**
     * Test for unSuccessfull Reset Password
     * Reset password using the token and 
     * setting the new password to be the password
     * Wrong token is passed for this test
     * 
     * @test
     */
    public function unsuccessfulResetPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/resetPassword', [
                "new_password" => "arafath1234",
                "password_confirmation" => "arafath1234",
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2FwaVwvZm9yZ290UGFzc3dvcmQiLCJpYXQiOjE2NDk4MjgzMDgsImV4cCI6MTY0OTgzMTkwOCwibmJmIjoxNjQ5ODI4MzA4LCJqdGkiOiI1SURSMzdCY2lIR2VNak41Iiwic3ViIjozLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.d93SpbOT1aL1qAOH9qjWfP2OdqfeB27vMV2fkx1hShA'
            ]);

            $response->assertStatus(400)->assertJson(['message' => 'Invalid Authorization Token']);
        }
    }

    /**
     * Test for Successfull Logout
     * Logout a user using the token generated at login
     * 
     * @test
     */
    public function successfulLogoutTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/logout', [
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY0OTkyNTI2MCwiZXhwIjoxNjQ5OTI4ODYwLCJuYmYiOjE2NDk5MjUyNjAsImp0aSI6InI2VklQYlJkWXRscFFjU2EiLCJzdWIiOjQsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.pd5x0pBsl9vdZaKPl2QMh_9WKRPNv2r5sFDjAgW5VTE'
            ]);

            $response->assertStatus(200)->assertJson(['message' => 'User Successfully Logged Out']);
        }
    }

    /**
     * Test for unSuccessfull Logout
     * Logout a user using the token generated at login
     * Passing the wrong token for this test
     * 
     * @test
     */
    public function unsuccessfulLogoutTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/logout', [
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY0OTkyNTI2MCwiZXhwIjoxNjQ5OTI4ODYwLCJuYmYiOjE2NDk5MjUyNjAsImp0aSI6InI2VklQYlJkWXRscFFjU2EiLCJzdWIiOjQsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.pd5x0pBsl9vdZaKPl2QMh_9WKRPNv2r5sFDjAgW5VTE'
            ]);

            $response->assertStatus(401)->assertJson(['message' => 'Invalid Authorization Token']);
        }
    }
}
