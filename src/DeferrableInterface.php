<?php
namespace PHPDeferrable;

interface DeferrableInterface
{
    public function isDeferrable(): bool;
}