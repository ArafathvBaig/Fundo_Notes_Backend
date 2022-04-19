<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    protected static $token;
    public static function setUpBeforeClass(): void
    {
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MDAxODQ5NywiZXhwIjoxNjUwMDIyMDk3LCJuYmYiOjE2NTAwMTg0OTcsImp0aSI6InpBREQyaDFvSmRVREpTMG8iLCJzdWIiOjQsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.En6FPHOHLjfdMoX1o7f_KtJ-0-uiMNvfajAo_TwywLU";
    }

    /**
     * Successful Create Note Test
     * Create not having title and description
     * using the authorization token
     * 
     * @test
     */
    public function successfulCreateNoteTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/createNote', [
                "title" => "Work",
                "description" => "Do the Work",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Notes Created Successfully']);
    }

    /**
     * UnSuccessful Create Note Test
     * Create not having title and description
     * using the authorization token
     * Wrong token is used for this test
     * 
     * @test
     */
    public function unSuccessfulCreateNoteTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/createNote', [
                "title" => "Work",
                "description" => "Do the Work",
                "token" => '1234567890'
            ]);
        $response->assertStatus(400)->assertJson(['message' => 'Wrong number of segments']);
    }

    /**
     * Successful Update Note By ID Test
     * Update a note using id and authorization token
     * 
     * @test
     */
    public function successfulUpdateNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updateNoteById', [
                "id" => "3",
                "title" => "Expence",
                "description" => "Write Down Your Expences",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note Updated Successfully']);
    }

    /**
     * UnSuccessful Update Note By ID Test
     * Update a note using id and authorization token
     * Passing wrong note or note which is not for this user, for this test
     * 
     * @test
     */
    public function unSuccessfulUpdateNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updateNoteById', [
                "id" => "50",
                "title" => "Expence",
                "description" => "Write Down Your Expences",
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Notes Not Found']);
    }

    /**
     * Successful Delete Note By ID Test
     * Delete note by using id and authorization token
     * 
     * @test
     */
    public function successfulDeleteNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteNoteById', [
                "id" => "10",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note Deleted Successfully']);
    }

    /**
     * UnSuccessful Delete Note By ID Test
     * Delete note by using id and authorization token
     * Passing wrong note or note which is not for this user, for this test
     * 
     * @test
     */
    public function unSuccessfulDeleteNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteNoteById', [
                "id" => "55",
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Notes Not Found']);
    }

    /**
     * Successful Add NoteLabel Test
     * Add NoteLabel using the label_id, note_id and authorization token
     * 
     * @test
     */
    public function successfulAddNoteLabelTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addNoteLabel', [
                'label_id' => '1',
                'note_id' => '10',
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'LabelNote Added Successfully']);
    }

    /**
     * UnSuccessful Add NoteLabel Test
     * Add NoteLabel using the label_id, note_id and authorization token
     * Using wrong label_id or note_id which is not of this user,
     * for this test
     * 
     * @test
     */
    public function unSuccessfulAddNoteLabelTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addNoteLabel', [
                'label_id' => '1',
                'note_id' => '10',
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Note Already Have This Label']);
    }

    /**
     * Successful Delete NoteLabel Test
     * Delete NoteLabel using the label_id, note_id and authorization token
     * 
     * @test
     */
    public function successfulDeleteNoteLabelTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteNoteLabel', [
                'label_id' => '1',
                'note_id' => '10',
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Label Note Successfully Deleted']);
    }

    /**
     * UnSuccessful Delete NoteLabel Test
     * Delete NoteLabel using the label_id, note_id and authorization token
     * Using wrong label_id or note_id which is not of this user,
     * for this test
     * 
     * @test
     */
    public function unSuccessfulDeleteNoteLabelTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteNoteLabel', [
                'label_id' => '1',
                'note_id' => '10',
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'LabelNotes Not Found With These Credentials']);
    }
}
