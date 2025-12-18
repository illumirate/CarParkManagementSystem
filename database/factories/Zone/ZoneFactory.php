<?php
  /**
  * Author: Adam Chin Wai Kin
  */
namespace Database\Factories\Zone;

use InvalidArgumentException;

class ZoneFactory
{
    public static function make(string $type): ZoneFactoryInterface
    {
        return match ($type) {
            'single' => new SingleLevelZoneFactory(),
            'multi'  => new MultiLevelZoneFactory(),
            default  => throw new InvalidArgumentException('Invalid zone type'),
        };
    }
}
