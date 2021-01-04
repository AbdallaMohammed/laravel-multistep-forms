<?php

namespace AbdallaMohammed\Forms\Tests\Unit;

use AbdallaMohammed\Forms\Tests\TestCase;

class JsonTest extends TestCase
{
    /** @test */
    public function test_first_step_errors()
    {
        $this->json('POST', route('form'), [])
            ->assertJsonValidationErrors([
                'name', 'step',
            ]);
    }

    /** @test */
    public function test_dynamic_rules()
    {
        $this->json('POST', route('form'), [
            '2.rules' => [
                'name' => ['required'],
            ],
        ])
        ->assertJsonValidationErrors([
            'name',
        ]);
    }

    /** @test */
    public function test_form_session_data()
    {
        $this->json('POST', route('form'), [
                'step' => 1,
            ])
            ->assertSessionHas('test.step', 1);
    }
}
