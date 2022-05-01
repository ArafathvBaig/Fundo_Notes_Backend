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
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MTQzMTM2MSwiZXhwIjoxNjUxNDM0OTYxLCJuYmYiOjE2NTE0MzEzNjEsImp0aSI6Im1TdmFzcXFTaTFnbXRvQXQiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.lURZ82AjfHMGTf9IJab2hQxeXgx5eSKGkwnYTl3PviY";
    }

    /**
     * Successful Create Note Test
     * Using Credentials Required and
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
                "label_id" => "12",
                "pin" => "0",
                "archive" => "0",
                "colour" => "red",
                "collaborator_email" => "arafathbaig1997@gmail.com",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Notes Created Successfully']);
    }

    /**
     * UnSuccessful Create Note Test
     * Using Credentials Required and
     * using the authorization token
     * Wrong Credentials is used for this test
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
                "label_id" => "-5",
                "pin" => "1",
                "archive" => "0",
                "colour" => "red",
                "collaborator_email" => "arafathbaig1997@gmail.com",
                "token" => self::$token
            ]);
        $response->assertStatus(400)->assertJson(['message' => 'Invalid Label Id']);
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
                "id" => "80",
                "title" => "Expence",
                "description" => "Write Down Your Expences",
                "label_id" => "12",
                "pin" => "1",
                "archive" => "0",
                "colour" => "red",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note Updated Successfully']);
    }

    /**
     * UnSuccessful Update Note By ID Test
     * Update a note using id and authorization token
     * Passing wrong note or noteId which is not for this user, for this test
     * 
     * @test
     */
    public function unSuccessfulUpdateNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updateNoteById', [
                "id" => "2",
                "title" => "Expence",
                "description" => "Write Down Your Expences",
                "label_id" => "12",
                "pin" => "1",
                "archive" => "0",
                "colour" => "red",
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
                "id" => "80",
                "token" => self::$token
            ]);
        $response->assertStatus(200)->assertJson(['message' => 'Note Deleted Successfully']);
    }

    /**
     * UnSuccessful Delete Note By ID Test
     * Delete note by using id and authorization token
     * Passing wrong note or noteId which is not for this user, for this test
     * 
     * @test
     */
    public function unSuccessfulDeleteNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteNoteById', [
                "id" => "80",
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
                'label_id' => '13',
                'note_id' => '81',
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
                'label_id' => '13',
                'note_id' => '81',
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
                'label_id' => '12',
                'note_id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(200)->assertJson(['message' => 'Label Note Successfully Deleted']);
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
                'label_id' => '12',
                'note_id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'LabelNotes Not Found With These Credentials']);
    }

    /**
     * Successful Pin Note by ID Test
     * Pin Note by ID Using note_id and authorization token
     * 
     * @test
     */
    public function successfulPinNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/pinNoteById', [
                'id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note Pinned Successfully']);
    }

    /**
     * UnSuccessful Pin Note by ID Test
     * Pin Note by ID Using note_id and authorization token
     * Using Wrong Credentials for UnSuccessful Test
     * 
     * @test
     */
    public function unSuccessfulPinNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/pinNoteById', [
                'id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Note Already Pinned']);
    }

    /**
     * Successful UnPin Note by ID Test
     * UnPin Note by ID Using note_id and authorization token
     * 
     * @test
     */
    public function successfulUnPinNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/unPinNoteById', [
                'id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note UnPinned Successfully']);
    }

    /**
     * UnSuccessful UnPin Note by ID Test
     * UnPin Note by ID Using note_id and authorization token
     * Using Wrong Credentials for UnSuccessful Test
     * 
     * @test
     */
    public function unSuccessfulUnPinNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/unPinNoteById', [
                'id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Note Already UnPinned']);
    }

    /**
     * Successful Archive Note by ID Test
     * Archive Note by ID Using note_id and authorization token
     * 
     * @test
     */
    public function successfulArchiveNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/archiveNoteById', [
                'id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note Archived Successfully']);
    }

    /**
     * UnSuccessful Archive Note by ID Test
     * Archive Note by ID Using note_id and authorization token
     * Using Wrong Credentials for UnSuccessful Test
     * 
     * @test
     */
    public function unSuccessfulArchiveNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/archiveNoteById', [
                'id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Note Already Archived']);
    }

    /**
     * Successful UnArchive Note by ID Test
     * UnArchive Note by ID Using note_id and authorization token
     * 
     * @test
     */
    public function successfulUnArchivedNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/unArchiveNoteById', [
                'id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note UnArchived Successfully']);
    }

    /**
     * UnSuccessful UnArchive Note by ID Test
     * UnArchive Note by ID Using note_id and authorization token
     * Using Wrong Credentials for UnSuccessful Test
     * 
     * @test
     */
    public function unSuccessfulUnArchiveNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/unArchiveNoteById', [
                'id' => '81',
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Note Already UnArchived']);
    }

    /**
     * Successful Colour Note by ID Test
     * Colour Note by ID Using note_id, colour and authorization token
     * 
     * @test
     */
    public function successfulColourNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/colourNoteById', [
                'id' => '81',
                "colour" => "blue",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note Coloured Sucessfully']);
    }

    /**
     * UnSuccessful Colour Note by ID Test
     * Colour Note by ID Using note_id, colour and authorization token
     * Using Wrong Credentials for UnSuccessful Test
     * 
     * @test
     */
    public function unSuccessfulColourNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/colourNoteById', [
                'id' => '81',
                "colour" => "black",
                "token" => self::$token
            ]);
        $response->assertStatus(406)->assertJson(['message' => 'Colour Not Specified in the List']);
    }

    /**
     * Successful Search Note Test
     * Search Note Using a key and Authorization Test
     * 
     * @test
     */
    public function successfulSearchNotesTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/searchNotes', [
                "search" => "Tasks",
                "token" => self::$token
            ]);
        $response->assertStatus(200)->assertJson(['message' => 'Fetched Notes Successfully']);
    }

    /**
     * UnSuccessful Search Note Test
     * Search Note Using a key and Authorization Token
     * Using Wrong Credentials for UnSuccessful test
     * 
     * @test
     */
    public function unSuccessfulSearchNotesTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/searchNotes', [
                "search" => "elephant",
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Notes Not Found']);
    }
}
