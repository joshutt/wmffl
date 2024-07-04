<?php

namespace WMFFL\enum;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

abstract class EnumType extends Type
{
    protected string $name;
    protected array $values = array();

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $values = array_map(function($val) { return "'".$val."'"; }, $this->values);

        return 'ENUM(' .implode(', ', $values). ')';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (!in_array($value, $this->values)) {
            throw new InvalidArgumentException("Invalid '".$this->name."' value.");
        }
        return $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}