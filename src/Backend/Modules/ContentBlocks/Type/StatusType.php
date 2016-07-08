<?php

namespace Backend\Modules\ContentBlocks\Type;

use Backend\Modules\ContentBlocks\ValueObject\Status;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class StatusType extends Type
{
    const CONTENT_BLOCKS_STATUS = 'content_blocks_status';

    /**
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'ENUM("' . implode(
            '","',
            Status::getPossibleStatuses()
        ) . '") COMMENT "(DC2Type:' . self::CONTENT_BLOCKS_STATUS . ')"';
    }

    /**
     * @param string $status
     * @param AbstractPlatform $platform
     *
     * @return Status
     */
    public function convertToPHPValue($status, AbstractPlatform $platform)
    {
        return Status::fromString($status);
    }

    /**
     * @param Status $status
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToDatabaseValue($status, AbstractPlatform $platform)
    {
        return (string) $status;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::CONTENT_BLOCKS_STATUS;
    }
}
