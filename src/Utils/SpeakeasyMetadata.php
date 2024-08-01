<?php

/**
 * Code generated by Speakeasy (https://speakeasyapi.com). DO NOT EDIT.
 */

declare(strict_types=1);

namespace Bannerify\Bannerify\Utils;

use Attribute;
use ReflectionAttribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class SpeakeasyMetadata
{
    public function __construct(
        public string $value,
    ) {
    }

    /**
     * @param  array<ReflectionAttribute<SpeakeasyMetadata>>  $attributes
     * @return ?string
     */
    public static function find(array $attributes, string $type): ?string
    {
        foreach ($attributes as $attr) {
            $arguments = $attr->getArguments();
            if (count($arguments) !== 1) {
                return null;
            }

            $prefix = explode(':', $arguments[0], 2);
            if (count($prefix) !== 2) {
                return null;
            }

            if ($prefix[0] === $type) {
                return $arguments[0];
            }
        }

        return null;
    }
}