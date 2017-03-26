<?php
namespace dnl_blkv\enum\test;

use PHPUnit\Framework\TestCase;
use BadMethodCallException;
use InvalidArgumentException;

/**
 */
class EnumTest extends TestCase
{
    /**
     * Expected result of converting SimpleEnum::FISH() to string.
     */
    const EXPECTED_RESULT_SIMPLE_ENUM_FISH_AS_STRING = <<<TEXT
{
    "dnl_blkv\\\\enum\\\\test\\\\SimpleEnum": {
        "FISH": 4
    }
}
TEXT;

    /**
     */
    public function testCanCreateEnumWithMagicMethod()
    {
        static::assertEquals(SimpleEnum::class, get_class(SimpleEnum::CAT()));
    }

    /**
     * @expectedException \dnl_blkv\enum\exception\UndefinedEnumNameException
     */
    public function testCanNotCreateEnumWithMagicMethodForUndefinedConstant()
    {
        SimpleEnum::SCAT();
    }

    /**
     * @expectedException \dnl_blkv\enum\exception\UndefinedEnumNameException
     */
    public function testCanNotCreateEnumWithMagicMethodForInternalConstant()
    {
        SimpleEnum::__SOME_INTERNAL_CONSTANT();
    }

    /**
     */
    public function testCanCreateEnumFromName()
    {
        static::assertEquals(SimpleEnum::DOG(), SimpleEnum::createFromName('DOG'));
    }

    /**
     * @expectedException \dnl_blkv\enum\exception\UndefinedEnumNameException
     */
    public function testCanNotCreateEnumFromUndefinedName()
    {
        SimpleEnum::createFromName('SCAT');
    }

    /**
     * @expectedException \dnl_blkv\enum\exception\UndefinedEnumNameException
     */
    public function testCanNotCreateEnumFromNameOfInternalConstant()
    {
        SimpleEnum::createFromName('__SOME_INTERNAL_CONSTANT');
    }

    /**
     */
    public function testCanCreateEnumFromOrdinal()
    {
        static::assertEquals(SimpleEnum::DOG(), SimpleEnum::createFromOrdinal(1));
    }

    /**
     */
    public function testCanCreateEnumFromCustomOrdinal()
    {
        static::assertEquals(SimpleEnum::BIRD(), SimpleEnum::createFromOrdinal(3));
    }

    /**
     * @expectedException \dnl_blkv\enum\exception\UndefinedEnumOrdinalException
     */
    public function testCanNotCreateEnumFromUndefinedOrdinal()
    {
        SimpleEnum::createFromOrdinal(20);
    }

    /**
     * @expectedException \dnl_blkv\enum\exception\UndefinedEnumOrdinalException
     */
    public function testCanNotCreateEnumFromOrdinalOfInternalConstant()
    {
        SimpleEnum::createFromOrdinal(222);
    }

    /**
     */
    public function testCanCheckEnumNameIsDefined()
    {
        self::assertTrue(SimpleEnum::isNameDefined('FISH'));
    }

    /**
     */
    public function testCanCheckEnumNameIsNotDefined()
    {
        self::assertFalse(SimpleEnum::isNameDefined('GISH'));
        self::assertFalse(SimpleEnum::isNameDefined('__SOME_INTERNAL_CONSTANT'));
    }

    /**
     */
    public function testCanGetName()
    {
        static::assertEquals('FISH', SimpleEnum::FISH()->getName());
    }

    /**
     */
    public function testCanCheckEnumOrdinalIsDefined()
    {
        self::assertTrue(SimpleEnum::isOrdinalDefined(1));
    }

    /**
     */
    public function testCanCheckEnumOrdinalIsNotDefined()
    {
        self::assertFalse(SimpleEnum::isOrdinalDefined(5));
        self::assertFalse(SimpleEnum::isOrdinalDefined(222));
    }

    /**
     */
    public function testCanGetOrdinal()
    {
        static::assertEquals(4, SimpleEnum::FISH()->getOrdinal());
    }

    /**
     */
    public function testCanConvertToString()
    {
        static::assertEquals(self::EXPECTED_RESULT_SIMPLE_ENUM_FISH_AS_STRING, strval(SimpleEnum::FISH()));
    }

    /**
     * @expectedException \dnl_blkv\enum\exception\InvalidEnumValueException
     */
    public function testCanNotCreateEnumIfWrongValuePresent()
    {
        EnumWithInvalidValue::VALID_VALUE();
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testCanNotCallNonExistingMethod()
    {
        SimpleEnum::nonExistingMethod();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCanNotCallMagicEnumMethodWithArguments()
    {
        SimpleEnum::FISH(true);
    }
}