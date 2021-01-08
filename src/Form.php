<?php

namespace AbdallaMohammed\Form;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Session\Store as Session;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;

class Form implements Responsable, Arrayable
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var string
     */
    protected $view = '';

    /**
     * @var string
     */
    protected $data = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Collection
     */
    protected $before;

    /**
     * @var Collection
     */
    protected $after;

    /**
     * @var Collection
     */
    protected $steps;

    /**
     * Form constructor.
     *
     * @param Request $request
     * @param Session $session
     * @param array $data
     */
    public function __construct(Request $request, Session $session)
    {
        $this->namespace = config('laravel-multistep-forms.session_name', 'multistep-forms');

        $this->request = $request;
        $this->session = $session;

        $this->before = new Collection();
        $this->after = new Collection();
        $this->steps = new Collection();
    }

    /**
     * Make Form Instance.
     *
     * @param Closure $callback
     * @return Form|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function make(Closure $callback)
    {
        return $this->tap($callback);
    }

    /**
     * Add step to form.
     *
     * @return Step
     */
    public function step(): Step
    {
        $this->steps->put($this->steps->count() + 1, $step = new Step($this->steps->count() + 1, $this->request));

        return $step;
    }

    /**
     * Add before step callback.
     *
     * @param int|string $step
     * @param Closure $closure
     * @return $this
     */
    public function before($step, Closure $closure): self
    {
        $this->before->put($this->getStepId($step), $closure);

        return $this;
    }

    /**
     * Add after step callback.
     *
     * @param int|string $step
     * @param Closure $closure
     * @return $this
     */
    public function after($step, Closure $closure): self
    {
        $this->after->put($this->getStepId($step), $closure);

        return $this;
    }

    /**
     * Set the session namespace.
     *
     * @param string $namespace
     * @return $this
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Set the method namespace.
     *
     * @param string $method
     * @return $this
     */
    public function method(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get current step.
     *
     * @return int
     */
    public function currentStep(): int
    {
        return (int) $this->session->get("{$this->namespace}.step", 1);
    }

    /**
     * Get requested step.
     *
     * @return int
     */
    public function requestedStep(): int
    {
        return (int) $this->request->get('step', 1);
    }

    /**
     * Determine the current step.
     *
     * @param int $step
     * @return bool
     */
    public function isStep(int $step = 1): bool
    {
        return $this->currentStep() === $step;
    }

    /**
     * Get last step number.
     *
     * @return int
     */
    public function lastStep(): int
    {
        return $this->steps->keys()->filter(function ($value) {
                return is_int($value);
            })->max() ?? 1;
    }

    /**
     * Increment the current step to the next.'.
     *
     * @return $this
     */
    protected function nextStep(): self
    {
        if (! $this->isStep($this->lastStep())) {
            $this->setValue('step', $this->requestedStep() + 1);
        }

        return $this;
    }

    /**
     * Get session value.
     *
     * @param string $key
     * @param mixed|null $fallback
     * @return mixed
     */
    public function getValue(string $key, $fallback = null)
    {
        return $this->session->get("{$this->namespace}.$key", $this->session->getOldInput($key, $fallback));
    }

    /**
     * Set session value.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setValue(string $key, $value): self
    {
        $this->session->put("{$this->namespace}.$key", $value);

        return $this;
    }

    /**
     * Is the current step the last?
     *
     * @return bool
     */
    public function isLastStep(): bool
    {
        return $this->isStep($this->lastStep());
    }

    /**
     * @param int $step
     * @param mixed|null $active
     * @param mixed|null $fallback
     * @return mixed
     */
    public function isActive(int $step, $active = true, $fallback = false)
    {
        if ($this->isStep($step)) {
            return $active;
        }

        return $fallback;
    }

    /**
     * @param int $step
     * @param mixed $active
     * @param mixed $fallback
     * @return mixed
     */
    public function isPrev(int $step, $active = true, $fallback = false)
    {
        if ($this->steps->has($step) && $this->currentStep() > $step) {
            return $active;
        }

        return $fallback;
    }

    /**
     * @param int $step
     * @param mixed|null $active
     * @param mixed|null $fallback
     * @return mixed
     */
    public function isNext(int $step, $active = true, $fallback = false)
    {
        if ($this->steps->has($step) && $this->currentStep() < $step) {
            return $active;
        }

        return $fallback;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->session->get($this->namespace, []);
    }

    /**
     * Get the instance as an Collection.
     *
     * @return Collection
     */
    public function toCollection(): Collection
    {
        return Collection::make($this->toArray());
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request|null $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request = null)
    {
        $this->request = ($request ?? $this->request);

        return $this->handleRequest();
    }

    /**
     * Get the current step config or by number.
     *
     * @param int|null $step
     * @return Step
     */
    public function stepConfig(?int $step = null): Step
    {
        return $this->steps->get($step ?? $this->currentStep());
    }

    /**
     * @param mixed ...$params
     */
    public function useView(...$params)
    {
        $this->view = func_get_arg(0);
        $this->data = array_merge($this->data, is_array(func_get_arg(1)) ? func_get_arg(1) : []);

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function mergeData(array $data)
    {
        $this->data = array_merge($data, $this->data);

        return $this;
    }

    /**
     * @param mixed $condition
     * @param Closure $callback
     * @return $this
     */
    public function mergeDataWhen($condition, Closure $callback)
    {
        $data = [];
        if ((is_integer($condition) && $this->isStep($this->getStepId($condition)))
            || (is_bool($condition) && $condition === true)
            || (is_callable($condition) && is_bool(value($condition($this))))) {
            $data = value($callback($this));
        }

        $this->data = array_merge($data, $this->data);

        return $this;
    }


    /**
     * Tap into instance (invokable classes).
     *
     * @param Closure|mixed $closure
     * @return $this
     */
    public function tap(Closure $closure): self
    {
        $closure($this);

        return $this;
    }

    /**
     * @param int $stepId
     * @return Step
     */
    public function getStepInstance(int $stepId): Step
    {
        return $this->steps->filter(function ($step) use ($stepId) {
            return $step->getId() == $stepId;
        })->first();
    }

    /**
     * Handle the validated request.
     *
     * @return mixed
     */
    protected function handleRequest()
    {
        $this->setupSession();

        if ($this->request->isMethod($this->method)) {
            if ($response = (
                $this->handleBefore('*') ??
                $this->handleBefore($this->requestedStep())
            )) {
                return $response;
            }

            $this->save($this->validate());

            if ($response = (
                $this->handleAfter('*') ??
                $this->handleAfter($this->currentStep())
            )) {
                return $response;
            }

            $this->nextStep();
        }

        return $this->renderResponse();
    }

    /**
     * Setup the session if it hasn't been started.
     *
     * @return void
     */
    protected function setupSession(): void
    {
        if (! is_numeric($this->getValue('step', false)) && config('laravel-mutlistep-forms.enable_session', true)) {
            $this->setValue('step', 1);
        }
    }

    /**
     * Render the request as a response.
     *
     * @return JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    protected function renderResponse()
    {
        if (! $this->usesViews() || $this->needsJsonResponse()) {
            return new JsonResponse((object) [
                'form' => $this->toArray(),
            ]);
        }

        if (! $this->request->isMethod('GET')) {
            return redirect()->back();
        }

        return View::make($this->view, array_merge([
            'form' => $this,
        ], $this->data));
    }

    /**
     * Request needs JSON response.
     *
     * @return bool
     */
    protected function needsJsonResponse(): bool
    {
        return $this->request->wantsJson() || $this->request->isXmlHttpRequest();
    }

    /**
     * @return bool
     */
    protected function usesViews(): bool
    {
        return ! empty($this->view) && is_string($this->view);
    }

    /**
     * Save the validation data to the session.
     *
     * @param array $data
     * @return $this
     */
    protected function save(array $data = []): self
    {
        if (config('laravel-multistep-forms.enable_session', true)) {
            $this->session->put($this->namespace, array_merge(
                $this->session->get($this->namespace, []),
                $data
            ));
        }

        return $this;
    }

    /**
     * Validate the request.
     *
     * @return array
     */
    protected function validate(): array
    {
        $step = $this->stepConfig($this->requestedStep());

        return $this->request->validate(
            array_merge($step->getRules(), [
                'step' => ['required', 'numeric', Rule::in(range(1, $this->lastStep()))],
            ]),
            $step->getMessages(),
            $step->getAttributes()
        );
    }

    /**
     * Handle "Before" Callback.
     *
     * @param int|string $key
     * @return mixed
     */
    protected function handleBefore($key)
    {
        if ($callback = $this->before->get($key)) {
            return $callback($this, $this->steps->get($key));
        }
    }

    /**
     * Handle "After" Callback.
     *
     * @param int|string $key
     * @return mixed
     */
    protected function handleAfter($key)
    {
        if ($callback = $this->after->get($key)) {
            return $callback($this, $this->steps->get($key));
        }
    }

    /**
     * @param $step
     * @return int
     */
    protected function getStepId($step): int
    {
        return (is_object($step) && $step instanceof Step) ? $step->getId() : $step;
    }
}
