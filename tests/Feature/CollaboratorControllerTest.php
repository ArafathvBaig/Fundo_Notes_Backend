<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CollaboratorControllerTest extends TestCase
{
    protected static $token;
    protected static $token1;
    public static function setUpBeforeClass(): void
    {
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MTQzOTEyMiwiZXhwIjoxNjUxNDQyNzIyLCJuYmYiOjE2NTE0MzkxMjIsImp0aSI6InlGQjB4cEN1d054NTI2NzAiLCJzdWIiOjMsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.Fq5FATzYTv_IktXgvLUZwKT_Piu19gGCS0fYmHjnGZ0";
        self::$token1 = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MTQzNjM2MSwiZXhwIjoxNjUxNDM5OTYxLCJuYmYiOjE2NTE0MzYzNjEsImp0aSI6IlB1dkhuVHNnbEdhUDUxMW0iLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.mHb991Evkvds3Hikda3XNfe1cZWf4-onuZL8TInVQ8M";
    }

    /**
     * Successful Add Collaborator Test
     * Using noteId and email for collaboration and
     * using the authorization token
     * 
     * @test
     */
    public function successfulAddCollaboratorTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addCollaborator', [
                "note_id" => "7",
                "email" => "arafathbaig1997@gmail.com",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Collaborator Created Sucessfully']);
    }

    /**
     * UnSuccessful Add Collaborator Test
     * Using noteId and email for collaboration and
     * using the authorization token
     * Using Wrong Credentials for UnSuccessful Test
     * 
     * @test
     */
    public function unSuccessfulAddCollaboratorTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addCollaborator', [
                "note_id" => "7",
                "email" => "arafathbaig1997@gmail.com",
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Collaborator Already Created']);
    }

    /**
     * Successful Update Note By Collaborator Test
     * Using the Credentials to update id, title and description and
     * using the authorization token
     * 
     * @test
     */
    public function successfulUpdateNoteByCollaboratorTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updateNoteByCollaborator', [
                "id" => "7",
                'title' => 'Marriage',
                'description' => 'Do all preparations for marriage',
                "token" => self::$token1
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note Updated Successfully']);
    }

    /**
     * UnSuccessful Update Note By Collaborator Test
     * Using the Credentials to update id, title and description and
     * using the authorization token
     * Using Wrong Credentials for UnSuccessful Test
     * 
     * @test
     */
    public function unSuccessfulUpdateNoteByCollaboratorTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updateNoteByCollaborator', [
                "id" => "1",
                'title' => 'Marriage',
                'description' => 'Do all preparations for marriage',
                "token" => self::$token1
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Collaborator Not Found']);
    }

    /**
     * Successful Remove Collaborator Test
     * Using noteId and email for collaboration and
     * using the authorization token
     * 
     * @test
     */
    public function successfulRemoveCollaboratorTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/removeCollaborator', [
                "note_id" => "7",
                "email" => "arafathbaig1997@gmail.com",
                "token" => self::$token
            ]);
        $response->assertStatus(200)->assertJson(['message' => 'Collaborator Deleted Sucessfully']);
    }

    /**
     * UnSuccessful Remove Collaborator Test
     * Using noteId and email for collaboration and
     * using the authorization token
     * Using Wrong Credentials for UnSuccessful Test
     * 
     * @test
     */
    public function unSuccessfulRemoveCollaboratorTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/removeCollaborator', [
                "note_id" => "7777",
                "email" => "arafathbaig1997@gmail.com",
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Collaborator Not Found']);
    }
}
