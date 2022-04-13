<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    /**
     * Successful Create Note Test
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
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY0OTg0Mzc4NCwiZXhwIjoxNjQ5ODQ3Mzg0LCJuYmYiOjE2NDk4NDM3ODQsImp0aSI6Im16NGpKM29QdDJ3TmQ0RmIiLCJzdWIiOjIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.FfGe_7BGgnhsidxSpf8DlWaapFKkFN3fNtXZwCSYOlM'
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Notes Created Successfully']);
    }

    /**
     * UnSuccessful Create Note Test
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
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY0OTg0Mzc4NCwiZXhwIjoxNjQ5ODQ3Mzg0LCJuYmYiOjE2NDk4NDM3ODQsImp0aSI6Im16NGpKM29QdDJ3TmQ0RmIiLCJzdWIiOjIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.FfGe_7BGgnhsidxSpf8DlWaapFKkFN3fNtXZwCSYOlM'
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note Updated Successfully']);
    }

    /**
     * UnSuccessful Update Note By ID Test
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
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY0OTg0Mzc4NCwiZXhwIjoxNjQ5ODQ3Mzg0LCJuYmYiOjE2NDk4NDM3ODQsImp0aSI6Im16NGpKM29QdDJ3TmQ0RmIiLCJzdWIiOjIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.FfGe_7BGgnhsidxSpf8DlWaapFKkFN3fNtXZwCSYOlM'
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Notes Not Found']);
    }

    /**
     * Successful Delete Note By ID Test
     * @test
     */
    public function successfulDeleteNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteNoteById', [
                "id" => "10",
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY0OTg0Mzc4NCwiZXhwIjoxNjQ5ODQ3Mzg0LCJuYmYiOjE2NDk4NDM3ODQsImp0aSI6Im16NGpKM29QdDJ3TmQ0RmIiLCJzdWIiOjIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.FfGe_7BGgnhsidxSpf8DlWaapFKkFN3fNtXZwCSYOlM'
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note Deleted Successfully']);
    }

    /**
     * UnSuccessful Delete Note By ID Test
     * @test
     */
    public function unSuccessfulDeleteNoteByIdTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteNoteById', [
                "id" => "55",
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY0OTg0Mzc4NCwiZXhwIjoxNjQ5ODQ3Mzg0LCJuYmYiOjE2NDk4NDM3ODQsImp0aSI6Im16NGpKM29QdDJ3TmQ0RmIiLCJzdWIiOjIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.FfGe_7BGgnhsidxSpf8DlWaapFKkFN3fNtXZwCSYOlM'
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Notes Not Found']);
    }
}
