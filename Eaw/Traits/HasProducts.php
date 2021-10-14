<?php

namespace Eaw\Traits;

use Eaw\Models\Product;

trait HasProducts
{
    public function products()
    {
        return $this->client->query($this->getFullPath() . '/products')->setModel(Product::class);
    }

    public function hasProduct(string $name)
    {
        $products = $this->products()->getAll();

        foreach ($products as $product) {
            if ($product->name == $name) {
                return true;
            }
        }

        return false;
    }
}
