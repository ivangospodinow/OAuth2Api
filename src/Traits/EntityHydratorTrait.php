<?php

namespace App\Traits;

trait EntityHydratorTrait
{
    public function hydrateEntity(array $props)
    {
        foreach ($props as $name => $value) {
            if ($name === 'id') {
                $this->id = $value;
            } else {
                $setter = 'set' . ucfirst($name);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
        }
    }
}
