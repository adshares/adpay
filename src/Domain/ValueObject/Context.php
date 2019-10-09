<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

class Context
{
    /* @var array */
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    public function get(string ...$keys)
    {
        $data = $this->data;
        foreach ($keys as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return null;
            }
            $data = $data[$key];
        }

        return $data;
    }
}
