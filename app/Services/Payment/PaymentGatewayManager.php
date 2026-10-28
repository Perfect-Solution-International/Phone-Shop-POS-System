<?php

namespace App\Services\Payment;

use App\Services\Payment\Adapters\SandboxPaymentGatewayAdapter;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    protected array $drivers = [];

    /**
     * Get a gateway driver instance.
     */
    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $name = $name ?: config('payment.default_driver', 'sandbox');

        if (! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    /**
     * Create a new driver instance.
     */
    protected function createDriver(string $name): PaymentGatewayInterface
    {
        return match ($name) {
            'sandbox' => new SandboxPaymentGatewayAdapter(),
            default => throw new InvalidArgumentException("Unsupported payment gateway driver [{$name}]."),
        };
    }

    /**
     * Register a custom gateway driver.
     */
    public function extend(string $name, PaymentGatewayInterface $driver): self
    {
        $this->drivers[$name] = $driver;

        return $this;
    }
}

