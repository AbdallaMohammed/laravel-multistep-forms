## Laravel Multistep Form


[![Build Status](https://travis-ci.org/AbdallaMohammed/laravel-multistep-forms.svg?branch=master)](https://travis-ci.org/AbdallaMohammed/laravel-multistep-forms)

* [Installation](#installation)
* [Example Usage](#example-usage)
* [Steps Usage](#steps-usage)
    * [Before](#before-step)
    * [After](#after-step)
    * [Dynamic](#dynamic-step)
* [Helper Methods](#helper-methods)

### Installation

```shell script
composer require abdallahmohammed/laravel-multistep-forms
```

### Example Usage

```php
use AbdallaMohammed\Form\Form;
use Illuminate\Support\Facades\Route;

Route::get('form', function () {
    return app(Form::class)->make(function (Form $form) {
        // Create a step instance and define rules, messages and attributes
        $form->step()->rules([
            'name' => ['required', 'string'],
        ])->messages([
            'required' => ':attribute Required',
        ])->attributes([
            'name' => 'Name',
        ]);

        // Add another step with dynamic rules
        $form->step()->dynamicRules();
    });
})->name('form');
```

### Steps Usage

#### Before Step 

Define a callback to fired **before** a step has been validated.

> Return a response from this hook to return early before validation occurs.

`before($step, Closure $closure)`

> $step could be Step instance, or the number of the step.

#### After Step

Define a callback to fired **after** a step has been validated.  Step Number or * for all.

> Return a response from this hook to return early before the form step is incremented.

`after($step, Closure $closure)`

> $step could be Step instance, or the number of the step.

#### Dynamic Step

You can set a step as dynamic, so the step will take it's **rules**, **messages** and **attributes** from the request.

For example

```php
use AbdallaMohammed\Form\Form;
use Illuminate\Support\Facades\Route;

Route::get('form', function () {
    return app(Form::class)->make(function (Form $form) {
        ...
        $form->step()->dynamicRules()->messages([
            'foo' => 'bar',
        ]);
        ...
    });
})->name('form');
```

From the example we have defined the **attributes** without the **rules**, so we must send the rules with the request.
Here it is the example of the request body.

```json
{
  "step": 1,
  "1.rules": {
    "name": ["required", "string"]
  }
}
```

**1.rules** is a reference to first step rules.

> You can change **1** to the number of the dynamic step.

As the previous example you can send **1.messages** and **1.attributes** in the request body.

### Helper Methods

#### `stepConfig(?int $step = null)`

Get the current step config, or a specific step config.

#### `getValue(string $key, $fallback = null)`

Get a field value from the form state (session / old input) or fallback to a default.

#### `setValue(string $key, $value)`

Set a field value from the session form state.

#### `currentStep()`

Get the current saved step number.

#### `requestedStep()`

Get the requested step number.

#### `isStep(int $step = 1)`

Get the current step number.

#### `isLastStep()`

Determine if the current step the last step.

#### `isPast(int $step, $truthy = true, $falsy = false)`

Determine if the specified step is in the past.

#### `isActive(int $step, $truthy = true, $falsy = false)`

Determine if the specified step is active.

#### `isNext(int $step, $truthy = true, $falsy = false)`

Determine if the specified step is in the next.


#### `toCollection`

Get the array representation of the form state as a collection.

#### `toArray`

Get the array representation of the form state.
