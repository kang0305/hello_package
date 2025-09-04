<?php

namespace Kang\SystemLogPackage\DTO;
use ReallifeKip\ImmutableBase\DataTransferObject;
use Reallifekip\ImmutableBase\ImmutableBase;

#[DataTransferObject]
class SystemLogDTO extends ImmutableBase
{
    public readonly string $type;
    public readonly string $level;
    public readonly string $module;
    public readonly string $ref_code;
    public readonly string $message;
    public readonly ?int $user_id;
    public readonly string $ip_address;
    public readonly string $request_path;
    public readonly string $user_agent;
    public readonly ?string $raw_payload;
}
