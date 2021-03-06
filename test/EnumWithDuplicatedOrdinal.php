<?php

namespace dnl_blkv\enum\test;

use dnl_blkv\enum\AbstractEnum;

/**
 * @method static static CAT()
 * @method static static DOG()
 * @method static static BIRD()
 * @method static static FISH()
 * @method static static DEFAULT()
 */
class EnumWithDuplicatedOrdinal extends AbstractEnum
{
    const CAT = null;
    const DOG = null;
    const BIRD = 3;
    const FISH = null;
    const DEFAULT = 0;
}
