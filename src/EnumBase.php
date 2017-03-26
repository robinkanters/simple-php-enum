<?php

namespace dnl_blkv\enum;

use dnl_blkv\enum\exception\InvalidEnumValueException;
use dnl_blkv\enum\exception\UndefinedEnumNameException;
use dnl_blkv\enum\exception\UndefinedEnumOrdinalException;
use Exception;
use InvalidArgumentException;
use BadMethodCallException;
use ReflectionClass;

/**
 * Base class for custom enums. Enum values are defined as constants. The names of the enum values constants MUST NOT
 * start from "__", and their ordinals MUST be either int or null (the latter is for automatic ordinal assignment).
 *
 * @example const CAT = 0;
 */
abstract class EnumBase implements Enum
{
    /**
     * Error constants.
     */
    const __ERROR_METHOD_NOT_FOUND = 'Method not found in "%s": "%s".';
    const __ERROR_ARGUMENTS_NOT_EMPTY = 'Calls to enum instantiation methods must not contain arguments.';
    const __ERROR_ENUM_NAME_UNDEFINED = 'Undefined enum name for "%s": "%s".';
    const __ERROR_ENUM_ORDINAL_UNDEFINED = 'Undefined enum ordinal for "%s": "%d".';
    const __ERROR_ENUM_ORDINAL_NOT_INCREASING = 'Last ordinal value "%s" is greater or equal then current "%s".';

    /**
     * Constants to check whether or not given constant is internal.
     */
    const __CONSTANT_NAME_PREFIX_INTERNAL_CONSTANT = '__';
    const __CONSTANT_NAME_PREFIX_START = 0;
    const __CONSTANT_NAME_PREFIX_LENGTH = 2;

    /**
     * @var int[]
     */
    protected static $nameToOrdinalMapCache = [];

    /**
     * @var string[]
     */
    protected static $ordinalToNameMapCache = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $ordinal;

    /**
     * @param string $name
     */
    protected function __construct(string $name)
    {
        $this->name = $name;
        $this->ordinal = static::getNameToOrdinalMap()[$name];
    }

    /**
     * @return string[]
     */
    protected static function getNameToOrdinalMap(): array
    {
        if (!isset(static::$nameToOrdinalMapCache[static::class])) {
            static::$nameToOrdinalMapCache[static::class] = static::createNameToOrdinalMap();
        }

        return static::$nameToOrdinalMapCache[static::class];
    }

    /**
     * @return int[]
     *
     * @throws Exception When ordinal has invalid format.
     */
    protected static function createNameToOrdinalMap(): array
    {
        $nameToOrdinalMap = [];
        $lastOrdinal = -1;

        foreach (static::createSelfReflection()->getConstants() as $name => $constantValue) {
            if (!static::isInternalConstantName($name)) {
                $currentOrdinal = static::getNextOrdinal($lastOrdinal, $constantValue);
                $nameToOrdinalMap[$name] = $currentOrdinal;
                $lastOrdinal = $currentOrdinal;
            }
        }

        return $nameToOrdinalMap;
    }

    /**
     * @return ReflectionClass
     */
    protected static function createSelfReflection(): ReflectionClass
    {
        return new ReflectionClass(static::class);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected static function isInternalConstantName(string $name): bool
    {
        return static::getConstantNamePrefix($name) === self::__CONSTANT_NAME_PREFIX_INTERNAL_CONSTANT;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected static function getConstantNamePrefix(string $name): string
    {
        return substr(
            $name,
            self::__CONSTANT_NAME_PREFIX_START,
            self::__CONSTANT_NAME_PREFIX_LENGTH
        );
    }

    /**
     * @param int $lastOrdinal
     * @param int|null $constantValue
     *
     * @return int
     * @throws InvalidEnumValueException When the enum constant value is invalid.
     */
    protected static function getNextOrdinal(int $lastOrdinal, int $constantValue = null): int
    {
        if ($lastOrdinal < $constantValue) {
            return $constantValue;
        } elseif (is_null($constantValue)) {
            return $lastOrdinal + 1;
        } else {
            throw new InvalidEnumValueException(
                vsprintf(self::__ERROR_ENUM_ORDINAL_NOT_INCREASING, [$lastOrdinal, $constantValue])
            );
        }
    }

    /**
     * @param int $ordinal
     *
     * @return static
     * @throws UndefinedEnumOrdinalException When the enum ordinal is undefined.
     */
    public static function createFromOrdinal(int $ordinal)
    {
        if (static::isOrdinalDefined($ordinal)) {
            return new static(static::getOrdinalToNameMap()[$ordinal]);
        } else {
            throw new UndefinedEnumOrdinalException(
                vsprintf(self::__ERROR_ENUM_ORDINAL_UNDEFINED, [static::class, $ordinal])
            );
        }
    }

    /**
     * @param int $ordinal
     *
     * @return bool
     */
    public static function isOrdinalDefined(int $ordinal): bool
    {
        return isset(static::getOrdinalToNameMap()[$ordinal]);
    }

    /**
     * @return string[]
     */
    protected static function getOrdinalToNameMap(): array
    {
        if (!isset(static::$ordinalToNameMapCache[static::class])) {
            static::$ordinalToNameMapCache[static::class] = array_flip(static::getNameToOrdinalMap());
        }

        return static::$ordinalToNameMapCache[static::class];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getOrdinal(): int
    {
        return $this->ordinal;
    }

    /**
     * @param string $name
     * @param mixed[] $arguments
     *
     * @return static
     * @throws BadMethodCallException When the method is not found.
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (strtoupper($name) === $name) {
            static::assertArgumentsEmpty($arguments);

            return static::createFromName($name);
        } else {
            throw new BadMethodCallException(vsprintf(self::__ERROR_METHOD_NOT_FOUND, [static::class, $name]));
        }
    }

    /**
     * @param mixed[] $arguments
     *
     * @throws InvalidArgumentException
     */
    protected static function assertArgumentsEmpty(array $arguments)
    {
        if (!empty($arguments)) {
            throw new InvalidArgumentException(self::__ERROR_ARGUMENTS_NOT_EMPTY);
        }
    }

    /**
     * @param string $name
     *
     * @return static
     * @throws UndefinedEnumNameException When the name does not correspond to an existing enum type.
     */
    public static function createFromName(string $name)
    {
        if (static::isNameDefined($name)) {
            return new static($name);
        } else {
            throw new UndefinedEnumNameException(vsprintf(self::__ERROR_ENUM_NAME_UNDEFINED, [static::class, $name]));
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function isNameDefined(string $name): bool
    {
        return isset(static::getNameToOrdinalMap()[$name]);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return json_encode(
            [static::class => [$this->name => $this->ordinal]],
            JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_SLASHES
        );
    }
}