<?php

return [
    App\E07Bundle\E07Bundle::class => ['all' => true],
    App\E06Bundle\E06Bundle::class => ['all' => true],
    App\E05Bundle\E05Bundle::class => ['all' => true],
    App\E04Bundle\E04Bundle::class => ['all' => true],
    App\E03Bundle\E03Bundle::class => ['all' => true],
    App\E02Bundle\E02Bundle::class => ['all' => true],
    App\E01Bundle\E01Bundle::class => ['all' => true],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
];
