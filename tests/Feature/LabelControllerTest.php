<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LabelControllerTest extends TestCase
{
    protected static $token;
    public static function setUpBeforeClass(): void
    {
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MDAxODQ5NywiZXhwIjoxNjUwMDIyMDk3LCJuYmYiOjE2NTAwMTg0OTcsImp0aSI6InpBREQyaDFvSmRVREpTMG8iLCJzdWIiOjQsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.En6FPHOHLjfdMoX1o7f_KtJ-0-uiMNvfajAo_TwywLU";
    }

    /**
     * Successful Create Label Test
     * @test
     */
    public function successfulCreateLabelTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/createLabel', [
                "labelname" => "Presentation",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Label Added Sucessfully']);
    }

    /**
     * UnSuccessful Create Label Test
     * @test
     */
    public function unSuccessfulCreateLabelTest()
    {

        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/createLabel', [
                "labelname" => "Presentation",
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Label Name Already Exists']);
    }

    /**
     * Successful Update Label Test
     * @test
     */
    public function successfulUpdateLabelTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updateLabel', [
                'id' => '7',
                'labelname' => 'Office Work',
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Label Updated Successfully']);
    }

    /**
     * UnSuccessful Update Label Test
     * @test
     */
    public function unSuccessfulUpdateLabelTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updateLabel', [
                'id' => '7',
                'labelname' => 'Office Work',
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Label Name Already Exists']);
    }

    /**
     * Successful Delete Label Test
     * @test
     */
    public function successfulDeleteLabelTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteLabel', [
                "id" => "7",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Label Successfully Deleted']);
    }

    /**
     * UnSuccessful Delete Label Test
     * @test
     */
    public function unSuccessfulDeleteLabelTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteLabel', [
                "id" => "55",
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Label Not Found']);
    }

    /**
     * Successful Add NoteLabel Test
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
