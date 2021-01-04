<?php

use AbdallaMohammed\Form\Form;
use Illuminate\Support\Facades\Route;

Route::any('/', function () {
    return app(Form::class)->make(function ($form) {
        $form->step()->rules([
            'name' => ['required', 'string'],
        ]);

        $form->step()->dynamicRules();
    })->setNamespace('test');
})->middleware('web')->name('form');
