<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;

abstract class ApiTestCase extends KernelTestCase
{
    use HasBrowser {
        browser as baseKernelBrowse;
    }

    protected function browser(array $options = [], array $server = []): KernelBrowser
    {
        return $this->baseKernelBrowse($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()
                    ->withHeaders(['Accept' => 'application/ld+json'])
            );
    }
}