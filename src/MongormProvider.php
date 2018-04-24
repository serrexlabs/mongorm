<?php

namespace Serrexlabs\Mongorm;


use Illuminate\Support\ServiceProvider;
use Serrexlabs\Mongorm\Console\MakeCollection;
use Serrexlabs\Mongorm\Console\MakeEntity;

class MongormProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCollection::class,
                MakeEntity::class,
            ]);
        }
    }


}