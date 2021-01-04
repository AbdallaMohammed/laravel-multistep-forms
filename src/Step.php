<?php

namespace AbdallaMohammed\Form;

use Illuminate\Http\Request;

class Step
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var bool
     */
    protected $dynamicRules = false;

    /**
     * Step constructor.
     *
     * @param int $id
     * @param Request $request
     */
    public function __construct(int $id, Request $request)
    {
        $this->id = $id;
        $this->request = $request;
    }

    /**
     * @param array $rules
     * @return $this
     */
    public function rules(array $rules = []): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @param array $messages
     * @return $this
     */
    public function messages(array $messages = []): self
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function attributes(array $attributes = []): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function dynamicRules($value = true): self
    {
        $this->dynamicRules = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDynamicRules(): bool
    {
        return $this->dynamicRules;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        if ($this->isDynamicRules() && ! empty($rules = $this->request->get("{$this->id}.rules"))) {
            return $rules;
        }

        return $this->rules;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        if ($this->isDynamicRules() && ! empty($messages = $this->request->get("{$this->id}.messages"))) {
            return $messages;
        }

        return $this->messages;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        if ($this->isDynamicRules() && ! empty($attributes = $this->request->get("{$this->id}.attributes"))) {
            return $attributes;
        }

        return $this->attributes;
    }
}
